<?php
require '../auth_check.php';
require '../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

/* ================= COUNTS ================= */

/* TOTAL EXAMS */
$totalExams = $conn->query("
    SELECT COUNT(*) c 
    FROM exams
")->fetch_assoc()['c'] ?? 0;


/* ================= EXTERNAL CLAIMS ================= */

/* External Pending */
$externalPending = $conn->query("
    SELECT COUNT(*) as total
    FROM attendance
    WHERE claim_status = 'pending'
")->fetch_assoc()['total'];
/* External Approved */
$externalApproved = $conn->query("
    SELECT COUNT(*) as total
    FROM attendance
    WHERE claim_status = 'approved'
")->fetch_assoc()['total'];

/* ================= INTERNAL CLAIMS ================= */

/* Internal Pending */
$internalPending = $conn->query("
    SELECT COUNT(*) as total
    FROM attendance
    WHERE status = 'pending'
")->fetch_assoc()['total'];
/* Internal Approved */
$internalApproved = $conn->query("
    SELECT COUNT(*) as total
    FROM attendance
    WHERE status = 'approved'
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.stat-card{
    border-radius:18px;
    padding:30px;
    color:#fff;
    text-align:center;
    box-shadow:0 15px 40px rgba(0,0,0,.18);
    transition:.25s;
}
.stat-card:hover{
    transform:translateY(-6px);
}
.stat-title{
    font-size:1rem;
    opacity:.9;
}
.stat-number{
    font-size:3rem;
    font-weight:800;
}
.bg-total{
    background:linear-gradient(135deg,#2563eb,#3b82f6);
}
.bg-pending{
    background:linear-gradient(135deg,#f59e0b,#f97316);
}
.bg-approved{
    background:linear-gradient(135deg,#16a34a,#22c55e);
}
.card-link{
    text-decoration:none;
    color:white;
}
.section-title{
    font-weight:700;
    margin:35px 0 15px;
}
</style>
</head>

<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
<div class="container-fluid p-4">

<h3 class="mb-4">Admin Dashboard</h3>

<!-- TOTAL EXAMS -->
<div class="row mb-5">
    <div class="col-md-5">
        <a href="exam_list.php" class="card-link">
            <div class="stat-card bg-total">
                <div class="stat-title">Total Exams</div>
                <div class="stat-number"><?= $totalExams ?></div>
            </div>
        </a>
    </div>
</div>

<!-- EXTERNAL CLAIMS -->
<h5 class="section-title">External Examiner Claims</h5>
<div class="row g-4 mb-5">

    <div class="col-md-4">
        <a href="pending_claims.php" class="card-link">
            <div class="stat-card bg-pending">
                <div class="stat-title">Pending Claims</div>
                <div class="stat-number"><?= $externalPending ?></div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="external_approved_claims.php" class="card-link">
            <div class="stat-card bg-approved">
                <div class="stat-title">Approved Claims</div>
                <div class="stat-number"><?= $externalApproved ?></div>
            </div>
        </a>
    </div>

</div>

<!-- INTERNAL CLAIMS -->
<h5 class="section-title">Internal Staff Claims</h5>
<div class="row g-4">

    <div class="col-md-4">
        <a href="internal_pending_claims.php" class="card-link">
            <div class="stat-card bg-pending">
                <div class="stat-title">Pending Claims</div>
                <div class="stat-number"><?= $internalPending ?></div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="internal_approved_claims.php" class="card-link">
            <div class="stat-card bg-approved">
                <div class="stat-title">Approved Claims</div>
                <div class="stat-number"><?= $internalApproved ?></div>
            </div>
        </a>
    </div>

</div>

</div>
</div>



</div>
</body>
</html>
