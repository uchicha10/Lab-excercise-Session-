<?php
// Shared helpers: session, CSRF, escaping, access guards.
session_start();

function e(?string $v): string {
    return htmlspecialchars($v ?? "", ENT_QUOTES, "UTF-8");
}

function csrf_token(): string {
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}

function csrf_check(): bool {
    return isset($_POST["csrf"], $_SESSION["csrf"])
        && hash_equals($_SESSION["csrf"], $_POST["csrf"]);
}

function is_logged_in(): bool {
    return isset($_SESSION["user_id"], $_SESSION["role"]);
}

function require_login(): void {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit;
    }
}

function redirect_if_logged_in(): void {
    if (is_logged_in()) {
        header("Location: home.php");
        exit;
    }
}
