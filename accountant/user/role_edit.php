<?php 
session_start();

// AUTH CHECK
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
include($link."container/head.php");
include($link."container/nav.php");
require($link . "php/config.php");
require_once($link . "php/userManagement/role.php");

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    header("Location: role_index.php");
    exit;
}

$role_id = intval($_GET['id']);
$role = getRoleById($conn, $role_id);

if (!$role || $role_id == 1) {
    header("Location: role_index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_name = trim($_POST['role_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($role_name)) {
        $error = 'Role name tidak boleh kosong';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Password tidak sama';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password mestilah sekurang-kurangnya 6 aksara';
    } else {
        $update_password = !empty($password) ? $password : null;
        if (updateRole($conn, $role_id, $role_name, $update_password)) {
            header("Location: role_index.php?msg=updated");
            exit;
        } else {
            $error = 'Gagal mengemaskini role';
        }
    }
}
?>

<div class="main">
    <div>
        <div class="SContainer">
            <h2>Edit Role</h2>
        </div>
        
        <?php if ($error): ?>
            <div style="padding: 10px; margin: 10px 0; border-radius: 5px; background: #f8d7da; color: #721c24;">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" style="max-width: 500px; margin: 20px 0;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Role Name:</label>
                <input 
                    type="text" 
                    name="role_name" 
                    required
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                    value="<?= htmlspecialchars($role['role_name']); ?>"
                >
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">New Password:</label>
                <input 
                    type="password" 
                    name="password" 
                    minlength="6"
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                >
                <small style="color: #666;">Kosongkan jika tidak mahu tukar password</small>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Confirm New Password:</label>
                <input 
                    type="password" 
                    name="confirm_password" 
                    minlength="6"
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                >
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update Role</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='role_index.php'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php 
include($link."container/footer.php");
?>