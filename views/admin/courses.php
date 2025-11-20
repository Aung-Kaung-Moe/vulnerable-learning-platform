<?php
auth_require_admin();

$pageTitle = 'Admin – Courses';
require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/navbar.php';

$courses = get_courses();
?>
<main class="mx-auto max-w-6xl px-4 py-6">
    <h1 class="text-2xl font-bold mb-4 text-amber-300">Manage Courses</h1>
    <p class="text-slate-400 text-sm mb-4">
        This is a fake admin panel. You can turn this into a CSRF / IDOR / XSS playground.
    </p>

    <table class="w-full text-sm border border-slate-800 rounded-xl overflow-hidden">
        <thead class="bg-slate-950 text-slate-300">
            <tr>
                <th class="text-left px-3 py-2 border-b border-slate-800">ID</th>
                <th class="text-left px-3 py-2 border-b border-slate-800">Title</th>
                <th class="text-left px-3 py-2 border-b border-slate-800">Tag</th>
                <th class="text-left px-3 py-2 border-b border-slate-800">Difficulty</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $c): ?>
                <tr class="odd:bg-slate-900 even:bg-slate-950">
                    <td class="px-3 py-2 border-b border-slate-800"><?= (int)$c['id'] ?></td>
                    <td class="px-3 py-2 border-b border-slate-800"><?= h($c['title']) ?></td>
                    <td class="px-3 py-2 border-b border-slate-800"><?= h($c['tag']) ?></td>
                    <td class="px-3 py-2 border-b border-slate-800"><?= h($c['difficulty']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
<?php require __DIR__ . '/../shared/footer.php'; ?>
