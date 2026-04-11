<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cybeat Premium Netflix Cookies Checker</title>

<link rel="icon" href="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
body { 
    background: #2b2b2b; 
    color: #c9d1d9; 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    padding-top: 120px; /* 🔥 FIXED SPACING */
}

.card {
    background: #161b22;
    border: 1px solid #30363d;
    border-radius: 15px;

    box-shadow: 0 0 10px rgba(0,0,0,0.4);
    transition: all 0.2s ease;
}

/* 🔥 SAME HOVER AS RESULT BOX */
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 20px rgba(88,166,255,0.2);
}

/* 🔥 AUTO LOOP NAV */
.nav-marquee {
    overflow: hidden;
    width: 100%;
}

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

/* MOBILE SAFE */
@media (max-width: 576px) {
    body { padding-top: 130px; }
}
    
.nft-btn {
    position: relative;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 14px;
    color: #fff;
    background: linear-gradient(135deg, #e50914, #b20710);
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 0 12px rgba(229, 9, 20, 0.4);
}

.nft-btn .btn-content {
    position: relative;
    z-index: 2;
}

.nft-btn .btn-glow {
    position: absolute;
    top: 0;
    left: -50%;
    width: 200%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.25), transparent);
    transform: skewX(-20deg);
    transition: 0.5s;
}

.nft-btn:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 0 20px rgba(229, 9, 20, 0.7);
}

.nft-btn:hover .btn-glow {
    left: 100%;
}    
 
.result-box {
    background: #0d1117;
    border: 1px solid #30363d;
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 15px;
    box-shadow: 0 0 10px rgba(0,0,0,0.4);
    transition: all 0.2s ease;
}

.result-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 20px rgba(88,166,255,0.2);
}

.result-box {
    animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}    

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 18px rgba(255,122,0,0.7);
}  
    
/* 🔥 NAVBAR GLOW (MATCH INDEX.PHP) */
.nav-glow {
    box-shadow: 0 0 12px rgba(88,166,255,0.25);
    transition: all 0.3s ease;
}

/* glow stronger on hover */
.nav-glow:hover {
    box-shadow: 0 0 25px rgba(88,166,255,0.6);
}
    
</style>

</head>

<body>
<div id="top"></div>

<!-- 🔥 NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top nav-glow"
     style="background:#161b22; border-bottom:1px solid #30363d;">
<div class="container-fluid px-0 d-flex flex-wrap align-items-center justify-content-between">

<!-- LEFT -->
<div class="d-flex align-items-center flex-wrap">

<!-- 🔥 AUTO LOOP LOGO -->
<div class="nav-marquee">
    <div class="nav-track" id="navTrack">

        <!-- ONLY ONE ITEM -->
        <div class="nav-item">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size:25px; font-weight:bold; color:#ffffff;">
                Cookies Checker
            </span>
        </div>

    </div>
</div>

</div>

<!-- RIGHT -->
<span style="font-size:12px; color:#8b949e;">
Powered by: <b>Cybeat</b>
</span>

</div>
</nav>

<!-- 🔥 CONTENT (ADDED EXTRA GAP) -->
<div class="container-fluid px-4 pb-2" style="margin-top: 5px;">
<div class="row justify-content-center">
<div class="col-md-9">

<div class="text-center mb-3">
<h2 class="font-weight-bold text-white mb-2" style="font-size:28px;">
<p class="text-gray-400">
<p class="text-gray-400" style="font-size:14px; opacity:0.8;">
<i class="fas fa-book-open mr-2 text-purple-400"></i> HOW TO USE THE CHECKER
</h2>

<!-- 🔥 PREMIUM LINE (PUT HERE) -->
<div style="height:3px; width:200px; margin:10px auto;
background:linear-gradient(90deg,#e50914,#58a6ff);
border-radius:10px;"></div>    
    
<p class="text-gray-400">Follow this guide to get the best experience on PC, Mobile, and TV.</p>
</div>

<div class="card mb-4">
<div class="card-body p-4">

<h5 class="text-white font-weight-bold mb-4 border-bottom border-secondary pb-2">
<i class="fas fa-play-circle mr-2 text-purple-400"></i> 1. How to Watch (Access Links)
</h5>

<div class="row">

<div class="col-md-4 mb-4">
<div class="result-box h-100">
<div class="btn w-100 mb-3" style="background:#22c55e; color:#000;">
<i class="fas fa-desktop"></i> PC
</div>
<p class="small">For PC or Laptop users, simply click the <strong>PC</strong> button to instantly log in and start watching on your browser.</p>
</div>
</div>

<div class="col-md-4 mb-4">
<div class="result-box h-100">
<div class="btn w-100 mb-3" style="background:#f59e0b; color:#000;">
<i class="fas fa-mobile-alt"></i> Mobile
</div>
<p class="small">
  Click the <strong>Mobile</strong> button. It works perfectly with the official mobile app, but if you are using a browser, you <u>must</u> use <strong>Google Chrome</strong> on your phone. <em><strong class="text-danger"> Safari is NOT supported.</strong></em>
</p>
</div>
</div>

<div class="col-md-4 mb-4">
<div class="result-box h-100">
<div class="btn w-100 mb-3" style="background:#9333ea; color:#000;">
<i class="fas fa-tv"></i> TV
</div>
<p class="small">Open the Netflix app on your TV and click "Sign In" to generate a code on your TV screen. Then, click the <strong>TV</strong> button here and enter that code in your browser.</p>
</div>
</div>

</div>

</div>
</div>

<!-- 🔥 RETURN HOME BUTTON (RELOCATED) -->
<div class="text-center mb-4">
    <a href="index.php"
       class="btn px-4 py-2"
       style="
       font-weight:600;
       border-radius:50px;
       background:linear-gradient(135deg,#ff7a00,#ff5500);
       color:#fff;
       box-shadow:0 0 10px rgba(255,122,0,0.4);
       transition:all 0.25s ease;">
       
       <i class="fas fa-arrow-left mr-1"></i> Back to Checker
    </a>
</div>    
<hr style="border-color:#4a455a;">

<div class="text-center text-gray-500" style="font-size:12px;">
<p>&copy; Cybeat Netflix Cookies Checker. All Rights Reserved.</p>
</div>    
</div>
</div>
</div>

<!-- 🔥 AUTO LOOP SCRIPT -->
<script>
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

    const duration = totalWidth / 80;
    track.style.animationDuration = duration + "s";
});
</script>

</body>
</html>