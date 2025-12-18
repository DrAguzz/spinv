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

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$products = getAllProductsForProduction($conn, $search);
?>

<div class="main">
    <div>
        <div class="SContainer">
            <h2>Stock List (View Only)</h2>
            
            <!-- Search Box -->
            <form method="GET" class="search-box">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by product name, SKU, or category..." 
                    value="<?= htmlspecialchars($search); ?>"
                />
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search): ?>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='stock_list.php'">Clear</button>
                <?php endif; ?>
            </form>
        </div>
        
        <div style="margin: 15px 0;">
            <button class="btn btn-secondary" onclick="history.back()"">
                ← Back to Dashboard
            </button>
            <button class="btn btn-primary" onclick="window.print()">
                🖨️ Print List
            </button>
        </div>
        
        <!-- Products Table -->
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>SKU</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Unit Price (RM)</th>
                    <th>Total Value (RM)</th>
                    <!-- <th>Status</th> -->
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products->num_rows === 0): ?>
                    <tr>
                        <td colspan="9" style="text-align:center;">
                            <?= $search ? 'No products found matching your search' : 'No products available' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $total_value = 0;
                    while ($row = $products->fetch_assoc()): 
                        $product_total = $row['quantity'] * $row['total_amount'];
                        $total_value += $product_total;
                        $is_low_stock = $row['quantity'] <= 10;
                    ?>
                        <tr style="<?= $is_low_stock ? 'background: #fff3cd;' : '' ?>">
                            <td><?= $row['stock_id']; ?></td>
                            <td><?= htmlspecialchars($row['stock_id']); ?></td>
                            <td><?= htmlspecialchars($row['description']); ?></td>
                            <td><?= htmlspecialchars($row['type_name']); ?></td>
                            <td>
                                <?= htmlspecialchars($row['quantity']); ?>
                            </td>
                            <td><?= number_format($row['total_amount'], 2); ?></td>
                            <td><?= number_format($product_total, 2); ?></td>
                            <!-- <td>
                                <?php if($row['quantity'] <= 5): ?>
                                    <span class="status status-cancel">Critical</span>
                                <?php elseif($row['quantity'] <= 10): ?>
                                    <span class="status status-pending">Low Stock</span>
                                <?php else: ?>
                                    <span class="status status-completed">Available</span>
                                <?php endif; ?>
                            </td> -->
                            <td>
                                <button 
                                    onclick="window.location.href='stock_detail.php?id=<?= $row['stock_id']; ?>'" 
                                    class="btn btn-primary">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <tr style="background: #f0f0f0; font-weight: bold;">
                        <td colspan="6" style="text-align: right;">TOTAL STOCK VALUE:</td>
                        <td colspan="3">RM <?= number_format($total_value, 2); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-radius: 5px;">
            <strong>Legend:</strong>
            <span style="margin-left: 15px;">🟢 Available (>10 units)</span>
            <span style="margin-left: 15px;">⚠️ Low Stock (≤10 units)</span>
            <span style="margin-left: 15px;">🔴 Critical (≤5 units)</span>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .search-box, nav, .status {
        display: none;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #000;
        padding: 5px;
    }
}
</style>

<?php 
include($link."container/footer.php");
?>