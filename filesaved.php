<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

session_start();

// 🔒 ADMIN ONLY ACCESS
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

include "auth.php";
requireLogin();

$file = "saved_results.json";

$data = [];

if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) $data = [];
}

/* REMOVE DUPES + EXCLUDE EXPIRED */
$unique = [];
$seen = [];

foreach ($data as $row) {

    if (isset($row['plan']) && strtolower(trim($row['plan'])) === 'expired') {
        continue;
    }

    $id = isset($row['netflixId']) ? trim(strtolower($row['netflixId'])) : '';
    if ($id === '' || $id === 'n/a') continue;

    if (!isset($seen[$id])) {
        $seen[$id] = true;
        $unique[] = $row;
    }
}

file_put_contents($file, json_encode($unique, JSON_PRETTY_PRINT));
$data = $unique;

/* COUNTS */
$today = date("Y-m-d");
$todayCount = 0;

foreach ($data as $row) {
    if (isset($row['time']) && strpos($row['time'], $today) !== false) {
        $todayCount++;
    }
}

$totalCount = count($data);

/* EXPORT ALL */
if (isset($_GET['export']) && $_GET['export'] === 'txt') {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="all.txt"');

    $out = [];
    $count = 1;

    foreach ($data as $row) {
        $out[] = "#{$count}\n📧 {$row['email']}\n📋 {$row['plan']}\n🌍 {$row['country']}\n👤 {$row['profile']}\n🍪 {$row['netflixId']}\nTime: {$row['time']}";
        $count++;
    }

    echo implode("\n\n----------------\n\n", $out);
    exit;
}

/* EXPORT SELECTED */
if (isset($_POST['export_selected'])) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="selected.txt"');

    $selected = $_POST['selected'] ?? [];
    $out = [];
    $count = 1;

    foreach ($data as $row) {
        if (!in_array($row['netflixId'], $selected)) continue;

        $out[] = "#{$count}\n📧Email: {$row['email']}\n📋Plan: {$row['plan']}\n🌍Country: {$row['country']}\n👤Profile: {$row['profile']}\n🍪NetflixId={$row['netflixId']}\nTime: {$row['time']}";
        $count++;
    }

    echo implode("\n\n----------------\n\n", $out);
    exit;
}

/* AJAX */
if (isset($_GET['ajax'])) {
    echo json_encode($data);
    exit;
}

$countries = array_unique(array_column($data, 'country'));
$plans = array_unique(array_column($data, 'plan'));
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Saved Results</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    text-align: center;
    padding: 10px 0;
    background: rgba(13,17,23,0.9);
    border-top: 1px solid #30363d;
    backdrop-filter: blur(10px);
    z-index: 999;
}
</style>
</head>

<body class="bg-[#0b0f14] text-white pb-16">

<form method="POST">

<!-- NAVBAR -->
<div class="sticky top-0 z-50 backdrop-blur-xl bg-white/5 border-b border-white/10 
px-4 md:px-6 py-3 flex flex-col md:flex-row md:flex-wrap gap-3 md:items-center shadow-lg">

<div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
    <a href="dashboard.php" class="text-lg font-bold text-red-400 hover:text-blue-300">
        🍪 Check Cookies
    </a>
    <div class="text-lg font-bold">
        📦 Saved Results
    </div>
</div>

<div class="flex flex-wrap gap-2 w-full md:w-auto">
    <div class="px-3 py-1.5 rounded-full text-xs md:text-sm font-bold bg-gradient-to-r from-green-500 to-emerald-500">
        🆕 Today: <span id="todayCount"><?= $todayCount ?></span>
    </div>

    <div id="totalCount"
        class="px-3 py-1.5 rounded-full text-xs md:text-sm font-bold bg-gradient-to-r from-indigo-500 to-cyan-500">
        🍪 Total: <?= $totalCount ?>
    </div>
</div>

<div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">

    <input id="search" placeholder="🔍 Search..."
        class="bg-black/30 border border-white/10 px-3 py-2 rounded-lg w-full sm:w-56">

    <select id="country" class="bg-black/30 border border-white/10 px-3 py-2 rounded-lg w-full sm:w-40">
        <option value="">🌍 Country</option>
        <?php foreach ($countries as $c): ?>
            <option value="<?= $c ?>"><?= $c ?></option>
        <?php endforeach; ?>
    </select>

    <select id="plan" class="bg-black/30 border border-white/10 px-3 py-2 rounded-lg w-full sm:w-40">
        <option value="">📋 Plan</option>
        <?php foreach ($plans as $p): ?>
            <option value="<?= $p ?>"><?= $p ?></option>
        <?php endforeach; ?>
    </select>

</div>

