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

es.name,
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
JOIN external_staff es ON es.id = e.external_staff_id
JOIN rate_settings rs ON rs.id = 1
LEFT JOIN college_distance cd ON cd.college_name = es.college_name
WHERE a.id = ?
");

$stmt->bind_param("i",$id);
$stmt->execute();
$claim = $stmt->get_result()->fetch_assoc();

/* CALCULATIONS */

$attendance = $claim['attendance'];

$da = $claim['da_per_day'];

$distance = $claim['distance_km'] ?? 0;
$ta = $distance * 2 * $claim['ta_per_km'];

$paper = $attendance * $claim['rate_per_paper'];

$questionSettingAmount =
($claim['question_setting'] ?? 0) *
($claim['paper_setting_amount'] ?? 0);

$total = $da + $ta + $paper + $questionSettingAmount;
?>

<style>
body{font-family:system-ui;font-size:14px;}

table{
width:100%;
border-collapse:collapse;
margin-bottom:15px;
}

td,th{
border:1px solid #999;
padding:6px;
}

.section-title{
font-weight:600;
text-decoration:underline;
margin-top:10px;
}
</style>

<div style="text-align:center">
<h3>KONGUNADU</h3>
<h4>ARTS AND SCIENCE COLLEGE</h4>
<b>(AUTONOMOUS)</b><br>
Coimbatore – 641 029
</div>


<h5 class="section-title">Faculty Details</h5>

<table>
<tr>
<td width="40%">Name of Faculty</td>
<td><?= $claim['name']; ?></td>
</tr>

<tr>
<td>College Name</td>
<td><?= $claim['college_name']; ?></td>
</tr>

<tr>
<td>Mobile No</td>
<td><?= $claim['phone']; ?></td>
</tr>

<tr>
<td>Email ID</td>
<td><?= $claim['email']; ?></td>
</tr>
</table>


<h5 class="section-title">Exam Details</h5>

<table>
<tr>
<td width="40%">Date of Exam & Code</td>
<td>
<?= date('Y-m-d',strtotime($claim['exam_date'])); ?> –
<?= $claim['subject_code']; ?>
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


<h5 class="section-title">Amount Details</h5>

<table>
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


<h5 class="section-title">Bank Details</h5>

<table>
<tr>
<td width="40%">Account No</td>
<td><?= $claim['bank_account']; ?></td>
</tr>

<tr>
<td>Branch</td>
<td><?= $claim['branch_name']; ?></td>
</tr>

<tr>
<td>IFSC Code</td>
<td><?= $claim['ifsc_code']; ?></td>
</tr>
</table>


<!-- SIGNATURE SECTION -->

<table style="width:100%; margin-top:220px; text-align:center; border:none;">
<tr>

<td style="width:33%; border:none;">
Internal Staff
</td>

<td style="width:33%; border:none;">
External Examiner
</td>

<td style="width:33%; border:none;">
Controller of Examinations
</td>

</tr>
</table>
