<?php
require_once "includes/auth.php";
require_once "config/db.php";

redirect_if_logged_in();

$error = "";
$success = $_SESSION["flash_success"] ?? "";
unset($_SESSION["flash_success"]);
$old = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $old = $username;

    if (!csrf_check()) {
        $error = "Session expired. Please try again.";
    } elseif ($username === "" || $password === "") {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, full_name, username, password, role
             FROM users WHERE username = ? LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"]   = (int)$user["id"];
            $_SESSION["username"]  = $user["username"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"]      = $user["role"];

            $upd = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $upd->bind_param("i", $user["id"]);
            $upd->execute();
            $upd->close();

            header("Location: home.php");
            exit;
        }
        $error = "Invalid username or password.";
    }
}

$pageTitle = "Login";
$bodyClass = "auth-page";
include "includes/header.php";
?>

<div class="card auth-card">
  <div class="card-body p-4 p-md-5">
    <div class="auth-logo"><i class="bi bi-shield-lock-fill"></i></div>
    <h3 class="text-center fw-bold mb-1">Welcome back</h3>
    <p class="text-center text-muted mb-4">Sign in to your account</p>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2" role="alert"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success py-2" role="alert"><i class="bi bi-check-circle me-1"></i><?= e($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="username" name="username"
                 maxlength="50" value="<?= e($old) ?>" required autofocus>
        </div>
      </div>

      <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-key"></i></span>
          <input type="password" class="form-control" id="password" name="password" required>
          <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">Login</button>
    </form>

    <p class="text-center mt-4 mb-0 text-muted">
      Don't have an account? <a href="register.php" class="fw-semibold text-decoration-none">Register</a>
    </p>
  </div>
</div>

<?php include "includes/footer.php"; ?>
