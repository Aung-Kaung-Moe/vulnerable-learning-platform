<?php
require_once __DIR__ . '/../app/helpers.php';

// If not logged in, you could show a public landing page,
// but simplest is just redirect to login:
if (!auth_is_logged_in()) {
    header('Location: login.php');
    exit;
}

$page = $_GET['page'] ?? 'dashboard';

// Map page names to view files
$routes = [
    'dashboard'       => __DIR__ . '/../views/user/dashboard.php',
    'courses'         => __DIR__ . '/../views/user/courses.php',
    'tracks'          => __DIR__ . '/../views/user/tracks.php',
    'labs'            => __DIR__ . '/../views/user/labs.php',
    'admin-dashboard' => __DIR__ . '/../views/admin/dashboard.php',
    'admin-courses'   => __DIR__ . '/../views/admin/courses.php',
    'admin-tracks'    => __DIR__ . '/../views/admin/tracks.php',
];

if (!isset($routes[$page])) {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    exit;
}

// Admin gate for admin pages
if (str_starts_with($page, 'admin-') && !auth_is_admin()) {
    auth_require_admin(); // will 403 and exit
}

require $routes[$page];
