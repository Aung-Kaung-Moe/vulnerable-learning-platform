<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user    = $_SESSION['user'];
$email   = $_SESSION['email'] ?? '';
$role    = $_SESSION['role'] ?? 'user';
$isAdmin = ($role === 'admin');

$knownUserIds = [
    'admin'   => 1,
    'mentor'  => 2,
    'student' => 3,
];

$currentUserId = $knownUserIds[$user] ?? 3;

$tracks = [
    [
        'id'        => 1,
        'title'     => 'Web Bug Hunter Path',
        'tag'       => 'Web',
        'level'     => 'Intermediate',
        'duration'  => '6–10 hours',
        'labs'      => 14,
        'badge'     => 'Bug bounty ready',
        'summary'   => 'From HTTP basics to multi-step web chains with XSS, SQLi, and access control bugs.',
        'modules'   => [
            'HTTP, cookies, and session primitives',
            'Finding input surfaces and parameter mining',
            'Intro to XSS and DOM clobbering',
            'SQL injection in real login flows',
            'Bypassing naive WAF-style filters',
            'Writing stable payloads for modern browsers',
        ],
    ],
    [
        'id'        => 2,
        'title'     => 'Pwn from Zero',
        'tag'       => 'Pwn',
        'level'     => 'Intermediate',
        'duration'  => '5–8 hours',
        'labs'      => 11,
        'badge'     => 'Stack smasher',
        'summary'   => 'Memory layout, calling conventions, and your first reliable ROP chain.',
        'modules'   => [
            'Process memory & calling conventions',
            'Stack overflows in practice',
            'Intro to ROP gadgets',
            'Leaking libc addresses',
            'Ret2libc and simple mitigations',
        ],
    ],
    [
        'id'        => 3,
        'title'     => 'Blue Team Forensics Track',
        'tag'       => 'Forensics',
        'level'     => 'Beginner',
        'duration'  => '4–6 hours',
        'labs'      => 9,
        'badge'     => 'Blue team curious',
        'summary'   => 'Work through disk, memory, and web logs to reconstruct attacker activity.',
        'modules'   => [
            'Case setup & triage notes',
            'Timeline analysis basics',
            'Memory capture & quick wins',
            'HTTP/HTTPS log artifacts',
            'Reporting findings clearly',
        ],
    ],
    [
        'id'        => 4,
        'title'     => 'Reverse Engineering Essentials',
        'tag'       => 'Reverse',
        'level'     => 'Intermediate',
        'duration'  => '5–7 hours',
        'labs'      => 8,
        'badge'     => 'Decompiler enjoyer',
        'summary'   => 'Disassemble, decompile, and reason about native binaries and protections.',
        'modules'   => [
            'x86-64 recap and calling convention',
            'Using Ghidra / IDA effectively',
            'String references & control flow',
            'Basic obfuscation patterns',
            'Patching simple protections',
        ],
    ],
    [
        'id'        => 5,
        'title'     => 'Crypto Warpath',
        'tag'       => 'Crypto',
        'level'     => 'Advanced',
        'duration'  => '6–9 hours',
        'labs'      => 10,
        'badge'     => 'Cipher breaker',
        'summary'   => 'Real-world crypto pitfalls: padding oracles, bad randomness, and protocol abuse.',
        'modules'   => [
            'Symmetric vs asymmetric recap',
            'Common implementation bugs',
            'Padding oracle lab',
            'Nonce reuse & stream ciphers',
            'Protocol-level attacks',
        ],
    ],
];

