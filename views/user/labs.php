<?php
// views/user/labs.php

require_once __DIR__ . '/../../app/helpers.php';

auth_require_login();

$user  = current_user_name();
$email = current_user_email();
$role  = auth_role();

$pingOutput  = '';
$lastCommand = '';

if (isset($_GET['ping_host'])) {
    $host = $_GET['ping_host'];

    // 🔥 Command injection: user input goes straight into shell command.
    $cmd = "ping " . $host;
    $lastCommand = $cmd;
    $pingOutput = shell_exec($cmd);
}

$pageTitle = 'NovaLearn | Lab Tools';
require __DIR__ . '/../shared/header.php';
$currentPage = 'labs';
require __DIR__ . '/../shared/navbar.php';
?>

<div class="pointer-events-none fixed inset-0 -z-10 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-70 bg-[radial-gradient(circle_at_top,_#22c55e33_0,_transparent_55%)]"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-60 bg-[radial-gradient(circle_at_bottom,_#6366f133_0,_transparent_60%)]"></div>

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
                <input type="hidden" name="page" value="labs">
                <div class="space-y-1">
                    <label for="ping_host" class="text-[11px] text-slate-300">Host / IP</label>
                    <input
                        type="text"
                        id="ping_host"
                        name="ping_host"
                        value="<?= isset($_GET['ping_host']) ? h($_GET['ping_host']) : '' ?>"
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
                    <?= h($lastCommand) ?>
                </div>
            <?php else: ?>
                <p class="text-[11px] text-slate-400 mb-3">
                    No command executed yet. Run a ping to see raw output here.
                </p>
            <?php endif; ?>

            <div class="font-mono text-[10px] bg-slate-950/80 border border-slate-800 rounded-xl px-3 py-2 min-h-[160px] overflow-x-auto overflow-y-auto">
                <?php if ($pingOutput !== ''): ?>
                    <?= h($pingOutput) ?>
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

<?php require __DIR__ . '/../shared/footer.php'; ?>
