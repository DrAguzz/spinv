
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
 $imgLink = "../../";
  include($link."container/head.php");
  include($link."container/nav.php");
  require($link . "php/config.php");
  require_once($link . "php/userManagement/user.php");

  if(!isset($_GET['id'])){
      echo "<script>alert('User ID tidak dijumpai!'); window.location.href='index.php';</script>";
      exit;
  }

  $user_id = intval($_GET['id']);
  $user = getUserById($conn, $user_id);

  if(!$user){
      echo "<script>alert('User tidak wujud!'); window.location.href='index.php';</script>";
      exit;
  }
?>
<!-- Main -->
<div class="umMain">
    <div class="user-management">

    <div>
      <img class="profile-placeholder profile-box" style="max-width: 300px;"  id="previewImage imageBox" src="<?= $user['image'] ? $imgLink.'uploads/users/'.$user['image'] : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'><rect width='100%' height='100%' fill='%23f0f0f0'/><text x='50%' y='50%' font-size='60' fill='%23999' text-anchor='middle' dominant-baseline='middle'>+</text></svg>" ?>" alt="Preview">
    </div>
    
    <div class="form">
      <h2>User Management</h2>
      <label class="lbl">Name</label>
      <input class="input-um" type="text" name="name" value="<?= $user['username']; ?>" style="text-align: left;">
      <label class="lbl">Email</label>
      <input class="input-um" type="text" name="email" value="<?= $user['email']; ?>" style="text-align: left;">
      <label class="lbl">Role</label>
      <select class="input-um" name="role">
        <option value="" disabled selected hidden><?= $user['role_name']; ?></option>
      </select>

      <div class="btn-umContainer">
        <a href="./index.php" class="cancel">close</a>
        <button class="btn btn-main" onclick="window.location.href='./edit.php?id=<?= $user['user_id']; ?>'">Edit</button>
      </div>
    </div>
    </div>
</div>

<?php 
  include($link."container/footer.php");
?>