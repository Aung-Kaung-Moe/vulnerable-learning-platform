<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user   = $_SESSION['user'];
$email  = $_SESSION['email'] ?? '';
$role   = $_SESSION['role'] ?? 'user';

// Fake stats / progress (hard-coded for now)
$stats = [
    'total_labs'      => 7,
    'web_labs'        => 3,
    'pwn_labs'        => 2,
    'crypto_labs'     => 1,
    'forensics_labs'  => 1,
    'streak_days'     => 3,
];

$last_lab = [
    'title'       => 'Modern SQL Injection Deep Dive',
    'progress'    => '40%',
    'difficulty'  => 'Intermediate',
    'category'    => 'Web / Database',
];

$next_reco = [
    'title'      => 'XSS: Reflected, Stored & DOM',
    'tag'        => 'Web',
    'level'      => 'Intermediate',
    'summary'    => 'Move from SQLi into browser-based exploitation and session hijacking.',
];

// Fake course data (for search + grid)
$courses = [
    [
        'title' => 'Intro to Web Exploitation',
        'level' => 'Beginner',
        'tag'   => 'Web',
        'desc'  => 'Learn how HTTP, cookies, and sessions can be abused in real-world apps.'
    ],
    [
        'title' => 'Modern SQL Injection Deep Dive',
        'level' => 'Intermediate',
        'tag'   => 'Database',
        'desc'  => 'Union-based, error-based, blind SQLi and practical extraction tactics.'
    ],
    [
        'title' => 'XSS: Reflected, Stored & DOM',
        'level' => 'Intermediate',
        'tag'   => 'Web',
        'desc'  => 'Weaponize JavaScript execution and bypass common filters.'
    ],
    [
        'title' => 'CTF Workflow & Tooling',
        'level' => 'Beginner',
        'tag'   => 'General',
        'desc'  => 'Structuring your approach for Jeopardy-style competitions.'
    ],
    [
        'title' => 'Binary Exploitation Basics',
        'level' => 'Intermediate',
        'tag'   => 'Pwn',
        'desc'  => 'Stack overflows, memory layout, and basic ROP.'
    ],
    [
        'title' => 'Crypto for Hackers',
        'level' => 'Intermediate',
        'tag'   => 'Crypto',
        'desc'  => 'Practical crypto failures seen in CTFs and real apps.'
    ],
    [
        'title' => 'Web Forensics & Log Analysis',
        'level' => 'Beginner',
        'tag'   => 'Forensics',
        'desc'  => 'Trace attacks, artifacts, and anomalies in web logs.'
    ],
    [
        'title' => 'Advanced Payload Crafting',
        'level' => 'Advanced',
        'tag'   => 'Web',
        'desc'  => 'Chaining bugs, evading filters, and building stable payloads.'
    ],
];

$query   = $_GET['q'] ?? '';
$results = [];

