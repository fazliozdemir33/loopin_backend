<?php
// Adminer'ın PHP 8.x uyumsuzluk warning'lerini ekrandan gizle (loglara yazmaya devam eder)
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
/**
 * Loopin — DB Admin (Adminer Auto-Login Wrapper)
 * Erişim: http://127.0.0.1:8000/dbadmin.php
 * ⚠️  Sadece LOCAL geliştirme için. Production'da silin!
 */

// Laravel .env'den DB bilgilerini oku
$envPath = __DIR__ . '/../.env';
$env = [];
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
        }
    }
}

$dbConnection = $env['DB_CONNECTION'] ?? 'sqlite';
$dbDatabase   = $env['DB_DATABASE']   ?? 'database.sqlite';

// SQLite için tam dosya yolunu çöz
if ($dbConnection === 'sqlite') {
    if (!str_starts_with($dbDatabase, '/')) {
        $dbDatabase = realpath(__DIR__ . '/../database/' . basename($dbDatabase));
    }
    // SQLite: server boş bırakılmalı, db=dosya yolu ile otomatik yönlendir
    if (!isset($_GET['sqlite']) && !isset($_GET['db'])) {
        header("Location: dbadmin.php?sqlite=&db=" . urlencode($dbDatabase));
        exit;
    }
}

// Adminer Auto-Login Plugin — tüm method imzaları parent ile uyumlu
function adminer_object() {
    class AdminerAutoLogin extends Adminer {
        // Her girişi otomatik kabul et
        function login($login, $password) {
            return true;
        }

        // Oturumu kalıcı tut — imza parent ile birebir eşleşmeli
        function permanentLogin($i = false) {
            return 'loopin_dev_token';
        }

        // Panel başlığı
        function name() {
            return '🌸 Loopin — DB Admin';
        }

        // Login formunu gizle ve otomatik submit et (SQLite şifresiz geçiş)
        function loginForm() {
            global $dbDatabase;
            echo '<style>
                #content { display:none !important; }
                body::before { 
                    content: "🌸 Bağlanıyor..."; 
                    display:block; 
                    padding: 40px; 
                    font-size:18px; 
                    font-family: sans-serif;
                    color: #555;
                }
            </style>';
            echo '<script>
                window.onload = function() {
                    var f = document.querySelector("form");
                    if (f) { f.submit(); }
                };
            </script>';
            // Parent form içeriğini gizli render et
            parent::loginForm();
        }
    }
    return new AdminerAutoLogin();
}

// Adminer core
include __DIR__ . '/adminer.php';
