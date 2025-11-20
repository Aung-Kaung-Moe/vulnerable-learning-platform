<?php
// views/shared/navbar.php

require_once __DIR__ . '/../../app/helpers.php';

auth_require_login();

// Figure out current page from router or allow override.
if (!isset($currentPage)) {
    $currentPage = $_GET['page'] ?? 'dashboard';
}

$user = current_user_name();
$role = auth_role();

$navItems = [
    'dashboard' => 'Dashboard',
    'courses'   => 'Courses',
    'tracks'    => 'Tracks',
    'labs'      => 'Labs',
];
?>
<header class="border-b border-slate-800/80 bg-slate-950/90 backdrop-blur-xl sticky top-0 z-20">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between gap-4">
        <!-- Brand -->
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-3xl bg-gradient-to-br from-emerald-500 to-indigo-500 flex items-center justify-center text-lg shadow-lg shadow-emerald-500/40">
                🎓
            </div>
            <div>
                <div class="font-semibold tracking-tight text-base">VulnLab</div>
                <div class="text-xs text-slate-400 -mt-0.5">Offensive Security Learning</div>
            </div>
        </div>

        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-7 text-sm text-slate-200">
            <?php foreach ($navItems as $page => $label): ?>
                <?php
                $isActive = ($currentPage === $page);
                $base = 'relative transition text-sm';
                $underline = ' after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:bg-emerald-400 after:transition-all';
                if ($isActive) {
                    // active: green + underline
                    $classes = $base . ' text-emerald-300' . $underline . ' after:w-full';
                } else {
                    // normal: underline on hover only
                    $classes = $base . ' hover:text-emerald-300' . $underline . ' after:w-0 hover:after:w-full';
                }
                ?>
                <a
                    href="<?= h('index.php?page=' . $page) ?>"
                    class="<?= $classes ?>"
                >
                    <?= h($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Right side (user + logout + hamburger) -->
        <div class="flex items-center gap-3">
            <div class="hidden sm:flex flex-col items-end leading-tight">
                <span class="text-[11px] text-slate-400">Logged in as</span>
                <span class="text-sm text-slate-50 font-medium">
                    <?= h($user) ?> <span class="text-slate-400 text-[11px]">(
                        <?= h($role) ?>
                    )</span>
                </span>
            </div>

            <a
                href="logout.php"
                class="hidden md:inline text-xs text-rose-300 hover:text-rose-200"
            >
                Logout
            </a>

            <!-- Hamburger button (mobile) -->
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

    <!-- Mobile menu -->
    <div id="mobile-menu" class="md:hidden hidden border-t border-slate-800/80 bg-slate-950/95">
        <div class="max-w-6xl mx-auto px-5 py-3 flex flex-col gap-2 text-sm text-slate-100">
            <?php foreach ($navItems as $page => $label): ?>
                <?php $isActive = ($currentPage === $page); ?>
                <a
                    href="<?= h('index.php?page=' . $page) ?>"
                    class="py-1 <?= $isActive ? 'text-emerald-300 font-medium' : 'hover:text-emerald-300' ?>"
                >
                    <?= h($label) ?>
                </a>
            <?php endforeach; ?>
            <div class="flex items-center justify-between pt-2 mt-1 border-t border-slate-800/80 text-xs">
                <span class="text-slate-300">
                    <?= h($user) ?> <span class="text-slate-500">(<?= h($role) ?>)</span>
                </span>
                <a href="logout.php" class="text-rose-300 hover:text-rose-200">Logout</a>
            </div>
        </div>
    </div>
</header>

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
