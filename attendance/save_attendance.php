<?php
session_start();
require '../config/db.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$exam_id  = $_POST['exam_id'];
$forenoon = $_POST['forenoon'];
$afternoon = $_POST['afternoon'];
$setting  = $_POST['question_setting'];

if (!$exam_id || $forenoon === null || $afternoon === null) {
    die("Missing attendance data");
}

/* ===== PREVENT DUPLICATE ENTRY ===== */
$check = $conn->prepare(
    "SELECT id FROM attendance WHERE exam_id = ?"
);
$check->bind_param("i", $exam_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "<script>
        alert('Attendance already submitted for this exam.');
        window.history.back();
    </script>";
    exit;
}

if ($forenoon > 30 || $afternoon > 30) {
    echo "<script>
        alert('Attendance count cannot be greater than 30.');
        window.history.back();
    </script>";
    exit;
}

/* ===== INSERT ATTENDANCE ===== */
$stmt = $conn->prepare("
    INSERT INTO attendance
    (exam_id, forenoon_count, afternoon_count, question_setting)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param("iiii",
    $exam_id,
    $forenoon,
    $afternoon,
    $setting
);

if ($stmt->execute()) {
    echo "<script>
        alert('Attendance saved successfully.');
        window.location.href = 'exam_panel.php';
    </script>";
} else {
    echo "Error saving attendance.";
}
