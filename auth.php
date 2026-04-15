<?php
session_start();
// 🔐 USERS DATABASE (can move to DB later)
$USERS = [
    [
        "username" => "admin",
        "password" => password_hash("admin110814", PASSWORD_DEFAULT),
        "role" => "admin"
    ],
    [
        "username" => "user",
        "password" => password_hash("user123", PASSWORD_DEFAULT),
        "role" => "user"
    ],
     [
        "username" => "Cybeat",
        "password" => password_hash("pass110814", PASSWORD_DEFAULT),
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
