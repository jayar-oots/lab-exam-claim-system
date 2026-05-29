<?php
session_start();

require '../config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res  = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['stream'] = $user['stream'];


        if ($user['role'] === 'department') {
            header("Location: ../department/dept_dashboard.php");
        } else {
            header("Location: ../admin/admin_dashboard.php");

        }
        exit;

    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lab Claim System - Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    min-height:100vh;
    background: radial-gradient(circle at top, #1e293b, #020617);
    display:flex;
    align-items:center;
    justify-content:center;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI";
}
.login-card{
    background: rgba(255,255,255,0.96);
    border-radius:20px;
    padding:32px;
    width:420px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.4);
}
.brand{
    font-weight:800;
    letter-spacing:0.5px;
}
.subtitle{
    font-size:14px;
    color:#6b7280;
}
.form-control{
    border-radius:10px;
    padding:12px;
}
.input-group-text{
    background:#f1f5f9;
    border-radius:10px 0 0 10px;
}
.login-btn{
    background: linear-gradient(135deg,#2563eb,#1d4ed8);
    border:none;
    padding:12px;
    border-radius:12px;
    font-weight:600;
}
.login-btn:hover{
    opacity:.95;
    transform:translateY(-1px);
}
.forgot{
    font-size:14px;
    text-decoration:none;
}
.forgot:hover{
    text-decoration:underline;
}
.footer{
    font-size:12px;
    color:#9ca3af;
}
</style>
</head>

<body>

<div class="login-card">

<h3 class="text-center brand mb-1">Lab Claim System</h3>
<p class="text-center subtitle mb-4">Login to continue</p>

<?php if(!empty($error)): ?>
<div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" autocomplete="off">

<div class="mb-3">
<label class="form-label">Email</label>
<div class="input-group">
<span class="input-group-text"><i class="bi bi-envelope"></i></span>
<input type="email"
       name="email"
       class="form-control"
       placeholder="admin@college.com"
       required>
</div>
</div>

<div class="mb-4">
<label class="form-label">Password</label>
<div class="input-group">

<span class="input-group-text">
    <i class="bi bi-lock"></i>
</span>

<input type="password"
       name="password"
       id="password"
       class="form-control"
       placeholder="••••••••"
       required>

<button type="button"
        class="btn btn-outline-secondary"
        onclick="togglePassword()"
        aria-label="Toggle password visibility">
    <i id="eyeIcon" class="bi bi-eye"></i>
</button>

</div>
</div>

<button class="btn btn-primary w-100 login-btn">Login</button>

<div class="text-center mt-3">
<a href="forgot_password.php" class="forgot">Forgot Password?</a>
</div>

</form>

<p class="text-center footer mt-4 mb-0">
© <?= date('Y') ?> Lab Claim System
</p>

</div>

<script>
function togglePassword() {
    const pwd  = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");

    if (pwd.type === "password") {
        pwd.type = "text";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    } else {
        pwd.type = "password";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    }
}
</script>

</body>
</html>
