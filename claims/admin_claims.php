<?php
require '../auth_check.php';
require '../config/db.php';


if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

/* ===== FETCH CLAIMS READY FOR ADMIN PROCESS ===== */
$sql = "
SELECT
    c.id AS claim_id,
    c.paper_count,
    c.created_at,

    e.subject_code,
    e.subject_name,
    e.exam_date,

    a.forenoon_count,
    a.afternoon_count

FROM claims c
JOIN exams e ON c.exam_id = e.id
JOIN attendance a ON a.exam_id = e.id
WHERE c.status = 'paper_entered'
ORDER BY e.exam_date DESC
";

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pending Claims</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background:#0f172a;
    font-family:system-ui;
}
.card-main {
    border-radius:16px;
    box-shadow:0 20px 40px rgba(15,23,42,.6);
}
</style>
</head>

<body>

<?php include 'admin_sidebar.php'; ?>

<div class="container py-4">

<h4 class="text-light mb-1">Pending Claims</h4>
<p class="text-muted small">
Paper count has been entered by department. Select a claim to process.
</p>

<div class="card card-main bg-white mt-3">
<div class="card-body">

<?php if ($res->num_rows === 0): ?>
    <p class="text-muted mb-0">No pending claims available.</p>
<?php else: ?>

<div class="table-responsive">
<table class="table table-sm align-middle">
<thead class="table-light">
<tr>
    <th>Claim ID</th>
    <th>Subject</th>
    <th>Exam Date</th>
    <th>Session</th>
    <th>Paper Count</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php while ($r = $res->fetch_assoc()): ?>

<?php
/* ===== SESSION LOGIC ===== */
if ($r['forenoon_count'] > 0 && $r['afternoon_count'] > 0) {
    $session = 'Forenoon & Afternoon';
} elseif ($r['forenoon_count'] > 0) {
    $session = 'Forenoon';
} elseif ($r['afternoon_count'] > 0) {
    $session = 'Afternoon';
} else {
    $session = 'N/A';
}
?>

<tr>
<td>#<?= $r['claim_id']; ?></td>
<td><?= $r['subject_code']." - ".$r['subject_name']; ?></td>
<td><?= $r['exam_date']; ?></td>
<td><?= $session; ?></td>
<td><?= $r['paper_count']; ?></td>
<td>
    <a href="claim_details.php?id=<?= $r['claim_id']; ?>"
       class="btn btn-sm btn-success">
        Process Claim
    </a>
</td>
</tr>

<?php endwhile; ?>

</tbody>
</table>
</div>

<?php endif; ?>

</div>
</div>

</div>

</body>
</html>
