<?php
// Contoh penggunaan dalam login.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'include/php/config.php'; // File connection database
    require_once './include/php/login.php';
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $result = loginUser($email, $password, $conn);
    
    if ($result['success']) {
        // Redirect ke modul yang sesuai
        header("Location: " . $result['redirect']);
        exit();
    } else {
        // Simpan error message untuk dipaparkan
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPInventory - Login</title>
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
        
        .login-wrapper {
            display: flex;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        
        .left-section {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }
        
        .logo {
            margin-bottom: 40px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #b8456d 0%, #9e3a5a 100%);
            display: inline-block;
            margin-right: 12px;
            vertical-align: middle;
            position: relative;
            border-radius: 4px;
        }
        
        .logo-icon::before,
        .logo-icon::after {
            content: '';
            position: absolute;
            background: rgba(255,255,255,0.3);
            border-radius: 2px;
        }
        
        .logo-icon::before {
            width: 20px;
            height: 2px;
            top: 12px;
            left: 10px;
            transform: rotate(45deg);
        }
        
        .logo-icon::after {
            width: 2px;
            height: 20px;
            top: 10px;
            left: 19px;
            transform: rotate(45deg);
        }
        
        .logo-text {
            display: inline-block;
            vertical-align: middle;
        }
        
        .logo-text h1 {
            font-size: 24px;
            color: #9e3a5a;
            font-weight: 600;
            letter-spacing: 1px;
            margin: 0;
            line-height: 1;
        }
        
        .logo-text span {
            font-size: 18px;
            color: #b8456d;
            font-weight: 400;
        }
        
        .welcome-text h2 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .welcome-text p {
            color: #7f8c8d;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .right-section {
            flex: 1;
            background: linear-gradient(135deg, #b8456d 0%, #9e3a5a 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-form {
            width: 100%;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #ffffff;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            font-size: 15px;
            background: rgba(255,255,255,0.95);
            transition: all 0.3s ease;
            color: #2c3e50;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ffffff;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .form-group input::placeholder {
            color: #95a5a6;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-login:hover {
            background: rgba(255,255,255,0.3);
            border-color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: rgba(255,255,255,0.95);
            color: #c0392b;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            border-left: 4px solid #c0392b;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }
            
            .left-section,
            .right-section {
                padding: 40px 30px;
            }
            
            .logo {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="left-section">
            <div class="logo">
                <div class="logo-text">
                    <img src="./image/stone-logo.png" alt="">
                </div>
            </div>
            
            <div class="welcome-text">
                <h2>Welcome to SPInventory</h2>
                <p>Please login to access<br>the main pages</p>
            </div>
        </div>
        
        <div class="right-section">
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">email</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>