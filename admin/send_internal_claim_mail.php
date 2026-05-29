<?php
session_start();

set_time_limit(300);
ini_set('max_execution_time',300);

require '../auth_check.php';
require '../config/db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$id = intval($_GET['id'] ?? 0);
$sendPdf = isset($_GET['send_pdf']);

if ($id <= 0) {
    die("Invalid ID");
}

/* ===================================================
   FETCH INTERNAL CLAIM DETAILS
=================================================== */

$sql = "
SELECT
    a.id AS attendance_id,
    e.id AS exam_id,

    isf.name,
    isf.email,
    isf.designation,

    e.subject_code,
    e.subject_name,
    e.exam_date,

    (a.forenoon_count + a.afternoon_count) AS attendance,

    rs.da_per_day,
    rs.rate_per_paper,
    rs.paper_setting_amount,

    a.question_setting

FROM attendance a
JOIN exams e ON a.exam_id = e.id
JOIN internal_staff isf 
     ON isf.id = e.internal_staff_id
LEFT JOIN rate_settings rs 
     ON rs.id = 1

WHERE a.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if(!$row){
    die("Claim not found");
}

/* ===================================================
   CALCULATION
=================================================== */

$da    = $row['da_per_day'];
$paper = $row['attendance'] * $row['rate_per_paper'];
$qset  = $row['question_setting'] * $row['paper_setting_amount'];

$total = $da + $paper + $qset;

$exam_id = $row['exam_id'];

/* ===================================================
   ✅ AUTO APPROVAL (LIKE EXTERNAL)
=================================================== */

$updateExam = $conn->prepare("
UPDATE exams
SET internal_claim_status='approved'
WHERE id=?
");

$updateExam->bind_param("i",$exam_id);
$updateExam->execute();


$updateAttendance = $conn->prepare("
UPDATE attendance
SET status='approved'
WHERE id=?
");

$updateAttendance->bind_param("i",$id);
$updateAttendance->execute();


/* ===================================================
   GET DEPARTMENT EMAIL
=================================================== */

$departmentEmail = null;

if($sendPdf){

$dept = $conn->query("
SELECT email FROM users
WHERE role='department'
LIMIT 1
");

if($dept && $dept->num_rows>0){
    $departmentEmail =
    $dept->fetch_assoc()['email'];
}

}

/* ===================================================
   SEND MAIL TO INTERNAL STAFF
=================================================== */

try{

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'examcell@gmail.com';
$mail->Password   = 'zyowujjytxuffpqt';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;

$mail->setFrom(
'examcell@gmail.com',
'Examiner Cell'
);

$mail->addAddress($row['email'],$row['name']);

$mail->Subject = "Internal Exam Claim Approved";

$mail->Body = "
Dear {$row['name']},

Your Internal Exam Claim has been approved.

Subject : {$row['subject_code']}
Exam Date : {$row['exam_date']}
Attendance : {$row['attendance']}

Total Amount : ₹{$total}

Regards,
Exam Cell
";

$mail->send();


/* ===================================================
   PDF MAIL TO DEPARTMENT
=================================================== */

if($sendPdf && $departmentEmail){

require_once('../tcpdf/tcpdf.php');

$pdf = new TCPDF();
$pdf->AddPage();

ob_start();

$_GET['id'] = $id;   // manually pass id
include '../claims/internal_claim_pdf_template.php';

$html = ob_get_clean();

$pdf->writeHTML($html);

$pdfDir = dirname(__DIR__)."/pdfs/";

if(!is_dir($pdfDir)){
mkdir($pdfDir,0777,true);
}

$file =
$pdfDir."internal_claim_".$id."_".time().".pdf";

$pdf->Output($file,'F');


$deptMail = new PHPMailer(true);

$deptMail->isSMTP();
$deptMail->Host='smtp.gmail.com';
$deptMail->SMTPAuth=true;
$deptMail->Username='examcell@gmail.com';
$deptMail->Password='zyowujjytxuffpqt';
$deptMail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;
$deptMail->Port=587;

$deptMail->setFrom(
'examcell@gmail.com',
'Examiner Cell'
);

$deptMail->addAddress($departmentEmail);

$deptMail->Subject =
"Approved Internal Claim - {$row['name']}";

$deptMail->Body =
"Approved internal claim attached.";

$deptMail->addAttachment($file);

$deptMail->send();

unlink($file);
}

/* ===================================================
   SUCCESS
=================================================== */

echo "
<script>
alert('Internal Claim Approved & Mail Sent');
window.location.href='admin_dashboard.php';
</script>
";

}
catch(Exception $e){

echo "
<script>
alert('Mail Error: {$e->getMessage()}');
window.history.back();
</script>";
}
?>