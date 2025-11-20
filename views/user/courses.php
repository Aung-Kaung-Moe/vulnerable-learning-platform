<?php
// views/user/courses.php

require_once __DIR__ . '/../../app/helpers.php';

auth_require_login();

$user  = current_user_name();
$email = current_user_email();
$role  = auth_role();

$courses = [
    [
        'id'        => 1,
        'title'     => 'Intro to Web Exploitation',
        'tag'       => 'Web',
        'level'     => 'Beginner',
        'duration'  => '3–4 hours',
        'labs'      => 6,
        'badge'     => 'Foundations',
        'desc'      => 'Understand HTTP, cookies, sessions and how basic logic flaws turn into real vulnerabilities.',
        'status'    => 'In Progress',
        'progress'  => 35
    ],
    [
        'id'        => 2,
        'title'     => 'Modern SQL Injection Deep Dive',
        'tag'       => 'Web',
        'level'     => 'Intermediate',
        'duration'  => '4–6 hours',
        'labs'      => 9,
        'badge'     => 'Lab-heavy',
        'desc'      => 'Union-based, error-based, and blind SQL injection with practical extraction techniques.',
        'status'    => 'Not Started',
        'progress'  => 0
    ],
    [
        'id'        => 3,
        'title'     => 'XSS: Reflected, Stored & DOM',
        'tag'       => 'Web',
        'level'     => 'Intermediate',
        'duration'  => '3–5 hours',
        'labs'      => 8,
        'badge'     => 'Browser',
        'desc'      => 'Exploit client-side execution, bypass naive filters, and pivot to session takeover.',
        'status'    => 'In Progress',
        'progress'  => 60
    ],
    [
        'id'        => 4,
        'title'     => 'Binary Exploitation Basics',
        'tag'       => 'Pwn',
        'level'     => 'Intermediate',
        'duration'  => '5–7 hours',
        'labs'      => 7,
        'badge'     => 'Low-level',
        'desc'      => 'Stack overflows, memory layout, calling conventions and your first ROP chain.',
        'status'    => 'Not Started',
        'progress'  => 0
    ],
    [
        'id'        => 5,
        'title'     => 'Reversing Native Binaries 101',
        'tag'       => 'Reverse',
        'level'     => 'Intermediate',
        'duration'  => '4–6 hours',
        'labs'      => 6,
        'badge'     => 'Reversing',
        'desc'      => 'Use tools like Ghidra-style flows to understand stripped binaries and logic.',
        'status'    => 'Not Started',
        'progress'  => 0
    ],
    [
        'id'        => 6,
        'title'     => 'Crypto for Hackers',
        'tag'       => 'Crypto',
        'level'     => 'Intermediate',
        'duration'  => '4–5 hours',
        'labs'      => 5,
        'badge'     => 'Math-light',
        'desc'      => 'Stream ciphers, padding oracles and common crypto mistakes seen in CTF challenges.',
        'status'    => 'Not Started',
        'progress'  => 0
    ],
    [
        'id'        => 7,
        'title'     => 'Web Forensics & Log Analysis',
        'tag'       => 'Forensics',
        'level'     => 'Beginner',
        'duration'  => '2–3 hours',
        'labs'      => 4,
        'badge'     => 'Blue-team',
        'desc'      => 'Rebuild timelines from web server logs, artifacts and suspicious payloads.',
        'status'    => 'Completed',
        'progress'  => 100
    ],
    [
        'id'        => 8,
        'title'     => 'Advanced Payload Crafting',
        'tag'       => 'Web',
        'level'     => 'Advanced',
        'duration'  => '6–8 hours',
        'labs'      => 10,
        'badge'     => 'Chaining',
        'desc'      => 'Chain multiple bugs, abuse desyncs and build stable, reusable payloads.',
        'status'    => 'Locked',
        'progress'  => 0
    ],
];

$activeTag   = $_GET['tag']   ?? 'all';
$activeLevel = $_GET['level'] ?? 'all';
$search      = trim($_GET['search'] ?? '');

$norm = function ($v) {
    return strtolower(trim($v));
};

$filtered = array_filter($courses, function ($course) use ($activeTag, $activeLevel, $search, $norm) {
    if ($activeTag !== 'all' && $norm($course['tag']) !== $norm($activeTag)) {
        return false;
    }
    if ($activeLevel !== 'all' && $norm($course['level']) !== $norm($activeLevel)) {
        return false;
    }
    if ($search !== '') {
        $s = $norm($search);
        if (
            strpos($norm($course['title']), $s) === false &&
            strpos($norm($course['desc']),  $s) === false
        ) {
            return false;
        }
    }
    return true;
});

// LFI bit (intentionally dumb)
$lfiParam   = $search;
$lfiContent = '';
if ($lfiParam !== '') {
    $target = $lfiParam;
    if (is_file($target)) {
        $lfiContent = @file_get_contents($target);
    }
}

