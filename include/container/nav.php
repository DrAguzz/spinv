<!-- Sidebar Navigation -->

<?php if (strtolower($_SESSION['role_name']) === 'accountant'): ?>
<?php
$user_image = isset($_SESSION['user_image']) && !empty($_SESSION['user_image']) 
              ? $_SESSION['user_image'] 
              : 'default.png';      
?>
    <!-- Navigation untuk Accountant -->
    <div class="sidebar" style="display:flex;flex-direction:column;height:100vh;padding:20px;box-sizing:border-box;">
    
        <!-- Profile Section -->
        <div class="profile" style="margin-bottom:30px;">
            <div class="profile-img" style="width:50px;height:50px;border-radius:50%;overflow:hidden;margin-bottom:10px;">
                <img src="<?php echo '../uploads/users/'.$user_image; ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
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
<?php endif; ?>

<?php if (strtolower($_SESSION['role_name']) === 'purchasing'): ?>
<?php
$user_image = isset($_SESSION['user_image']) && !empty($_SESSION['user_image']) 
              ? $_SESSION['user_image'] 
              : 'default.png';      
?>
    <!-- Navigation untuk Purchasing -->
    <div class="sidebar" style="display:flex;flex-direction:column;height:100vh;padding:20px;box-sizing:border-box;">
    
        <!-- Profile Section -->
        <div class="profile" style="margin-bottom:30px;">
            <div class="profile-img" style="width:50px;height:50px;border-radius:50%;overflow:hidden;margin-bottom:10px;">
                <img src="<?php echo '../uploads/users/'.$user_image; ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div class="profile-info">
                <div class="profile-name" style="font-weight:600;">
                    <p>welcome! <br> <?= htmlspecialchars($_SESSION['username']) ?></p>
                    <small style="color:#95a5a6;font-size:11px;display:block;margin-top:3px;">Purchasing Staff</small>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav style="flex-grow:1;">
            <a href="<?= $nav ?>index.php" 
               class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'purchasing') !== false) ? 'active' : '' ?>" 
               style="display:block;padding:10px 0;">
               ➕ Add Purchase
            </a>
            
            <a href="<?= $nav ?>pending.php" 
               class="<?= (basename($_SERVER['PHP_SELF']) == 'pending.php') ? 'active' : '' ?>" 
               style="display:block;padding:10px 0;">
               ⏳ Pending List
            </a>
            
            <a href="<?= $nav ?>history.php" 
               class="<?= (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'active' : '' ?>" 
               style="display:block;padding:10px 0;">
               📋 My History
            </a>
        </nav>

        <!-- Logout Button di bawah -->
        <div style="margin-top:auto;">
            <a href="<?= $nav ?>logout.php" style="display:block;padding:10px 0;color:#e74c3c;font-weight:600;text-decoration:none;">🚪 Logout</a>
        </div>
    </div>
<?php endif; ?>

<?php if (strtolower($_SESSION['role_name']) === 'production'): ?>
    <!-- Navigation untuk Production Manager -->
    <div class="sidebar" style="display:flex;flex-direction:column;height:100vh;padding:20px;box-sizing:border-box;">
    
        <!-- Profile Section -->
        <div class="profile" style="margin-bottom:30px;">
            <div class="profile-img" style="width:50px;height:50px;border-radius:50%;overflow:hidden;margin-bottom:10px;background:#667eea;">
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:white;font-size:24px;font-weight:bold;">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                </div>
            </div>
            <div class="profile-info">
                <div class="profile-name" style="font-weight:600;">
                    <p>Welcome! <br> <?= htmlspecialchars($_SESSION['username']) ?></p>
                    <small style="color:#666;font-size:11px;display:block;margin-top:5px;">(Production Manager - View Only)</small>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav style="flex-grow:1;">
            <?php if (file_exists($nav . '/index.php')): ?>
            <a href="<?= $nav ?>index.php" 
               class="<?= (strpos($_SERVER['PHP_SELF'], 'production_dashboard.php') !== false) ? 'active' : '' ?>" 
               style="display:block;padding:10px 0;">
                Dashboard
            </a>
            <?php endif; ?>
            
            <?php if (file_exists($nav . 'stock_list.php')): ?>
            <a href="<?= $nav ?>stock_list.php" 
               class="<?= (strpos($_SERVER['PHP_SELF'], 'stock_list.php') !== false) ? 'active' : '' ?>" 
               style="display:block;padding:10px 0;">
                Stock List
            </a>
            <?php endif; ?>
            
            <?php if (file_exists($nav . 'stock_report.php')): ?>
            <a href="<?= $nav ?>stock_report.php" 
               class="<?= (strpos($_SERVER['PHP_SELF'], 'stock_report.php') !== false) ? 'active' : '' ?>" 
               style="display:block;padding:10px 0;">
                Reports
            </a>
            <?php endif; ?>
        </nav>

        <!-- Logout Button di bawah -->
        <div style="margin-top:auto;">
            <a href="<?= $link ?>/php/logout.php" style="display:block;padding:10px 0;color:#e74c3c;font-weight:600;text-decoration:none;">🚪 Logout</a>
        </div>
    </div>
<?php endif; ?>