$userProgress = [
    1 => [
        'user_id'   => 1,
        'username'  => 'admin',
        'role'      => 'admin',
        'tracks'    => [
            1 => ['status' => 'Completed',     'progress' => 100],
            2 => ['status' => 'Completed',     'progress' => 100],
            5 => ['status' => 'In progress',   'progress' => 40],
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

if (!isset($_SESSION['track_notes'])) {
    $_SESSION['track_notes'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track_id'], $_POST['note'])) {
    $tid  = (int)($_POST['track_id']);
    $note = $_POST['note'];

    $_SESSION['track_notes'][$tid] = $note;
}

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NovaLearn | Tracks</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<div class="pointer-events-none fixed inset-0 -z-10 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-70 bg-[radial-gradient(circle_at_top,_#22c55e33_0,_transparent_55%)]"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-60 bg-[radial-gradient(circle_at_bottom,_#6366f133_0,_transparent_60%)]"></div>

<header class="border-b border-slate-800/80 bg-slate-950/90 backdrop-blur-xl sticky top-0 z-20">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-3xl bg-gradient-to-br from-emerald-500 to-indigo-500 flex items-center justify-center text-xl shadow-lg shadow-emerald-500/40">
                🎓
            </div>
            <div>
                <div class="font-semibold tracking-tight text-base">NovaLearn</div>
                <div class="text-xs text-slate-400 -mt-0.5">Offensive Security Learning</div>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-7 text-sm text-slate-200">
            <a href="dashboard.php"
               class="relative hover:text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-emerald-400 after:transition-all">
                Dashboard
            </a>
            <a href="courses.php"
               class="relative hover:text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-emerald-400 after:transition-all">
                Courses
            </a>
            <a href="tracks.php"
               class="relative text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-full after:bg-emerald-400">
                Tracks
            </a>
            <a href="#"
               class="relative hover:text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-emerald-400 after:transition-all">
                Labs
            </a>
        </nav>

        <div class="flex items-center gap-3">
            <div class="hidden sm:flex flex-col items-end leading-tight">
                <span class="text-[11px] text-slate-400">Logged in as</span>
                <span class="text-sm text-slate-50 font-medium">
                    <?php echo htmlspecialchars($user, ENT_QUOTES, 'UTF-8'); ?>
                    <?php if ($isAdmin): ?>
                        <span class="ml-1 text-[10px] uppercase tracking-wide text-emerald-400/90 border border-emerald-500/50 px-1.5 py-0.5 rounded-full bg-emerald-500/10">Admin</span>
                    <?php endif; ?>
                </span>
                <?php if ($email): ?>
                    <span class="text-[11px] text-slate-400"><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <button id="nav-toggle" class="md:hidden inline-flex items-center justify-center h-8 w-8 rounded-full border border-slate-700/80 bg-slate-900/80">
                    <span class="sr-only">Toggle navigation</span>
                    ☰
                </button>
                <a href="logout.php"
                   class="hidden md:inline-flex items-center text-xs font-medium px-3 py-1.5 rounded-full border border-slate-700 bg-slate-900/80 hover:bg-slate-800 transition">
                    Logout
                </a>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="md:hidden hidden border-t border-slate-800 bg-slate-950/95 backdrop-blur-xl">
        <div class="max-w-6xl mx-auto px-5 py-3 flex flex-col gap-2 text-sm">
            <a href="dashboard.php" class="py-1 hover:text-emerald-300">Dashboard</a>
            <a href="courses.php" class="py-1 hover:text-emerald-300">Courses</a>
            <a href="tracks.php" class="py-1 text-emerald-300">Tracks</a>
            <a href="#" class="py-1 hover:text-emerald-300">Labs</a>
            <a href="logout.php" class="py-1 text-slate-300 hover:text-emerald-300">Logout</a>
        </div>
    </div>
</header>

<main class="max-w-6xl mx-auto px-5 py-8 space-y-8">

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
                Each track is a curated path of labs and courses. Start where you are, follow the path,
                and get comfortable attacking (and defending) real systems.
            </p>
        </div>

        <aside class="w-full md:w-72 mt-4 md:mt-0">
            <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4 shadow-lg shadow-emerald-500/10 text-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-slate-100">API preview</span>
                    <span class="text-[10px] text-slate-400">experimental</span>
                </div>
                <p class="text-[11px] text-slate-400 mb-3">
                    Export your track progress as JSON for dashboards or notes.
                    This endpoint is intentionally minimal.
                </p>

                <?php
                $exampleExportUrl = 'tracks.php?export_user_id=' . urlencode($currentUserId);
                ?>

                <div class="font-mono text-[10px] bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 mb-3 overflow-x-auto">
                    GET <?php echo htmlspecialchars($exampleExportUrl, ENT_QUOTES, 'UTF-8'); ?>

                </div>

                <a href="<?php echo htmlspecialchars($exampleExportUrl, ENT_QUOTES, 'UTF-8'); ?>"
                   class="inline-flex items-center justify-center w-full rounded-xl bg-emerald-500/90 text-slate-950 font-semibold py-1.5 text-[11px] hover:bg-emerald-400 transition">
                    Download my JSON
                </a>

                <p class="mt-2 text-[10px] text-slate-500">
                    Tip: attackers may try different <code class="font-mono text-[10px] text-emerald-300">export_user_id</code> values.
                </p>
            </div>
        </aside>
    </section>

    <section class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="get" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="text-slate-400 text-[11px]">Tag</span>
                <select name="tag" class="bg-slate-900/80 border border-slate-700/80 rounded-full px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-400">
                    <?php
                    $tags = ['all' => 'All', 'Web' => 'Web', 'Pwn' => 'Pwn', 'Forensics' => 'Forensics', 'Reverse' => 'Reverse', 'Crypto' => 'Crypto'];
                    foreach ($tags as $value => $label):
                        $selected = ($activeTag === $value) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
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
                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-1 rounded-full border border-slate-700 px-3 py-1.5 text-[11px] font-medium text-slate-100 bg-slate-900/80 hover:bg-slate-800 transition">
                Apply
            </button>

            <a href="tracks.php" class="text-[11px] text-slate-400 hover:text-emerald-300">
                Reset
            </a>
        </form>

        <div class="text-[11px] text-slate-400">
            Viewing <span class="text-slate-100 font-medium"><?php echo count($filteredTracks); ?></span> of
            <span class="text-slate-100 font-medium"><?php echo count($tracks); ?></span> tracks.
        </div>
    </section>

    <section class="grid gap-5">
        <?php foreach ($filteredTracks as $track): ?>
            <?php
            $tid = $track['id'];
            $state = $currentUserState['tracks'][$tid] ?? ['status' => 'Not started', 'progress' => 0];
            $progress = (int)$state['progress'];
            $status   = $state['status'];
            $note     = $_SESSION['track_notes'][$tid] ?? '';
            ?>
            <article class="rounded-3xl border border-slate-800/80 bg-slate-900/70 shadow-lg shadow-black/40 overflow-hidden">
                <div class="flex flex-col lg:flex-row">
                    <div class="flex-1 p-5 lg:p-6 space-y-3">
                        <div class="flex items-center gap-2 text-[11px] uppercase tracking-wide">
                            <span class="rounded-full bg-slate-800/80 border border-slate-700/80 px-2 py-0.5 text-slate-300">
                                <?php echo htmlspecialchars($track['tag'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="rounded-full bg-slate-900/90 border border-slate-700/80 px-2 py-0.5 text-slate-300">
                                <?php echo htmlspecialchars($track['level'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="rounded-full bg-emerald-500/10 border border-emerald-500/40 px-2 py-0.5 text-emerald-300">
                                <?php echo htmlspecialchars($track['badge'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <h2 class="text-lg md:text-xl font-semibold tracking-tight">
                            <?php echo htmlspecialchars($track['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </h2>

                        <p class="text-sm text-slate-300">
                            <?php echo htmlspecialchars($track['summary'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>

                        <div class="flex flex-wrap items-center gap-4 text-[11px] text-slate-400">
                            <span><?php echo htmlspecialchars($track['duration'], ENT_QUOTES, 'UTF-8'); ?> total</span>
                            <span><?php echo (int)$track['labs']; ?> labs</span>
                            <span><?php echo count($track['modules']); ?> modules</span>
                        </div>

                        <div class="mt-3">
                            <p class="text-[11px] text-slate-400 mb-1">You will cover:</p>
                            <ul class="text-xs text-slate-200 space-y-1 list-disc list-inside">
                                <?php
                                $modules = $track['modules'];
                                $preview = array_slice($modules, 0, 3);
                                foreach ($preview as $mod):
                                ?>
                                    <li><?php echo htmlspecialchars($mod, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                                <?php if (count($modules) > 3): ?>
                                    <li class="text-slate-400">…and <?php echo count($modules) - 3; ?> more modules.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="w-full lg:w-80 border-t lg:border-t-0 lg:border-l border-slate-800/80 bg-slate-950/40 p-5 lg:p-6 flex flex-col justify-between gap-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-xs text-slate-300">
                                    Status:
                                    <span class="font-medium text-slate-50"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="text-xs text-slate-400">
                                    <?php echo $progress; ?>%
                                </div>
                            </div>

                            <div class="h-2 rounded-full bg-slate-800/80 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-400 to-indigo-500" style="width: <?php echo max(0, min(100, $progress)); ?>%;"></div>
                            </div>

                            <?php if ($note !== ''): ?>
                                <div class="mt-2 text-[11px] bg-amber-950/40 border border-amber-500/40 rounded-xl px-3 py-2 text-amber-100">
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="uppercase tracking-wide text-[10px] text-amber-300/90">Your note</span>
                                        <span class="text-[9px] text-amber-400/80">rendered as HTML</span>
                                    </div>
                                    <?php
                                    echo $note;
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <form method="post" class="space-y-2 mt-1">
                            <input type="hidden" name="track_id" value="<?php echo (int)$tid; ?>">
                            <label class="block text-[11px] text-slate-400">
                                Private note for this track
                            </label>
                            <textarea
                                name="note"
                                rows="2"
                                class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 text-xs text-slate-100 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-emerald-400 resize-none"
                                placeholder="e.g. payload ideas, offsets, or &lt;script&gt; experiments…"><?php
                                echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8');
                                ?></textarea>

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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('nav-toggle');
    const menu   = document.getElementById('mobile-menu');

    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            menu.classList.toggle('hidden');
        });
    }
});
</script>
</body>
</html>