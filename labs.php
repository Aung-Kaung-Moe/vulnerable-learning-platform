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

$pingOutput = '';
$lastCommand = '';

if (isset($_GET['ping_host'])) {
    $host = $_GET['ping_host'];

    // 🔥 Command injection: user input goes straight into shell command.
    // On Linux, "-c 2" means send 2 ICMP packets.
    $cmd = "ping " . $host;
    $lastCommand = $cmd;
    $pingOutput = shell_exec($cmd);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NovaLearn | Lab Tools</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<div class="pointer-events-none fixed inset-0 -z-10 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-70 bg-[radial-gradient(circle_at_top,_#22c55e33_0,_transparent_55%)]"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-60 bg-[radial-gradient(circle_at_bottom,_#6366f133_0,_transparent_60%)]"></div>

<header class="border-b border-slate-800/80 bg-slate-950/90 backdrop-blur-xl sticky top-0 z-20">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-3xl bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center text-xl shadow-lg shadow-emerald-500/40">
                🧪
            </div>
            <div>
                <div class="font-semibold tracking-tight text-base">NovaLearn</div>
                <div class="text-xs text-slate-400 -mt-0.5">Offensive Security Learning</div>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-7 text-sm text-slate-200">
            <a href="dashboard.php" class="relative hover:text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-emerald-400 after:transition-all">
                Dashboard
            </a>
            <a href="courses.php" class="relative hover:text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-emerald-400 after:transition-all">
                Courses
            </a>
            <a href="tracks.php" class="relative hover:text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-emerald-400 after:transition-all">
                Tracks
            </a>
            <a href="labs.php" class="relative text-emerald-300 after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-full after:bg-emerald-400">
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
            <a href="#" class="py-1 hover:text-emerald-300">Labs</a>
            <a href="logout.php" class="py-1 text-rose-300 hover:text-rose-200">Log out</a>
        </div>
    </div>
</header>

<main class="max-w-5xl mx-auto px-5 py-8 space-y-6">
    <section class="space-y-2">
        <h1 class="text-xl font-semibold tracking-tight">Lab Connectivity Check</h1>
        <p class="text-sm text-slate-300 max-w-2xl">
            Use this utility to verify if a lab host is reachable from the platform.
            It sends a couple of ICMP packets and shows the raw output.
        </p>
    </section>

    <section class="grid gap-5 md:grid-cols-[minmax(0,1.5fr)_minmax(0,1.5fr)] items-start">
        <!-- Form -->
        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4 shadow-lg shadow-emerald-500/10 text-xs">
            <h2 class="text-sm font-medium mb-2">Ping a host</h2>
            <p class="text-[11px] text-slate-400 mb-3">
                Enter a lab IP or hostname. Example:
                <code class="font-mono text-[11px] text-emerald-300">10.10.0.5</code> or
                <code class="font-mono text-[11px] text-emerald-300">lab.novalearn.local</code>.
            </p>

            <form method="get" class="space-y-3">
                <div class="space-y-1">
                    <label for="ping_host" class="text-[11px] text-slate-300">Host / IP</label>
                    <input
                        type="text"
                        id="ping_host"
                        name="ping_host"
                        value="<?php echo isset($_GET['ping_host']) ? htmlspecialchars($_GET['ping_host'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                        placeholder="10.10.0.5"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-500/90 text-slate-950 text-xs font-semibold px-3 py-2 hover:bg-emerald-400 transition">
                    Run ping
                </button>

                <p class="text-[10px] text-slate-500">
                    For training purposes, this tool shows the exact command being run.
                    Curious minds might try more than just hostnames…
                </p>
            </form>
        </div>

        <!-- Output -->
        <div class="rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4 shadow-lg shadow-indigo-500/10 text-xs">
            <h2 class="text-sm font-medium mb-2">Output</h2>

            <?php if ($lastCommand): ?>
                <p class="text-[11px] text-slate-400 mb-1">Executed command:</p>
                <div class="font-mono text-[10px] bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 mb-3 overflow-x-auto">
                    <?php echo htmlspecialchars($lastCommand, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php else: ?>
                <p class="text-[11px] text-slate-400 mb-3">
                    No command executed yet. Run a ping to see raw output here.
                </p>
            <?php endif; ?>

            <div class="font-mono text-[10px] bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 min-h-[160px] overflow-x-auto overflow-y-auto">
                <?php if ($pingOutput !== ''): ?>
                    <?php echo htmlspecialchars($pingOutput, ENT_QUOTES, 'UTF-8'); ?>
                <?php else: ?>
                    <span class="text-slate-500">Awaiting output…</span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <p class="text-[11px] text-slate-500">
        Dev note: this endpoint is intentionally naive for practice. In a real system,
        commands would be sanitized or avoided entirely.
    </p>
</main>
</body>
</html>
