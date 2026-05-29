<?php
require '../auth_check.php';
require '../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

/* ======================================================
   FETCH ALL EXTERNAL PENDING CLAIMS (LIST)
   ====================================================== */
$listSql = "
SELECT 
    a.id AS attendance_id,
    e.subject_code,
    (a.forenoon_count + a.afternoon_count) AS total_attendance
FROM attendance a
JOIN exams e ON a.exam_id = e.id
WHERE a.claim_status = 'pending'
ORDER BY e.exam_date DESC
";

$listRes = $conn->query($listSql);

/* ======================================================
   FETCH SINGLE CLAIM DETAILS (VIEW OVERLAY)
   ====================================================== */
$d = null;

if (isset($_GET['view'])) {

    $attendance_id = (int)$_GET['view'];

    $viewSql = "
    SELECT 
        a.id AS attendance_id,
        e.exam_date,
        e.subject_code,
        e.subject_name,
        e.semester,
        e.lab_no,
        e.session,
        u.name AS department_name,
        i.name AS internal_staff_name,
        es.name AS external_staff_name,
        (a.forenoon_count + a.afternoon_count) AS total_attendance
    FROM attendance a
    JOIN exams e ON e.id = a.exam_id
    LEFT JOIN users u ON e.created_by = u.id
    LEFT JOIN internal_staff i ON e.internal_staff_id = i.id
    LEFT JOIN external_staff es ON e.external_staff_id = es.id
    WHERE a.id = ?
    ";

    $stmt = $conn->prepare($viewSql);
    $stmt->bind_param("i", $attendance_id);
    $stmt->execute();
    $viewRes = $stmt->get_result();

    if ($viewRes->num_rows > 0) {
        $d = $viewRes->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>External Pending Claims</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.claim-overlay{
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:9999;
}
.claim-modal{
    background:#fff;
    width:70%;
    max-height:85%;
    overflow:auto;
    border-radius:10px;
}
</style>
</head>

<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content p-4">

<h3 class="mb-4">External Pending Claims</h3>

<table class="table table-bordered">
<thead class="table-dark">
<tr>
    <th>Subject Code</th>
    <th>Total Attendance</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php if ($listRes && $listRes->num_rows > 0): ?>
    <?php while ($row = $listRes->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['subject_code']) ?></td>
        <td><?= $row['total_attendance'] ?></td>
        <td>
            <a href="../claims/claim_details.php?id=<?= $row['attendance_id'] ?>"
               class="btn btn-success">
               Claim
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="3" class="text-center text-muted">
            No pending claims found
        </td>
    </tr>
<?php endif; ?>

</tbody>
</table>

</div>

<!-- ================= VIEW OVERLAY ================= -->

<?php if ($d): ?>
<div class="claim-overlay">

<div class="claim-modal">

<div class="d-flex justify-content-between align-items-center p-3 border-bottom">
    <h5 class="mb-0">External Claim Details</h5>
    <a href="pending_claims.php" class="btn btn-danger btn-sm">✕</a>
</div>

<div class="p-4">
<table class="table table-bordered">

<tr><th>Exam Date</th><td><?= $d['exam_date'] ?></td></tr>
<tr><th>Subject Code</th><td><?= $d['subject_code'] ?></td></tr>
<tr><th>Subject Name</th><td><?= $d['subject_name'] ?></td></tr>
<tr><th>Department</th><td><?= $d['department_name'] ?? '-' ?></td></tr>
<tr><th>Semester</th><td><?= $d['semester'] ?></td></tr>
<tr><th>Lab No</th><td><?= $d['lab_no'] ?></td></tr>
<tr><th>Session</th><td><?= ucfirst(str_replace('_',' & ',$d['session'])) ?></td></tr>
<tr><th>Internal Staff</th><td><?= $d['internal_staff_name'] ?: '-' ?></td></tr>
<tr><th>External Staff</th><td><?= $d['external_staff_name'] ?: '-' ?></td></tr>

<tr class="table-info">
    <th>Total Attendance</th>
    <td><b><?= $d['total_attendance'] ?></b></td>
</tr>

</table>
</div>

<div class="text-end p-3 border-top">
    <a href="../claims/claim_details.php?id=<?= $d['attendance_id'] ?>"
       class="btn btn-success">
       Claim
    </a>
</div>

</div>
</div>
<?php endif; ?>

</body>
</html>