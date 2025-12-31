<?php
require './vendor/autoload.php';
require './include/php/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    
    // Debug mode - uncomment untuk tengok details
    // $mail->SMTPDebug = 2;
    
    // Recipients
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress('afifketot@gmail.com', 'Test User'); // 👈 Tukar dengan email anda untuk test
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from SPInventory';
    $mail->Body    = '<h1>Email Configuration Works!</h1><p>PHPMailer is configured correctly.</p>';
    
    $mail->send();
    echo '✅ Email sent successfully! Check your inbox.';
    
} catch (Exception $e) {
    echo "❌ Email error: {$mail->ErrorInfo}";
}
?>