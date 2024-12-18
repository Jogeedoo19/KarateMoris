<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor\autoload.php';
function sendEmail(){
$mail = new PHPMailer(TRUE);
/* Open the try/catch block. */
try {
/* Set the mail sender. */
$mail->setFrom('gjogeedoo@gmail.com', 'Joe');
/* Add a recipient. */
$mail->addAddress($_POST["txtemail"], 'Username');
//$mail->addCC('janeadmin@gmail.com', 'Jane Lauren');
//$mail->addBCC('lukedane@hotmail.com', 'Luke Daniel');

/* Set the subject. */
$mail->Subject = 'Welcome to KarateMoris';
/* Set the mail message body. */
$mail->isHTML(TRUE);
$mail->Body = '<html>Your account has been activated, you may now join
the KarateMoris. Click on the link to <strong>
<a href="http://localhost:8080/wat2108c/oswt/client/login.php">login</a></strong>.</html>';
$mail->AltBody = 'In case the html does not work.';
$fn = $_FILES['profilepic']['name'];
//to ensure image is available in folder upload
$mail->addAttachment("..\upload" . DIRECTORY_SEPARATOR. $fn);
/* SMTP parameters. */
/* Tells PHPMailer to use SMTP. */
$mail->isSMTP();
/* SMTP server address. */
$mail->Host = 'smtp.gmail.com';
/* Use SMTP authentication. */
$mail->SMTPAuth = TRUE;
/* Set the encryption system. */
$mail->SMTPSecure = 'tls';
/* SMTP authentication username. */
$mail->Username = 'gjogeedoo@gmail.com';
/* SMTP authentication password. */
$mail->Password = 'ztqgxjiflfytxtba';
/* Set the SMTP port. */
$mail->Port = 587;
/* Finally send the mail. */
$retval = $mail->send();
if( $retval == true ) {
$_SESSION['successmsg'] = "Registration successful, please check your email...";
}else {
$_SESSION['errormsg'] = "Email could not be sent...";
}
}

catch (Exception $e)
{
/* PHPMailer exception. */
echo $e->errorMessage();
}
catch (\Exception $e)
{
/* PHP exception (note the backslash to select the global namespace
Exception class). */
echo $e->getMessage();
}
}

?>