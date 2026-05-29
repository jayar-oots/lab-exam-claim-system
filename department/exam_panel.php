<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'department') {
    die("Department not logged in.");
}

$dept_id = $_SESSION['user_id'];

$activePage = 'exam';
$today = date('Y-m-d');

include 'dept_sidebar.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Panel</title>
    <style>

body {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background:#0b1a3c;
    margin:0;
}

.card {
    background:white;
    width:75%;
    margin:40px 0 40px 280px; 
    padding:35px;
    border-radius:12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

h2 {
    font-size:28px;
    margin-bottom:10px;
    font-weight:600;
}

h3 {
    font-size:20px;
    margin-top:25px;
    margin-bottom:10px;
    font-weight:600;
}

table {
    width:100%;
    border-collapse: collapse;
    margin-top:15px;
    font-size:16px;
}

th, td {
    padding:14px;
    border:1px solid #ddd;
    text-align:center;
}

th {
    background:#1f2c3d;
    color:white;
    font-weight:600;
}

tbody tr:nth-child(even) {
    background:#f7f7f7;
}

tbody tr:hover {
    background:#eef3ff;
    transition:0.2s;
}

.no-record {
    margin-top:15px;
    color:#c0392b;
    font-weight:500;
}
    </style>
</head>
<body>

<div class="card">
<h2>Exam Panel</h2>

<?php
/* ================= TODAY EXAM ================= */

$stmt = $conn->prepare("
    SELECT *
    FROM exams
    WHERE department = ?
    AND exam_date = ?
");
$stmt->bind_param("is", $dept_id, $today);
$stmt->execute();
$res = $stmt->get_result();

if($today_exam = $res->fetch_assoc()):
?>

<h3>Today's Exam</h3>
<p><strong>Subject Code:</strong> <?= $today_exam['subject_code'] ?></p>
<p><strong>Subject Name:</strong> <?= $today_exam['subject_name'] ?></p>
<p><strong>Exam Date:</strong> <?= date('d-m-Y', strtotime($today_exam['exam_date'])) ?></p>
<p><strong>Session:</strong> <?= $today_exam['session'] ?></p>

<hr>

<?php endif; ?>


<?php
/* ================= ATTENDANCE HISTORY ================= */

$stmt2 = $conn->prepare("
    SELECT 
        e.subject_code,
        e.exam_date,
        (a.forenoon_count + a.afternoon_count) AS total
    FROM exams e
    JOIN attendance a ON e.id = a.exam_id
    WHERE e.department = ?          -- ✅ filter by department
    ORDER BY e.exam_date DESC
");

$stmt2->bind_param("i", $dept_id);   // ✅ bind BEFORE execute
$stmt2->execute();
$res2 = $stmt2->get_result();
?>

<h3>Attendance History</h3>

<?php if($res2->num_rows > 0): ?>

<table>
    <thead>
        <tr>
            <th>Attendance</th>
            <th>Subject Code</th>
            <th>Exam Date</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $res2->fetch_assoc()): ?>
        <tr>
            <td><?= $row['total'] ?></td>
            <td><?= $row['subject_code'] ?></td>
            <td><?= date('d-m-Y', strtotime($row['exam_date'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php else: ?>

<p class="no-record">No attendance records found.</p>

<?php endif; ?>
    </tbody>
</table>
</div>
</body>
</html>