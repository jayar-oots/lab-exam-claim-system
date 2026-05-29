<?php
require '../auth_check.php';
require '../config/db.php';

/* ===== ACCESS CHECK ===== */
if (!in_array($_SESSION['role'], ['admin', 'department'])) {
    die("Access Denied");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid claim ID");
}

/* ===== FETCH DATA ===== */
$stmt = $conn->prepare("
SELECT
a.id,
e.subject_code,
e.subject_name,
e.exam_date,
e.session,

es.name AS external_name,
es.college_name,
es.phone,
es.email,
es.bank_account,
es.branch_name,
es.ifsc_code,

a.forenoon_count,
a.afternoon_count,
(a.forenoon_count + a.afternoon_count) AS attendance,

rs.da_per_day,
rs.ta_per_km,
rs.rate_per_paper,
rs.paper_setting_amount,
a.question_setting,
cd.distance_km

FROM attendance a
JOIN exams e ON a.exam_id = e.id
LEFT JOIN external_staff es 
       ON es.id = e.external_staff_id
JOIN rate_settings rs 
       ON rs.id = 1
LEFT JOIN college_distance cd
       ON cd.college_name = es.college_name
WHERE a.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$claim = $stmt->get_result()->fetch_assoc();

if (!$claim) {
    die("Claim not found");
}

/* ===== CALCULATIONS ===== */

$attendance = $claim['attendance'];

$da = $claim['da_per_day'];

$ta = ($claim['distance_km'] ?? 0) * 2 * $claim['ta_per_km'];

$paper = $attendance * $claim['rate_per_paper'];

$questionSettingCount = $claim['question_setting'] ?? 0;

$questionSettingAmount =
    $questionSettingCount * ($claim['paper_setting_amount'] ?? 0);

    $total = $da + $ta + $paper + $questionSettingAmount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lab Exam Claim #<?= $claim['id']; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#e5e7eb; font-family:system-ui; }

.print-container{
    width:210mm;
    min-height:297mm;
    margin:10mm auto;
    background:#fff;
    padding:20mm;
    box-shadow:0 0 10px rgba(0,0,0,.15);
}

.section-title{
    font-weight:600;
    font-size:14px;
    text-decoration:underline;
    margin-top:10px;
}

.table-sm th,.table-sm td{
    font-size:12px;
    padding:4px 6px;
}

.no-print{
    text-align:center;
    margin:10px;
}

@media print{
    body{background:#fff;}
    .print-container{
        box-shadow:none;
        margin:0;
        padding:0;
    }
    .no-print{display:none;}
}
</style>
</head>

<body>

<div class="no-print">
<button class="btn btn-sm btn-secondary" onclick="window.close()">← Back</button>
<button class="btn btn-sm btn-primary" onclick="window.print()">Print</button>
</div>

<div class="print-container">

<div class="text-center">
<h4><b>KONGUNADU</b></h4>
<h5><b>ARTS AND SCIENCE COLLEGE</b></h5>
<b>(AUTONOMOUS)</b><br>
Coimbatore – 641 029, Tamil Nadu, India
</div>

<!-- FACULTY DETAILS -->
<h6 class="section-title">Faculty Details</h6>

<table class="table table-bordered table-sm">
<tr>
<td width="40%">Name of Faculty</td>
<td><?= $claim['external_name']; ?></td>
</tr>

<tr>
<td>College Name</td>
<td><?= $claim['college_name']; ?></td>
</tr>

<tr>
<td>Mobile No</td>
<td><?= $claim['phone'] ?? '' ?></td>
</tr>

<tr>
<td>Email ID</td>
<td><?= $claim['email'] ?? '' ?></td>
</tr>
</table>

<!-- EXAM DETAILS -->
<h6 class="section-title">Exam Details</h6>

<table class="table table-bordered table-sm">
<tr>
<td width="40%">Date of Exam & Code</td>
<td>
<?= date('Y-m-d', strtotime($claim['exam_date'])); ?>
– <?= $claim['subject_code']; ?>
(<?= $claim['subject_name']; ?>)
</td>
</tr>

<tr>
<td>Session</td>
<td><?= ucfirst($claim['session']); ?></td>
</tr>

<tr>
<td>Attendance Count</td>
<td><?= $attendance; ?></td>
</tr>
</table>

<!-- AMOUNT DETAILS -->
<h6 class="section-title">Amount Details</h6>

<table class="table table-bordered table-sm">
<tr>
<td width="40%">DA Amount</td>
<td><?= number_format($da,2); ?></td>
</tr>

<tr>
<td>TA Amount</td>
<td><?= number_format($ta,2); ?></td>
</tr>

<tr>
<td>Paper Amount</td>
<td><?= number_format($paper,2); ?></td>
</tr>

<tr>
<td>Question Setting Amount</td>
<td><?= number_format($questionSettingAmount,2); ?></td>
</tr>

<tr>
<th>Total Amount</th>
<th><?= number_format($total,2); ?></th>
</tr>
</table>

<!-- BANK DETAILS -->
<h6 class="section-title">Bank Details</h6>

<table class="table table-bordered table-sm">
<tr>
<td width="40%">Account No</td>
<td><?= $claim['bank_account'] ?? '' ?></td>
</tr>

<tr>
<td>Branch</td>
<td><?= $claim['branch_name'] ?? '' ?></td>
</tr>

<tr>
<td>IFSC Code</td>
<td><?= $claim['ifsc_code'] ?? '' ?></td>
</tr>
</table>

<!-- SIGNATURE -->
<div class="row text-center" style="margin-top:140px;">
    
    <div class="col-4">
        <div style="margin-bottom:40px;"></div>
        <hr>
        <b>Internal Examiner</b>
    </div>

    <div class="col-4">
        <div style="margin-bottom:40px;"></div>
        <hr>
        <b>External Examiner</b>
    </div>

    <div class="col-4">
        <div style="margin-bottom:40px;"></div>
        <hr>
        <b>Controller of Examinations</b>
    </div>

</div>

</div>
</body>
</html>