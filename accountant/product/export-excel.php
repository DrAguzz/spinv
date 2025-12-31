<?php
session_start();


if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit();
}
if (strtolower($_SESSION['role_name']) !== 'accountant') {
    header("Location: ../../login.php"); // pastikan path betul
    exit();
}
$nav = "../";
  $link = "../../include/";
  require($link . "php/config.php");
require_once ($link . "php/acc-auth.php");

if ($_SESSION['role_name'] !== 'accountant') {
    header("Location: /login.php");
    exit();
}
session_start();

// Security: Check if user is logged in (optional - uncomment bila dah ada auth)
// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../login.php");
//     exit();
// }

// Get all approved stock data grouped by marble type
$sql = "
    SELECT 
        s.stock_id,
        s.description,
        s.length,
        s.width,
        s.quantity,
        s.total_area,
        s.cost_per_m2,
        s.total_amount,
        s.created_at,
        mt.type_id,
        mt.code,
        mt.name AS marble_type,
        mt.finish_type
    FROM stock s
    LEFT JOIN marble_type mt ON s.type_id = mt.type_id
    WHERE s.status = 1
    ORDER BY mt.name ASC, s.stock_id ASC
";

$result = $conn->query($sql);

if (!$result) {
    die("Error fetching data: " . $conn->error);
}

// Group data by marble type
$stockByType = [];
$totals = [
    'quantity' => 0,
    'total_area' => 0,
    'total_amount' => 0
];

while ($row = $result->fetch_assoc()) {
    $marbleType = $row['marble_type'] ?? 'Uncategorized';
    
    if (!isset($stockByType[$marbleType])) {
        $stockByType[$marbleType] = [
            'code' => $row['code'] ?? 'N/A',
            'items' => []
        ];
    }
    
    $stockByType[$marbleType]['items'][] = $row;
    
    // Calculate totals
    $totals['quantity'] += $row['quantity'];
    $totals['total_area'] += $row['total_area'];
    $totals['total_amount'] += $row['total_amount'];
}

