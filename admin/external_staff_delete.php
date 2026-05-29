<?php
require '../auth_check.php';
require '../config/db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM external_staff WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: external_staff_list.php?deleted=1");
exit;
