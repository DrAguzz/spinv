<?php 
session_start();

// AUTH CHECK - Untuk Production Manager sahaja
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit();
}
if (strtolower($_SESSION['role_name']) !== 'production') {
    header("Location: ../../login.php");
    exit();
}

$nav = "./";
$link = "../include/";
include($link."container/head.php");
include($link."container/nav.php");
require($link . "php/config.php");
require_once($link . "php/productionManager/production_functions.php");

// Get statistics - using corrected functions
$total_value = getTotalStockValue($conn);
$total_quantity = getTotalStockQuantity($conn);
$total_items = getTotalStockItems($conn);
$low_stock = getLowStockItems($conn);
$stock_by_type = getStockByType($conn);
$low_stock_list = getLowStockList($conn);
?>

<style>
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #007bff;
}

.stat-card.warning {
    border-left-color: #ffc107;
}

.stat-card.success {
    border-left-color: #28a745;
}

.stat-card.info {
    border-left-color: #17a2b8;
}

.stat-card h3 {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 14px;
    font-weight: normal;
}

.stat-card .value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
}

.stat-card .label {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.section-title {
    margin: 30px 0 15px 0;
    font-size: 20px;
    font-weight: bold;
    color: #333;
}

.type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.type-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.type-card h4 {
    margin: 0 0 10px 0;
    color: #007bff;
}

.type-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.type-stats div {
    text-align: center;
}

.type-stats .label {
    font-size: 11px;
    color: #666;
    margin-bottom: 3px;
}

.type-stats .value {
    font-size: 16px;
    font-weight: bold;
    color: #333;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

table thead {
    background: #007bff;
    color: white;
}

table th, table td {
    padding: 12px;
    text-align: left;
}

table tbody tr:hover {
    background: #f1f1f1;
}

.stock-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}
</style>

<div class="main">
    <div>
        <h2>Production Dashboard</h2>
        <p style="color: #666; margin-bottom: 20px;">Welcome, <?= htmlspecialchars($_SESSION['username']); ?> - View Only Access</p>
        
        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card success">
                <div>
                    <h3>Total Stock Value</h3>
                <div class="value">RM <?= number_format($total_value, 2); ?></div>
                <div class="label">Total nilai inventory</div>
                </div>
            </div>
            
            <div class="stat-card info">
                <div>
                    <h3>Total Stock Items</h3>
                <div class="value"><?= number_format($total_items); ?></div>
                <div class="label">Jumlah item dalam stock</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div>
                    <h3>Total Quantity</h3>
                    <div class="value"><?= number_format($total_quantity, 2); ?></div>
                    <div class="label">Jumlah kuantiti dalam store</div>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div>
                    <h3>Low Stock Items</h3>
                <div class="value"><?= $low_stock; ?></div>
                <div class="label">Item dengan stock rendah (≤10)</div>
                </div>
            </div>
        </div>
        
        <!-- Stock by Type -->
        <h3 class="section-title">Stock by Type</h3>
        <div class="type-grid">
            <?php while ($type = $stock_by_type->fetch_assoc()): ?>
                <div class="type-card">
                    <h4><?= htmlspecialchars($type['type_name'] ?? 'Unknown Type'); ?></h4>
                    <div class="type-stats">
                        <div>
                            <div class="label">Items</div>
                            <div class="value"><?= $type['item_count']; ?></div>
                        </div>
                        <div>
                            <div class="label">Quantity</div>
                            <div class="value"><?= number_format($type['total_quantity'] ?? 0, 2); ?></div>
                        </div>
                        <div>
                            <div class="label">Total Area (m²)</div>
                            <div class="value"><?= number_format($type['total_area'] ?? 0, 2); ?></div>
                        </div>
                        <div>
                            <div class="label">Value (RM)</div>
                            <div class="value"><?= number_format($type['type_value'] ?? 0, 2); ?></div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Low Stock Alert -->
        <?php if ($low_stock > 0): ?>
            <h3 class="section-title" style="color: #ffc107;">⚠️ Low Stock Alert</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Stock ID</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Dimensions (L×W)</th>
                            <th>Quantity</th>
                            <th>Total Area (m²)</th>
                            <th>Cost/m²</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $low_stock_list->fetch_assoc()): ?>
                            <tr style="background: <?= $row['quantity'] <= 5 ? '#fff3cd' : '#f8f9fa' ?>;">
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="../uploads/products/<?= htmlspecialchars($row['image']); ?>" alt="Stock Image" class="stock-image">
                                    <?php else: ?>
                                        <div class="stock-image" style="background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($row['stock_id']); ?></strong></td>
                                <td><?= htmlspecialchars($row['description']); ?></td>
                                <td><?= htmlspecialchars($row['type_name'] ?? 'N/A'); ?></td>
                                <td><?= number_format($row['length'], 2); ?> × <?= number_format($row['width'], 2); ?></td>
                                <td>
                                    <!-- <strong style="color: <?= $row['quantity'] <= 5 ? '#dc3545' : '#ffc107' ?>;">
                                        <?= number_format($row['quantity'], 2); ?>
                                    </strong> -->
                                    <?= number_format($row['quantity'], 2); ?>
                                </td>
                                <td><?= number_format($row['total_area'], 2); ?></td>
                                <td>RM <?= number_format($row['cost_per_m2'], 2); ?></td>
                                <td><strong>RM <?= number_format($row['total_amount'], 2); ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="background: #d4edda; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0; color: #155724;">
                <h3 style="margin: 0;">✓ All Stock Levels are Healthy</h3>
                <p style="margin: 10px 0 0 0;">No items currently below threshold</p>
            </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div style="margin: 30px 0; display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="btn btn-primary" onclick="window.location.href='stock_list.php'">
                📋 View All Stock
            </button>
            <button class="btn btn-secondary" onclick="window.location.href='stock_report.php'">
                📊 Generate Report
            </button>
        </div>
    </div>
</div>

<?php 
include($link."container/footer.php");
?>