// Set headers for Excel download
$filename = "Stock_Inventory_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Stock Inventory Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .section-header {
            background-color: #2c3e50;
            color: white;
            padding: 10px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 20px;
            border: 1px solid #2c3e50;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: #34495e;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c3e50;
            font-size: 11px;
        }
        
        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-row {
            background-color: #ecf0f1;
            font-weight: bold;
        }
        
        .grand-total-row {
            background-color: #3498db;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        
        .summary-table {
            margin-top: 30px;
            width: 50%;
        }
        
        .summary-table th {
            background-color: #95a5a6;
        }
        
        .category-count {
            background-color: #e8f5e9;
            padding: 5px 10px;
            border-radius: 3px;
            display: inline-block;
            margin-left: 10px;
            font-size: 11px;
            border: 1px solid #4caf50;
        }
    </style>
</head>
<body>

<!-- Report Header -->
<div class="report-header">
    <div class="report-title">STONE PAVILION INVENTORY SYSTEM</div>
    <div class="report-subtitle">Stock Inventory Report</div>
    <div class="report-subtitle">Generated: <?= date('F d, Y H:i:s') ?></div>
    <div class="report-subtitle">Total Categories: <?= count($stockByType) ?> | Total Items: <?= $result->num_rows ?></div>
</div>

<!-- Stock Data by Category -->
<?php foreach ($stockByType as $marbleType => $data): ?>
    <?php 
    $categoryTotals = [
        'quantity' => 0,
        'total_area' => 0,
        'total_amount' => 0
    ];
    ?>
    
    <!-- Category Header -->
    <div class="section-header">
        <?= strtoupper($marbleType) ?> (<?= $data['code'] ?>)
        <span class="category-count"><?= count($data['items']) ?> items</span>
    </div>
    
    <!-- Category Table -->
    <table>
        <thead>
            <tr>
                <th width="10%">Stock ID</th>
                <th width="25%">Description</th>
                <th width="8%">Finish</th>
                <th width="8%" class="text-center">Length (cm)</th>
                <th width="8%" class="text-center">Width (cm)</th>
                <th width="8%" class="text-right">Qty (pcs)</th>
                <th width="10%" class="text-right">Area (m²)</th>
                <th width="10%" class="text-right">Cost/m² (RM)</th>
                <th width="13%" class="text-right">Total (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['items'] as $item): ?>
                <?php
                $categoryTotals['quantity'] += $item['quantity'];
                $categoryTotals['total_area'] += $item['total_area'];
                $categoryTotals['total_amount'] += $item['total_amount'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['stock_id']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= htmlspecialchars($item['finish_type']) ?></td>
                    <td class="text-center"><?= number_format($item['length'], 2) ?></td>
                    <td class="text-center"><?= number_format($item['width'], 2) ?></td>
                    <td class="text-right"><?= number_format($item['quantity'], 0) ?></td>
                    <td class="text-right"><?= number_format($item['total_area'], 2) ?></td>
                    <td class="text-right"><?= number_format($item['cost_per_m2'], 2) ?></td>
                    <td class="text-right"><?= number_format($item['total_amount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            
            <!-- Category Subtotal -->
            <tr class="total-row">
                <td colspan="5" style="text-align: right;"><strong>Subtotal <?= $marbleType ?>:</strong></td>
                <td class="text-right"><strong><?= number_format($categoryTotals['quantity'], 0) ?></strong></td>
                <td class="text-right"><strong><?= number_format($categoryTotals['total_area'], 2) ?></strong></td>
                <td class="text-right">-</td>
                <td class="text-right"><strong><?= number_format($categoryTotals['total_amount'], 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
<?php endforeach; ?>

<!-- Grand Total -->
<table style="margin-top: 30px;">
    <tr class="grand-total-row">
        <td width="62%" style="text-align: right; padding: 12px;"><strong>GRAND TOTAL:</strong></td>
        <td width="8%" class="text-right" style="padding: 12px;"><strong><?= number_format($totals['quantity'], 0) ?> pcs</strong></td>
        <td width="10%" class="text-right" style="padding: 12px;"><strong><?= number_format($totals['total_area'], 2) ?> m²</strong></td>
        <td width="10%" class="text-right" style="padding: 12px;">-</td>
        <td width="13%" class="text-right" style="padding: 12px;"><strong>RM <?= number_format($totals['total_amount'], 2) ?></strong></td>
    </tr>
</table>

<!-- Summary Statistics -->
<table class="summary-table">
    <thead>
        <tr>
            <th colspan="2" style="text-align: center;">INVENTORY SUMMARY</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Total Categories:</strong></td>
            <td class="text-right"><?= count($stockByType) ?></td>
        </tr>
        <tr>
            <td><strong>Total Stock Items:</strong></td>
            <td class="text-right"><?= $result->num_rows ?></td>
        </tr>
        <tr>
            <td><strong>Total Quantity:</strong></td>
            <td class="text-right"><?= number_format($totals['quantity'], 0) ?> pieces</td>
        </tr>
        <tr>
            <td><strong>Total Area:</strong></td>
            <td class="text-right"><?= number_format($totals['total_area'], 2) ?> m²</td>
        </tr>
        <tr style="background-color: #e8f5e9;">
            <td><strong>Total Inventory Value:</strong></td>
            <td class="text-right"><strong>RM <?= number_format($totals['total_amount'], 2) ?></strong></td>
        </tr>
        <tr>
            <td><strong>Average Cost per m²:</strong></td>
            <td class="text-right">RM <?= $totals['total_area'] > 0 ? number_format($totals['total_amount'] / $totals['total_area'], 2) : '0.00' ?></td>
        </tr>
    </tbody>
</table>

<!-- Footer -->
<div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #333; text-align: center; font-size: 10px; color: #666;">
    <p><strong>Stone Pavilion Inventory System (SPINV)</strong></p>
    <p>This report is auto-generated and contains confidential information.</p>
    <p>Report ID: INV-<?= date('Ymd-His') ?></p>
</div>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>