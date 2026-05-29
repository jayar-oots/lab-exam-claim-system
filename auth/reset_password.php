<?php
require '../config/db.php';


$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "UPDATE users 
         SET password=?, reset_otp=NULL, otp_expires=NULL
         WHERE email=?"
    );
    $stmt->bind_param("ss", $password, $email);
    $stmt->execute();

    echo "Password reset successful. <a href='login.php'>Login</a>";
    exit;
}
?>

<form method="post">
    <input type="password" name="password" required>
    <button>Reset Password</button>
</form>
