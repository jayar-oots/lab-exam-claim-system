<?php
date_default_timezone_set('Asia/Kolkata');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';


/* =========================================
   FIND EXAMS 30 MINUTES BEFORE SESSION
========================================= */

$now = date('Y-m-d H:i:s');

$sql = "
SELECT 
    e.*,
    i.email AS internal_email,
    i.name AS internal_name,
    es.name AS external_name,
    es.phone AS external_phone
FROM exams e
LEFT JOIN internal_staff i ON e.internal_staff_id = i.id
LEFT JOIN external_staff es ON e.external_staff_id = es.id
WHERE e.exam_date = CURDATE()
AND e.reminder_sent = 0
";

$result = $conn->query($sql);

while ($exam = $result->fetch_assoc()) {

    $examDate = $exam['exam_date'];
    $session  = $exam['session'];

    /* ===== SET SESSION TIME ===== */

    if ($session == 'forenoon') {
        $examTime = "$examDate 09:30:00";
    } 
    elseif ($session == 'afternoon') {
        $examTime = "$examDate 13:30:00";
    } 
    elseif ($session == 'forenoon_afternoon') {
        $examTime = "$examDate 09:30:00";
    } 
    else {
        continue;
    }

    $reminderTime = date('Y-m-d H:i:s', strtotime($examTime . ' -30 minutes'));

    /* ===== CHECK IF TIME MATCHES ===== */

    if ($now >= $reminderTime && $now <= $examTime) {

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '231ct020@kongunaducollege.ac.in';   // CHANGE
            $mail->Password   = 'your_app_password';     // CHANGE
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('yourgmail@gmail.com', 'Lab Exam System');
            $mail->addAddress($exam['internal_email']);

            $mail->isHTML(true);
            $mail->Subject = "Lab Exam Reminder - {$exam['subject_code']}";

            $mail->Body = "
                <h3>Lab Exam Reminder</h3>
                <p><b>Subject Code:</b> {$exam['subject_code']}</p>
                <p><b>Subject Name:</b> {$exam['subject_name']}</p>
                <p><b>External Examiner:</b> {$exam['external_name']}</p>
                <p><b>External Mobile:</b> {$exam['external_phone']}</p>
                <br>
                <p>Please be prepared for the session.</p>
            ";

            $mail->send();

            /* ===== MARK AS SENT ===== */
            $update = $conn->prepare("UPDATE exams SET reminder_sent = 1 WHERE id = ?");
            $update->bind_param("i", $exam['id']);
            $update->execute();

        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
        }
    }
}
