<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit();
}
if (strtolower($_SESSION['role_name']) !== 'accountant') {
    header("Location: ../../login.php");
    exit();
}

$nav = "../";
$link = "../../include/";
$imgLink = "../../";
include($link . "container/head.php");
include($link . "container/nav.php");
require($link . "php/config.php");
require_once($link . "php/profile/profile.php");

$id = $_GET['id'];

// Ambil data accountant
$user = getAccountantById($conn, $id);

// Jika user tiada
if (!$user) {
    echo "<script>alert('Rekod tidak ditemui!'); window.location.href='profile.php';</script>";
    exit;
}

// ===============================
// HANDLE UPDATE PROFILE
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];   

    // Handle upload image
    $imageName = "";
    if (!empty($_FILES['image']['name'])) {
        // Validate file type
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($fileExt, $allowed)) {
            // Gunakan nama user + timestamp untuk nama file
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($name));
            $imageName = $cleanName . "_" . time() . "." . $fileExt;
            $uploadPath = "../../uploads/users/" . $imageName;
            
            // Create directory if not exists
            if (!is_dir("../../uploads/users/")) {
                mkdir("../../uploads/users/", 0777, true);
            }
            
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
        } else {
            echo "<script>alert('Format gambar tidak sah! Gunakan JPG, JPEG, PNG atau GIF.');</script>";
        }
    }

    // Update accountant
    $update = updateAccountant($conn, $id, $name, $email, $password, $imageName);

    if ($update) {
        echo "<script>alert('Profile berjaya dikemaskini!'); window.location.href='./index.php?id=".$user['user_id']."';</script>";
    } else {
        echo "<script>alert('Gagal kemaskini profil!');</script>";
    }
}
?>

<div class="umMain">
    <form class="user-management" action="" method="POST" enctype="multipart/form-data">

        <div style="cursor: pointer;" title="Click to change image">
            <img class="profile-placeholder profile-box" 
                 style="max-width: 300px;" 
                 id="previewImage" 
                 src="<?= $user['image'] ? '../../uploads/users/'.$user['image'] : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'><rect width='100%' height='100%' fill='%23f0f0f0'/><text x='50%' y='50%' font-size='60' fill='%23999' text-anchor='middle' dominant-baseline='middle'>+</text></svg>" ?>" 
                 alt="Preview">
            
            <!-- Input file (hidden) -->
            <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
        </div>

        <div class="form">
            <h2>Edit Accountant Profile</h2>

            <label class="lbl">Name</label>
            <input class="input-um" type="text" name="name" 
                   value="<?= htmlspecialchars($user['username']); ?>" required>

            <label class="lbl">Email</label>
            <input class="input-um" type="email" name="email" 
                   value="<?= htmlspecialchars($user['email']); ?>" required>

            <label class="lbl">New Password (optional)</label>
            <input class="input-um" type="password" name="password" 
                   placeholder="Leave empty to keep current password">

            <div class="btn-umContainer">
                <a href="index.php?id=<?= $user['user_id'];?>" class="cancel">Cancel</a>
                <button type="submit" class="btn btn-main">Save</button>
            </div>
        </div>

    </form>
</div>

<script>
// Click image to select new
document.getElementById('previewImage').addEventListener('click', function () {
    document.getElementById('imageInput').click();
});

// Auto preview
document.getElementById('imageInput').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        document.getElementById('previewImage').src = URL.createObjectURL(file);
    }
});
</script>

<?php include($link . "container/footer.php"); ?>