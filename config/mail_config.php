<?php
date_default_timezone_set('Asia/Kolkata');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ==== LOAD PHPMailer FILES ==== */
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

/* ==== MAIL CONFIG FUNCTION ==== */
function getMailer()
{
    $mail = new PHPMailer(true);

    // SMTP SETTINGS
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // 👇 CHANGE ONLY THESE TWO
    $mail->Username   = '231ct020@kongunaducollege.ac.in';
    $mail->Password   = 'fviypdyipbnbxbna';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // MAIL SETTINGS
    $mail->setFrom('231ct020@kongunaducollege.ac.in', 'Lab Claim System');
    $mail->isHTML(true);

    return $mail;
}
