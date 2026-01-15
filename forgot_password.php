<?php
// forgot_password.php (root folder)
session_start();

// Kalau dah login, redirect
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'include/php/config.php';
    require_once 'include/php/forgot_password.php';
    
    $email = $_POST['email'];
    
    $result = sendPasswordResetEmail($email, $conn);
    
    if ($result['success']) {
        $message = $result['message'];
        $message_type = 'success';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SPInventory</title>
    <link rel="stylesheet" href="./include/css/accountant.css">
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
        
        .forgot-password-container {
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
        
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            border-left: 4px solid #b8456d;
        }
        
        .info-box p {
            font-size: 13px;
            color: #666;
            line-height: 1.6;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .forgot-password-container {
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
    <div class="forgot-password-container">
        <div class="header-section">
            <h1>🔒 Forgot Password</h1>
            <p>Enter your email to receive password reset instructions</p>
        </div>
        
        <div class="form-section">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message_type === 'success' ? '✅' : '❌'; ?>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your registered email"
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <button type="submit" class="btn-submit">Send Reset Link</button>
            </form>
            
            <div class="back-to-login">
                <a href="login.php">← Back to Login</a>
            </div>
            
            <div class="info-box">
                <p>
                    📧 You will receive an email with instructions to reset your password. 
                    The link will be valid for 1 hour.
                </p>
            </div>
        </div>
    </div>
</body>
</html>