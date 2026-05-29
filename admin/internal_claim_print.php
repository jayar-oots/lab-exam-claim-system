<?php
require '../config/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    die("Invalid Claim ID");
}

/* =========================
   FETCH INTERNAL CLAIM
========================= */

$sql = "
SELECT
    isf.name,
    'KONGUNADU ARTS AND SCIENCE COLLEGE' AS college_name,
    isf.phone,
    isf.email,
    isf.account_number,
    isf.branch_name,
    isf.ifsc_code,

    e.exam_date,
    e.subject_code,
    e.subject_name,
    e.session,

    a.forenoon_count,
    a.afternoon_count,
    (a.forenoon_count + a.afternoon_count) AS attendance,

    rs.da_per_day,
    rs.rate_per_paper,
    rs.paper_setting_amount,
    a.question_setting

FROM attendance a
JOIN exams e ON a.exam_id = e.id
JOIN internal_staff isf ON isf.id = e.internal_staff_id
JOIN rate_settings rs ON rs.id = 1
WHERE a.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Claim not found");
}

$row = $result->fetch_assoc();

/* =========================
   CALCULATION
========================= */

$attendance = $row['attendance'];

$da = $row['da_per_day'];

$paper =
$attendance * $row['rate_per_paper'];

$question =
($row['question_setting'] ?? 0)
* $row['paper_setting_amount'];

$total = $da + $paper + $question;
?>

<!DOCTYPE html>
<html>
<head>
<title>Internal Claim Print</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#e5e7eb;
font-family:Arial;
}

.print-container{
width:210mm;
min-height:297mm;
margin:auto;
background:#fff;
padding:25mm;
}

.section-title{
font-weight:bold;
text-decoration:underline;
margin-top:15px;
}

@media print{
.no-print{display:none;}
body{background:white;}
}

</style>
</head>

<body>

<div class="no-print text-center mb-3">
<button onclick="history.back()" class="btn btn-secondary">Back</button>
<button onclick="window.print()" class="btn btn-primary">Print</button>
</div>

<div class="print-container">

<!-- HEADER -->
<div class="text-center">
<h4><b>KONGUNADU</b></h4>
<h5><b>ARTS AND SCIENCE COLLEGE</b></h5>
<b>(AUTONOMOUS)</b><br>
Coimbatore – 641029
</div>


<!-- FACULTY DETAILS -->
<h6 class="section-title">Faculty Details</h6>

<table class="table table-bordered table-sm">
<tr>
<td width="40%">Name of Faculty</td>
<td><?= $row['name'] ?></td>
</tr>

<tr>
<td>College Name</td>
<td><?= $row['college_name'] ?></td>
</tr>

<tr>
<td>Mobile No</td>
<td><?= $row['phone'] ?></td>
</tr>

<tr>
<td>Email ID</td>
<td><?= $row['email'] ?></td>
</tr>
</table>


<!-- EXAM DETAILS -->
<h6 class="section-title">Exam Details</h6>

<table class="table table-bordered table-sm">
<tr>
<td width="40%">Date of Exam & Code</td>
<td>
<?= $row['exam_date'] ?> -
<?= $row['subject_code'] ?>(<?= $row['subject_name']; ?>)
</td>
</tr>

<tr>
<td>Session</td>
<td><?= ucfirst($row['session']) ?></td>
</tr>

<tr>
<td>Attendance Count</td>
<td><?= $attendance ?></td>
</tr>
</table>


<!-- AMOUNT DETAILS -->
<h6 class="section-title">Amount Details</h6>

<table class="table table-bordered table-sm">

<tr>
<td width="40%">DA Amount</td>
<td><?= number_format($da,2) ?></td>
</tr>

<tr>
<td>Paper Amount</td>
<td><?= number_format($paper,2) ?></td>
</tr>

<tr>
<td>Question Setting Amount</td>
<td><?= number_format($question,2) ?></td>
</tr>

<tr>
<th>Total Amount</th>
<th><?= number_format($total,2) ?></th>
</tr>

</table>


<!-- BANK -->
<h6 class="section-title">Bank Details</h6>

<table class="table table-bordered table-sm">

<tr>
<td width="40%">Account No</td>
<td><?= $row['account_number'] ?></td>
</tr>

<tr>
<td>Branch</td>
<td><?= $row['branch_name'] ?></td>
</tr>

<tr>
<td>IFSC Code</td>
<td><?= $row['ifsc_code'] ?></td>
</tr>

</table>


<!-- SIGN -->
<div class="row text-center mt-5">
<div class="col">Internal Staff</div>
<div class="col">Controller of Examinations</div>
</div>

</div>

</body>
</html>