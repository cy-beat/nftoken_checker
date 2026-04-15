<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 🔐 USERS DATABASE (can move to DB later)
$USERS = [
    [
        "username" => "admin",
        "password" => "$2y$10$wQJmRNdO5P8ix6gS1hMy9OInw19LtGPfE06NMJO4k27q8hltF5pE6",
        "role" => "admin"
    ],
    [
        "username" => "user1",
        "password" => "$2y$10$YKHu/E1X9ZkyNJZ.kr3JBOqskcAEcw8bf.7JNdFvt44TtukS9WAxW",
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
