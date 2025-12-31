<?php
// include/php/forgot_password.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoload
require __DIR__ . '/../../vendor/autoload.php';

/**
 * Generate reset token dan hantar email
 */
function sendPasswordResetEmail($email, $conn) {
    // Sanitize input
    $email = mysqli_real_escape_string($conn, trim($email));
    
    // Check if email exists dan user active
    $sql = "SELECT u.user_id, u.username, u.email, u.role_id, u.status,
                   r.role_name
            FROM user u
            INNER JOIN role r ON u.role_id = r.role_id
            WHERE u.email = ? AND u.status = 1
            LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        return [
            'success' => false,
            'message' => 'Email not found or account is inactive'
        ];
    }
    
    $user = mysqli_fetch_assoc($result);
    
    // Check if user is Accountant - only Accountant can reset password
    if (strtolower($user['role_name']) !== 'accountant') {
        return [
            'success' => false,
            'message' => 'Password reset is only available for Accountant role'
        ];
    }
    
    // Generate unique reset token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid 1 jam
    
    // Simpan token ke database
    $insert_sql = "INSERT INTO password_reset (user_id, token, expiry, created_at) 
                   VALUES (?, ?, ?, NOW())
                   ON DUPLICATE KEY UPDATE token = ?, expiry = ?, created_at = NOW()";
    
    $insert_stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "issss", 
        $user['user_id'], 
        $token, 
        $expiry,
        $token,
        $expiry
    );
    
    if (!mysqli_stmt_execute($insert_stmt)) {
        return [
            'success' => false,
            'message' => 'Failed to generate reset token'
        ];
    }
    
    // Create reset link
    $reset_link = SITE_URL . "/reset_password.php?token=" . $token;
    
    // Send email using PHPMailer
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
        
        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email, $user['username']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - SPInventory';
        
        $mail->Body = getResetEmailTemplate($user['username'], $reset_link);
        $mail->AltBody = "Hello {$user['username']},\n\n"
                       . "You requested to reset your password.\n\n"
                       . "Please click this link to reset: {$reset_link}\n\n"
                       . "This link will expire in 1 hour.\n\n"
                       . "If you didn't request this, please ignore this email.";
        
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Password reset link has been sent to your email'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to send email: ' . $mail->ErrorInfo
        ];
    }
}

/**
 * Verify reset token
 */
function verifyResetToken($token, $conn) {
    $token = mysqli_real_escape_string($conn, $token);
    
    $sql = "SELECT pr.*, u.user_id, u.email, u.username, u.role_id
            FROM password_reset pr
            INNER JOIN user u ON pr.user_id = u.user_id
            WHERE pr.token = ? 
            AND pr.expiry > NOW()
            AND pr.used = 0
            LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        return [
            'success' => false,
            'message' => 'Invalid or expired reset token'
        ];
    }
    
    return [
        'success' => true,
        'data' => mysqli_fetch_assoc($result)
    ];
}

/**
 * Reset password - Update password di table role
 */
function resetPassword($token, $new_password, $conn) {
    // Verify token dulu
    $verify = verifyResetToken($token, $conn);
    
    if (!$verify['success']) {
        return $verify;
    }
    
    $user = $verify['data'];
    
    // Update password di table role
    $update_sql = "UPDATE role SET password = ? WHERE role_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "si", $new_password, $user['role_id']);
    
    if (!mysqli_stmt_execute($update_stmt)) {
        return [
            'success' => false,
            'message' => 'Failed to update password'
        ];
    }
    
    // Mark token as used
    $mark_sql = "UPDATE password_reset SET used = 1 WHERE token = ?";
    $mark_stmt = mysqli_prepare($conn, $mark_sql);
    mysqli_stmt_bind_param($mark_stmt, "s", $token);
    mysqli_stmt_execute($mark_stmt);
    
    return [
        'success' => true,
        'message' => 'Password has been reset successfully'
    ];
}

/**
 * Email template untuk reset password
 */
function getResetEmailTemplate($username, $reset_link) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #b8456d 0%, #9e3a5a 100%); 
                      color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
            .button { display: inline-block; padding: 12px 30px; background: #b8456d; 
                      color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔒 Password Reset Request</h1>
            </div>
            <div class="content">
                <p>Hello <strong>' . htmlspecialchars($username) . '</strong>,</p>
                
                <p>We received a request to reset your password for your SPInventory account.</p>
                
                <p>Click the button below to reset your password:</p>
                
                <p style="text-align: center;">
                    <a href="' . htmlspecialchars($reset_link) . '" class="button">Reset Password</a>
                </p>
                
                <p>Or copy and paste this link into your browser:</p>
                <p style="background: white; padding: 10px; border-left: 4px solid #b8456d; word-break: break-all;">
                    ' . htmlspecialchars($reset_link) . '
                </p>
                
                <p><strong>⏰ This link will expire in 1 hour.</strong></p>
                
                <p>If you didn\'t request this password reset, please ignore this email. Your password will remain unchanged.</p>
                
                <p>Best regards,<br><strong>SPInventory Team</strong></p>
            </div>
            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; ' . date('Y') . ' SPInventory. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
}

/**
 * Clean expired tokens (run via cron job)
 */
function cleanExpiredTokens($conn) {
    $sql = "DELETE FROM password_reset WHERE expiry < NOW() OR used = 1";
    mysqli_query($conn, $sql);
}
?>