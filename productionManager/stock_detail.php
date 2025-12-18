<?php 
session_start();

// AUTH CHECK
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

if (!isset($_GET['id'])) {
    header("Location: stock_list.php");
    exit;
}

$product_id = intval($_GET['id']);
$product = getProductByIdForProduction($conn, $product_id);

if (!$product) {
    header("Location: stock_list.php?msg=not_found");
    exit;
}

$total_value = $product['quantity'] * $product['unit_price'];
$is_low_stock = $product['quantity'] <= 10;
?>

<style>
.detail-container {
    max-width: 800px;
    margin: 20px auto;
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.detail-header {
    border-bottom: 2px solid #007bff;
    padding-bottom: 15px;
    margin-bottom: 25px;
}

.detail-header h2 {
    margin: 0;
    color: #333;
}

.detail-section {
    margin: 20px 0;
}

.detail-row {
    display: grid;
    grid-template-columns: 200px 1fr;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: bold;
    color: #666;
}

.detail-value {
    color: #333;
}

.alert-box {
    padding: 15px;
    border-radius: 5px;
    margin: 20px 0;
}

.alert-warning {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    color: #856404;
}

.alert-danger {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.alert-success {
    background: #d4edda;
    border-left: 4px solid #28a745;
    color: #155724;
}

.value-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    margin: 20px 0;
}

.value-card .amount {
    font-size: 32px;
    font-weight: bold;
    color: #28a745;
    margin: 10px 0;
}

.value-card .label {
    color: #666;
    font-size: 14px;
}
</style>

<div class="main">
    <div class="detail-container">
        <div class="detail-header">
            <h2>📦 Product Details (View Only)</h2>
            <p style="color: #666; margin: 5px 0 0 0;">Complete product information</p>
        </div>
        
        <!-- Alert for Low Stock -->
        <?php if ($product['quantity'] <= 5): ?>
            <div class="alert-box alert-danger">
                <strong>⚠️ CRITICAL STOCK LEVEL!</strong><br>
                Stok sangat rendah (<?= $product['quantity']; ?> units). Sila maklumkan kepada Accountant untuk restock.
            </div>
        <?php elseif ($is_low_stock): ?>
            <div class="alert-box alert-warning">
                <strong>⚠️ Low Stock Warning</strong><br>
                Stok semakin rendah (<?= $product['quantity']; ?> units). Mungkin perlu restock tidak lama lagi.
            </div>
        <?php else: ?>
            <div class="alert-box alert-success">
                <strong>✅ Stock Level Normal</strong><br>
                Stok mencukupi untuk masa ini.
            </div>
        <?php endif; ?>
        
        <!-- Product Information -->
        <div class="detail-section">
            <h3 style="color: #007bff; margin-bottom: 15px;">Product Information</h3>
            
            <div class="detail-row">
                <div class="detail-label">Product ID:</div>
                <div class="detail-value"><?= $product['product_id']; ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">SKU:</div>
                <div class="detail-value"><strong><?= htmlspecialchars($product['sku']); ?></strong></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Product Name:</div>
                <div class="detail-value"><strong><?= htmlspecialchars($product['product_name']); ?></strong></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Category:</div>
                <div class="detail-value"><?= htmlspecialchars($product['category_name']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Description:</div>
                <div class="detail-value"><?= nl2br(htmlspecialchars($product['description'] ?? 'No description')); ?></div>
            </div>
        </div>
        
        <!-- Stock Information -->
        <div class="detail-section">
            <h3 style="color: #007bff; margin-bottom: 15px;">Stock Information</h3>
            
            <div class="detail-row">
                <div class="detail-label">Current Quantity:</div>
                <div class="detail-value">
                    <strong style="font-size: 24px; color: <?= $product['quantity'] <= 5 ? '#dc3545' : ($is_low_stock ? '#ffc107' : '#28a745') ?>;">
                        <?= $product['quantity']; ?> units
                    </strong>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Unit Price:</div>
                <div class="detail-value"><strong>RM <?= number_format($product['unit_price'], 2); ?></strong></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Created Date:</div>
                <div class="detail-value"><?= date('d/m/Y H:i:s', strtotime($product['created_at'])); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Last Updated:</div>
                <div class="detail-value"><?= date('d/m/Y H:i:s', strtotime($product['updated_at'])); ?></div>
            </div>
        </div>
        
        <!-- Total Value Card -->
        <div class="value-card">
            <div class="label">TOTAL STOCK VALUE</div>
            <div class="amount">RM <?= number_format($total_value, 2); ?></div>
            <div class="label"><?= $product['quantity']; ?> units × RM <?= number_format($product['unit_price'], 2); ?></div>
        </div>
        
        <!-- Action Buttons -->
        <div style="margin-top: 30px; display: flex; gap: 10px; justify-content: center;">
            <button class="btn btn-secondary" onclick="window.location.href='stock_list.php'">
                ← Back to Stock List
            </button>
            <button class="btn btn-primary" onclick="window.print()">
                🖨️ Print Details
            </button>
        </div>
        
        <!-- Note -->
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px; text-align: center;">
            <small style="color: #666;">
                <strong>Note:</strong> Anda hanya mempunyai akses untuk melihat maklumat ini. 
                Untuk membuat sebarang perubahan, sila hubungi Accountant.
            </small>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, nav, .alert-box {
        display: none !important;
    }
    .detail-container {
        box-shadow: none;
        max-width: 100%;
    }
}
</style>

<?php 
include($link."container/footer.php");
?>