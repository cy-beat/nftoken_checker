<?php
session_start();
include "auth.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['username'], $_POST['password'], $USERS)) {

        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['role'] = $_SESSION['user']['role'];
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
<link rel="icon" href="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
body {
    background: radial-gradient(circle at top, #0d1117, #020617);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    color:white;
    margin:0;
}

/* 🔥 NAVBAR */
.nav-glow {
    box-shadow: 0 0 12px rgba(88,166,255,0.25);
    transition: all 0.3s ease;
}
.nav-glow:hover {
    box-shadow: 0 0 25px rgba(88,166,255,0.6);
}

.nav-marquee { overflow: hidden; width: 100%; }
.nav-track {
    display: flex;
    width: max-content;
    animation: scrollNav linear infinite;
}

@keyframes scrollNav {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}

.nav-item {
    display: flex;
    align-items: center;
    margin-right: 60px;
    white-space: nowrap;
    animation: floatY 2.5s ease-in-out infinite;
}

@keyframes floatY {
    0%,100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

.nav-item span {
    animation: glowPulse 3s ease-in-out infinite;
}

@keyframes glowPulse {
    0%,100% { text-shadow: 0 0 6px rgba(217,119,6,0.4); }
    50% { text-shadow: 0 0 14px rgba(217,119,6,0.9); }
}

/* 🔐 LOGIN UI */
.login-box {
    background: rgba(22,27,34,0.85);
    backdrop-filter: blur(15px);
    padding:30px;
    border-radius:16px;
    width:320px;
    box-shadow: 0 0 30px rgba(88,166,255,0.25);
    animation: fadeIn 0.6s ease;
}

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

.demo-box {
    background: rgba(13,17,23,0.7);
    border: 1px solid #30363d;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 15px;
    box-shadow: inset 0 0 10px rgba(88,166,255,0.08);
}

.demo-title {
    font-size: 13px;
    color: #58a6ff;
    font-weight: bold;
    margin-bottom: 8px;
}

.demo-row {
    display: flex;
    justify-content: space-between;
    background: #0d1117;
    border: 1px solid #30363d;
    padding: 8px 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s;
}

.demo-row:hover {
    border-color: #58a6ff;
    box-shadow: 0 0 12px rgba(88,166,255,0.4);
    transform: scale(1.02);
}

.demo-user { color: #79c0ff; font-weight: 500; }
.demo-pass { color: #ff7b72; font-weight: 500; }

/* ✅ Layout Fix */
.page-wrapper {
    width:100%;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    padding-top:90px;
    padding-bottom:60px;
}

/* 🔻 FOOTER */
footer {
    position:fixed;
    bottom:0;
    width:100%;
    text-align:center;
    padding:10px 0;
    background:rgba(13,17,23,0.9);
    border-top:1px solid #30363d;
    backdrop-filter: blur(10px);
    z-index:999;
}
</style>
</head>

<body>

<!-- 🔥 NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top nav-glow px-0"
     style="background:#161b22; border-bottom:1px solid #30363d;">

<div class="container-fluid px-0 mx-0 d-flex flex-wrap align-items-center">

    <!-- LEFT SIDE -->
    <div class="d-flex align-items-center flex-wrap pl-0 ml-0">
        <div class="nav-marquee">
            <div class="nav-track" id="navTrack">
                <div class="nav-item">
                    <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                         style="width:45px; height:40px; margin-right:8px; margin-left:0;">
                    <span style="font-size:25px; font-weight:bold; color:#ffffff;">
                        Cookies Checker
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE (SPACED) -->
    <span class="ml-auto mr-3" style="font-size:12px; color:#8b949e;">
        Powered by: <b>Cybeat</b>
    </span>

</div>
</nav>

<div class="page-wrapper">
    <div class="login-box">

        <h2 class="text-center text-xl font-bold mb-4">🔐 User Login</h2>

        <?php if ($error): ?>
            <p style="color:#ef4444; font-size:13px; margin-bottom:10px;">
                <?= $error ?>
            </p>
        <?php endif; ?>

        <div class="demo-box">
            <p class="demo-title">👤 User Account</p>

            <div class="demo-row" onclick="fillLogin('user','user123')">
                <span class="demo-user">User: user</span>
                <span class="demo-pass">Pass: user123</span>
            </div>
        </div>

        <form method="POST">
            <input class="input" name="username" placeholder="Username" required>
            <input class="input" type="password" name="password" placeholder="Password" required>

            <button class="btn">Login</button>
        </form>

    </div>
</div>

<!-- 🔻 FOOTER -->
<footer>
    <div class="text-gray-500" style="font-size:12px;">
        <p style="margin:0;">
            &copy; 2026 <span style="color:#58a6ff; font-weight:500;">Cybeat</span> 
            Netflix Cookies Checker. All Rights Reserved.
        </p>
    </div>
</footer>

<script>
function fillLogin(u,p){
    document.querySelector('[name="username"]').value = u;
    document.querySelector('[name="password"]').value = p;
}

document.addEventListener("DOMContentLoaded", () => {
    const track = document.getElementById("navTrack");
    if (!track) return;

    const items = Array.from(track.children);
    let totalWidth = track.scrollWidth;
    const screenWidth = window.innerWidth;

    while (totalWidth < screenWidth * 2) {
        items.forEach(item => {
            track.appendChild(item.cloneNode(true));
        });
        totalWidth = track.scrollWidth;
    }

    Array.from(track.children).forEach(item => {
        track.appendChild(item.cloneNode(true));
    });

    const duration = totalWidth / 150;
    track.style.animationDuration = duration + "s";
});   
</script>

</body>
</html>