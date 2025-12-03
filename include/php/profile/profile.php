<?php

// =======================================
// GET ACCOUNTANT (untuk paparan profile)
// =======================================
function getAccountant($conn) {
    $sql = "
        SELECT u.*, r.role_name, r.password
        FROM user u
        LEFT JOIN role r ON u.role_id = r.role_id
        WHERE u.role_id = '1'
        LIMIT 1
    ";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// =======================================
// GET ACCOUNTANT BY ID (untuk edit.php)
// =======================================
function getAccountantById($conn, $id) {
    $sql = "
        SELECT u.*, r.role_name, r.password
        FROM user u
        LEFT JOIN role r ON u.role_id = r.role_id
        WHERE u.user_id = '$id'
        LIMIT 1
    ";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// =======================================
// UPDATE ACCOUNTANT (profile + password)
// =======================================
function updateAccountant($conn, $id, $name, $email, $password, $imageName) {

    // Get current data
    $old = getAccountantById($conn, $id);

    // ---------------------------
    // HANDLE PASSWORD
    // ---------------------------
    if (!empty($password)) {
        // Hash password baru
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update password dalam role (bukan table user)
        $sqlRole = "
            UPDATE role
            SET password = '$hashedPassword'
            WHERE role_id = '{$old['role']}'
        ";
        $conn->query($sqlRole);

    } else {
        // Jika password kosong → kekalkan password role lama
        $hashedPassword = $old['role_password'];
    }

    // ---------------------------
    // HANDLE IMAGE
    // ---------------------------
    if (!empty($imageName)) {
        $finalImage = $imageName;
    } else {
        $finalImage = $old['image']; // kekalkan
    }

    // ---------------------------
    // UPDATE USER PROFILE
    // ---------------------------
    $sql = "
        UPDATE user 
        SET 
            username = '$name',
            email = '$email',
            image = '$finalImage'
        WHERE user_id = '$id'
    ";

    return $conn->query($sql);
}

?>
