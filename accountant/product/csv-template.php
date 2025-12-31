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
/**
 * CSV Template Generator with Marble Type Support
 * File: csv-template.php
 * 
 * Generates a downloadable CSV template for bulk product import
 */

require("../../include/php/config.php");

// Fetch marble types for examples
$marble_query = "SELECT type_id, code, name FROM marble_type ORDER BY name LIMIT 5";
$marble_result = $conn->query($marble_query);
$marble_examples = [];
if ($marble_result) {
  while ($row = $marble_result->fetch_assoc()) {
    $marble_examples[] = $row;
  }
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=stock_import_template.csv');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV Header Row
$headers = [
    'Stock ID',
    'Description',
    'Marble Type (ID/Code/Name)',
    'Length (cm)',
    'Width (cm)',
    'Quantity',
    'Cost per m²',
    'Image URL/Path'
];

fputcsv($output, $headers);

// Add sample data rows with real marble type examples
$sample_data = [];

if (count($marble_examples) > 0) {
  // Use real data from database
  foreach ($marble_examples as $index => $marble) {
    $stock_num = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
    
    // Show different methods: ID, Code, Name
    if ($index == 0) {
      $marble_ref = $marble['type_id']; // Method 1: Use ID
    } elseif ($index == 1) {
      $marble_ref = $marble['code']; // Method 2: Use Code
    } else {
      $marble_ref = $marble['name']; // Method 3: Use Name
    }
    
    $sample_data[] = [
      'STK-' . $stock_num,
      $marble['name'] . ' Premium Slab',
      $marble_ref,
      '240',
      '120',
      '1',
      '150.50',
      'https://example.com/images/' . strtolower(str_replace(' ', '-', $marble['name'])) . '.jpg'
    ];
  }
} else {
  // Fallback sample data if no marble types in database
  $sample_data = [
    ['STK-001', 'Premium Granite Slab', '1', '240', '120', '1', '150.50', 'https://example.com/image1.jpg'],
    ['STK-002', 'Exotic Marble Premium', 'EXO-001', '300', '150', '2', '200.00', 'https://imgur.com/abc123.png'],
    ['STK-003', 'Quartz Off Cut', 'Calacatta Gold', '180', '90', '5', '120.00', './images/quartz.jpg'],
  ];
}

foreach ($sample_data as $row) {
    fputcsv($output, $row);
}

// Add instruction rows
fputcsv($output, []);
fputcsv($output, ['# ====================================']);
fputcsv($output, ['# STOCK IMPORT INSTRUCTIONS']);
fputcsv($output, ['# ====================================']);
fputcsv($output, []);
fputcsv($output, ['# BEFORE YOU START:']);
fputcsv($output, ['# 1. Check available marble types in the upload page']);
fputcsv($output, ['# 2. You can use type_id, code, or name for Marble Type column']);
fputcsv($output, ['# 3. Delete all sample rows above (keep only the header row)']);
fputcsv($output, []);
fputcsv($output, ['# COLUMN DESCRIPTIONS:']);
fputcsv($output, ['# 1. Stock ID - REQUIRED - Unique identifier (e.g., STK-001, PRD-123)']);
fputcsv($output, ['# 2. Description - REQUIRED - Product description']);
fputcsv($output, ['# 3. Marble Type - REQUIRED - Use one of these:']);
fputcsv($output, ['#    - type_id (e.g., 1, 5, 12)']);
fputcsv($output, ['#    - code (e.g., EXO-001, GRN-002)']);
fputcsv($output, ['#    - name (e.g., Black Galaxy, Carrara White)']);
fputcsv($output, ['# 4. Length - OPTIONAL - In centimeters (default: 0)']);
fputcsv($output, ['# 5. Width - OPTIONAL - In centimeters (default: 0)']);
fputcsv($output, ['# 6. Quantity - OPTIONAL - Number of pieces (default: 1)']);
fputcsv($output, ['# 7. Cost per m² - OPTIONAL - Cost per square meter (default: 0)']);
fputcsv($output, ['# 8. Image URL/Path - OPTIONAL - Image URL or local file path']);
fputcsv($output, []);
fputcsv($output, ['# AUTOMATIC CALCULATIONS:']);
fputcsv($output, ['# - Total Area = (Length × Width × Quantity) ÷ 10,000 m²']);
fputcsv($output, ['# - Total Amount = Total Area × Cost per m²']);
fputcsv($output, ['# - Stock Record will be created automatically for audit trail']);
fputcsv($output, []);
fputcsv($output, ['# MARBLE TYPE EXAMPLES:']);
if (count($marble_examples) > 0) {
  foreach ($marble_examples as $marble) {
    fputcsv($output, [
      "# ID: {$marble['type_id']} | Code: {$marble['code']} | Name: {$marble['name']}"
    ]);
  }
} else {
  fputcsv($output, ['# Please check the marble_type table in your database']);
}
fputcsv($output, ['# (Full list available in the upload page)']);
fputcsv($output, []);
fputcsv($output, ['# IMAGE UPLOAD METHODS:']);
fputcsv($output, ['# METHOD 1: URL - https://example.com/images/product.jpg']);
fputcsv($output, ['# METHOD 2: Local Path - ./images/product.jpg or /var/www/uploads/image.jpg']);
fputcsv($output, ['# System will automatically download (URL) or copy (local path) images']);
fputcsv($output, []);
fputcsv($output, ['# VALIDATION:']);
fputcsv($output, ['# - Stock ID must be unique (checked against existing records)']);
fputcsv($output, ['# - Marble Type must exist in marble_type table']);
fputcsv($output, ['# - Duplicate Stock IDs will be rejected']);
fputcsv($output, ['# - Invalid Marble Types will be rejected']);
fputcsv($output, []);
fputcsv($output, ['# EXAMPLE VALID ROWS:']);
fputcsv($output, ['# STK-001,Black Galaxy Premium,1,240,120,1,150.50,https://example.com/img.jpg']);
fputcsv($output, ['# STK-002,Carrara White Marble,EXO-001,300,150,2,200.00,./images/carrara.jpg']);
fputcsv($output, ['# STK-003,Calacatta Gold,Calacatta Gold,180,90,5,120.00,']);
fputcsv($output, []);
fputcsv($output, ['# Start adding your data below! Good luck! 🚀']);

fclose($output);
exit();
?>