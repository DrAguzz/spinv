
<?php 
  $nav = "../";
 $link = "../../include/";
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
    <div class="user-management" >

    
    <div class="profile-box" id="imageBox">
      <img class="profile-placeholder" id="previewImage" src="<?= $link; ?>upload/user/<?= $user['image']; ?>" alt="Preview">
      <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;">
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