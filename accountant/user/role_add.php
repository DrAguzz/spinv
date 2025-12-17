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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_name = trim($_POST['role_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($role_name) || empty($password)) {
        $error = 'Sila isi semua medan';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak sama';
    } elseif (strlen($password) < 6) {
        $error = 'Password mestilah sekurang-kurangnya 6 aksara';
    } else {
        if (addRole($conn, $role_name, $password)) {
            header("Location: role_index.php?msg=added");
            exit;
        } else {
            $error = 'Gagal menambah role';
        }
    }
}
?>

<div class="main">
    <div>
        <div class="SContainer">
            <h2>Add New Role</h2>
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
                    value="<?= isset($_POST['role_name']) ? htmlspecialchars($_POST['role_name']) : ''; ?>"
                >
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
                <input 
                    type="password" 
                    name="password" 
                    required
                    minlength="6"
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                >
                <small style="color: #666;">Minimum 6 aksara</small>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Confirm Password:</label>
                <input 
                    type="password" 
                    name="confirm_password" 
                    required
                    minlength="6"
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                >
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Add Role</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='role_index.php'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php 
include($link."container/footer.php");
?>