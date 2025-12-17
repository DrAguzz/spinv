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
    return $result->fetch_assoc();
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
    return $stmt->get_result()->fetch_assoc();
}

// =======================================
// UPDATE ACCOUNTANT (profile + password)
// =======================================
function updateAccountant($conn, $id, $name, $email, $password, $imageName) {

    // Ambil data lama
    $old = getAccountantById($conn, $id);
    if (!$old) return false;

    // ---------------------------
    // HANDLE PASSWORD (ROLE)
    // ---------------------------
    if (!empty(trim($password))) {

        // Hash password BARU (sekali sahaja)
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sqlRole = "
            UPDATE role 
            SET password = ?
            WHERE role_id = ?
        ";
        $stmtRole = $conn->prepare($sqlRole);
        $stmtRole->bind_param("si", $hash, $old['role_id']);
        $stmtRole->execute();
    }
    // jika password kosong → tak buat apa-apa (kekalkan hash lama)

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

    return $stmtUser->execute();
}

?>
