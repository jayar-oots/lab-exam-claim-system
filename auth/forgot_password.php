<?php
session_start();

require '../config/db.php';
require '../config/mail_config.php';

// 🔄 Clear stale OTP session on fresh page load
if (
    !isset($_POST['send_otp']) &&
    !isset($_POST['verify_otp']) &&
    !isset($_POST['resend_otp']) &&
    !isset($_POST['reset_password'])
) {
    unset($_SESSION['otp_sent'], $_SESSION['otp_verified'], $_SESSION['reset_email']);
}



error_reporting(E_ALL);
ini_set('display_errors', 1);

$msg  = "";
$step = 1;

if (isset($_SESSION['otp_sent'])) {
    $step = 2;
}
if (isset($_SESSION['otp_verified'])) {
    $step = 3;
}


/* ================= STEP 1 : SEND OTP ================= */
if (isset($_POST['send_otp'])) {

    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {

        $otp     = rand(100000, 999999);
        $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $stmt = $conn->prepare(
            "UPDATE users SET reset_otp=?, otp_expires=? WHERE email=?"
        );
        $stmt->bind_param("sss", $otp, $expires, $email);
        $stmt->execute();

        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_sent']   = true;
        $step = 2;

        if (sendOTP($email, $otp)) {
            $msg = "<div class='alert alert-success'>OTP OTP sent to your email</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Failed to send OTP email</div>";
        }

    } else {
        $msg = "<div class='alert alert-danger'>Email not found</div>";
    }
}

/* ================= STEP 2 : VERIFY OTP ================= */
if (isset($_POST['verify_otp']) && isset($_SESSION['otp_sent'])) {

    $step = 2;

    if (empty($_POST['otp'])) {
        $msg = "<div class='alert alert-warning'>Please enter OTP</div>";
    } else {

        if (empty($_SESSION['reset_email'])) {
            $msg  = "<div class='alert alert-danger'>Session expired. Request OTP again.</div>";
            $step = 1;
            unset($_SESSION['otp_sent']);
        } else {

            $email = $_SESSION['reset_email'];
            $otp   = trim($_POST['otp']);
$now = date("Y-m-d H:i:s");

$stmt = $conn->prepare(
    "SELECT id FROM users
     WHERE email=?
     AND reset_otp=?
     AND otp_expires > ?"
);
$stmt->bind_param("sss", $email, $otp, $now);
$stmt->execute();
$res = $stmt->get_result();

            if ($res->num_rows === 1) {
                $_SESSION['otp_verified'] = true;
                $step = 3;
            } else {
                $msg = "<div class='alert alert-danger'>Invalid or expired OTP</div>";
            }
        }
    }
}

/* ================= STEP 3 : RESET PASSWORD ================= */
if (isset($_POST['reset_password'])) {

    if (!isset($_SESSION['otp_verified'])) {
        die("Unauthorized access");
    }

    $email = $_SESSION['reset_email'];
    $hash  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "UPDATE users
         SET password=?, reset_otp=NULL, otp_expires=NULL
         WHERE email=?"
    );
    $stmt->bind_param("ss", $hash, $email);
    $stmt->execute();

    unset($_SESSION['otp_sent'], $_SESSION['otp_verified'], $_SESSION['reset_email']);
    session_destroy();

    header("Location: login.php");
    exit;
}

/* ================= RESEND OTP ================= */
if (isset($_POST['resend_otp']) && isset($_SESSION['otp_sent'])) {

    $email = $_SESSION['reset_email'];

    $otp     = rand(100000, 999999);
    $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $stmt = $conn->prepare(
        "UPDATE users SET reset_otp=?, otp_expires=? WHERE email=?"
    );
    $stmt->bind_param("sss", $otp, $expires, $email);
    $stmt->execute();

    unset($_SESSION['otp_verified']);
    $step = 2;

    if (sendOTP($email, $otp)) {
        $msg = "<div class='alert alert-info'>New OTP sent to your email</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to resend OTP</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    min-height:100vh;
    background: radial-gradient(circle at top, #1e293b, #020617);
    display:flex;
    align-items:center;
    justify-content:center;
}
.card{
    width:420px;
    border-radius:20px;
    box-shadow:0 25px 50px rgba(0,0,0,.4);
}
</style>
</head>

<body>
<div class="card p-4 bg-white">
<h4 class="text-center mb-3">Forgot Password</h4>

<?= $msg ?>

<?php if ($step === 1): ?>
<form method="post">
    <input type="email" name="email" class="form-control mb-3" placeholder="Registered Email" required>
    <button class="btn btn-primary w-100" name="send_otp">Send OTP</button>
</form>

<?php elseif ($step === 2): ?>
<form method="post">
    <input type="text" name="otp" class="form-control mb-3"
           placeholder="Enter OTP" required maxlength="6" inputmode="numeric">
    <button class="btn btn-success w-100 mb-2" name="verify_otp">Verify OTP</button>
    <button class="btn btn-link w-100" name="resend_otp">Resend OTP</button>
</form>

<?php elseif ($step === 3): ?>
<form method="post">
    <input type="password" name="password" class="form-control mb-3"
           placeholder="New Password" required>
    <button class="btn btn-dark w-100" name="reset_password">Reset Password</button>
</form>
<?php endif; ?>

<div class="text-center mt-3">
    <a href="login.php">← Back to Login</a>
</div>
</div>
</body>
</html>
