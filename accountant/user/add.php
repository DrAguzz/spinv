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
  require_once($link . "php/userManagement/user.php");
  
  $roles = getRoleList($conn);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = addUser($conn, $_POST, $_FILES);

    if ($result) {
        echo "<script>alert('User berjaya ditambah!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal simpan produk!');</script>";
    }
}
?>
<!-- Main -->
<div class="umMain">
  <form class="user-management" action="" method="post" enctype="multipart/form-data">
    <div id="imageBox">
      <img class="profile-placeholder profile-box" style="max-width: 300px;"  id="previewImage" src="data:image/svg+xml;utf8,
                        <svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'>
                            <rect width='100%' height='100%' fill='%23f0f0f0'/>
                            <text x='50%' y='50%' font-size='60' fill='%23999' text-anchor='middle' dominant-baseline='middle'>+</text>
                        </svg>" alt="Preview">
        <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;">
    </div>
    <div class="form">
      <h2>User Management</h2>
      <label class="lbl">Name</label>
      <input class="input-um" type="text" name="name" value="" style="text-align: left;">
      <label class="lbl">Email</label>
      <input class="input-um" type="text" name="email" value="" style="text-align: left;">
      <label class="lbl">Role</label>
      <select class="input-um" name="role" required>
    <option value="" disabled selected hidden>-- Pilih Role --</option>
    <?php while($row = $roles->fetch_assoc()) : ?>
        <option value="<?= $row['role_id']; ?>"><?= $row['role_name']; ?></option>
    <?php endwhile; ?>
      </select>

        <div class="btn-umContainer">
          <a href="#" class="cancel">Cancel</a>
          <button type="submit" class="btn btn-main">Add</button>
        </div>
      </div>
    </div>
  </form>
</div>
<script>
const imageBox = document.getElementById('imageBox');
const imageInput = document.getElementById('imageInput');
const previewImage = document.getElementById('previewImage');

imageBox.addEventListener('click', () => {
    imageInput.click();
});

imageInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        previewImage.src = URL.createObjectURL(file);
    }
});
</script>
<?php 
  include($link."container/footer.php");
?>
