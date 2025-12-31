<?php
session_start();

function loginUser($email, $password, $conn) {

    $query = "SELECT u.user_id, u.username, u.email, u.status, u.image,
                     r.role_id, r.role_name, r.password AS role_password
              FROM user u
              INNER JOIN role r ON u.role_id = r.role_id
              WHERE u.email = ? AND u.status = 1
              LIMIT 1";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {

        $user = mysqli_fetch_assoc($result);

        // Verify password dengan role password
        if (password_verify($password, $user['role_password'])) {

            // Set session
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role_id']   = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['image'] = $user['image'];
            $_SESSION['logged_in'] = true;

            // Determine redirect based on role
            $redirect = '';
            
            switch (strtolower($user['role_name'])) {
                case 'accountant':
                    $redirect = './accountant/index.php';
                    break;
                    
                case 'production':
                    $redirect = './productionManager/production_dashboard.php';
                    break;
                    
                default:
                    // Jika role lain, redirect ke default page
                    $redirect = 'page/dashboard.php';
                    break;
            }

            return [
                'success'  => true,
                'redirect' => $redirect,
                'role'     => $user['role_name']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Password tidak betul'
            ];
        }
    }

    return [
        'success' => false,
        'message' => 'Email tidak dijumpai atau akaun tidak aktif'
    ];
}
?>