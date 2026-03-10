<?php
error_reporting(0);
ini_set('display_errors', 0);

$host     = 'localhost';
$dbname   = 'batiments_ruine';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("
    <div style='font-family:Arial;padding:30px;text-align:center'>
        <h2 style='color:red'>❌ خطأ في الاتصال بقاعدة البيانات</h2>
        <p style='color:#666;margin:10px 0'>" . $e->getMessage() . "</p>
        <a href='init_db.php'
           style='background:#1a3c5e;color:white;padding:10px 20px;
                  border-radius:6px;text-decoration:none'>
            🔧 إنشاء قاعدة البيانات
        </a>
    </div>");
}
?>