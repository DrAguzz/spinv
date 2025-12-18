<!-- Tambah dalam nav.php, section untuk Production Manager -->

<?php if (strtolower($_SESSION['role_name']) === 'production'): ?>
    <!-- Navigation untuk Production Manager -->
    <nav>
        <div class="nav-brand">
            <h2>Production Dashboard</h2>
        </div>
        <ul class="nav-menu">
            <li>
                <a href="<?= $nav; ?>production/production_dashboard.php" class="nav-link">
                    📊 Dashboard
                </a>
            </li>
            <li>
                <a href="<?= $nav; ?>production/stock_list.php" class="nav-link">
                    📦 Stock List
                </a>
            </li>
            <li>
                <a href="<?= $nav; ?>production/stock_report.php" class="nav-link">
                    📋 Reports
                </a>
            </li>
            <li>
                <a href="<?= $nav; ?>../logout.php" class="nav-link" style="color: #dc3545;">
                    🚪 Logout
                </a>
            </li>
        </ul>
        <div class="nav-user">
            <span>👤 <?= htmlspecialchars($_SESSION['username']); ?></span>
            <span style="font-size: 12px; color: #666;">(Production Manager - View Only)</span>
        </div>
    </nav>
<?php endif; ?>

<?php if (strtolower($_SESSION['role_name']) === 'accountant'): ?>
    <!-- Navigation untuk Accountant (existing) -->
    <nav>
        <!-- Your existing accountant nav here -->
    </nav>
<?php endif; ?>