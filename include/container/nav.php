<!-- Sidebar Navigation -->
<div class="sidebar" style="display:flex;flex-direction:column;height:100vh;padding:20px;box-sizing:border-box;">
    
    <!-- Profile Section -->
    <div class="profile" style="margin-bottom:30px;">
        <div class="profile-img" style="width:50px;height:50px;border-radius:50%;overflow:hidden;margin-bottom:10px;">
            <img src="<?= $link ?>/upload/user/1764081370_WhatsApp Image 2024-09-10 at 23.52.40_da6b03f3.jpg" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
        </div>
        <div class="profile-info">
            <div class="profile-name" style="font-weight:600;"><p>welcome! <br> <?= $_SESSION['username'] ?></p></div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav style="flex-grow:1;">
        <a href="<?= $nav ?>index.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['page'])) ? 'active' : '' ?>" style="display:block;padding:10px 0;">Dashboard</a>
        <a href="<?= $nav ?>product/index.php" class="<?= (strpos($_SERVER['PHP_SELF'], 'product') !== false) ? 'active' : '' ?>" style="display:block;padding:10px 0;">Product</a>
        <a href="<?= $nav ?>user/index.php" class="<?= (strpos($_SERVER['PHP_SELF'], 'user') !== false) ? 'active' : '' ?>" style="display:block;padding:10px 0;">Manage User</a>
        <a href="<?= $nav ?>profile/index.php" class="<?= (strpos($_SERVER['PHP_SELF'], 'profile') !== false) ? 'active' : '' ?>" style="display:block;padding:10px 0;">Profile</a>
    </nav>

    <!-- Logout Button di bawah -->
    <div style="margin-top:auto;">
        <a href="<?= $nav ?>logout.php" style="display:block;padding:10px 0;color:#e74c3c;font-weight:600;text-decoration:none;">Logout</a>
    </div>
</div>
