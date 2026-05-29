<?php
require '../auth_check.php';
require '../config/db.php';

$type   = $_REQUEST['type'] ?? '';
$action = $_REQUEST['action'] ?? '';

$status = ($action === 'disable') ? 'disabled' : 'active';

/* =========================
   TOGGLE SINGLE STAFF
   ========================= */
if ($type === 'staff' && isset($_GET['id'])) {

    $id = (int)$_GET['id'];

    $stmt = $conn->prepare(
        "UPDATE external_staff SET status=? WHERE id=?"
    );
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

/* =========================
   TOGGLE BY COLLEGE (BULK)
   ========================= */
if ($_POST['type'] === 'college') {
    $college = $_POST['college'];
    $status  = ($_POST['action'] === 'disable') ? 'disabled' : 'active';

    $stmt = $conn->prepare("
        UPDATE external_staff 
        SET status = ? 
        WHERE college_name = ?
    ");
    $stmt->bind_param("ss", $status, $college);
    $stmt->execute();

    header("Location: external_staff_list.php");
    exit;
}

