<?php
// views/user/tracks.php

require_once __DIR__ . '/../../app/helpers.php';

auth_require_login();

$user    = current_user_name();
$email   = current_user_email();
$role    = auth_role();
$isAdmin = auth_is_admin();

/**
 * Fake user → ID map for the "progress API"
 */
$knownUserIds = [
    'admin'   => 1,
    'mentor'  => 2,
    'student' => 3,
];

$currentUserId = $knownUserIds[$user] ?? 3;

// ----------------------------
// Fake track definitions
// ----------------------------

$tracks = [
    [
        'id'       => 1,
        'title'    => 'Web Bug Hunter Path',
        'tag'      => 'Web',
        'level'    => 'Intermediate',
        'duration' => '6–10 hours',
        'labs'     => 14,
        'badge'    => 'Bug bounty ready',
        'summary'  => 'From HTTP basics to multi-step web chains with XSS, SQLi, and access control bugs.',
        'modules'  => [
            'HTTP, cookies, and session primitives',
            'Finding input surfaces and parameter mining',
            'Intro to XSS and DOM clobbering',
            'SQL injection in real login flows',
            'Bypassing naive WAF-style filters',
            'Writing stable payloads for modern browsers',
        ],
    ],
    [
        'id'       => 2,
        'title'    => 'Pwn from Zero',
        'tag'      => 'Pwn',
        'level'    => 'Intermediate',
        'duration' => '5–8 hours',
        'labs'     => 11,
        'badge'    => 'Stack smasher',
        'summary'  => 'Memory layout, calling conventions, and your first reliable ROP chain.',
        'modules'  => [
            'Process memory & calling conventions',
            'Stack overflows in practice',
            'Intro to ROP gadgets',
            'Leaking libc addresses',
            'Ret2libc and simple mitigations',
        ],
    ],
    [
        'id'       => 3,
        'title'    => 'Blue Team Forensics Track',
        'tag'      => 'Forensics',
        'level'    => 'Beginner',
        'duration' => '4–6 hours',
        'labs'     => 9,
        'badge'    => 'Blue team curious',
        'summary'  => 'Work through disk, memory, and web logs to reconstruct attacker activity.',
        'modules'  => [
            'Case setup & triage notes',
            'Timeline analysis basics',
            'Memory capture & quick wins',
            'HTTP/HTTPS log artifacts',
            'Reporting findings clearly',
        ],
    ],
    [
        'id'       => 4,
        'title'    => 'Reverse Engineering Essentials',
        'tag'      => 'Reverse',
        'level'    => 'Intermediate',
        'duration' => '5–7 hours',
        'labs'     => 8,
        'badge'    => 'Decompiler enjoyer',
        'summary'  => 'Disassemble, decompile, and reason about native binaries and protections.',
        'modules'  => [
            'x86-64 recap and calling convention',
            'Using Ghidra / IDA effectively',
            'String references & control flow',
            'Basic obfuscation patterns',
            'Patching simple protections',
        ],
    ],
    [
        'id'       => 5,
        'title'    => 'Crypto Warpath',
        'tag'      => 'Crypto',
        'level'    => 'Advanced',
        'duration' => '6–9 hours',
        'labs'     => 10,
        'badge'    => 'Cipher breaker',
        'summary'  => 'Real-world crypto pitfalls: padding oracles, bad randomness, and protocol abuse.',
        'modules'  => [
            'Symmetric vs asymmetric recap',
            'Common implementation bugs',
            'Padding oracle lab',
            'Nonce reuse & stream ciphers',
            'Protocol-level attacks',
        ],
    ],
];

// ----------------------------
// Fake per-user progress (for IDOR)
// ----------------------------

$userProgress = [
    1 => [ // admin
        'user_id'   => 1,
        'username'  => 'admin',
        'role'      => 'admin',
        'tracks'    => [
            1 => ['status' => 'Completed',    'progress' => 100],
            2 => ['status' => 'Completed',    'progress' => 100],
            5 => ['status' => 'In progress',  'progress' => 40],
        ],
        'internal_note' => 'admin:admin123',
    ],
    2 => [
        'user_id'   => 2,
        'username'  => 'mentor',
        'role'      => 'staff',
        'tracks'    => [
            1 => ['status' => 'In progress', 'progress' => 60],
            3 => ['status' => 'Completed',   'progress' => 100],
        ],
        'internal_note' => 'Use this account for demos, not live users.',
    ],
    3 => [
        'user_id'   => 3,
        'username'  => 'student',
        'role'      => 'user',
        'tracks'    => [
            1 => ['status' => 'In progress', 'progress' => 35],
            2 => ['status' => 'Not started', 'progress' => 0],
        ],
        'internal_note' => 'Onboarding cohort Spring.',
    ],
];

