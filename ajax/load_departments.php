<?php
require '../config/db.php';

$stream = $_GET['stream'] ?? '';

if (!$stream) {
    echo '<option value="">-- Select Department --</option>';
    exit;
}

$stmt = $conn->prepare("
    SELECT id, name 
    FROM users 
    WHERE role = 'department'
    AND stream = ?
");

$stmt->bind_param("s", $stream);
$stmt->execute();
$res = $stmt->get_result();

echo '<option value="">-- Select Department --</option>';

while ($row = $res->fetch_assoc()) {
    echo "<option value='{$row['id']}'>{$row['name']}</option>";
}
?>