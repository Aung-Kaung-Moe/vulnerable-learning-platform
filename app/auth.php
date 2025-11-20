<?php
// app/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Vulnerable login using raw string interpolation (intended for SQLi lab).
 */
function auth_login(string $username, string $password): bool
{
    global $mysqli;

    // ⚠ Intentionally vulnerable: no escaping / prepared statements.
    $query  = "SELECT * FROM users WHERE username = '$username' AND password = '$password' LIMIT 1";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        $_SESSION['user']  = $row['username'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['role']  = $row['role'];

        return true;
    }

    return false;
}

/**
 * Registration logic (also intentionally not using prepared statements).
 * Returns ['success' => bool, 'error' => string, 'message' => string]
 */
function auth_register(
    string $username,
    string $email,
    string $password,
    string $confirm,
    string $role = 'user'
): array {
    global $mysqli;

    $username = trim($username);
    $email    = trim($email);

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        return ['success' => false, 'error' => 'Please fill in all fields.', 'message' => ''];
    }

    if ($password !== $confirm) {
        return ['success' => false, 'error' => 'Passwords do not match.', 'message' => ''];
    }

    // Check duplicate username
    $checkQuery  = "SELECT id FROM users WHERE username = '$username' LIMIT 1";
    $checkResult = $mysqli->query($checkQuery);

    if ($checkResult && $checkResult->num_rows > 0) {
        return ['success' => false, 'error' => 'That username is already taken.', 'message' => ''];
    }

    // Insert new user
    $insertQuery = "
        INSERT INTO users (username, email, password, role)
        VALUES ('$username', '$email', '$password', '$role')
    ";

    if ($mysqli->query($insertQuery)) {
        return ['success' => true, 'error' => '', 'message' => 'Account created. You can log in now.'];
    }

    return [
        'success' => false,
        'error'   => 'Registration failed: ' . $mysqli->error,
        'message' => '',
    ];
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

function auth_is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function auth_require_login(): void
{
    if (!auth_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function auth_role(): string
{
    return $_SESSION['role'] ?? 'user';
}

function auth_is_admin(): bool
{
    return auth_role() === 'admin';
}

function auth_require_admin(): void
{
    auth_require_login();
    if (!auth_is_admin()) {
        http_response_code(403);
        echo "<h1>403 Forbidden</h1><p>You are not allowed to access this area.</p>";
        exit;
    }
}
