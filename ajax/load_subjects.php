<?php
require '../config/db.php';

header('Content-Type: application/json');

$sem  = $_GET['sem'] ?? '';
$dept = $_GET['dept'] ?? '';

if (!$sem || !$dept) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT subject_code AS code, subject_name AS name
    FROM subjects
    WHERE semester = ?
    AND department_id = ?
");

$stmt->bind_param("ii", $sem, $dept);
$stmt->execute();

$result = $stmt->get_result();

$subjects = [];

while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

echo json_encode($subjects);