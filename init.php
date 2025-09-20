<?php
session_start();
require_once __DIR__ . '/functions.php';
initializeSystem();
$currentUser = getCurrentUser();
if (isset($_GET['toggle_theme'])) {
    toggleTheme();
    $parts = parse_url($_SERVER['REQUEST_URI']);
    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
        unset($query['toggle_theme']);
    }
    $target = $parts['path'] ?? 'index.php';
    if (!empty($query)) {
        $target .= '?' . http_build_query($query);
    }
    header('Location: ' . $target);
    exit;
}
