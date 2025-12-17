<?php
// include/php/userManagement/role.php

function getAllRoles($conn) {
    $sql = "SELECT * FROM role ORDER BY role_id ASC";
    return $conn->query($sql);
}

function getRoleById($conn, $role_id) {
    $sql = "SELECT * FROM role WHERE role_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function addRole($conn, $role_name, $password) {
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO role (role_name, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $role_name, $hashed_password);
    
    if ($stmt->execute()) {
        return true;
    }
    return false;
}

function updateRole($conn, $role_id, $role_name, $password = null) {
    if ($password !== null && $password !== '') {
        // Update dengan password baru
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE role SET role_name = ?, password = ? WHERE role_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $role_name, $hashed_password, $role_id);
    } else {
        // Update tanpa ubah password
        $sql = "UPDATE role SET role_name = ? WHERE role_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $role_name, $role_id);
    }
    
    if ($stmt->execute()) {
        return true;
    }
    return false;
}

function deleteRole($conn, $role_id) {
    // Jangan bagi delete role id 1 (Admin) dan role yang ada user
    if ($role_id == 1) {
        return false;
    }
    
    // Check jika ada user guna role ini
    $check = "SELECT COUNT(*) as count FROM users WHERE role_id = ?";
    $stmt = $conn->prepare($check);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        return false; // Ada user guna role ini
    }
    
    $sql = "DELETE FROM roles WHERE role_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $role_id);
    
    if ($stmt->execute()) {
        return true;
    }
    return false;
}

function verifyRolePassword($conn, $role_id, $password) {
    $role = getRoleById($conn, $role_id);
    if ($role && password_verify($password, $role['password'])) {
        return true;
    }
    return false;
}
?>