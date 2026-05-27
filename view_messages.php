<?php
session_start();

$file = 'messages.json';
$correctPassword = 'Hossein123'; // 👈 رمز رو عوض کن

// اگه لاگین کرده بود
$isLoggedIn = $_SESSION['admin_logged_in'] ?? false;

// خروج از لاگین
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// چک کردن لاگین
if (isset($_POST['password'])) {
    if ($_POST['password'] === $correctPassword) {
        $_SESSION['admin_logged_in'] = true;
        $isLoggedIn = true;
    } else {
        $loginError = '❌ رمز عبور اشتباه است!';
    }
}

// حذف پیام
if ($isLoggedIn && isset($_GET['delete']) && isset($_GET['id'])) {
    $deleteId = $_GET['id'];
    if (file_exists($file)) {
        $messages = json_decode(file_get_contents($file), true) ?? [];
        $messages = array_filter($messages, function($msg) use ($deleteId) {
            return $msg['id'] != $deleteId;
        });
        $messages = array_values($messages);
        file_put_contents($file, json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// خوندن پیام‌ها
$messages = [];
if ($isLoggedIn && file_exists($file)) {
    $content = file_get_contents($file);
    $messages = json_decode($content, true) ?? [];
}
$totalMessages = count($messages);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👻 پیام‌های ناشناس • پنل مدیریت</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0a0f;
            color: #e7e9ea;
            font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 700px;
        }
        
        /* ========== LOGIN PAGE ========== */
        .login-box {
            background: #16181c;
            border: 1px solid #2f3336;
            border-radius: 18px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .login-icon {
            font-size: 4rem;
            display: block;
            margin-bottom: 20px;
        }
        
        .login-box h1 {
            color: #e7e9ea;
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        
        .login-box p {
            color: #71767b;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 16px;
        }
        
        .input-group input {
            width: 100%;
            padding: 14px 50px 14px 16px;
            background: #0a0a0f;
            border: 1px solid #2f3336;
            border-radius: 12px;
            color: #e7e9ea;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }
        
        .input-group input::placeholder {
            color: #5b5b70;
        }
        
        .input-group .icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #5b5b70;
            font-size: 1.2rem;
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }
        
        .login-btn:hover {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
        }
        
        .login-btn:active {
            transform: scale(0.97);
        }
        
        .error-msg {
            background: #1a0a0a;
            border: 1px solid #3f1111;
            color: #e0245e;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        
        /* ========== DASHBOARD ========== */
        .dashboard {
            display: none;
        }
        
        .dashboard.active {
            display: block;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .header h1 {
            color: #8b5cf6;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logout-btn {
            background: #1a0a0a;
            border: 1px solid #3f1111;
            color: #e0245e;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .logout-btn:hover {
            background: #2d0a0a;
            border-color: #e0245e;
        }
        
        .stats {
            color: #71767b;
            font-size: 0.9rem;
            margin-bottom: 20px;
            padding: 14px 18px;
            background: #16181c;
            border-radius: 12px;
            border: 1px solid #2f3336;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .stats span {
            color: #8b5cf6;
            font-weight: 700;
        }
        
        .refresh-link {
            color: #1d9bf0;
            text-decoration: none;
            font-size: 0.85rem;
            cursor: pointer;
        }
        
        .refresh-link:hover {
            text-decoration: underline;
        }
        
        .msg-card {
            background: #16181c;
            border: 1px solid #2f3336;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 12px;
            transition: all 0.2s;
            position: relative;
        }
        
        .msg-card:hover {
            border-color: #8b5cf6;
        }
        
        .msg-text {
            font-size: 1rem;
            line-height: 1.8;
            color: #e7e9ea;
            margin-bottom: 12px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .msg-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #536471;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .msg-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .msg-id {
            color: #3f4447;
            font-size: 0.7rem;
        }
        
        .delete-btn {
            background: #1a0a0a;
            border: 1px solid #3f1111;
            color: #e0245e;
            padding: 6px 14px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.75rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .delete-btn:hover {
            background: #2d0a0a;
            border-color: #e0245e;
        }
        
        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #536471;
        }
        
        .empty .icon {
            font-size: 4rem;
            display: block;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        @media (max-width: 500px) {
            .login-box { padding: 30px 20px; }
            .header h1 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$isLoggedIn): ?>
        <!-- ========== صفحه لاگین ========== -->
        <div class="login-box">
            <span class="login-icon">👻</span>
            <h1>پنل مدیریت پیام‌های ناشناس</h1>
            <p>برای دیدن پیام‌ها رمز عبور رو وارد کن</p>
            
            <?php if (isset($loginError)): ?>
            <div class="error-msg"><?= $loginError ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="input-group">
                    <input type="password" name="password" placeholder="رمز عبور..." required autofocus>
                    <span class="icon">🔒</span>
                </div>
                <button type="submit" class="login-btn">
                    🚀 ورود به پنل
                </button>
            </form>
        </div>
        
        <?php else: ?>
        <!-- ========== داشبورد ========== -->
        <div class="dashboard active">
            <div class="header">
                <h1>
                    <span>👻</span>
                    پیام‌های ناشناس
                </h1>
                <a href="?logout=1" class="logout-btn">
                    🚪 خروج
                </a>
            </div>
            
            <div class="stats">
                <div>📬 تعداد پیام‌ها: <span><?= $totalMessages ?></span></div>
                <a href="<?= $_SERVER['PHP_SELF'] ?>" class="refresh-link">🔄 بروزرسانی</a>
            </div>
            
            <?php if ($totalMessages === 0): ?>
            <div class="empty">
                <span class="icon">📭</span>
                <p>هنوز هیچ پیامی دریافت نشده.</p>
                <p style="font-size: 0.8rem; margin-top: 8px;">منتظر اولین پیام ناشناس باش...</p>
            </div>
            <?php else: ?>
                <?php foreach (array_reverse($messages) as $index => $msg): ?>
                <div class="msg-card">
                    <div class="msg-text"><?= nl2br(htmlspecialchars($msg['text'])) ?></div>
                    <div class="msg-footer">
                        <div class="msg-date">
                            📅 <?= $msg['date'] ?>
                            <span class="msg-id">#<?= $totalMessages - $index ?></span>
                        </div>
                        <a href="?delete=1&id=<?= $msg['id'] ?>" 
                           class="delete-btn"
                           onclick="return confirm('مطمئنی می‌خوای این پیام رو حذف کنی؟\nاین کار قابل برگشت نیست.')">
                            🗑️ حذف
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>