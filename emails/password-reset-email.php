<?php


require_once __DIR__ . "/../app/flow.php";
checkFlowAccess();

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../helpers/url.php";
require_once __DIR__ . "/../app/configs.php";


use PHPMailer\PHPMailer\PHPMailer;



/**
 * Send password reset token
 * @param string $email
 * @param string $name
 * @param string $token
 * @param string $validDuration Token valid duration in minutes
 */
function sendPasswordResetRequestEmail($email, $name, $token, $validDuration)
{
    global $config;
    global $mailConfig;

    $mail = new PHPMailer();

    $url = globalUrlPath("/password-reset/reset-password.php?token={$token}");
    
    $mail->CharSet =  "utf-8";
    $mail->IsSMTP();

    // enable SMTP authentication
    $mail->SMTPAuth = true;

    // GMAIL username
    $mail->Username = $mailConfig->username;

    // GMAIL password
    $mail->Password = $mailConfig->password;
    $mail->SMTPSecure = "ssl";

    // sets GMAIL as the SMTP server
    $mail->Host = $mailConfig->host;

    // set the SMTP port for the GMAIL server
    $mail->Port = $mailConfig->port;
    $mail->From = $mailConfig->email;
    $mail->FromName = $config->appname;

    $mail->AddAddress($email, $name);

    $mail->Subject  =  'Reset Password';
    $mail->IsHTML(true);
    $mail->Body   = "Click on this link to reset password <a href='{$url}'>click to reset password</a> (valid {$validDuration} minutes).";
 
    return $mail->send();   
}