$pageTitle = 'NovaLearn | Courses';
require __DIR__ . '/../shared/header.php';
$currentPage = 'courses';
require __DIR__ . '/../shared/navbar.php';
?>

<div class="pointer-events-none fixed inset-0 -z-10 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-60 blur-3xl bg-[radial-gradient(circle_at_top,_#22c55e_0,_transparent_45%),_radial-gradient(circle_at_bottom,_#6366f1_0,_transparent_55%)]"></div>

<main class="max-w-6xl mx-auto px-5 py-8 space-y-8">
    <section class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold text-emerald-300 uppercase tracking-[0.18em] mb-2">
                Course catalog
            </p>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-50 mb-2">
                Browse all NovaLearn courses
            </h1>
            <p class="text-sm text-slate-300 max-w-xl">
                Focused on web, pwn, forensics, reverse and crypto. Each course comes with hands-on labs designed to feel like real CTF challenges.
            </p>
        </div>
        <div class="text-xs text-slate-400">
            <p>Total courses: <span class="text-slate-200 font-medium"><?= count($courses) ?></span></p>
            <p>Filtered: <span class="text-emerald-300 font-medium"><?= count($filtered) ?></span></p>
        </div>
    </section>

    <section class="grid md:grid-cols-[260px,1fr] gap-6 items-start">
        <aside class="bg-slate-900/95 border border-slate-800/80 rounded-3xl p-5 space-y-5">
            <div>
                <h2 class="text-sm font-semibold text-slate-50 mb-1">Filters</h2>
                <p class="text-xs text-slate-400">
                    Narrow down by category, level and keywords.
                </p>
            </div>

            <div class="space-y-2">
                <p class="text-xs font-semibold text-slate-300">Category</p>
                <div class="flex flex-wrap gap-2">
                    <?php
                    $tags = [
                        'all'       => 'All',
                        'Web'       => 'Web',
                        'Pwn'       => 'Pwn',
                        'Reverse'   => 'Reverse',
                        'Forensics' => 'Forensics',
                        'Crypto'    => 'Crypto',
                    ];
                    foreach ($tags as $val => $label):
                        $selected = (strtolower($activeTag) === strtolower($val));
                        $paramTag = $val === 'all' ? 'all' : $val;
                        $url = 'index.php?page=courses'
                            . '&tag=' . urlencode($paramTag)
                            . '&level=' . urlencode($activeLevel)
                            . '&search=' . urlencode($search);
                    ?>
                        <a
                            href="<?= h($url) ?>"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] border
                                   <?= $selected
                                       ? 'bg-emerald-500 text-slate-950 border-emerald-400'
                                       : 'bg-slate-950/80 text-slate-200 border-slate-700 hover:border-emerald-400/70'; ?>"
                        >
                            <?= h($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="space-y-2">
                <p class="text-xs font-semibold text-slate-300">Level</p>
                <div class="flex flex-wrap gap-2">
                    <?php
                    $levels = [
                        'all'          => 'All',
                        'Beginner'     => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced'     => 'Advanced',
                    ];
                    foreach ($levels as $val => $label):
                        $selected = (strtolower($activeLevel) === strtolower($val));
                        $url = 'index.php?page=courses'
                            . '&tag=' . urlencode($activeTag)
                            . '&level=' . urlencode($val)
                            . '&search=' . urlencode($search);
                    ?>
                        <a
                            href="<?= h($url) ?>"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] border
                                   <?= $selected
                                       ? 'bg-indigo-500 text-slate-50 border-indigo-400'
                                       : 'bg-slate-950/80 text-slate-200 border-slate-700 hover:border-indigo-400/70'; ?>"
                        >
                            <?= h($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <form method="GET" class="space-y-2">
                <p class="text-xs font-semibold text-slate-300">Search</p>
                <input type="hidden" name="page" value="courses">
                <input
                    type="hidden"
                    name="tag"
                    value="<?= h($activeTag) ?>"
                >
                <input
                    type="hidden"
                    name="level"
                    value="<?= h($activeLevel) ?>"
                >
                <input
                    type="text"
                    name="search"
                    value="<?= h($search) ?>"
                    class="w-full px-3 py-2 rounded-2xl bg-slate-950/90 border border-slate-700/80 text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 placeholder:text-slate-500"
                    placeholder="Search by title or description"
                >
                <button
                    type="submit"
                    class="w-full mt-1 px-3 py-2 rounded-2xl bg-emerald-500 text-slate-950 text-xs font-medium hover:bg-emerald-400"
                >
                    Apply
                </button>
            </form>

            <?php if ($search !== ''): ?>
                <div class="mt-3 text-[11px] rounded-2xl border border-emerald-500/60 bg-slate-950/90 px-3 py-2">
                    <span class="text-slate-300">You searched for:</span>
                    <span class="font-mono text-emerald-300 ml-1">
                        <?= $search // intentionally unescaped for XSS practice ?>
                    </span>
                </div>
                <p class="mt-1 text-[10px] text-slate-500">
                    This reflection is intentionally not escaped. Use it to test reflected XSS payloads.
                </p>
            <?php endif; ?>

            <div class="pt-2 border-t border-slate-800 mt-3 text-[11px] text-slate-500">
                In a real app this catalog would sync with your actual progress.
            </div>
        </aside>

        <section class="space-y-4">
            <?php if (empty($filtered)): ?>
                <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 text-sm text-slate-300">
                    No courses match your filters. Try clearing some filters or using a broader search term.
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($filtered as $course): ?>
                        <article class="bg-slate-900/95 border border-slate-800/80 rounded-3xl p-5 flex flex-col justify-between shadow-lg shadow-black/40 hover:shadow-emerald-500/30 hover:border-emerald-500/60 hover:-translate-y-1 transition-all">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] bg-slate-800 text-slate-200">
                                            <?= h($course['tag']) ?>
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] border border-slate-700 text-slate-300 uppercase tracking-[0.16em]">
                                            <?= h($course['level']) ?>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-emerald-300 font-medium">
                                        <?= h($course['badge']) ?>
                                    </span>
                                </div>
                                <h2 class="text-base font-semibold text-slate-50">
                                    <?= h($course['title']) ?>
                                </h2>
                                <p class="text-sm text-slate-300 leading-relaxed">
                                    <?= h($course['desc']) ?>
                                </p>
                            </div>

                            <div class="mt-4 space-y-2">
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span>Duration: <?= h($course['duration']) ?></span>
                                    <span>Labs: <?= (int)$course['labs'] ?></span>
                                </div>

                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="<?php
                                        if ($course['status'] === 'Completed') {
                                            echo 'text-emerald-300';
                                        } elseif ($course['status'] === 'In Progress') {
                                            echo 'text-sky-300';
                                        } elseif ($course['status'] === 'Locked') {
                                            echo 'text-rose-300';
                                        } else {
                                            echo 'text-slate-400';
                                        }
                                    ?>">
                                        <?= h($course['status']) ?>
                                    </span>
                                    <?php if ($course['progress'] > 0): ?>
                                        <span class="text-slate-300">
                                            <?= (int)$course['progress'] ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($course['progress'] > 0): ?>
                                    <div class="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
                                        <div class="h-full bg-emerald-500" style="width: <?= (int)$course['progress'] ?>%"></div>
                                    </div>
                                <?php elseif ($course['status'] === 'Locked'): ?>
                                    <div class="h-1.5 w-full rounded-full bg-slate-900 border border-rose-500/40 flex items-center justify-center text-[9px] text-rose-300/80">
                                        <span class="px-2">Complete prerequisite courses to unlock</span>
                                    </div>
                                <?php endif; ?>

                                <div class="pt-1 flex items-center justify-between text-[11px]">
                                    <button
                                        class="px-3 py-1.5 rounded-xl text-xs font-medium
                                        <?php
                                            if ($course['status'] === 'Completed') {
                                                echo 'bg-slate-800 text-slate-100 hover:bg-slate-700';
                                            } elseif ($course['status'] === 'Locked') {
                                                echo 'bg-slate-900 text-slate-500 border border-slate-700 cursor-not-allowed';
                                            } elseif ($course['progress'] > 0) {
                                                echo 'bg-emerald-500 text-slate-950 hover:bg-emerald-400';
                                            } else {
                                                echo 'bg-indigo-500 text-slate-50 hover:bg-indigo-400';
                                            }
                                        ?>"
                                        <?= $course['status'] === 'Locked' ? 'disabled' : '' ?>
                                    >
                                        <?php
                                            if ($course['status'] === 'Completed') {
                                                echo 'Review course';
                                            } elseif ($course['progress'] > 0) {
                                                echo 'Resume';
                                            } elseif ($course['status'] === 'Locked') {
                                                echo 'Locked';
                                            } else {
                                                echo 'Start course';
                                            }
                                        ?>
                                    </button>

                                    <span class="text-slate-500">
                                        ID: <?= (int)$course['id'] ?>
                                    </span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($lfiContent !== ''): ?>
                <section class="mt-6">
                    <div class="bg-slate-900/95 border border-rose-500/60 rounded-3xl p-5">
                        <h2 class="text-sm font-semibold text-rose-300 mb-2">
                            LFI debug output
                        </h2>
                        <p class="text-[11px] text-slate-400 mb-3">
                            The search parameter is being used as a file path. Try values like <code>login.php</code> or <code>../index.php</code>.
                        </p>
                        <pre class="whitespace-pre-wrap break-all text-[11px] leading-snug text-slate-100 bg-slate-950/80 border border-slate-800 rounded-2xl p-3 overflow-auto max-h-64">
<?= h($lfiContent) ?>
                        </pre>
                    </div>
                </section>
            <?php endif; ?>
        </section>
    </section>
</main>

<?php require __DIR__ . '/../shared/footer.php'; ?>
