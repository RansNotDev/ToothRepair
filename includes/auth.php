<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header("Location: ../index.php");
        exit();
    }
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>