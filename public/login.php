<?php
// public/login.php

require_once __DIR__ . '/../app/auth.php';    // starts session + loads DB
require_once __DIR__ . '/../app/helpers.php'; // for h()

$loginError      = '';
$registerError   = '';
$registerSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (auth_login($username, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $loginError = 'Invalid username or password.';
        }
    }

    if ($action === 'register') {
        $username = $_POST['reg_username'] ?? '';
        $email    = $_POST['reg_email'] ?? '';
        $password = $_POST['reg_password'] ?? '';
        $confirm  = $_POST['reg_confirm'] ?? '';
        $role     = $_POST['reg_role'] ?? 'user';

        $result = auth_register($username, $email, $password, $confirm, $role);

        if ($result['success']) {
            $registerSuccess = $result['message'];
        } else {
            $registerError = $result['error'];
        }
    }
}

// If already logged in, you *could* redirect straight to dashboard:
if (auth_is_logged_in()) {
    // comment this out if you want to stay on login page even when logged in
    // header('Location: index.php');
    // exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NovaLearn | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<div class="pointer-events-none fixed inset-0 -z-10 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-70 bg-[radial-gradient(circle_at_top,_#22c55e33_0,_transparent_55%)]"></div>
<div class="pointer-events-none fixed inset-0 -z-10 opacity-50 bg-[radial-gradient(circle_at_bottom,_#6366f133_0,_transparent_60%)]"></div>

<div class="flex items-center justify-center min-h-screen px-4">
    <div class="w-full max-w-md">
        <div class="flex flex-col items-center mb-6">
            <div class="h-12 w-12 rounded-3xl bg-gradient-to-br from-emerald-500 to-indigo-500 flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/40">
                🎓
            </div>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight">NovaLearn</h1>
            <p class="text-sm text-slate-400 mt-1">Offensive security learning platform</p>
        </div>

        <div class="rounded-3xl border border-slate-800/80 bg-slate-900/70 shadow-xl shadow-black/40 overflow-hidden">
            <div class="flex text-xs font-medium text-slate-300 border-b border-slate-800/80">
                <button
                    id="tab-login"
                    class="flex-1 py-3 text-center cursor-pointer relative border-b-2 border-emerald-400 bg-slate-900/90">
                    Login
                </button>
                <button
                    id="tab-register"
                    class="flex-1 py-3 text-center cursor-pointer relative border-b-2 border-transparent hover:border-slate-700/80">
                    Register
                </button>
            </div>

            <div class="p-5 space-y-4">

                <!-- LOGIN FORM -->
                <form id="form-login" method="post" class="space-y-4">
                    <input type="hidden" name="action" value="login">

                    <?php if ($loginError): ?>
                        <div class="text-xs rounded-xl border border-red-500/60 bg-red-500/10 text-red-200 px-3 py-2">
                            <?= h($loginError) ?>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-1">
                        <label for="username" class="text-xs text-slate-300">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                            placeholder="e.g. student"
                            required
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="password" class="text-xs text-slate-300">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>Tip: try basic SQL tricks here.</span>
                        <a href="#" class="text-emerald-300 hover:text-emerald-200">Forgot password?</a>
                    </div>

                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center rounded-xl bg-emerald-500/90 text-slate-950 text-sm font-medium py-2.5 hover:bg-emerald-400 transition">
                        Sign in
                    </button>

                    <p class="text-[11px] text-slate-400 text-center">
                        New here?
                        <button
                            type="button"
                            id="switch-to-register"
                            class="text-emerald-300 hover:text-emerald-200 underline underline-offset-2">
                            Create an account
                        </button>
                    </p>
                </form>

                <!-- REGISTER FORM -->
                <form id="form-register" method="post" class="space-y-4 hidden">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="reg_role" value="user">

                    <?php if ($registerError): ?>
                        <div class="text-xs rounded-xl border border-red-500/60 bg-red-500/10 text-red-200 px-3 py-2">
                            <?= h($registerError) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($registerSuccess): ?>
                        <div class="text-xs rounded-xl border border-emerald-500/60 bg-emerald-500/10 text-emerald-200 px-3 py-2">
                            <?= h($registerSuccess) ?>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-1">
                        <label for="reg_username" class="text-xs text-slate-300">Username</label>
                        <input
                            type="text"
                            id="reg_username"
                            name="reg_username"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                            placeholder="Pick a handle"
                            required
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="reg_email" class="text-xs text-slate-300">Email</label>
                        <input
                            type="email"
                            id="reg_email"
                            name="reg_email"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                            placeholder="you@example.com"
                            required
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="reg_password" class="text-xs text-slate-300">Password</label>
                        <input
                            type="password"
                            id="reg_password"
                            name="reg_password"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="reg_confirm" class="text-xs text-slate-300">Confirm password</label>
                        <input
                            type="password"
                            id="reg_confirm"
                            name="reg_confirm"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <div class="text-[11px] text-slate-400">
                        Account type: <span class="text-slate-100 font-medium">Student</span>
                    </div>

                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center rounded-xl bg-slate-800/90 text-slate-100 text-sm font-medium py-2.5 border border-slate-700 hover:bg-slate-700 transition">
                        Create account
                    </button>

                    <p class="text-[11px] text-slate-400 text-center">
                        Already have an account?
                        <button
                            type="button"
                            id="switch-to-login"
                            class="text-emerald-300 hover:text-emerald-200 underline underline-offset-2">
                            Sign in
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <p class="mt-4 text-[11px] text-slate-500 text-center">
            Built for practice. Do not reuse real passwords.
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabLogin       = document.getElementById('tab-login');
    const tabRegister    = document.getElementById('tab-register');
    const formLogin      = document.getElementById('form-login');
    const formRegister   = document.getElementById('form-register');
    const switchToReg    = document.getElementById('switch-to-register');
    const switchToLogin  = document.getElementById('switch-to-login');

    function showLogin() {
        tabLogin.classList.add('border-emerald-400', 'bg-slate-900/90');
        tabLogin.classList.remove('border-transparent');
        tabRegister.classList.remove('border-emerald-400', 'bg-slate-900/90');
        tabRegister.classList.add('border-transparent');

        formLogin.classList.remove('hidden');
        formRegister.classList.add('hidden');
    }

    function showRegister() {
        tabRegister.classList.add('border-emerald-400', 'bg-slate-900/90');
        tabRegister.classList.remove('border-transparent');
        tabLogin.classList.remove('border-emerald-400', 'bg-slate-900/90');
        tabLogin.classList.add('border-transparent');

        formRegister.classList.remove('hidden');
        formLogin.classList.add('hidden');
    }

    tabLogin.addEventListener('click', showLogin);
    tabRegister.addEventListener('click', showRegister);

    if (switchToReg) {
        switchToReg.addEventListener('click', showRegister);
    }
    if (switchToLogin) {
        switchToLogin.addEventListener('click', showLogin);
    }

    <?php if ($registerError || $registerSuccess): ?>
    showRegister();
    <?php endif; ?>
});
</script>
</body>
</html>
