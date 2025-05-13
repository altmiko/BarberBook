<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';


$mail = new PHPMailer(true);

try {

    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
    $mail->isSMTP();                                         
    $mail->Host       = 'smtp.gmail.com';                    
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = 'barberbookservices@gmail.com';                 
    $mail->Password   = 'aiqd szub qvlu cdsb';                            
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
    $mail->Port       = 465;                                

    $mail->setFrom('barberbookservices@gmail.com', 'BarberBook');
    $mail->addAddress('shameem.alienware00@gmail.com', 'Shameem');   
    $mail->addReplyTo('barberbookservices@gmail.com', 'BarberBook');
    // $mail->addCC('cc@example.com');


    $mail->isHTML(true);                               
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'Testing!';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}