<div class="flex flex-wrap items-center gap-2 w-full md:w-auto md:ml-auto">

    <div id="cookieCount"
        class="px-3 py-1.5 rounded-full text-xs md:text-sm font-bold bg-gradient-to-r from-indigo-500 to-cyan-500">
        🗂️ 0
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" id="selectAll"> Select
    </label>

    <button name="export_selected"
        class="bg-purple-600 px-3 md:px-4 py-2 rounded-lg font-bold text-sm">
        ⬇ Selected
    </button>

    <a href="?export=txt"
        class="bg-green-600 px-3 md:px-4 py-2 rounded-lg font-bold text-sm text-center">
        ⬇ All
    </a>

</div>

</div>

<!-- CONTENT -->
<div class="p-4 md:p-6">
    <div id="results" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
</div>

</form>

<footer>
    <div class="text-gray-500" style="font-size:12px;">
        <p style="margin:0;">
            &copy; 2026 
            <span style="color:#58a6ff; font-weight:500;">Cybeat</span> 
            Netflix Cookies Checker. All Rights Reserved.
        </p>
    </div>
</footer>

<script>
let fullData = [];
let selectedItems = new Set();

let previousIds = new Set();
let firstLoad = true;

function fetchData() {
    fetch('?ajax=1')
    .then(res => res.json())
    .then(data => {

        let newCount = 0;
        let currentIds = new Set();

        data.forEach(row => {
            currentIds.add(row.netflixId);

            if (!previousIds.has(row.netflixId) && !firstLoad) {
                newCount++;
            }
        });

        if (newCount > 0) showNotification(newCount);

        previousIds = currentIds;
        firstLoad = false;

        fullData = data;

        // 🔥 REAL-TIME TOTAL
        document.getElementById('totalCount').innerText = `🍪 Total: ${data.length}`;

        // 🔥 REAL-TIME TODAY
        let today = new Date().toISOString().split('T')[0];
        let todayCount = data.filter(row => row.time && row.time.includes(today)).length;
        document.getElementById('todayCount').innerText = todayCount;

        render();
    });
}

function showNotification(count) {
    const el = document.createElement('div');
    el.innerText = `🆕 ${count} new cookies`;
    el.className = "fixed top-20 right-4 bg-green-500 text-black px-4 py-3 rounded-xl font-bold shadow-lg animate-bounce z-50";
    document.body.appendChild(el);

    setTimeout(() => el.remove(), 3000);
}

function render() {
    let search = document.getElementById('search').value.toLowerCase();
    let country = document.getElementById('country').value;
    let plan = document.getElementById('plan').value;

    let html = '';
    let visibleCount = 0;
    let count = 1;

    fullData.forEach(row => {

        if (country && row.country !== country) return;
        if (plan && row.plan !== plan) return;

        let combined = (row.email + row.netflixId).toLowerCase();
        if (search && !combined.includes(search)) return;

        visibleCount++;

        let checked = selectedItems.has(row.netflixId) ? 'checked' : '';

        html += `
        <div class="bg-white/5 border border-white/10 p-4 rounded-xl hover:scale-[1.02] transition">
            <div class="flex justify-between mb-2">
                <strong>#${count}</strong>
                <input type="checkbox" class="itemCheckbox" name="selected[]" value="${row.netflixId}" ${checked}>
            </div>

            <div class="text-sm space-y-1">
                <p>📧Email: ${row.email}</p>
                <p>📋Plan: ${row.plan}</p>
                <p>🌍Country: ${row.country}</p>
                <p>👤Profile: ${row.profile}</p>
                <p class="text-green-400 break-all">🍪NetflixId=${row.netflixId}</p>
            </div>

            <p class="text-xs text-gray-500 mt-2">${row.time}</p>
        </div>
        `;
        count++;
    });

    document.getElementById('results').innerHTML =
        html || '<p class="text-gray-400">No results found.</p>';

    document.getElementById('cookieCount').innerText = `🗂️ ${visibleCount}`;

    attachCheckboxEvents();
    updateSelectAllState();
}

document.getElementById('search').addEventListener('input', render);
document.getElementById('country').addEventListener('change', render);
document.getElementById('plan').addEventListener('change', render);

document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.itemCheckbox').forEach(cb => {
        cb.checked = this.checked;
        this.checked ? selectedItems.add(cb.value) : selectedItems.delete(cb.value);
    });
});

function attachCheckboxEvents() {
    document.querySelectorAll('.itemCheckbox').forEach(cb => {
        cb.addEventListener('change', () => {
            cb.checked ? selectedItems.add(cb.value) : selectedItems.delete(cb.value);
            updateSelectAllState();
        });
    });
}

function updateSelectAllState() {
    let all = document.querySelectorAll('.itemCheckbox');
    let checked = document.querySelectorAll('.itemCheckbox:checked');
    let selectAll = document.getElementById('selectAll');

    selectAll.checked = checked.length === all.length && all.length > 0;
    selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
}

setInterval(fetchData, 5000);
fetchData();
</script>

</body>
</html>