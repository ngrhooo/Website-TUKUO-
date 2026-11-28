<?php
require_once "config.php";
ensure_session();
if (isset($_SESSION["user_id"])) { header("Location: index.php"); exit; }

$error = "";
$success = "";
$email = "";
if (isset($_GET["registered"])) $success = "Akun berhasil dibuat. Silakan login.";
if (isset($_GET["password_updated"])) $success = "Password berhasil diperbarui. Silakan login dengan password baru.";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    if (!csrf_is_valid()) {
        $error = "Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.";
    } else {
        $stmt = db()->prepare("SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([":email" => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
            header("Location: index.php");
            exit;
        }
        $error = "Email atau password belum sesuai.";
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — TUKUO!</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="auth-screen">
    <a class="auth-brand" href="index.php"><span class="auth-brand-name">TUKUO!</span><span class="auth-brand-est">EST. 2028</span></a>
    <section class="auth-card"><div class="auth-card-inner">
      <h1 class="auth-heading">Welcome Back</h1>
      <p class="auth-subheading">Login to your account</p>
      <?php if ($success): ?><p class="auth-message success"><?= e($success) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="auth-message"><?= e($error) ?></p><?php endif; ?>
      <form class="auth-form" action="login.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_token"]) ?>">
        <label class="auth-field"><input type="email" name="email" placeholder="Enter Email" autocomplete="email" required value="<?= e($email) ?>"></label>
        <label class="auth-field"><input id="login-password" type="password" name="password" placeholder="Password" autocomplete="current-password" required></label>
        <div class="auth-forgot-row"><a href="new-password.php">Forgot Password?</a></div>
        <button class="button auth-submit" type="submit">Login</button>
      </form>
      <p class="auth-switch">Don’t have an account? <a href="register.php">Sign Up</a></p>
    </div></section>
    <p class="auth-footer">EST. 2028 — TUKUO!</p>
  </main>
  <script src="auth.js"></script>
</body>
</html>
