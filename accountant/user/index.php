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
  include($link."container/head.php");
  include($link."container/nav.php");
  require($link . "php/config.php");
  require_once($link . "php/userManagement/user.php");
  $users = getAllUsers($conn);

  if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($_GET['action'] === 'delete') {
        deleteUser($conn, $id);
        header("Location:.php?msg=deleted");
        exit;
    }

    if ($_GET['action'] === 'toggle') {
        toggleUserStatus($conn, $id);
        header("Location: user.php?msg=updated");
        exit;
    }
}

?>
  <!-- Main content -->
  <div class="main">
    <!-- Header with title and actions -->
    <div>
      <div class="SContainer">
    <h2>User management</h2>

    <div class="search-box">
        <input type="text" placeholder="Search..." />
        <button class="btn btn-primary">Search</button>
    </div>

    </div>
    <div>
      <button class="btn btn-primary" onclick="window.location.href='add.php'">Add User +</button>
      <button class="btn btn-secondary" onclick="window.location.href='role_index.php'"> Manage Roles </button>
    </div>
    <!-- Table -->
    <table>
    <!-- User table -->
    <table style="margin-top: 30px;">
      <thead>
        <tr>
          <th>User ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Position</th>
          <th>Status</th>
          <th>Detail</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($users->num_rows === 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center;">No product found</td>
                </tr>
        <?php else: ?>
        <?php while ($row = $users->fetch_assoc()):?>
          <tr>
            <td><?= $row['user_id']; ?></td>
            <td><?= $row['username']; ?></td>
            <td><?= $row['email']; ?></td>
            <td><?= $row['role_name']; ?></td>
            <?php if($row['role_id'] == 1): ?>
              <td></td>
              <td></td>
              <td></td>
            <?php else: ?>
            <td>
              <?php if($row['status'] == 1): ?>
                <button class="status status-completed">Active</button>
              <?php else: ?>
                <button class="status status-cancel">Disactive</button>
              <?php endif; ?>
            </td>
            <td> 
              <button onclick="window.location.href='detail.php?id=<?= $row['user_id']; ?>'" class="btn btn-primary" style="text-decoration: none;">
                View                    
              </button>  
            </td>
            <td>
              <?php if($row['status'] == 1): ?>
                  <button onclick="if(confirm('block this users?')) window.location.href='index.php?action=toggle&id=<?= $row['user_id']; ?>'" class="btn btn-secondary">
                      Block
                  </button>
              <?php else: ?>
                  <button onclick="if(confirm('block this users?')) window.location.href='index.php?action=toggle&id=<?= $row['user_id']; ?>'"  class="btn btn-secondary">
                      Unblock
                  </button>
              <?php endif; ?>      
              <button 
                class="btn btn-main"
                onclick="if(confirm('Delete this user?')) window.location.href='index.php?action=delete&id=<?= $row['user_id']; ?>';">
                  Delete
              </button>
            </td>
            <?php endif; ?>
          </tr>
        <?php endwhile; ?>
        <?php endif; ?>
        <!-- Additional user rows can be added here -->
      </tbody>
    </table>
  </div>
</body>
</html>
<?php 
  include($link."container/footer.php");
?>