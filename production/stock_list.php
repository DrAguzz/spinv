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
            <button class="btn btn-secondary" onclick="window.location.href='./index.php'">
                ← Back to Dashboard
            </button>
            <button class="btn btn-primary" onclick="printTable()">
                🖨️ Print List
            </button>
        </div>
        
        <!-- Products Table -->
        <div id="printTable">
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

    .sidebar {
        display: none;
    }
}
</style>

<script>
    function printTable() {
  var table = document.getElementById("printTable").outerHTML;
  var win = window.open("", "", "width=900,height=700");

  win.document.write(`
    <html>
      <head>
        <title>Print</title>
        <style>
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #000; padding: 8px; }
          .report-container {
    max-width: 1200px;
    margin: 20px auto;
    background: white;
    padding: 40px;
}

.report-header {
    text-align: center;
    border-bottom: 3px solid #007bff;
    padding-bottom: 20px;
    margin-bottom: 30px;
}

.report-header h1 {
    margin: 0;
    color: #333;
}

.report-info {
    display: flex;
    justify-content: space-between;
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin: 30px 0;
}

.summary-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    border: 2px solid #dee2e6;
}

.summary-box .value {
    font-size: 28px;
    font-weight: bold;
    color: #007bff;
    margin: 10px 0;
}

.summary-box .label {
    color: #666;
    font-size: 14px;
}

.report-section {
    margin: 40px 0;
}

.report-section h2 {
    color: #007bff;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border: 1px solid #dee2e6;
}

table thead {
    background: #007bff;
    color: white;
}

table tbody tr:nth-child(even) {
    background: #f8f9fa;
}

table tbody tr:hover {
    background: #e9ecef;
}

#actionNone{
display: none;
}
        </style>
      </head>
      <body>
        ${table}
      </body>
    </html>
  `);

  win.document.close();
  win.print();
  win.close();
}
</script>

<?php 
include($link."container/footer.php");
?>