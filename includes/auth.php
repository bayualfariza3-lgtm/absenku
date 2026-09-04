<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login()
{
    if (!isset($_SESSION['user'])) {
        header("Location: /absenku/login.php");
        exit;
    }
}

function require_role($roles)
{
    require_login();

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!in_array($_SESSION['user']['role'], $roles)) {
        http_response_code(403);
        die("Akses ditolak.");
    }
}

function e($text)
{
    return htmlspecialchars(
        $text ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}