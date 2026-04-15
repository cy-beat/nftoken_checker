<?php
session_start();
// 🔐 USERS DATABASE (can move to DB later)
$USERS = [
    [
        "username" => "admin",
        "password" => "$2y$10$wH1vVh5lPZ0eQ5r0kCqK5e0W6Fv6P8kF5KkQZ8nZ1gkU3wX6YyG6K",
        "role" => "admin"
    ],
    [
        "username" => "user",
        "password" => "$2y$10$wH1vVh5lPZ0eQ5r0kCqK5e0W6Fv6P8kF5KkQZ8nZ1gkU3wX6YyG6K",
        "role" => "user"
    ],
    [
        "username" => "Cybeat",
        "password" => "$2y$10$wH1vVh5lPZ0eQ5r0kCqK5e0W6Fv6P8kF5KkQZ8nZ1gkU3wX6YyG6K",
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
