<?php
session_start();


if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit();
}
if (strtolower($_SESSION['role_name']) !== 'accountant') {
    header("Location: ../../login.php"); // pastikan path betul
    exit();
}

$nav = "../";
$link = "../../include/";
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
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $link."upload/user/" . $imageName);
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

        <div class="profile-box" id="imageBox">
            <img class="profile-placeholder" id="previewImage"
                 src="<?= $link; ?>upload/user/<?= $user['image']; ?>" 
                 alt="Preview">
            <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;">
        </div>

        <div class="form">
            <h2>Edit Accountant Profile</h2>

            <label class="lbl">Name</label>
            <input class="input-um" type="text" name="name" 
                   value="<?= $user['username']; ?>" required>

            <label class="lbl">Email</label>
            <input class="input-um" type="email" name="email" 
                   value="<?= $user['email']; ?>" required>

            <label class="lbl">New Password (optional)</label>
            <input class="input-um" type="password" name="password" 
                   placeholder="Leave empty to keep current password">

            <div class="btn-umContainer">
                <a href="index.php??id=<?= $user['user_id'];?>" class="cancel">Cancel</a>
                <button type="submit" class="btn btn-main">Save</button>
            </div>
        </div>

    </form>
</div>

<script>
// Click image to select new
document.getElementById('imageBox').addEventListener('click', function () {
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
