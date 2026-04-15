<?php
// 🔥 STEP 1: NO CACHE (PUT HERE FIRST)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// 🔥 STEP 2: SESSION + AUTH
session_start();
include "auth.php";
requireLogin();

$file = "saved_results.json";

$data = [];

if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) $data = [];
}

// 🔥 REMOVE DUPLICATES (by netflixId)
$unique = [];
$seen = [];

foreach ($data as $row) {

    $id = isset($row['netflixId']) 
        ? trim(strtolower($row['netflixId'])) 
        : '';

    // skip invalid
    if ($id === '' || $id === 'n/a') continue;

    if (!isset($seen[$id])) {
        $seen[$id] = true;
        $unique[] = $row;
    }
}

// ✅ Rewrite cleaned data back to file
file_put_contents($file, json_encode($unique, JSON_PRETTY_PRINT));

// Replace data for display
$data = $unique;

if (isset($_GET['export']) && $_GET['export'] === 'txt') {

    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="saved_results.txt"');

    $bulkResults = [];

    $count = 1;

    foreach ($data as $row) {
        $bulkResults[] =
"#{$count}
📧 Email: {$row['email']}
📋 Plan: {$row['plan']}
🌍 Country: {$row['country']}
👤 Profile: {$row['profile']}
🍪 NetflixId= {$row['netflixId']}
Time: {$row['time']}";
        $count++;
    }
    
    // Header (nice touch 👇)
    $finalText = "📦 BULK RESULTS\n\n" . implode("\n\n----------------------\n\n", $bulkResults);

    echo $finalText;
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saved Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background:#0d1117; color:#fff; padding:20px;">

<h2 style="font-size:22px; margin-bottom:20px;">📦 Saved Results</h2>

<a href="index.php" style="color:#58a6ff;">⬅ Back</a>
<a href="?export=txt" style="
    display:inline-block;
    margin-left:15px;
    background:#238636;
    padding:8px 14px;
    border-radius:8px;
    color:white;
    text-decoration:none;
    font-weight:bold;
">
    ⬇ Export as TXT
</a>
<div style="margin-top:20px;">
<?php if (empty($data)): ?>
    <p>No saved results yet.</p>
<?php else: ?>
<?php $count = 1; ?>
<?php foreach ($data as $row): ?>
    <div style="
        background:#161b22;
        border:1px solid #30363d;
        padding:15px;
        margin-bottom:10px;
        border-radius:10px;
    ">
        <strong>#<?= $count ?></strong><br>

        <strong>📧 Email:</strong> <?= htmlspecialchars($row['email']) ?><br>
        <strong>📋 Plan:</strong> <?= htmlspecialchars($row['plan']) ?><br>
        <strong>🌍 Country:</strong> <?= htmlspecialchars($row['country']) ?><br>
        <strong>👤 Profile:</strong> <?= htmlspecialchars($row['profile']) ?><br>
        <strong>🍪 NetflixId: </strong><?= htmlspecialchars($row['netflixId']) ?><br>
        <small>🕒 <?= $row['time'] ?></small>
    </div>
<?php $count++; ?>
<?php endforeach; ?>
<?php endif; ?>
</div>

</body>
</html>
