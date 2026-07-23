<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin pages are always generated fresh (session-gated, DB-backed) — never
// let the browser cache the HTML response itself, only the static CSS/JS
// assets (which use their own filemtime cache-busting).
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
