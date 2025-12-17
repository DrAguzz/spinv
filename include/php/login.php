<?php
session_start(); // 🔴 INI YANG AWAK TERLEPAS

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

        if (password_verify($password, $user['role_password'])) {

            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['logged_in'] = true;

            return [
                'success'  => true,
                'redirect' => 'accountant/index.php'
            ];
        }
    }

    return [
        'success' => false,
        'message' => 'Login gagal'
    ];
}
