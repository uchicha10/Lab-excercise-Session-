<?php
require_once "includes/auth.php";
require_once "config/db.php";

require_login();

$isAdmin = $_SESSION["role"] === "admin";

// Fresh data from the DB for the logged-in user
$stmt = $conn->prepare("SELECT full_name, username, role, created_at, last_login FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$me) { // account was deleted while logged in
    $_SESSION = [];
    session_destroy();
    header("Location: index.php");
    exit;
}

// Admin-only data
$users = [];
$totalUsers = $totalAdmins = 0;
if ($isAdmin) {
    $res = $conn->query("SELECT id, full_name, username, role, created_at, last_login FROM users ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
        $totalUsers++;
        if ($row["role"] === "admin") $totalAdmins++;
    }
}

$fmt = fn(?string $d) => $d ? date("M j, Y g:i A", strtotime($d)) : "—";

$pageTitle = $isAdmin ? "Admin Dashboard" : "Home";
$bodyClass = "home-page";
include "includes/header.php";
?>

<nav class="navbar navbar-dark navbar-brand-custom shadow-sm">
  <div class="container">
    <span class="navbar-brand fw-bold"><i class="bi bi-shield-check me-2"></i>Login System</span>
    <div class="d-flex align-items-center gap-3">
      <span class="text-white-50 d-none d-sm-inline">
        <i class="bi bi-person-circle me-1"></i><?= e($me["username"]) ?>
        <span class="badge <?= $isAdmin ? "bg-warning text-dark" : "bg-light text-dark" ?> ms-1"><?= e($me["role"]) ?></span>
      </span>
      <a href="logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
  </div>
</nav>

<main class="container py-4 py-md-5">

  <div class="welcome-banner p-4 p-md-5 mb-4">
    <h2 class="fw-bold mb-1">Welcome, <?= e($me["full_name"]) ?>! 👋</h2>
    <p class="mb-0 opacity-75">
      <?= $isAdmin ? "You are logged in as an administrator." : "You are logged in as a regular user." ?>
    </p>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-person-badge"></i></div>
        <div><div class="text-muted small">Username</div><div class="fw-semibold"><?= e($me["username"]) ?></div></div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-calendar-check"></i></div>
        <div><div class="text-muted small">Member since</div><div class="fw-semibold"><?= e($fmt($me["created_at"])) ?></div></div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-clock-history"></i></div>
        <div><div class="text-muted small">Last login</div><div class="fw-semibold"><?= e($fmt($me["last_login"])) ?></div></div>
      </div></div>
    </div>
  </div>

  <?php if ($isAdmin): ?>
    <div class="row g-3 mb-4">
      <div class="col-sm-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
          <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-people"></i></div>
          <div><div class="text-muted small">Total accounts</div><div class="fs-4 fw-bold"><?= $totalUsers ?></div></div>
        </div></div>
      </div>
      <div class="col-sm-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
          <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-person-gear"></i></div>
          <div><div class="text-muted small">Administrators</div><div class="fs-4 fw-bold"><?= $totalAdmins ?></div></div>
        </div></div>
      </div>
    </div>

    <div class="card stat-card">
      <div class="card-header bg-white fw-semibold py-3"><i class="bi bi-table me-2"></i>Registered users (admin only)</div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr><th>#</th><th>Name</th><th>Username</th><th>Role</th><th>Registered</th><th>Last login</th></tr>
          </thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int)$u["id"] ?></td>
              <td><?= e($u["full_name"]) ?></td>
              <td><?= e($u["username"]) ?></td>
              <td><span class="badge <?= $u["role"] === "admin" ? "bg-warning text-dark" : "bg-secondary" ?>"><?= e($u["role"]) ?></span></td>
              <td><?= e($fmt($u["created_at"])) ?></td>
              <td><?= e($fmt($u["last_login"])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="card stat-card">
      <div class="card-body p-4">
        <h5 class="fw-bold"><i class="bi bi-house-heart me-2 text-primary"></i>Your home page</h5>
        <p class="text-muted mb-0">This is your personal area. Administrator-only content such as the user list is hidden from regular accounts.</p>
      </div>
    </div>
  <?php endif; ?>

</main>

<?php include "includes/footer.php"; ?>
