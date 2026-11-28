<?php
require_once "config.php";
ensure_session();
if (isset($_SESSION["user_id"])) { header("Location: index.php"); exit; }

$error = "";
$email = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["new_password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if (!csrf_is_valid()) $error = "Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Masukkan email akun yang terdaftar.";
    elseif (strlen($password) < 8) $error = "Password baru minimal 8 karakter.";
    elseif ($password !== $confirm) $error = "Konfirmasi password baru tidak sama.";
    else {
        $check = db()->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $check->execute([":email" => $email]);
        if (!$check->fetch()) {
            $error = "Email belum terdaftar. Periksa kembali email akunmu.";
        } else {
            $update = db()->prepare("UPDATE users SET password = :password WHERE email = :email");
            $update->execute([
                ":password" => password_hash($password, PASSWORD_DEFAULT),
                ":email" => $email,
            ]);
            header("Location: login.php?password_updated=1");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Password — TUKUO!</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="auth-screen">
    <a class="auth-brand" href="index.php"><span class="auth-brand-name">TUKUO!</span><span class="auth-brand-est">EST. 2028</span></a>
    <section class="auth-card"><div class="auth-card-inner">
      <h1 class="auth-heading">New Password</h1>
      <p class="auth-subheading">Create a new password for your account</p>
      <p class="auth-note">Masukkan email akun dan password baru kamu.</p>
      <?php if ($error): ?><p class="auth-message"><?= e($error) ?></p><?php endif; ?>
      <form class="auth-form" action="new-password.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_token"]) ?>">
        <label class="auth-field"><input type="email" name="email" placeholder="Account Email" autocomplete="email" required value="<?= e($email) ?>"></label>
        <label class="auth-field"><input id="new-password" type="password" name="new_password" placeholder="New Password" minlength="8" autocomplete="new-password" required></label>
        <label class="auth-field"><input id="confirm-new-password" type="password" name="confirm_password" placeholder="Confirm New Password" minlength="8" autocomplete="new-password" required></label>
        <button class="button auth-submit" type="submit">Update Password</button>
      </form>
      <a class="auth-back" href="login.php">← Back to Login</a>
    </div></section>
    <p class="auth-footer">EST. 2028 — TUKUO!</p>
  </main>
  <script src="auth.js"></script>
</body>
</html>
