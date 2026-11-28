<?php
declare(strict_types=1);
define("DB_HOST", "localhost");
define("DB_NAME", "tukuo_db");
define("DB_USER", "root");
define("DB_PASS", "");

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
function ensure_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION["csrf_token"])) $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
function csrf_is_valid(): bool {
    return isset($_POST["csrf_token"], $_SESSION["csrf_token"])
        && hash_equals($_SESSION["csrf_token"], (string)$_POST["csrf_token"]);
}
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}
function is_admin(): bool {
    if (!isset($_SESSION["user_id"])) return false;
    $stmt = db()->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    return $stmt->fetchColumn() === "admin";
}
