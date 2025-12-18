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

$roles = getAllRoles($conn);

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($_GET['action'] === 'delete') {
        if (deleteRole($conn, $id)) {
            header("Location: role_index.php?msg=deleted");
        } else {
            header("Location: role_index.php?msg=error");
        }
        exit;
    }
}
?>

<div class="main">
    <div>
        <div class="SContainer">
            <h2>Role Management</h2>
        </div>
        
        <?php if (isset($_GET['msg'])): ?>
            <div style="padding: 10px; margin: 10px 0; border-radius: 5px; background: <?= $_GET['msg'] === 'deleted' ? '#d4edda' : '#f8d7da' ?>; color: <?= $_GET['msg'] === 'deleted' ? '#155724' : '#721c24' ?>;">
                <?php 
                    if ($_GET['msg'] === 'deleted') echo 'Role berjaya dipadam';
                    elseif ($_GET['msg'] === 'added') echo 'Role berjaya ditambah';
                    elseif ($_GET['msg'] === 'updated') echo 'Role berjaya dikemaskini';
                    else echo 'Tidak dapat memadamkan role (mungkin ada user menggunakannya)';
                ?>
            </div>
        <?php endif; ?>
        
        <div>
            <!-- <button class="add-btn" onclick="window.location.href='./role_add.php'">Add Role +</button> -->
        </div>
        
        <table style="margin-top: 40px;">
            <thead>
                <tr>
                    <th>Role ID</th>
                    <th>Role Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($roles->num_rows === 0): ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No roles found</td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $roles->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['role_id']; ?></td>
                            <td><?= htmlspecialchars($row['role_name']); ?></td>
                            <td>
                                <?php if($row['role_id'] != 1): ?>
                                    <button onclick="window.location.href='role_edit.php?id=<?= $row['role_id']; ?>'" class="btn btn-primary">
                                        Edit
                                    </button>
                                    <button 
                                        class="btn btn-main"
                                        onclick="if(confirm('Delete this role?')) window.location.href='role_index.php?action=delete&id=<?= $row['role_id']; ?>';">
                                        Delete
                                    </button>
                                <?php else: ?>
                                    <span style="color: #999;">System Role</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <button class="btn btn-secondary" onclick="window.location.href='index.php'">Back to User Management</button>
        </div>
    </div>
</div>

<?php 
include($link."container/footer.php");
?>