<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../config/db.php';
require '../auth_check.php';

$isPdf = isset($_GET['pdf']);
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    die("Invalid Claim ID.");
}

/* ---------- FETCH DETAILS ---------- */
$sql = "
SELECT

e.internal_staff_id,

isf.name,
isf.email,
isf.phone,
isf.address,
isf.bank_name,
isf.account_number,
isf.branch_name,
isf.ifsc_code,
isf.designation,

e.exam_date,
e.subject_code,
e.subject_name,
e.session,

a.forenoon_count,
a.afternoon_count,
(a.forenoon_count + a.afternoon_count) AS attendance,

rs.da_per_day,
rs.ta_per_km,
rs.rate_per_paper,
rs.paper_setting_amount,

a.question_setting

FROM attendance a

JOIN exams e 
    ON a.exam_id = e.id

JOIN internal_staff isf 
    ON isf.id = e.internal_staff_id

LEFT JOIN rate_settings rs 
    ON rs.id = 1

WHERE a.id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) die("SQL Error: " . $conn->error);

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0)
    die("No claim data found.");

$row = $result->fetch_assoc();

/* ---------- CLAIM TYPE ---------- */
$isInternal = isset($row['internal_staff_id']);

/* ---------- SESSION TEXT ---------- */
if ($row['forenoon_count'] > 0 && $row['afternoon_count'] > 0)
    $session = 'Forenoon & Afternoon';
elseif ($row['forenoon_count'] > 0)
    $session = 'Forenoon';
elseif ($row['afternoon_count'] > 0)
    $session = 'Afternoon';
else
    $session = 'N/A';

/* ---------- CALCULATIONS ---------- */

$daAmount = $row['da_per_day'] ?? 0;

$distance = $row['distance_km'] ?? 0;
$taAmount = $distance * 2 * ($row['ta_per_km'] ?? 0);

$paperAmount =
($row['attendance'] ?? 0) *
($row['rate_per_paper'] ?? 0);

$questionSettingAmount =
($row['question_setting'] ?? 0) *
($row['paper_setting_amount'] ?? 0);

$totalAmount =
$daAmount +
$taAmount +
$paperAmount +
$questionSettingAmount;
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<title>Claim Details</title>

</head>

<body style="background:#f4f6f9;">

<div class="container mt-4">

<div class="card shadow p-4">

<h4 class="text-center mb-4">
<?= $isInternal ? 'Internal Claim Details' : 'External Claim Details' ?>
</h4>

<h4 class="text-center mb-4">Claim Details</h4>

<table class="table table-bordered">

<tr>
<th>Name</th>
<td><?= htmlspecialchars($row['name'] ?? '-') ?></td>
</tr>

<tr>
<th>College</th>
<td>Kongunadu Arts and Science College</td>
</tr>

<tr>
<th>Exam Date</th>
<td><?= htmlspecialchars($row['exam_date'] ?? '-') ?></td>
</tr>

<tr>
<th>Subject</th>
<td>
<?= htmlspecialchars($row['subject_code']) ?>
(<?= htmlspecialchars($row['subject_name']) ?>)
</td>
</tr>

<tr>
<th>Session</th>
<td><?= $session ?></td>
</tr>

<tr>
<th>Total Attendance</th>
<td><?= $row['attendance'] ?></td>
</tr>

<tr>
<th>DA Amount</th>
<td>₹ <?= number_format($daAmount,2) ?></td>
</tr>

<tr>
<th>TA Amount</th>
<td>₹ <?= number_format($taAmount,2) ?></td>
</tr>

<tr>
<th>Paper Amount</th>
<td>₹ <?= number_format($paperAmount,2) ?></td>
</tr>

<tr>
<th>Question Setting Amount</th>
<td>₹ <?= number_format($questionSettingAmount,2) ?></td>
</tr>

<tr class="table-success">
<th>Total Amount</th>
<td><b>₹ <?= number_format($totalAmount,2) ?></b></td>
</tr>

</table>

<!-- ACTION BUTTONS -->

<div class="text-center mt-4">

<a href="internal_claim_print.php?id=<?= $id ?>"
   target="_blank"
   class="btn btn-primary">
Print
</a>

<button class="btn btn-success ms-3 px-4"
        data-bs-toggle="modal"
        data-bs-target="#confirmModal">
Send Mail
</button>

<a href="/myclaim/admin/admin_dashboard.php"
   class="btn btn-secondary ms-3 px-4">
Back
</a>

</div>

</div>

</div>

<!-- CONFIRM MODAL -->

<div class="modal fade" id="confirmModal" tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Confirm Claim Submission</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<p>Are you sure you want to send this claim?</p>

<div class="form-check">

<input class="form-check-input"
       type="checkbox"
       id="sendPdfCheck">

<label class="form-check-label">
Also send PDF copy to Department
</label>

</div>

</div>

<form method="GET"
action="send_internal_claim_mail.php"
id="claimForm">

<input type="hidden" name="id" value="<?= $id ?>">

<input type="hidden" name="send_pdf"
       id="send_pdf_input"
       value="0">

<div class="modal-footer">

<button type="button"
        class="btn btn-secondary"
        data-bs-dismiss="modal">
Cancel
</button>

<button type="submit"
        class="btn btn-success">
Confirm & Send
</button>

</div>

</form>

</div>
</div>
</div>

<script>

document.getElementById("claimForm")
.addEventListener("submit", function () {

let checkbox =
document.getElementById("sendPdfCheck");

document.getElementById("send_pdf_input").value =
checkbox.checked ? "1" : "0";

});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>