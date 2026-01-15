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
$img = "../../";
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

        <div>
            <img class="profile-placeholder profile-box" style="max-width: 300px;"  id="previewImage imageBox" src="<?= $user['image'] ? $img.'upload/user/'.$user['image'] : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'><rect width='100%' height='100%' fill='%23f0f0f0'/><text x='50%' y='50%' font-size='60' fill='%23999' text-anchor='middle' dominant-baseline='middle'>+</text></svg>" ?>" alt="Preview">
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
