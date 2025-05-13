<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();                                         
        $mail->Host       = 'smtp.gmail.com';                    
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = 'barberbookservices@gmail.com';                 
        $mail->Password   = 'aiqd szub qvlu cdsb';                            
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = 465;                                

        $mail->setFrom('barberbookservices@gmail.com', 'BarberBook');
        $mail->addAddress('iamnotsham@gmail.com');   // Main recipient
        if ($to !== 'iamnotsham@gmail.com') {  // Add original recipient as CC if different
            $mail->addCC($to);
        }
        $mail->addReplyTo('barberbookservices@gmail.com', 'BarberBook');

        $mail->isHTML(true);                               
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}