if ($query !== '') {
    foreach ($courses as $course) {
        if (
            stripos($course['title'], $query) !== false ||
            stripos($course['desc'],  $query) !== false ||
            stripos($course['tag'],   $query) !== false
        ) {
            $results[] = $course;
        }
    }
    // 🔥 Deliberately not escaping $query when reflecting it below (XSS lab)
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NovaLearn | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<!-- Background glow -->
<div class="pointer-events-none fixed inset-0 -z-10 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-60 blur-3xl bg-[radial-gradient(circle_at_top,_#22c55e_0,_transparent_45%),_radial-gradient(circle_at_bottom,_#6366f1_0,_transparent_55%)]"></div>

<!-- NAVBAR -->
<header class="border-b border-slate-800/80 bg-slate-950/90 backdrop-blur-xl sticky top-0 z-20">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between gap-4">
        <!-- Left: logo -->
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-3xl bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center text-xl shadow-lg shadow-emerald-500/40">
                📚
            </div>
            <div>
                <div class="font-semibold tracking-tight text-base">NovaLearn</div>
                <div class="text-xs text-slate-400 -mt-0.5">Hands-on Security Learning</div>
            </div>
        </div>

        <!-- Center: desktop nav -->
        <nav class="hidden md:flex items-center gap-7 text-sm text-slate-200">
            <a href="dashboard.php" class="relative text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-full after:bg-emerald-400">
                Dashboard
            </a>
            <a href="courses.php" class="relative hover:text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-emerald-400 after:transition-all">
                Courses
            </a>
            <a href="#" class="relative hover:text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-emerald-400 after:transition-all">
                Tracks
            </a>
            <a href="#" class="relative hover:text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-emerald-400 after:transition-all">
                Labs
            </a>
        </nav>

        <!-- Right: user info + logout / hamburger -->
        <div class="flex items-center gap-3">
            <!-- desktop user info -->
            <div class="hidden sm:flex flex-col items-end leading-tight">
                <span class="text-[11px] text-slate-400">Logged in as</span>
                <span class="text-sm text-slate-50 font-medium">
                    <?php echo htmlspecialchars($user); ?>
                </span>
            </div>

            <!-- desktop logout -->
            <a href="logout.php" class="hidden md:inline text-xs text-slate-300 hover:text-rose-300">
                Log out
            </a>

            <!-- mobile hamburger -->
            <button
                id="nav-toggle"
                type="button"
                class="md:hidden inline-flex items-center justify-center rounded-xl p-2 border border-slate-700/80 bg-slate-900/80 text-slate-100 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                aria-label="Toggle navigation"
            >
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- MOBILE MENU (hidden by default) -->
    <div id="mobile-menu" class="md:hidden hidden border-t border-slate-800/80 bg-slate-950/95">
        <div class="max-w-6xl mx-auto px-5 py-3 flex flex-col gap-2 text-sm text-slate-100">
            <a href="dashboard.php" class="py-1 text-emerald-300">Dashboard</a>
            <a href="courses.php" class="py-1 hover:text-emerald-300">Courses</a>
            <a href="#" class="py-1 hover:text-emerald-300">Tracks</a>
            <a href="#" class="py-1 hover:text-emerald-300">Labs</a>
            <a href="logout.php" class="py-1 text-rose-300 hover:text-rose-200">Log out</a>
        </div>
    </div>
</header>

<main class="max-w-6xl mx-auto px-5 py-8 space-y-10">

    <!-- HERO + PROFILE / LAB PLAN -->
    <section class="grid md:grid-cols-[3fr,2fr] gap-7 items-start">
        <div class="bg-slate-900/95 border border-slate-800/80 rounded-3xl p-7 shadow-2xl shadow-black/60">
            <p class="text-xs font-semibold text-emerald-300 uppercase tracking-[0.18em] mb-3">
                Dashboard
            </p>
            <h1 class="text-3xl font-semibold tracking-tight mb-3 text-slate-50">
                Welcome back, <?php echo htmlspecialchars($user); ?> 👋
            </h1>
            <p class="text-base text-slate-300 mb-6 leading-relaxed">
                Continue your offensive security journey. Pick up where you left off, explore new labs,
                and abuse intentionally vulnerable components like the login SQLi and reflected XSS search below.
            </p>

            <!-- Quick stats cards -->
            <div class="grid sm:grid-cols-3 gap-3">
                <div class="bg-slate-950/80 border border-slate-800/90 rounded-2xl p-4 text-sm">
                    <p class="text-[11px] text-slate-400 uppercase tracking-[0.18em] mb-1">Labs completed</p>
                    <p class="text-2xl font-semibold text-emerald-300">
                        <?php echo $stats['total_labs']; ?>
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        Web: <?php echo $stats['web_labs']; ?> · Pwn/Rev: <?php echo $stats['pwn_labs']; ?>
                    </p>
                </div>
                <div class="bg-slate-950/80 border border-slate-800/90 rounded-2xl p-4 text-sm">
                    <p class="text-[11px] text-slate-400 uppercase tracking-[0.18em] mb-1">Track focus</p>
                    <p class="text-sm text-slate-200">
                        Web / Forensics / Pwn / Crypto
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        Crypto: <?php echo $stats['crypto_labs']; ?> · Forensics: <?php echo $stats['forensics_labs']; ?>
                    </p>
                </div>
                <div class="bg-slate-950/80 border border-slate-800/90 rounded-2xl p-4 text-sm">
                    <p class="text-[11px] text-slate-400 uppercase tracking-[0.18em] mb-1">Streak</p>
                    <p class="text-2xl font-semibold text-emerald-300">
                        <?php echo $stats['streak_days']; ?><span class="text-sm text-slate-300 ml-1">days</span>
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        Keep solving at least one lab per day.
                    </p>
                </div>
            </div>

            <!-- Continue where you left off -->
            <div class="mt-6 bg-slate-950/80 border border-emerald-500/50 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-[11px] text-emerald-300 uppercase tracking-[0.18em] mb-1">
                        Continue where you left off
                    </p>
                    <p class="text-sm text-slate-50 font-semibold">
                        <?php echo htmlspecialchars($last_lab['title']); ?>
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        <?php echo htmlspecialchars($last_lab['category']); ?> ·
                        Difficulty: <?php echo htmlspecialchars($last_lab['difficulty']); ?> ·
                        Progress: <?php echo htmlspecialchars($last_lab['progress']); ?>
                    </p>
                </div>
                <button class="px-4 py-2 rounded-xl bg-emerald-500 text-slate-950 text-sm font-medium hover:bg-emerald-400">
                    Resume lab →
                </button>
            </div>
        </div>

        <!-- Sidebar: learner profile + roadmap -->
        <aside class="space-y-4">
            <div class="bg-slate-900/95 border border-slate-800/80 rounded-3xl p-5">
                <h2 class="text-base font-semibold text-slate-50 mb-3">
                    Learner profile
                </h2>
                <p class="text-sm text-slate-300 mb-1">
                    Username:
                    <span class="font-mono text-slate-50">
                        <?php echo htmlspecialchars($user); ?>
                    </span>
                </p>
                <?php if ($email): ?>
                    <p class="text-sm text-slate-300 mb-1">
                        Email:
                        <span class="font-mono text-slate-50">
                            <?php echo htmlspecialchars($email); ?>
                        </span>
                    </p>
                <?php endif; ?>
                <p class="text-sm text-slate-300 mt-4">
                    Current focus:
                    <span class="text-emerald-300 font-medium">Web exploitation & SQLi</span>
                </p>
            </div>

            <div class="bg-slate-900/95 border border-slate-800/80 rounded-3xl p-5 text-sm text-slate-300 space-y-2">
                <h2 class="text-base font-semibold text-slate-50 mb-2">Today’s lab checklist</h2>
                <ul class="list-disc list-inside space-y-1">
                    <li>Abuse SQL injection in the login form to escalate.</li>
                    <li>Trigger reflected XSS in the search parameter.</li>
                    <li>Think about how to turn these into full account takeover.</li>
                </ul>
            </div>
        </aside>
    </section>

    <!-- SEARCH (XSS LAB) + RECOMMENDED NEXT LAB -->
    <section class="grid md:grid-cols-[3fr,2fr] gap-7 items-start">
        <div class="bg-slate-900/95 border border-slate-800/80 rounded-3xl p-7 shadow-xl shadow-black/40">
            <p class="text-xs font-semibold text-slate-300 uppercase tracking-[0.18em] mb-3">
                Search courses (Reflected XSS)
            </p>
            <p class="text-sm text-slate-300 mb-4">
                This search box is intentionally vulnerable to reflected XSS. Your input is reflected
                below without escaping. Use it to practice payloads.
            </p>

            <!-- Search form -->
            <form method="GET" class="space-y-3">
                <div class="flex flex-col sm:flex-row gap-3">
                    <input
                        type="text"
                        name="q"
                        value="<?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?>"
                        class="flex-1 px-3.5 py-2.5 rounded-2xl bg-slate-950/90 border border-slate-700/80 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 placeholder:text-slate-500 text-slate-50"
                        placeholder="e.g. web, sql, &lt;script&gt;alert(1)&lt;/script&gt;"
                    >
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-sm sm:text-base font-medium shadow-lg shadow-emerald-500/40 transition-transform hover:-translate-y-[1px]"
                    >
                        Search
                    </button>
                </div>
            </form>

            <?php if ($query !== ''): ?>
                <!-- ❗ Intentionally unsanitized output for reflected XSS -->
                <div class="mt-5 text-sm rounded-2xl border border-emerald-500/60 bg-slate-950/90 px-4 py-3">
                    <span class="text-slate-300">You searched for:</span>
                    <span class="font-mono text-emerald-300 ml-1">
                        <?php echo $query; ?>
                    </span>
                </div>
                <p class="mt-2 text-[11px] text-slate-400">
                    Tip: Inject HTML/JS here. Think about how you’d turn this into cookie theft or redirect.
                </p>
            <?php endif; ?>
        </div>

        <!-- Recommended next lab card -->
        <div class="bg-slate-900/95 border border-slate-800/80 rounded-3xl p-6">
            <p class="text-xs font-semibold text-slate-300 uppercase tracking-[0.18em] mb-3">
                Recommended next lab
            </p>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] bg-slate-800 text-slate-200 mb-2">
                <?php echo htmlspecialchars($next_reco['tag']); ?>
            </span>
            <h3 class="text-lg font-semibold text-slate-50 mb-1.5">
                <?php echo htmlspecialchars($next_reco['title']); ?>
            </h3>
            <p class="text-xs text-emerald-300 mb-2">
                Level: <?php echo htmlspecialchars($next_reco['level']); ?>
            </p>
            <p class="text-sm text-slate-300 mb-4">
                <?php echo htmlspecialchars($next_reco['summary']); ?>
            </p>
            <button class="px-4 py-2 rounded-xl bg-indigo-500 text-slate-50 text-sm font-medium hover:bg-indigo-400">
                Start lab →
            </button>
        </div>
    </section>

    <!-- COURSE LIST -->
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm md:text-base font-semibold text-slate-50 tracking-[0.18em] uppercase">
                Available Courses
            </h2>
            <span class="text-[11px] md:text-xs text-slate-400">
                Showing <?php echo $query === '' ? count($courses) : count($results); ?> course(s)
            </span>
        </div>

        <div class="grid md:grid-cols-3 gap-5">
            <?php
            $toShow = $query === '' ? $courses : $results;

            if (empty($toShow)) {
                echo '<p class="text-sm text-slate-400 col-span-full bg-slate-900/70 border border-slate-800 rounded-2xl px-4 py-3">No courses matched your search.</p>';
            } else {
                foreach ($toShow as $course): ?>
                    <article class="bg-slate-900/95 border border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between shadow-lg shadow-black/40 hover:shadow-emerald-500/30 hover:border-emerald-500/60 hover:-translate-y-1 transition-all">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-full text-[11px] bg-slate-800 text-slate-200">
                                    <?php echo htmlspecialchars($course['tag']); ?>
                                </span>
                                <span class="text-[11px] text-emerald-300 font-medium">
                                    <?php echo htmlspecialchars($course['level']); ?>
                                </span>
                            </div>
                            <h3 class="text-base font-semibold text-slate-50 mb-2">
                                <?php echo htmlspecialchars($course['title']); ?>
                            </h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                <?php echo htmlspecialchars($course['desc']); ?>
                            </p>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Estimated: 3–5 hours</span>
                            <button class="text-emerald-300 hover:text-emerald-200">
                                View module →
                            </button>
                        </div>
                    </article>
                <?php endforeach;
            }
            ?>
        </div>
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
