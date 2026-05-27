<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// اگه OPTIONS بود (preflight request) رد کن
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// دریافت پیام (هم POST هم GET رو قبول کنه)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $message = trim($_GET['message'] ?? '');
}

// اعتبارسنجی
if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'پیام نمی‌تونه خالی باشه']);
    exit;
}

if (mb_strlen($message) > 500) {
    http_response_code(400);
    echo json_encode(['error' => 'حداکثر ۵۰۰ کاراکتر']);
    exit;
}

// فیلتر کاراکترهای خطرناک
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// فایل ذخیره‌سازی
$file = 'messages.json';

// خوندن پیام‌های قبلی
$messages = [];
if (file_exists($file)) {
    $content = file_get_contents($file);
    $messages = json_decode($content, true);
    if (!is_array($messages)) {
        $messages = [];
    }
}

// اضافه کردن پیام جدید
$messages[] = [
    'id' => time(),
    'text' => $message,
    'date' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];

// ذخیره تو فایل
if (file_put_contents($file, json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'پیام با موفقیت ذخیره شد'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'خطا تو ذخیره‌سازی']);
}
?>