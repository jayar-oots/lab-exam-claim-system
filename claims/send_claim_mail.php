<?php
session_start();

set_time_limit(300);
ini_set('max_execution_time',300);

require '../auth_check.php';
require '../config/db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$id      = intval($_GET['id'] ?? 0);
$sendPdf = isset($_GET['send_pdf']);

if ($id <= 0) {
    die("Invalid Claim ID");
}

/* =================================================
   FETCH CLAIM DETAILS
================================================= */

$sql = "
SELECT
e.id AS exam_id,

es.name,
es.email,
es.college_name,

e.exam_date,
e.subject_code,
e.subject_name,

a.forenoon_count,
a.afternoon_count,
(a.forenoon_count + a.afternoon_count) AS attendance,

rs.da_per_day,
rs.ta_per_km,
rs.rate_per_paper,

cd.distance_km

FROM attendance a

JOIN exams e
ON a.exam_id = e.id

JOIN external_staff es
ON es.id = e.external_staff_id

JOIN rate_settings rs
ON rs.id = 1

LEFT JOIN college_distance cd
ON cd.college_name = es.college_name

WHERE a.id = ?
";

$stmt = $conn->prepare($sql);

if(!$stmt){
    die("SQL Error : ".$conn->error);
}

$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if(!$row){
    die("Claim not found");
}

/* =================================================
   DETERMINE CLAIM TYPE
================================================= */

$type = !empty($row['external_staff_id']) ? 'external' : 'internal';
/* =================================================
   CALCULATIONS
================================================= */

$da    = $row['da_per_day'];
$ta    = ($row['distance_km'] ?? 0) * 2 * $row['ta_per_km'];
$paper = $row['attendance'] * $row['rate_per_paper'];
$total = $da + $ta + $paper;

$exam_id = $row['exam_id'];

/* =================================================
   UPDATE STATUS
================================================= */

$updateExam = $conn->prepare("
    UPDATE exams
    SET external_claim_status='approved'
    WHERE id=?
");

$updateExam->bind_param("i",$exam_id);
$updateExam->execute();

$updateAttendance = $conn->prepare("
    UPDATE attendance
    SET claim_status='approved'
    WHERE id=?
");

$updateAttendance->bind_param("i",$id);
$updateAttendance->execute();

/* =================================================
   GET DEPARTMENT EMAIL
================================================= */

$departmentEmail = null;

if($sendPdf){

    $deptQuery = $conn->query("
        SELECT email 
        FROM users 
        WHERE role='department'
        LIMIT 1
    ");

    if($deptQuery && $deptQuery->num_rows>0){
        $departmentEmail =
            $deptQuery->fetch_assoc()['email'];
    }
}

/* =================================================
   MAIL PROCESS
================================================= */

try{

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = '231ct020@kongunaducollege.ac.in';
$mail->Password   = 'zyowujjytxuffpqt';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
$mail->Timeout    = 20;

$mail->setFrom(
    '231ct020@kongunaducollege.ac.in',
    'Examiner Cell'
);

if(!empty($row['email'])){
    $mail->addAddress($row['email'],$row['name']);
}

$mail->Subject = "Exam Duty Claim Approved";

$mail->Body = "
Dear {$row['name']},

Your claim has been approved.

Subject : {$row['subject_code']}
Exam Date : {$row['exam_date']}
Attendance : {$row['attendance']}

Total Amount : ₹{$total}

Regards,
Examiner Cell
";

$mail->send();

/* =================================================
   GENERATE PDF
================================================= */

if($sendPdf && $departmentEmail){

require_once('../tcpdf/tcpdf.php');

$pdf = new TCPDF();
$pdf->AddPage();

$claim = $row;

ob_start();
include 'external_claim_pdf_template.php';
$html = ob_get_clean();

$pdf->writeHTML($html);

$pdfDir = dirname(__DIR__)."/pdfs/";

if(!is_dir($pdfDir)){
    mkdir($pdfDir,0777,true);
}

$filePath =
$pdfDir."claim_".$id."_".time().".pdf";

$pdf->Output($filePath,'F');

/* Department Mail */

$deptMail = new PHPMailer(true);

$deptMail->isSMTP();
$deptMail->Host       = 'smtp.gmail.com';
$deptMail->SMTPAuth   = true;
$deptMail->Username   = '231ct020@kongunaducollege.ac.in';
$deptMail->Password   = 'zyowujjytxuffpqt';
$deptMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$deptMail->Port       = 587;

$deptMail->setFrom(
'231ct020@kongunaducollege.ac.in',
'Examiner Cell'
);

$deptMail->addAddress($departmentEmail);

$deptMail->Subject =
"Approved Claim - {$row['name']}";

$deptMail->Body =
"Approved claim PDF attached.";

$deptMail->addAttachment($filePath);

$deptMail->send();

unlink($filePath);
}

/* =================================================
   SUCCESS
================================================= */

echo "
<script>
alert('Claim processed successfully!');
window.location.href='../admin/admin_dashboard.php';
</script>";
exit;

}
catch(Exception $e){

echo "
<script>
alert('Mail Error: {$e->getMessage()}');
window.history.back();
</script>";
}
?>