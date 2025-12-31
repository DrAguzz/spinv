<?php
// include/php/login.php

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Login user dengan email dan password
 * Password diambil dari table role
 */
function loginUser($email, $password, $conn) {
    // Sanitize input
    $email = $conn->real_escape_string(trim($email));
    $password = trim($password);
    
    // Query untuk ambil user details dengan role
    $sql = "SELECT u.user_id, u.username, u.email, u.role_id, u.status, u.image,
                   r.role_id, r.role_name, r.password as role_password
            FROM user u
            INNER JOIN role r ON u.role_id = r.role_id
            WHERE u.email = ? AND u.status = 1
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return [
            'success' => false,
            'message' => 'Email not found or account is inactive'
        ];
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password dengan password dari role
    if ($password !== $user['role_password']) {
        return [
            'success' => false,
            'message' => 'Invalid password'
        ];
    }
    
    // Set session
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'];
    $_SESSION['user_image'] = $user['image'];
    
    // Determine redirect based on role
    $redirect = '';
    switch (strtolower($user['role_name'])) {
        case 'accountant':
            $redirect = 'accountant/index.php';
            break;
        case 'production':
            $redirect = 'production/index.php';
            break;
        case 'purchasing':
            $redirect = 'purchasing/index.php';
            break;
        default:
            $redirect = 'dashboard.php';
    }
    
    return [
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirect,
        'user' => [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'role_name' => $user['role_name']
        ]
    ];
}

/**
 * Logout user
 */
function logoutUser() {
    session_start();
    session_unset();
    session_destroy();
    
    header("Location: " . SITE_URL . "/login.php");
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user has specific role
 */
function hasRole($role_name) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return strtolower($_SESSION['role_name']) === strtolower($role_name);
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "/login.php");
        exit();
    }
}

/**
 * Require specific role - redirect if user doesn't have role
 */
function requireRole($role_name) {
    requireLogin();
    
    if (!hasRole($role_name)) {
        header("Location: " . SITE_URL . "/unauthorized.php");
        exit();
    }
}
?>