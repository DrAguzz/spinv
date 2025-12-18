<?php
// include/php/auth/role_helper.php

/**
 * Get redirect path based on role
 */
function getRoleRedirect($role_name) {
    $redirects = [
        'accountant' => 'accountant/accountant/index.php',
        'production' => 'productionManager/production_dashboard.php',
    ];
    
    $role_lower = strtolower($role_name);
    
    return isset($redirects[$role_lower]) 
        ? $redirects[$role_lower] 
        : '/login.php';
}

/**
 * Check if user has permission to access a page
 */
function hasRoleAccess($required_role, $current_role = null) {
    if ($current_role === null) {
        $current_role = $_SESSION['role_name'] ?? '';
    }
    
    $required_roles = is_array($required_role) ? $required_role : [$required_role];
    
    return in_array(strtolower($current_role), array_map('strtolower', $required_roles));
}

/**
 * Redirect if not authorized
 */
function requireRole($required_role, $redirect_to = '../../login.php') {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: $redirect_to");
        exit();
    }
    
    if (!hasRoleAccess($required_role)) {
        // Redirect ke dashboard role sendiri
        $redirect = getRoleRedirect($_SESSION['role_name']);
        header("Location: ../../$redirect");
        exit();
    }
}

/**
 * Check if user is logged in
 */
function requireLogin($redirect_to = '../../login.php') {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: $redirect_to");
        exit();
    }
}

/**
 * Get role display name
 */
function getRoleDisplayName($role_name) {
    $names = [
        'accountant' => 'Accountant',
        'production' => 'Production Manager',
    ];
    
    $role_lower = strtolower($role_name);
    
    return isset($names[$role_lower]) 
        ? $names[$role_lower] 
        : ucfirst($role_name);
}

/**
 * Check if user can edit/delete (for view-only roles)
 */
function canModifyData($role_name = null) {
    if ($role_name === null) {
        $role_name = $_SESSION['role_name'] ?? '';
    }
    
    $view_only_roles = ['production']; // Roles yang view only
    
    return !in_array(strtolower($role_name), $view_only_roles);
}

/**
 * Logout user
 */
function logoutUser() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>