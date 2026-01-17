<?php

// =======================================
// GET ACCOUNTANT (paparan profile)
// =======================================
function getAccountant($conn) {
    $sql = "
        SELECT 
            u.user_id,
            u.username,
            u.email,
            u.image,
            u.status,
            r.role_id,
            r.role_name,
            r.password AS role_password
        FROM user u
        LEFT JOIN role r ON u.role_id = r.role_id
        WHERE u.role_id = 1
        LIMIT 1
    ";

    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

// =======================================
// GET ACCOUNTANT BY ID (edit.php)
// =======================================
function getAccountantById($conn, $id) {
    $sql = "
        SELECT 
            u.user_id,
            u.username,
            u.email,
            u.image,
            u.status,
            r.role_id,
            r.role_name,
            r.password AS role_password
        FROM user u
        LEFT JOIN role r ON u.role_id = r.role_id
        WHERE u.user_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result ? $result : null;
}

// =======================================
// UPDATE ACCOUNTANT (profile + password)
// =======================================
function updateAccountant($conn, $id, $name, $email, $password, $imageName) {

    // Ambil data lama
    $old = getAccountantById($conn, $id);
    if (!$old) return false;

    // Mulakan transaction untuk data consistency
    $conn->begin_transaction();

    try {
        // ---------------------------
        // HANDLE PASSWORD (ROLE)
        // ---------------------------
        if (!empty(trim($password))) {
            // Hash password BARU (sekali sahaja)
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $sqlRole = "UPDATE role SET password = ? WHERE role_id = ?";
            $stmtRole = $conn->prepare($sqlRole);
            $stmtRole->bind_param("si", $hash, $old['role_id']);
            
            if (!$stmtRole->execute()) {
                throw new Exception("Failed to update password");
            }
            $stmtRole->close();
        }

        // ---------------------------
        // HANDLE IMAGE
        // ---------------------------
        $finalImage = !empty($imageName) ? $imageName : $old['image'];

        // ---------------------------
        // UPDATE USER PROFILE
        // ---------------------------
        $sqlUser = "
            UPDATE user 
            SET 
                username = ?,
                email = ?,
                image = ?
            WHERE user_id = ?
        ";

        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bind_param("sssi", $name, $email, $finalImage, $id);
        
        if (!$stmtUser->execute()) {
            throw new Exception("Failed to update user profile");
        }
        $stmtUser->close();

        // Commit transaction jika semua berjaya
        $conn->commit();
        return true;

    } catch (Exception $e) {
        // Rollback jika ada error
        $conn->rollback();
        error_log("Update Accountant Error: " . $e->getMessage());
        return false;
    }
}

// =======================================
// DELETE OLD IMAGE (optional - helper function)
// =======================================
function deleteOldImage($imagePath) {
    if (!empty($imagePath) && file_exists($imagePath)) {
        unlink($imagePath);
    }
}

?>