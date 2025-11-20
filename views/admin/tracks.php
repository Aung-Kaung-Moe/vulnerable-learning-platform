<?php
auth_require_admin();

$pageTitle = 'Admin – Tracks';
require __DIR__ . '/../shared/header.php';
require __DIR__ . '/../shared/navbar.php';

$tracks = get_tracks();
?>
<main class="mx-auto max-w-6xl px-4 py-6">
    <h1 class="text-2xl font-bold mb-4 text-amber-300">Manage Tracks</h1>
    <table class="w-full text-sm border border-slate-800 rounded-xl overflow-hidden">
        <thead class="bg-slate-950 text-slate-300">
            <tr>
                <th class="text-left px-3 py-2 border-b border-slate-800">ID</th>
                <th class="text-left px-3 py-2 border-b border-slate-800">Title</th>
                <th class="text-left px-3 py-2 border-b border-slate-800">Tag</th>
                <th class="text-left px-3 py-2 border-b border-slate-800">Level</th>
                <th class="text-left px-3 py-2 border-b border-slate-800">Duration</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tracks as $t): ?>
                <tr class="odd:bg-slate-900 even:bg-slate-950">
                    <td class="px-3 py-2 border-b border-slate-800"><?= (int)$t['id'] ?></td>
                    <td class="px-3 py-2 border-b border-slate-800"><?= h($t['title']) ?></td>
                    <td class="px-3 py-2 border-b border-slate-800"><?= h($t['tag']) ?></td>
                    <td class="px-3 py-2 border-b border-slate-800"><?= h($t['level']) ?></td>
                    <td class="px-3 py-2 border-b border-slate-800"><?= h($t['duration']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
<?php require __DIR__ . '/../shared/footer.php'; ?>
