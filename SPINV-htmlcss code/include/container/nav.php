<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="profile">
        <div class="profile-img">👤</div>
        <div class="profile-info">
            <div class="profile-name">Azeti Emiza</div>
            <a href="<?= $nav ?>logout.php" class="logout">logout</a>
        </div>
    </div>
    
    <nav>
        <a href="<?= $nav ?>index.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['page'])) ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            <span>Dashboard</span>
        </a>
        <a href="<?= $nav ?>product/index.php" class="<?= (strpos($_SERVER['PHP_SELF'], 'product') !== false) ? 'active' : '' ?>">
            <span class="nav-icon">📦</span>
            <span>Product</span>
        </a>
        <a href="<?= $nav ?>user/index.php" class="<?= (strpos($_SERVER['PHP_SELF'], 'user') !== false) ? 'active' : '' ?>">
            <span class="nav-icon">👥</span>
            <span>Manage User</span>
        </a>
        <a href="<?= $nav ?>profile/index.php" class="<?= (strpos($_SERVER['PHP_SELF'], 'profile') !== false) ? 'active' : '' ?>">
            <span class="nav-icon">⚙️</span>
            <span>Profile</span>
        </a>
    </nav>
</div>