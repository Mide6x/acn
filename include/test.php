<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer();
echo "Mail is about";

$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host       = "letstango.com"; // SMTP server
$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->SMTPSecure = "tls";                 
$mail->Host       = "smtp.gmail.com";      // SMTP server
$mail->Port       = 587;                   // SMTP port
$mail->Username   = "";  // username
$mail->Password   = "";            // password

$mail->SetFrom('acreontop@gmail.com', 'Test');

$mail->Subject    = "I hope this works!";

$mail->MsgHTML('Blah');

$address = "ace@syntaximagination.com";
$mail->AddAddress($address, "Test");

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}

