<?php
require_once "config.php";
ensure_session();
if (isset($_SESSION["user_id"])) { header("Location: index.php"); exit; }

$error = "";
$name = "";
$email = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if (!csrf_is_valid()) $error = "Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.";
    elseif ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Nama dan email harus diisi dengan benar.";
    elseif (strlen($password) < 8) $error = "Password minimal 8 karakter.";
    elseif ($password !== $confirm) $error = "Konfirmasi password tidak sama.";
    else {
        try {
            $stmt = db()->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":password" => password_hash($password, PASSWORD_DEFAULT),
            ]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() === "23000") $error = "Email sudah terdaftar. Silakan login atau gunakan email lain.";
            else $error = "Registrasi gagal. Periksa koneksi database TUKUO.";
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — TUKUO!</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="auth-screen">
    <a class="auth-brand" href="index.php"><span class="auth-brand-name">TUKUO!</span><span class="auth-brand-est">EST. 2028</span></a>
    <section class="auth-card"><div class="auth-card-inner">
      <h1 class="auth-heading">Create Account</h1>
      <p class="auth-subheading">Register and be part of TUKUO!</p>
      <?php if ($error): ?><p class="auth-message"><?= e($error) ?></p><?php endif; ?>
      <form class="auth-form" action="register.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_token"]) ?>">
        <label class="auth-field"><input type="text" name="name" placeholder="Full Name" maxlength="100" autocomplete="name" required value="<?= e($name) ?>"></label>
        <label class="auth-field"><input type="email" name="email" placeholder="Enter Email" maxlength="190" autocomplete="email" required value="<?= e($email) ?>"></label>
        <label class="auth-field"><input id="register-password" type="password" name="password" placeholder="Password (min. 8 characters)" minlength="8" autocomplete="new-password" required></label>
        <label class="auth-field"><input id="register-confirm" type="password" name="confirm_password" placeholder="Confirm Password" minlength="8" autocomplete="new-password" required></label>
        <button class="button auth-submit" type="submit">Sign Up</button>
      </form>
      <p class="auth-switch">Already have an account? <a href="login.php">Login</a></p>
    </div></section>
    <p class="auth-footer">EST. 2028 — TUKUO!</p>
  </main>
  <script src="auth.js"></script>
</body>
</html>
