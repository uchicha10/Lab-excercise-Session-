<?php
require_once "includes/auth.php";
require_once "config/db.php";

redirect_if_logged_in();

$error = "";
$full_name = $username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $username  = trim($_POST["username"] ?? "");
    $password  = $_POST["password"] ?? "";
    $confirm   = $_POST["confirm_password"] ?? "";

    if (!csrf_check()) {
        $error = "Session expired. Please try again.";
    } elseif ($full_name === "" || $username === "" || $password === "" || $confirm === "") {
        $error = "All fields are required.";
    } elseif (strlen($full_name) > 100) {
        $error = "Full name is too long.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $error = "Username must be 3-50 characters: letters, numbers, underscores.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();
        $exists = $check->num_rows > 0;
        $check->close();

        if ($exists) {
            $error = "Username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Public registration always creates a regular user.
            $stmt = $conn->prepare(
                "INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, 'user')"
            );
            $stmt->bind_param("sss", $full_name, $username, $hash);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                $_SESSION["flash_success"] = "Registration successful. You can now log in.";
                header("Location: index.php");
                exit;
            }
            $error = "Registration failed. Please try again.";
        }
    }
}

$pageTitle = "Register";
$bodyClass = "auth-page";
include "includes/header.php";
?>

<div class="card auth-card">
  <div class="card-body p-4 p-md-5">
    <div class="auth-logo"><i class="bi bi-person-plus-fill"></i></div>
    <h3 class="text-center fw-bold mb-1">Create account</h3>
    <p class="text-center text-muted mb-4">It only takes a minute</p>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2" role="alert"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <div class="mb-3">
        <label for="full_name" class="form-label">Full name</label>
        <input type="text" class="form-control" id="full_name" name="full_name"
               maxlength="100" value="<?= e($full_name) ?>" required autofocus>
      </div>

      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username"
               minlength="3" maxlength="50" value="<?= e($username) ?>" required>
        <div class="form-text">Letters, numbers, and underscores only.</div>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="password" name="password" minlength="8" required>
          <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <div class="form-text">At least 8 characters.</div>
      </div>

      <div class="mb-4">
        <label for="confirm_password" class="form-label">Confirm password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
      </div>

      <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">Register</button>
    </form>

    <p class="text-center mt-4 mb-0 text-muted">
      Already registered? <a href="index.php" class="fw-semibold text-decoration-none">Login</a>
    </p>
  </div>
</div>

<?php include "includes/footer.php"; ?>
