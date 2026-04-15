<?php
session_start();
// 🔐 USERS DATABASE (can move to DB later)
$USERS = [
    [
        "username" => 'admin',
        "password" => '$2y$10$cHc.9/ZvrqvuAYAPfw/HYeZlxwN4X13YcuaDpRYp0q3rFPdBt1ii2',
        "role" => "admin"
    ],
    [
        "username" => 'user',
        "password" => '$2y$12$Oip/CxfPWcPyzw.emGkHYeD3ZoXGazrii8jaNAow/gdM0zzuYBKcS',
        "role" => "user"
    ],
];

// 🔑 LOGIN FUNCTION
function login($username, $password, $USERS) {
    foreach ($USERS as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            
            // 🔥 VERY IMPORTANT (ADD HERE)
            session_regenerate_id(true);

            $_SESSION['user'] = $user;
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

            return true;
        }
    }
    return false;
}

// 🔒 PROTECT PAGE
function requireLogin() {

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['user'])) {
        session_unset();
        session_destroy();

        header("Location: login.php");
        exit;
    }
}