$currentUserState = $userProgress[$currentUserId] ?? ['tracks' => []];

// ----------------------------
// Stored notes (XSS)
// ----------------------------

if (!isset($_SESSION['track_notes'])) {
    $_SESSION['track_notes'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track_id'], $_POST['note']) && !isset($_POST['import_progress'])) {
    $tid  = (int)($_POST['track_id']);
    $note = $_POST['note']; // raw, will be echoed later

    $_SESSION['track_notes'][$tid] = $note;
}

// ----------------------------
// XML Import (XXE)
// ----------------------------

$xmlImportMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_progress']) && isset($_FILES['progress_xml'])) {
    $fileTmp    = $_FILES['progress_xml']['tmp_name'] ?? '';
    $xmlContent = $fileTmp ? @file_get_contents($fileTmp) : '';

    if ($xmlContent === '') {
        $xmlImportMessage = 'No XML content uploaded.';
    } else {
        libxml_disable_entity_loader(false);
        $dom = new DOMDocument();

        $ok = @$dom->loadXML($xmlContent, LIBXML_NOENT | LIBXML_DTDLOAD);
        if ($ok) {
            $sx = simplexml_import_dom($dom);
            $importUser   = (string)($sx->user ?? 'unknown');
            $importTracks = (string)($sx->trackCount ?? '0');
            $importNote   = (string)($sx->note ?? '');

            $xmlImportMessage = "Imported progress for '{$importUser}' ({$importTracks} tracks). Note: {$importNote}";
        } else {
            $xmlImportMessage = 'Failed to parse XML. Is it valid?';
        }
    }
}

// ----------------------------
// IDOR JSON export
// ----------------------------

