<?php
session_start();
include "auth.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

if (login($username, $password, $USERS)) {

        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
body {
    background: radial-gradient(circle at top, #0d1117, #020617);
    display:flex;
    align-items:center;
    justify-content:center;
    height:100vh;
    color:white;
}

/* 🔥 glass card */
.login-box {
    background: rgba(22,27,34,0.85);
    backdrop-filter: blur(15px);
    padding:30px;
    border-radius:16px;
    width:320px;
    box-shadow: 0 0 30px rgba(88,166,255,0.25);
    animation: fadeIn 0.6s ease;
}

/* glow input */
.input {
    width:100%;
    padding:10px;
    margin-bottom:12px;
    border-radius:10px;
    border:1px solid #30363d;
    background:#0d1117;
    color:white;
}

.input:focus {
    border-color:#58a6ff;
    box-shadow:0 0 12px rgba(88,166,255,0.6);
    outline:none;
}

/* button glow */
.btn {
    width:100%;
    padding:10px;
    border:none;
    border-radius:50px;
    background:linear-gradient(135deg,#e50914,#b20710);
    font-weight:bold;
    transition:0.2s;
}

.btn:hover {
    transform:scale(1.03);
    box-shadow:0 0 20px rgba(229,9,20,0.7);
}

@keyframes fadeIn {
    from {opacity:0; transform:translateY(10px);}
    to {opacity:1;}
}
</style>
</head>

<body>

<div class="login-box">
echo "Password entered: [" . $password . "]";
exit;
    <h2 class="text-center text-xl font-bold mb-4">🔐 User Login</h2>

    <?php if ($error): ?>
        <p style="color:#ef4444; font-size:13px; margin-bottom:10px;">
            <?= $error ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input class="input" name="username" placeholder="Username" required>
        <input class="input" type="password" name="password" placeholder="Password" required>

        <button class="btn">Login</button>
    </form>

</div>

</body>
</html>
