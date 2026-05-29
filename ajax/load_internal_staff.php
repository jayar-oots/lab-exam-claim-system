<?php
require '../config/db.php';

$dept_id = $_GET['dept_id'] ?? '';

if (!$dept_id) {
    echo '<option value="">-- Select Internal Staff --</option>';
    exit;
}

$stmt = $conn->prepare("
    SELECT id, name 
    FROM internal_staff
    WHERE department_id = ?
");

$stmt->bind_param("i", $dept_id);
$stmt->execute();
$res = $stmt->get_result();

echo '<option value="">-- Select Internal Staff --</option>';

while ($row = $res->fetch_assoc()) {
    echo "<option value='{$row['id']}'>{$row['name']}</option>";
}
