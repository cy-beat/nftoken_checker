<?php
session_start();
// 🔐 USERS DATABASE (can move to DB later)
$USERS = [
    [
        "username" => "admin",
        "password" => "$2y$10$Z8UZuHrEn5WoTw5UJlh1FO2TGwQ0hVi/xl/CKDyZ9qaGR0p5qEB9q",
        "role" => "admin"
    ],
    [
        "username" => "user1",
        "password" => "$2y$10$LcBoFQv3P0vZ/Q74/DK1MeiEU9FJ277XDPHOdzGct56317iduI9hO",
        "role" => "user"
    ],
    [
        "username" => "Cybeat",
        "password" => "$2y$10$byF3zhRIaOYUHAydShe2ae43rSqFxxbatCwgUKX4rLknUlaMq9zXy",
        "role" => "user"
    ]
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

    // 🔐 IP LOCK
    if (!isset($_SESSION['ip']) || $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();

        header("Location: login.php");
        exit;
    }
}
