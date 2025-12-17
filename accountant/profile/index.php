<?php
session_start();

// 🔒 AUTH CHECK PALING ATAS
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
include($link."container/head.php");
include($link."container/nav.php");
require($link . "php/config.php");
require_once($link . "php/profile/profile.php");

$user = getAccountant($conn);

?>

<!-- Main -->
<div class="umMain">
    <div class="user-management">

        <div class="profile-box">
            <img class="profile-placeholder" id="previewImage"
                 src="<?= $link; ?>upload/user/<?= $user['image']; ?>" 
                 alt="Preview">
        </div>

        <div class="form">
            <h2>Accountant Profile</h2>

            <label class="lbl">Name</label>
            <input class="input-um" type="text" value="<?= $user['username']; ?>" disabled>

            <label class="lbl">Email</label>
            <input class="input-um" type="text" value="<?= $user['email']; ?>" disabled>

            <label class="lbl">Password (Role Password)</label>
            <input class="input-um" type="password" value="****" disabled>

            <div class="btn-umContainer">

                <!-- Pergi ke page edit -->
                <button class="btn btn-main" 
                        onclick="window.location.href='./edit.php?id=<?= $user['user_id']; ?>'">
                    Edit
                </button>
            </div>
        </div>

    </div>
</div>

<?php 
include($link."container/footer.php");
?>
