<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 🚨 INTENTIONALLY VULNERABLE SQLI (for lab use only)
    // Example payload: ' OR 1=1 --
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' LIMIT 1";

    $result = $mysqli->query($query);
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // set session data
        $_SESSION['user']  = $user['username'];
        $_SESSION['email'] = $user['email'] ?? '';
        // 🔥 NEW: set role (comes from your ALTER TABLE users ADD role ... )
        $_SESSION['role']  = $user['role'] ?? 'user';

        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials (or your SQLi failed 😈)';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NovaLearn | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-indigo-500/20 via-slate-900 to-emerald-500/20 blur-3xl opacity-70"></div>

    <div class="w-full max-w-md mx-4">
        <div class="mb-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-900/80 border border-slate-700/60 shadow-lg shadow-indigo-500/20">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs tracking-wide uppercase text-slate-300">
                    NovaLearn • Offensive Edition
                </span>
            </div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl shadow-2xl shadow-black/60 backdrop-blur-xl p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">Welcome back</h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Sign in to your web exploitation lab.
                    </p>
                </div>
                <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center text-xl">
                    🔐
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-4 text-sm text-rose-300 bg-rose-950/70 border border-rose-800 px-3 py-2 rounded-xl">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm mb-1 text-slate-300">Username</label>
                    <input
                        type="text"
                        name="username"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950/80 border border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-slate-500"
                        placeholder="Type username"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm mb-1 text-slate-300">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950/80 border border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-slate-500"
                        placeholder="Type password"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="w-full mt-2 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-emerald-500 text-sm font-medium shadow-lg shadow-indigo-500/40 hover:shadow-emerald-500/40 hover:-translate-y-[1px] transition-all"
                >
                    <span>Login</span>
                    <span class="text-lg">➡️</span>
                </button>
            </form>

            <div class="mt-6 text-xs text-slate-500 border-t border-slate-800 pt-4 space-y-1">
                <p><strong>Note:</strong> This app is intentionally vulnerable. Lab only, not for production.</p>
                <p>Try SQL injection in the username field to bypass password checks.</p>
            </div>
        </div>
    </div>
</body>
</html>
