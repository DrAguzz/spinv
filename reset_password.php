<?php
// reset_password.php (root folder)
session_start();

include 'include/php/config.php';
require_once 'include/php/forgot_password.php';

$message = '';
$message_type = '';
$token_valid = false;

// Check if token exists
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];

// Verify token
$verify = verifyResetToken($token, $conn);

if (!$verify['success']) {
    $message = $verify['message'];
    $message_type = 'error';
} else {
    $token_valid = true;
    $user_data = $verify['data'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($new_password) || empty($confirm_password)) {
        $message = 'Please fill in all fields';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Passwords do not match';
        $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'Password must be at least 6 characters';
        $message_type = 'error';
    } else {
        // Reset password
        $result = resetPassword($token, $new_password, $conn);
        
        if ($result['success']) {
            $message = $result['message'];
            $message_type = 'success';
            $token_valid = false; // Disable form after success
        } else {
            $message = $result['message'];
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SPInventory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8edf2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .reset-password-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }
        
        .header-section {
            background: linear-gradient(135deg, #b8456d 0%, #9e3a5a 100%);
            padding: 40px;
            text-align: center;
            color: white;
        }
        
        .header-section h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header-section p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .form-section {
            padding: 40px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #b8456d;
            box-shadow: 0 0 0 3px rgba(184, 69, 109, 0.1);
        }
        
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }
        
        .strength-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .strength-bar-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #b8456d 0%, #9e3a5a 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(184, 69, 109, 0.3);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-to-login a {
            color: #b8456d;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        
        .back-to-login a:hover {
            text-decoration: underline;
        }
        
        .success-message {
            text-align: center;
            padding: 30px;
        }
        
        .success-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .success-message h2 {
            color: #28a745;
            margin-bottom: 15px;
        }
        
        .success-message p {
            color: #666;
            margin-bottom: 25px;
        }
        
        .btn-login {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #b8456d 0%, #9e3a5a 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(184, 69, 109, 0.3);
        }
        
        @media (max-width: 768px) {
            .reset-password-container {
                margin: 20px;
            }
            
            .header-section {
                padding: 30px 20px;
            }
            
            .form-section {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="header-section">
            <h1>🔑 Reset Password</h1>
            <p>Create a new password for your account</p>
        </div>
        
        <div class="form-section">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message_type === 'success' ? '✅' : '❌'; ?>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($message_type === 'success'): ?>
                <!-- Success state -->
                <div class="success-message">
                    <div class="success-icon">✓</div>
                    <h2>Password Reset Successful!</h2>
                    <p>Your password has been successfully reset. You can now login with your new password.</p>
                    <a href="login.php" class="btn-login">Go to Login</a>
                </div>
            <?php elseif ($token_valid): ?>
                <!-- Form untuk reset password -->
                <form method="POST" action="" id="resetForm">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required 
                               placeholder="Enter new password" minlength="6">
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-bar-fill" id="strengthBar"></div>
                            </div>
                            <span id="strengthText"></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm new password" minlength="6">
                    </div>
                    
                    <button type="submit" class="btn-submit">Reset Password</button>
                </form>
            <?php else: ?>
                <!-- Invalid/expired token -->
                <div class="back-to-login">
                    <p style="color: #666; margin-bottom: 15px;">This reset link is invalid or has expired.</p>
                    <a href="forgot_password.php">← Request New Reset Link</a>
                </div>
            <?php endif; ?>
            
            <?php if ($token_valid || $message_type !== 'success'): ?>
                <div class="back-to-login">
                    <a href="login.php">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Password strength checker
        const passwordInput = document.getElementById('new_password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 6) strength++;
                if (password.length >= 10) strength++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/\d/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;
                
                strengthBar.className = 'strength-bar-fill';
                
                if (strength <= 2) {
                    strengthBar.classList.add('strength-weak');
                    strengthText.textContent = 'Weak password';
                    strengthText.style.color = '#dc3545';
                } else if (strength <= 4) {
                    strengthBar.classList.add('strength-medium');
                    strengthText.textContent = 'Medium password';
                    strengthText.style.color = '#ffc107';
                } else {
                    strengthBar.classList.add('strength-strong');
                    strengthText.textContent = 'Strong password';
                    strengthText.style.color = '#28a745';
                }
            });
        }
        
        // Confirm password validation
        const form = document.getElementById('resetForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                }
            });
        }
    </script>
</body>
</html>