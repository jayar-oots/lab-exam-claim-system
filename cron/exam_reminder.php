<?php
date_default_timezone_set('Asia/Kolkata');

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
    die("PHPMailer class NOT found ❌");
} else {
    echo "PHPMailer class loaded successfully ✅<br>";
}


echo "Autoload loaded successfully<br>";

echo "<h3>Reminder file executed</h3>";

$now = date('Y-m-d H:i:s');
echo "Current Time: $now <br><br>";

/* =========================================
   FIND TODAY EXAMS
========================================= */

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

    echo "<hr>";
    echo "Checking Exam ID: " . $exam['id'] . "<br>";

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

    echo "Exam Time: $examTime <br>";

    $reminderTime = date('Y-m-d H:i:s', strtotime($examTime . ' -30 minutes'));

    echo "Reminder Time: $reminderTime <br>";

    /* ===== CHECK TIME MATCH ===== */

    if ($now >= $reminderTime && $now <= $examTime) {

        echo "<b>Mail condition matched ✅</b><br>";

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'examcell@gmail.com';  // YOUR EMAIL
            $mail->Password   = 'fviypdyipbnbxbna';                  // APP PASSWORD
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('examcell@gmail.com', 'Lab Exam System');
            $mail->addAddress($exam['internal_email']);

            $mail->isHTML(true);
            $mail->Subject = "Lab Exam Reminder - {$exam['subject_code']}";

            $mail->Body = "
                <h3>Lab Exam Reminder</h3>
                <p><b>Internal:</b> {$exam['internal_name']}</p>
                <p><b>Subject Code:</b> {$exam['subject_code']}</p>
                <p><b>Subject Name:</b> {$exam['subject_name']}</p>
                <p><b>Lab:</b> {$exam['lab_no']}</p>
                <p><b>External Examiner:</b> {$exam['external_name']}</p>
                <p><b>External Mobile:</b> {$exam['external_phone']}</p>
                <br>
                <p>Please be ready for the session.</p>
            ";
            $mail->SMTPDebug = 2;
$mail->Debugoutput = 'html';

$mail->send();

           
            

            echo "Mail sent successfully to: " . $exam['internal_email'] . "<br>";

            /* ===== MARK AS SENT ===== */

            $update = $conn->prepare("UPDATE exams SET reminder_sent = 1 WHERE id = ?");
            $update->bind_param("i", $exam['id']);
            $update->execute();

            echo "Reminder marked as sent in DB ✔<br>";

        } catch (Exception $e) {

            echo "<span style='color:red;'>Mail Error: {$mail->ErrorInfo}</span><br>";
        }

    } else {

        echo "Condition not matched ❌<br>";
    }
}

echo "<br><b>Reminder process completed.</b>";
?>
