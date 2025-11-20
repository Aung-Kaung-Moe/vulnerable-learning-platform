<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user   = $_SESSION['user'];
$email  = $_SESSION['email'] ?? '';
$role   = $_SESSION['role'] ?? 'user';
$isAdmin = ($role === 'admin');

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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NovaLearn | Learning Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">

<?php if ($isAdmin): ?>

    <div class="min-h-screen flex items-center justify-center bg-slate-950 text-slate-100">
        <div class="bg-slate-900/80 border border-emerald-500/60 rounded-2xl px-8 py-6 text-center shadow-2xl shadow-emerald-500/30">
            <h1 class="text-2xl font-semibold mb-3">You are admin</h1>
            <p class="text-sm text-slate-300 mb-4">
                This is the admin view. Normal users see the full NovaLearn dashboard.
            </p>
            <a href="logout.php"
               class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm bg-emerald-500 text-slate-950 hover:bg-emerald-400">
                Logout
            </a>
        </div>
    </div>

<?php else: ?>

    <div class="pointer-events-none fixed inset-0 -z-10 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950"></div>
    <div class="pointer-events-none fixed inset-0 -z-10 opacity-60 blur-3xl bg-[radial-gradient(circle_at_top,_#22c55e_0,_transparent_45%),_radial-gradient(circle_at_bottom,_#6366f1_0,_transparent_55%)]"></div>

    <header class="border-b border-slate-800/80 bg-slate-950/90 backdrop-blur-xl sticky top-0 z-20">
        <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-3xl bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center text-xl shadow-lg shadow-emerald-500/40">
                    📚
                </div>
                <div>
                    <div class="font-semibold tracking-tight text-base">NovaLearn</div>
                    <div class="text-xs text-slate-400 -mt-0.5">Hands-on Security Learning</div>
                </div>
            </div>

            <nav class="hidden md:flex items-center gap-7 text-sm text-slate-200">
                <a href="dashboard.php" class="relative hover:text-emerald-300">
                    Dashboard
                </a>
                <a href="courses.php" class="relative hover:text-emerald-300">
                    Courses
                </a>
                <a href="tracks.php" class="relative hover:text-emerald-300">
                    Tracks
                </a>
                <a href="#" class="relative hover:text-emerald-300">
                    Labs
                </a>
            </nav>


            <div class="flex items-center gap-3">
                <div class="hidden sm:flex flex-col items-end leading-tight">
                    <span class="text-[11px] text-slate-400">Logged in as</span>
                    <span class="text-sm text-slate-50 font-medium">
                        <?php echo htmlspecialchars($user); ?>
                    </span>
                </div>

                <a href="logout.php" class="hidden md:inline text-xs text-slate-300 hover:text-rose-300">
                    Log out
                </a>

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

        <div id="mobile-menu" class="md:hidden hidden border-t border-slate-800/80 bg-slate-950/95">
            <div class="max-w-6xl mx-auto px-5 py-3 flex flex-col gap-2 text-sm text-slate-100">
                <a href="dashboard.php" class="py-1 text-emerald-300">Dashboard</a>
                <a href="courses.php" class="py-1 hover:text-emerald-300">Courses</a>
                <a href="tracks.php" class="py-1 hover:text-emerald-300">Tracks</a>
                <a href="labs.php" class="py-1 hover:text-emerald-300">Labs</a>
                <a href="logout.php" class="py-1 text-rose-300 hover:text-rose-200">Log out</a>
            </div>
        </div>

    </header>

    <main class="max-w-6xl mx-auto px-5 py-8 space-y-10">

        <section class="grid md:grid-cols-[3fr,2fr] gap-7 items-start">
            <div class="bg-slate-900/95 border border-slate-800/80 rounded-3xl p-7 shadow-2xl shadow-black/60">
                <p class="text-xs font-semibold text-emerald-300 uppercase tracking-[0.18em] mb-3">
                    Learning Platform
                </p>
                <h1 class="text-3xl font-semibold tracking-tight mb-3 text-slate-50">
                    Continue your offensive security journey.
                </h1>
                <p class="text-base text-slate-300 mb-6 leading-relaxed">
                    Browse courses, search topics, and (intentionally) break things along the way.
                    This search box doubles as your
                    <span class="text-emerald-300 font-medium">reflected XSS lab</span>.
                </p>

                <form method="GET" class="space-y-3">
                    <label class="block text-[11px] font-semibold text-slate-300 tracking-[0.2em] uppercase">
                        Search courses
                    </label>
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
                    <div class="mt-5 text-sm rounded-2xl border border-emerald-500/60 bg-slate-950/90 px-4 py-3">
                        <span class="text-slate-300">You searched for:</span>
                        <span class="font-mono text-emerald-300 ml-1">
                            <?php echo $query; ?>
                        </span>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-400">
                        Tip: Try injecting HTML/JS here. This reflection is deliberately not escaped.
                    </p>
                <?php endif; ?>
            </div>

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
                        Track:
                        <span class="text-emerald-300 font-medium">Web / Forensics / Pwn / Crypto</span>
                    </p>
                </div>

                <div class="bg-slate-900/95 border border-slate-800/80 rounded-3xl p-5 text-sm text-slate-300 space-y-2">
                    <h2 class="text-base font-semibold text-slate-50 mb-2">Lab notes</h2>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Login form is vulnerable to SQL injection.</li>
                        <li>This search bar reflects your input without escaping → Reflected XSS.</li>
                        <li>This is a learning lab. Never deploy this configuration to production.</li>
                    </ul>
                </div>
            </aside>
        </section>

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

<?php endif; ?>

</body>
</html>