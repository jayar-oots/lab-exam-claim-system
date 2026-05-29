<?php
require '../auth_check.php';
require '../config/db.php';


$stmt = $conn->prepare("
INSERT INTO claims (exam_id, paper_count_fn, paper_count_an, status, created_by)
VALUES (?, ?, ?, 'paper_entered', ?)
");

$stmt->bind_param(
"iiii",
$_POST['exam_id'],
$_POST['fn'],
$_POST['an'],
$_SESSION['user_id']
);

$stmt->execute();
header("Location: dept_dashboard.php");
exit;
