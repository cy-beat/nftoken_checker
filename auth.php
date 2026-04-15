<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 🔐 USERS DATABASE (can move to DB later)
$USERS = [
    [
        "username" => "admin",
        "password" => "$2y$10$cHc.9/ZvrqvuAYAPfw/HYeZlxwN4X13YcuaDpRYp0q3rFPdBt1ii2",
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

    $username = trim($username);
    $password = trim($password);

    foreach ($USERS as $user) {

        if ($user['username'] === $username) {
    echo "Password entered: [" . $password . "]";
    exit;
            if (password_verify($password, $user['password'])) {

                session_regenerate_id(true);
                $_SESSION['user'] = $user;

                return true;
            }
        }
    }

    return false;
}
