<?php
auth_require_admin();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/navbar.php';

$courses = get_courses();
$tracks  = get_tracks();
$labs    = get_labs();
?>
<main class="mx-auto max-w-6xl px-4 py-6 space-y-6">
    <h1 class="text-2xl font-bold mb-2 text-amber-300">Admin Dashboard</h1>
    <p class="text-slate-400 text-sm">
        You are viewing the admin side. Here you could manage users, courses, tracks, etc.
        (for now it's just read-only stats, but you can turn this into a lab).
    </p>

    <section class="grid md:grid-cols-3 gap-4">
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
            <div class="text-xs text-slate-400 mb-1">Courses</div>
            <div class="text-2xl font-semibold"><?= count($courses) ?></div>
        </div>
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
            <div class="text-xs text-slate-400 mb-1">Tracks</div>
            <div class="text-2xl font-semibold"><?= count($tracks) ?></div>
        </div>
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
            <div class="text-xs text-slate-400 mb-1">Labs</div>
            <div class="text-2xl font-semibold"><?= count($labs) ?></div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../shared/footer.php'; ?>