if (isset($_GET['export_user_id']) && $_GET['export_user_id'] !== '') {
    $exportId = (int)$_GET['export_user_id'];
    $payload  = $userProgress[$exportId] ?? null;

    header('Content-Type: application/json; charset=utf-8');

    if ($payload) {
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([
            'error'   => 'No track progress found for that user id.',
            'user_id' => $exportId,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    exit;
}

// ----------------------------
// Filters
// ----------------------------

$activeTag   = $_GET['tag']   ?? 'all';
$activeLevel = $_GET['level'] ?? 'all';

$norm = function ($v) {
    return strtolower(trim($v));
};

$filteredTracks = array_filter($tracks, function ($track) use ($activeTag, $activeLevel, $norm) {
    if ($activeTag !== 'all' && $norm($track['tag']) !== $norm($activeTag)) {
        return false;
    }
    if ($activeLevel !== 'all' && $norm($track['level']) !== $norm($activeLevel)) {
        return false;
    }
    return true;
});

$pageTitle = 'NovaLearn | Tracks';
require __DIR__ . '/../shared/header.php';
$currentPage = 'tracks';
require __DIR__ . '/../shared/navbar.php';
?>

<div class="pointer-events-none fixed inset-0 -z-10 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-70 bg-[radial-gradient(circle_at_top,_#22c55e33_0,_transparent_55%)]"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-60 bg-[radial-gradient(circle_at_bottom,_#6366f133_0,_transparent_60%)]"></div>

<main class="max-w-6xl mx-auto px-5 py-8 space-y-8">

    <!-- Hero + API card -->
    <section class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div class="space-y-3 max-w-xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1 text-[11px] uppercase tracking-wide text-emerald-200">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Opinionated learning tracks</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">
                Tracks for Web, Pwn, Forensics, Reverse &amp; Crypto.
            </h1>
            <p class="text-sm text-slate-300">
                Each track is a curated path of labs and courses. Follow the path, collect flags, and
                get comfortable attacking real systems.
            </p>
        </div>

        <!-- API / IDOR card -->
        <aside class="w-full md:w-72 mt-4 md:mt-0">
            <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4 shadow-lg shadow-emerald-500/10 text-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-slate-100">Progress API</span>
                    <span class="text-[10px] text-slate-400">experimental</span>
                </div>
                <p class="text-[11px] text-slate-400 mb-3">
                    Export your track progress as JSON for dashboards or notes.
                </p>
                <?php
                $exampleExportUrl = 'index.php?page=tracks&export_user_id=' . urlencode($currentUserId);
                ?>
                <div class="font-mono text-[10px] bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 mb-3 overflow-x-auto">
                    GET <?= h($exampleExportUrl) ?>

                </div>
                <a href="<?= h($exampleExportUrl) ?>"
                   class="inline-flex items-center justify-center w-full rounded-xl bg-emerald-500/90 text-slate-950 font-semibold py-1.5 text-[11px] hover:bg-emerald-400 transition">
                    Download my JSON
                </a>
                <p class="mt-2 text-[10px] text-slate-500">
                    Tip: curious attackers might try other
                    <code class="font-mono text-[10px] text-emerald-300">export_user_id</code> values.
                </p>
            </div>
        </aside>
    </section>

    <!-- Filters + XML Import -->
    <section class="space-y-4">
        <!-- Filters row -->
        <div>
            <form method="get" class="flex flex-wrap items-center gap-3 text-xs">
                <input type="hidden" name="page" value="tracks">

                <div class="flex items-center gap-2">
                    <span class="text-slate-400 text-[11px]">Tag</span>
                    <select name="tag" class="bg-slate-900/80 border border-slate-700/80 rounded-full px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-400">
                        <?php
                        $tags = ['all' => 'All', 'Web' => 'Web', 'Pwn' => 'Pwn', 'Forensics' => 'Forensics', 'Reverse' => 'Reverse', 'Crypto' => 'Crypto'];
                        foreach ($tags as $value => $label):
                            $selected = ($activeTag === $value) ? 'selected' : '';
                        ?>
                            <option value="<?= h($value) ?>" <?= $selected ?>>
                                <?= h($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-slate-400 text-[11px]">Level</span>
                    <select name="level" class="bg-slate-900/80 border border-slate-700/80 rounded-full px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-400">
                        <?php
                        $levels = ['all' => 'All', 'Beginner' => 'Beginner', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'];
                        foreach ($levels as $value => $label):
                            $selected = ($activeLevel === $value) ? 'selected' : '';
                        ?>
                            <option value="<?= h($value) ?>" <?= $selected ?>>
                                <?= h($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit"
                        class="inline-flex items-center gap-1 rounded-full border border-slate-700 px-3 py-1.5 text-[11px] font-medium text-slate-100 bg-slate-900/80 hover:bg-slate-800 transition">
                    Apply
                </button>

                <a href="index.php?page=tracks" class="text-[11px] text-slate-400 hover:text-emerald-300">
                    Reset
                </a>

                <span class="text-[11px] text-slate-500">
                    Showing <span class="text-slate-100"><?= count($filteredTracks) ?></span> /
                    <span class="text-slate-100"><?= count($tracks) ?></span>
                </span>
            </form>
        </div>

        <!-- XML Import card (XXE) -->
        <div class="w-full max-w-2xl mx-auto">
            <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4 text-xs shadow-lg shadow-indigo-500/10">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-slate-100">Import Track Progress</span>
                    <span class="text-[10px] text-slate-400">XML / beta</span>
                </div>

                <p class="text-[11px] text-slate-400 mb-3">
                    Upload a <code class="font-mono text-[11px] text-emerald-300">progress.xml</code> exported from another NovaLearn instance
                    to restore your track completion state.
                </p>

                <?php if ($xmlImportMessage): ?>
                    <div class="mb-2 rounded-xl border border-emerald-500/60 bg-emerald-500/10 px-3 py-2">
                        <p class="text-[11px] text-emerald-100 font-medium mb-1">Import result</p>
                        <pre class="text-[10px] text-emerald-100 whitespace-pre-wrap max-h-32 overflow-y-auto">
<?= h($xmlImportMessage) ?>
                        </pre>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" class="space-y-2">
                    <input type="hidden" name="import_progress" value="1">
                    <input type="file" name="progress_xml" accept=".xml,text/xml"
                           class="block w-full text-[11px] text-slate-200
                                  file:mr-3 file:py-1.5 file:px-3 file:rounded-full
                                  file:border-0 file:text-[11px] file:font-medium
                                  file:bg-slate-800 file:text-slate-100 hover:file:bg-slate-700"
                           required>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-800/90 text-[11px] font-medium text-slate-100 border border-slate-700 hover:bg-slate-700 transition px-3 py-1.5">
                        Import XML
                    </button>
                </form>

                <p class="mt-2 text-[10px] text-slate-500">
                    Advanced users sometimes peek inside the XML and experiment with entities… 👀
                </p>
            </div>
        </div>
    </section>

    <!-- Track cards -->
    <section class="grid gap-5">
        <?php foreach ($filteredTracks as $track): ?>
            <?php
            $tid      = $track['id'];
            $state    = $currentUserState['tracks'][$tid] ?? ['status' => 'Not started', 'progress' => 0];
            $progress = (int)$state['progress'];
            $status   = $state['status'];
            $note     = $_SESSION['track_notes'][$tid] ?? '';
            ?>
            <article class="rounded-3xl border border-slate-800/80 bg-slate-900/70 shadow-lg shadow-black/40 overflow-hidden">
                <div class="flex flex-col lg:flex-row">
                    <!-- Left -->
                    <div class="flex-1 p-5 lg:p-6 space-y-3">
                        <div class="flex items-center gap-2 text-[11px] uppercase tracking-wide">
                            <span class="rounded-full bg-slate-800/80 border border-slate-700/80 px-2 py-0.5 text-slate-300">
                                <?= h($track['tag']) ?>
                            </span>
                            <span class="rounded-full bg-slate-900/90 border border-slate-700/80 px-2 py-0.5 text-slate-300">
                                <?= h($track['level']) ?>
                            </span>
                            <span class="rounded-full bg-emerald-500/10 border border-emerald-500/40 px-2 py-0.5 text-emerald-300">
                                <?= h($track['badge']) ?>
                            </span>
                        </div>

                        <h2 class="text-lg md:text-xl font-semibold tracking-tight">
                            <?= h($track['title']) ?>
                        </h2>

                        <p class="text-sm text-slate-300">
                            <?= h($track['summary']) ?>
                        </p>

                        <div class="flex flex-wrap items-center gap-4 text-[11px] text-slate-400">
                            <span><?= h($track['duration']) ?> total</span>
                            <span><?= (int)$track['labs'] ?> labs</span>
                            <span><?= count($track['modules']) ?> modules</span>
                        </div>

                        <div class="mt-3">
                            <p class="text-[11px] text-slate-400 mb-1">You will cover:</p>
                            <ul class="text-xs text-slate-200 space-y-1 list-disc list-inside">
                                <?php
                                $modules = $track['modules'];
                                $preview = array_slice($modules, 0, 3);
                                foreach ($preview as $mod):
                                    ?>
                                    <li><?= h($mod) ?></li>
                                <?php endforeach; ?>
                                <?php if (count($modules) > 3): ?>
                                    <li class="text-slate-400">…and <?= count($modules) - 3 ?> more modules.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Right: progress + note -->
                    <div class="w-full lg:w-80 border-t lg:border-t-0 lg:border-l border-slate-800/80 bg-slate-950/40 p-5 lg:p-6 flex flex-col justify-between gap-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-xs text-slate-300">
                                    Status:
                                    <span class="font-medium text-slate-50"><?= h($status) ?></span>
                                </div>
                                <div class="text-xs text-slate-400">
                                    <?= $progress ?>%
                                </div>
                            </div>

                            <div class="h-2 rounded-full bg-slate-800/80 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-400 to-indigo-500"
                                     style="width: <?= max(0, min(100, $progress)) ?>%;"></div>
                            </div>

                            <?php if ($note !== ''): ?>
                                <div class="mt-2 text-[11px] bg-amber-950/40 border border-amber-500/40 rounded-xl px-3 py-2 text-amber-100">
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="uppercase tracking-wide text-[10px] text-amber-300/90">Your note</span>
                                        <span class="text-[9px] text-amber-400/80">rendered as HTML</span>
                                    </div>
                                    <?php
                                    // 🔥 Stored XSS: raw echo
                                    echo $note;
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <form method="post" class="space-y-2 mt-1">
                            <input type="hidden" name="track_id" value="<?= (int)$tid ?>">
                            <label class="block text-[11px] text-slate-400">
                                Private note for this track
                            </label>
                            <textarea
                                name="note"
                                rows="2"
                                class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 text-xs text-slate-100 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-emerald-400 resize-none"
                                placeholder="e.g. payload ideas, offsets, or &lt;script&gt; experiments…"><?= h($note) ?></textarea>

                            <button type="submit"
                                    class="inline-flex items-center justify-center w-full rounded-xl bg-slate-800/90 text-[11px] font-medium text-slate-100 border border-slate-700 hover:bg-slate-700 transition">
                                Save note
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (count($filteredTracks) === 0): ?>
            <div class="text-sm text-slate-300 border border-slate-800/80 bg-slate-900/70 rounded-2xl px-4 py-3">
                No tracks match your filters yet. Try widening the tag or level.
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>
