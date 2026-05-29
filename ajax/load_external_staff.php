<?php

require '../config/db.php';


$college = $_GET['college'] ?? '';

$stmt = $conn->prepare("
    SELECT id, name 
    FROM external_staff 
    WHERE college_name = ? 
    AND status = 'active'
");
$stmt->bind_param("s", $college);
$stmt->execute();
$res = $stmt->get_result();

echo '<option value="">-- Select External Staff --</option>';
while ($r = $res->fetch_assoc()) {
    echo "<option value='{$r['id']}'>{$r['name']}</option>";
}
