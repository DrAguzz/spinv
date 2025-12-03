<?php
// ========================================
// FUNCTION: GET ROLE LIST
// ========================================
function getRoleList($conn){
    $sql = "SELECT * FROM role ORDER BY role_name ASC";
    return $conn->query($sql);
}

// ========================================
// FUNCTION: GET ALL USERS
// ========================================
function getAllUsers($conn){
    $sql = "
        SELECT 
            u.user_id,
            u.username,
            u.email,
            u.status,
            u.image,
            u.role_id,
            r.role_name
        FROM user u
        LEFT JOIN role r ON r.role_id = u.role_id
        ORDER BY u.user_id ASC
    ";
    return $conn->query($sql);
}

// ========================================
// FUNCTION: ADD USER
// ========================================
function addUser($conn, $data, $file){
    $name  = $data['name'];
    $email = $data['email'];
    $role  = $data['role'];

    $imagePath = "default.png";

    // Validate gambar
    if (!empty($file['image']['name'])) {

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 2 * 1024 * 1024;

        $fileType = mime_content_type($file['image']['tmp_name']);
        $fileExt  = strtolower(pathinfo($file['image']['name'], PATHINFO_EXTENSION));
        $fileSize = $file['image']['size'];

        if (!in_array($fileType, $allowedTypes) || !in_array($fileExt, ['jpg','jpeg','png'])) {
            throw new Exception("Format fail tidak dibenarkan! Hanya JPG/PNG.");
        }

        if ($fileSize > $maxSize) {
            throw new Exception("Fail melebihi 2MB.");
        }

        $folder = "../../include/uploads/user/";
        if (!file_exists($folder)) mkdir($folder, 0777, true);

        $imageName = time() . "_" . basename($file['image']['name']);
        $targetFile = $folder . $imageName;

        if (move_uploaded_file($file['image']['tmp_name'], $targetFile)) {
            $imagePath = $imageName;
        }
    }

    $sql = "INSERT INTO user (username, email, role_id, image, status)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $status = 1;

    $stmt->bind_param("ssiss", $name, $email, $role, $imagePath, $status);

    return $stmt->execute();
}

// ========================================
// FUNCTION: TOGGLE STATUS (1 → 0 / 0 → 1)
// ========================================
function toggleUserStatus($conn, $user_id){
    $sql = "SELECT status FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) return false;

    $newStatus = ($row['status'] == 1) ? 0 : 1;

    $sql2 = "UPDATE user SET status = ? WHERE user_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ii", $newStatus, $user_id);

    return $stmt2->execute();
}

// ========================================
// FUNCTION: DELETE USER
// ========================================
function deleteUser($conn, $user_id){
    $sql = "DELETE FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// ========================================
// FUNCTION: GET USER BY ID
// ========================================
function getUserById($conn, $user_id){
    $sql = "
        SELECT 
            u.user_id,
            u.username,
            u.email,
            u.status,
            u.image,
            r.role_id,
            r.role_name
        FROM user u
        LEFT JOIN role r ON r.role_id = u.role_id
        WHERE u.user_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

// ========================================
// FUNCTION: UPDATE USER
// ========================================
function updateUser($conn, $id, $data, $file){
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $role = $conn->real_escape_string($data['role']);

    $imageSql = "";

    if(!empty($file['image']['name'])){

        $old = getUserById($conn, $id);
        if (!empty($old['image']) && file_exists("../include/uploads/user/" . $old['image'])) {
            unlink("../../include/uploads/user/" . $old['image']);
        }

        $imageName = time() . "_" . basename($file['image']['name']);
        $uploadPath = "../../include/uploads/user/" . $imageName;

        move_uploaded_file($file['image']['tmp_name'], $uploadPath);

        $imageSql = ", image='$imageName'";
    }

    $sql = "
        UPDATE user SET
            username='$name',
            email='$email',
            role_id='$role'
            $imageSql
        WHERE user_id='$id'
    ";

    return $conn->query($sql);
}

// ========================================
// HANDLE GET ACTION (toggle/delete)
// ========================================
if(isset($_GET['action']) && isset($_GET['id'])){
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];

    if($action === "toggle"){
        toggleUserStatus($conn, $user_id);
    }
    elseif($action === "delete"){
        deleteUser($conn, $user_id);
    }

    header("Location: index.php");
    exit;
}
?>
