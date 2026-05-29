<?php
require '../auth_check.php';
require '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid claim ID");
}

$stmt = $conn->prepare("
SELECT
a.id,
e.subject_code,
e.subject_name,
e.exam_date,
e.session,

isf.name,
'KONGUNADU ARTS AND SCIENCE COLLEGE' AS college_name,
isf.phone,
isf.email,
isf.account_number AS bank_account,
isf.branch_name,
isf.ifsc_code,

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
");

$stmt->bind_param("i",$id);
$stmt->execute();
$claim = $stmt->get_result()->fetch_assoc();

$attendance = $claim['attendance'];
$da = $claim['da_per_day'];
$paper = $attendance * $claim['rate_per_paper'];
$questionSettingAmount = ($claim['question_setting'] ?? 0) * ($claim['paper_setting_amount'] ?? 0);
$total = $da + $paper + $questionSettingAmount;
?>

<style>
body{font-family:system-ui;font-size:14px;}
table{width:100%;border-collapse:collapse;margin-bottom:15px;}
td,th{border:1px solid #999;padding:6px;}
.section-title{font-weight:600;text-decoration:underline;margin-top:10px;}
.signature-table{width:100%;margin-top:80px;text-align:center;}
.signature-line{border-top:1px solid #000;width:70%;margin:40px auto 8px;}
</style>

<div style="text-align:center">
<h3>KONGUNADU</h3>
<h4>ARTS AND SCIENCE COLLEGE</h4>
<b>(AUTONOMOUS)</b><br>
Coimbatore – 641 029
</div>

<h5 class="section-title">Faculty Details</h5>
<table>
<tr><td width="40%">Name of Faculty</td><td><?= $claim['name']; ?></td></tr>
<tr><td>College Name</td><td><?= $claim['college_name']; ?></td></tr>
<tr><td>Mobile No</td><td><?= $claim['phone']; ?></td></tr>
<tr><td>Email ID</td><td><?= $claim['email']; ?></td></tr>
</table>

<h5 class="section-title">Exam Details</h5>
<table>
<tr>
<td width="40%">Date of Exam & Code</td>
<td><?= date('Y-m-d',strtotime($claim['exam_date'])); ?> – <?= $claim['subject_code']; ?> (<?= $claim['subject_name']; ?>)</td>
</tr>
<tr><td>Session</td><td><?= ucfirst($claim['session']); ?></td></tr>
<tr><td>Attendance Count</td><td><?= $attendance; ?></td></tr>
</table>

<h5 class="section-title">Amount Details</h5>
<table>
<tr><td width="40%">DA Amount</td><td><?= number_format($da,2); ?></td></tr>
<tr><td>Paper Amount</td><td><?= number_format($paper,2); ?></td></tr>
<tr><td>Question Setting Amount</td><td><?= number_format($questionSettingAmount,2); ?></td></tr>
<tr><th>Total Amount</th><th><?= number_format($total,2); ?></th></tr>
</table>

<h5 class="section-title">Bank Details</h5>
<table>
<tr><td width="40%">Account No</td><td><?= $claim['bank_account']; ?></td></tr>
<tr><td>Branch</td><td><?= $claim['branch_name']; ?></td></tr>
<tr><td>IFSC Code</td><td><?= $claim['ifsc_code']; ?></td></tr>
</table>

<table style="height:150px; border:none;">
<tr><td></td></tr>
</table>

<table style="width:100%; text-align:center; border:none;">
<tr>

<td style="width:50%; border:none;">
Internal Staff
</td>

<td style="width:50%; border:none;">
Controller of Examinations
</td>

</tr>
</table>