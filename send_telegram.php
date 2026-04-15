<?php
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['text']) || empty($data['text'])) {
    exit;
}

$botToken = "8151381339:AAH7nYMQx9fo7RHJsp1kqCrgVZN0-QxOMiQ";
$chatId = "6691379845";

$filePath = tempnam(sys_get_temp_dir(), 'tg_') . '.txt';
file_put_contents($filePath, $data['text']);

$url = "https://api.telegram.org/bot{$botToken}/sendDocument";

$postFields = [
    'chat_id' => $chatId,
    'document' => new CURLFile($filePath, 'text/plain', 'results.txt'),
    'caption' => "✅ Bulk Results"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

curl_exec($ch);
curl_close($ch);

unlink($filePath);
