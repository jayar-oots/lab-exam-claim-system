<?php
require_once '../config/mail_config.php';

/* ==== SEND OTP FUNCTION ==== */
function sendOTP($toEmail, $otp)
{
    try {
        $mail = getMailer();

        $mail->addAddress($toEmail);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "
            <h3>Password Reset</h3>
            <p>Your OTP is:</p>
            <h2 style='color:blue;'>$otp</h2>
            <p>This OTP is valid for <b>10 minutes</b>.</p>
            <p>If you did not request this, please ignore.</p>
        ";

        return $mail->send();

    } catch (Exception $e) {
        return false;
    }
}
