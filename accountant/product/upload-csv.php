<?php 
  $nav = "../";
  $link = "../../include/";
  $imgLink = "../../";
  session_start();


if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit();
}
if (strtolower($_SESSION['role_name']) !== 'accountant') {
    header("Location: ../../login.php"); // pastikan path betul
    exit();
}

  include($link."container/head.php");
  include($link."container/nav.php");
  require($link . "php/config.php");

  $upload_message = '';
  $upload_type = '';
  $upload_details = [];

  // Fetch all marble types for reference
  $marble_types_query = "SELECT type_id, code, name, finish_type FROM marble_type ORDER BY name";
  $marble_types_result = $conn->query($marble_types_query);
  $marble_types = [];
  if ($marble_types_result) {
    while ($mt = $marble_types_result->fetch_assoc()) {
      $marble_types[] = $mt;
    }
  }

  // Handle CSV Upload with Images
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Server-side validations: upload error, size, extension
    if ($file['error'] === UPLOAD_ERR_OK) {
      // Max 5MB
      $max_size = 5 * 1024 * 1024;
      if ($file['size'] > $max_size) {
        $upload_type = 'danger';
        $upload_message = "File terlalu besar. Saiz maksimum 5MB.";
      } else {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
          $upload_type = 'danger';
          $upload_message = "Jenis fail tidak sah. Sila muat naik fail CSV.";
        } else {
          $handle = fopen($file['tmp_name'], 'r');
          
          if ($handle !== FALSE) {
            $header = fgetcsv($handle); // Read header row
            
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            $row_number = 1;
            
            // Create upload directory if not exists
            $upload_dir = '../../uploads/products/';
            if (!file_exists($upload_dir)) {
              mkdir($upload_dir, 0777, true);
            }
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
              while (($data = fgetcsv($handle)) !== FALSE) {
                $row_number++;
                
                // Skip completely empty rows
                if (empty(array_filter($data))) continue;
                
                // Parse CSV columns (safe fallback to indexes)
                $stock_id = isset($data[0]) ? trim($data[0]) : '';
                $description = isset($data[1]) ? trim($data[1]) : '';
                $marble_type_input = isset($data[2]) ? trim($data[2]) : ''; // Can be type_id, code, or name
                $length = isset($data[3]) ? trim($data[3]) : '0';
                $width = isset($data[4]) ? trim($data[4]) : '0';
                $quantity = isset($data[5]) ? trim($data[5]) : '1';
                $cost_per_m2 = isset($data[6]) ? trim($data[6]) : '0';
                $image_url = isset($data[7]) ? trim($data[7]) : ''; // Image URL or path
                
                // Validate required fields
                if (empty($stock_id) || empty($description) || empty($marble_type_input)) {
                  $errors[] = "Row $row_number: Missing required fields (Stock ID, Description, or Marble Type)";
                  $error_count++;
                  continue;
                }
                
                // Resolve Marble Type ID
                $type_id = null;
                
                // Method 1: Check if input is numeric (type_id)
                if (is_numeric($marble_type_input)) {
                  $type_id = intval($marble_type_input);
                  
                  // Verify type_id exists in database
                  $check_stmt = $conn->prepare("SELECT type_id FROM marble_type WHERE type_id = ?");
                  $check_stmt->bind_param("i", $type_id);
                  $check_stmt->execute();
                  $check_result = $check_stmt->get_result();
                  
                  if ($check_result->num_rows === 0) {
                    $errors[] = "Row $row_number: Marble Type ID '$marble_type_input' not found in database";
                    $error_count++;
                    $check_stmt->close();
                    continue;
                  }
                  $check_stmt->close();
                  
                } else {
                  // Method 2 & 3: Search by code or name
                  $search_stmt = $conn->prepare("SELECT type_id FROM marble_type WHERE code = ? OR name = ? LIMIT 1");
                  $search_stmt->bind_param("ss", $marble_type_input, $marble_type_input);
                  $search_stmt->execute();
                  $search_result = $search_stmt->get_result();
                  
                  if ($search_result->num_rows > 0) {
                    $type_row = $search_result->fetch_assoc();
                    $type_id = $type_row['type_id'];
                  } else {
                    $errors[] = "Row $row_number: Marble Type '$marble_type_input' not found. Use type_id, code, or exact name from marble_type table";
                    $error_count++;
                    $search_stmt->close();
                    continue;
                  }
                  $search_stmt->close();
                }
                
                // Calculate total_area and total_amount
                $length_val = floatval($length);
                $width_val = floatval($width);
                $quantity_val = floatval($quantity);
                $cost_val = floatval($cost_per_m2);
                
                $total_area = ($length_val * $width_val * $quantity_val) / 10000; // Convert cm² to m²
                $total_amount = $total_area * $cost_val;
                
                // Handle image
                $image_filename = NULL;
                
                if (!empty($image_url)) {
                  // Check if it's a URL or local path
                  if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                    // Download image from URL
                    $image_content = @file_get_contents($image_url);
                    
                    if ($image_content !== FALSE) {
                      $image_ext = pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION);
                      if (empty($image_ext)) $image_ext = 'jpg';
                      
                      $image_filename = $stock_id . '_' . time() . '.' . $image_ext;
                      $image_path = $upload_dir . $image_filename;
                      
                      if (!file_put_contents($image_path, $image_content)) {
                        $errors[] = "Row $row_number: Failed to save image from URL";
                      }
                    } else {
                      $errors[] = "Row $row_number: Failed to download image from URL: $image_url";
                    }
                  } else {
                    // Local file path
                    $local_image_path = $image_url;
                    
                    // Check if file exists
                    if (file_exists($local_image_path)) {
                      $image_ext = pathinfo($local_image_path, PATHINFO_EXTENSION);
                      $image_filename = $stock_id . '_' . time() . '.' . $image_ext;
                      $image_path = $upload_dir . $image_filename;
                      
                      if (!copy($local_image_path, $image_path)) {
                        $errors[] = "Row $row_number: Failed to copy image from: $local_image_path";
                        $image_filename = NULL;
                      }
                    } else {
                      $errors[] = "Row $row_number: Image file not found: $local_image_path";
                    }
                  }
                }
                
                // Check if stock_id already exists
                $check_stock = $conn->prepare("SELECT id FROM stock WHERE stock_id = ?");
                $check_stock->bind_param("s", $stock_id);
                $check_stock->execute();
                $stock_exists = $check_stock->get_result();
                
                if ($stock_exists->num_rows > 0) {
                  $errors[] = "Row $row_number: Stock ID '$stock_id' already exists in database";
                  $error_count++;
                  $check_stock->close();
                  continue;
                }
                $check_stock->close();
                
                // Insert into stock table
                $stmt = $conn->prepare("INSERT INTO stock (stock_id, description, type_id, length, width, quantity, total_area, cost_per_m2, total_amount, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
                $stmt->bind_param("ssidddddds", $stock_id, $description, $type_id, $length_val, $width_val, $quantity_val, $total_area, $cost_val, $total_amount, $image_filename);
                
                if ($stmt->execute()) {
                  $inserted_stock_id = $conn->insert_id;
                  
                  // Insert into stock_record for audit trail
                  $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 if no session
                  $action_type = 'STOCK_IN';
                  $action_date = date('Y-m-d');
                  $note = 'Bulk import via CSV';
                  
                  $record_stmt = $conn->prepare("INSERT INTO stock_record (stock_id, user_id, action_type, action_date, qty_change, note) VALUES (?, ?, ?, ?, ?, ?)");
                  $record_stmt->bind_param("iissds", $inserted_stock_id, $user_id, $action_type, $action_date, $quantity_val, $note);
                  $record_stmt->execute();
                  $record_stmt->close();
                  
                  $success_count++;
                } else {
                  $errors[] = "Row $row_number: Database error - " . $stmt->error;
                  $error_count++;
                }
                
                $stmt->close();
              }
              
              // Commit transaction
              $conn->commit();
              
              $upload_type = $error_count > 0 ? 'warning' : 'success';
              $upload_message = "$success_count products imported successfully.";
              if ($error_count > 0) {
                $upload_message .= " $error_count rows had errors.";
              }
              $upload_details = $errors;
              
            } catch (Exception $e) {
              $conn->rollback();
              $upload_type = 'danger';
              $upload_message = "Import failed: " . $e->getMessage();
            }
            
            fclose($handle);
          } else {
            $upload_type = 'danger';
            $upload_message = "Failed to open CSV file.";
          }
        }
      }
    } else {
      $upload_type = 'danger';
      $upload_message = "File upload error: " . $file['error'];
    }
  }
?>

<!-- Main Content -->
<div class="main">
  <!-- Header -->
  <div class="header">
    <button class="back-icon" onclick="window.location.href='index.php'">
      ← Back
    </button>
    <h2 class="header-title">Bulk Import Products (CSV)</h2>
  </div>

  <!-- Alert Messages -->
  <?php if (!empty($upload_message)): ?>
    <div class="alert alert-<?= $upload_type ?>">
      <span class="alert-icon">
        <?php if ($upload_type === 'success'): ?>
          ✓
        <?php elseif ($upload_type === 'warning'): ?>
          ⚠
        <?php else: ?>
          ✕
        <?php endif; ?>
      </span>
      <span><?= htmlspecialchars($upload_message) ?></span>
    </div>
    
    <?php if (!empty($upload_details)): ?>
      <div class="section-container" style="margin-bottom: 25px;">
        <h3 style="color: #e74c3c; margin-bottom: 15px;">⚠️ Error Details:</h3>
        <div style="max-height: 300px; overflow-y: auto; background: #fff5f5; padding: 15px; border-radius: 8px; border-left: 4px solid #e74c3c;">
          <ul style="color: #721c24; line-height: 1.8; margin: 0;">
            <?php foreach ($upload_details as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Instructions Section -->
  <div class="section-container">
    <div class="section-header-clean">
      <div>
        <h2 class="section-title-clean">Upload Instructions</h2>
        <p class="section-desc">Follow these steps to successfully import your products with images</p>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
      <!-- Step cards (kept same) -->
      <div class="method-card" style="cursor: default; border-color: #3498db;">
        <div class="method-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: #fff;">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
        </div>
        <h4>Step 1: Download Template</h4>
        <p>Download the CSV template with the correct format and headers</p>
      </div>

      <div class="method-card" style="cursor: default; border-color: #9b59b6;">
        <div class="method-icon" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: #fff;">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
          </svg>
        </div>
        <h4>Step 2: Check Marble Types</h4>
        <p>Review available marble types below before filling data</p>
      </div>

      <div class="method-card" style="cursor: default; border-color: #2ecc71;">
        <div class="method-icon" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: #fff;">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
          </svg>
        </div>
        <h4>Step 3: Fill Data</h4>
        <p>Add product data using type_id, code, or name</p>
      </div>

      <div class="method-card" style="cursor: default; border-color: #f39c12;">
        <div class="method-icon" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: #fff;">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
        </div>
        <h4>Step 4: Upload File</h4>
        <p>Upload CSV and system will process automatically</p>
      </div>
    </div>
  </div>

  <!-- Available Marble Types Reference -->
  <div class="section-container">
    <h3 class="section-title-clean" style="margin-bottom: 15px;">🗃️ Available Marble Types (Reference)</h3>
    <p class="section-desc" style="margin-bottom: 20px;">Use any of these values in the "Marble Type" column: <strong>type_id</strong>, <strong>code</strong>, or <strong>name</strong></p>
    
    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Type ID</th>
            <th>Code</th>
            <th>Name</th>
            <th>Finish Type</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($marble_types) > 0): ?>
            <?php foreach ($marble_types as $type): ?>
              <tr class="data-row">
                <td class="cell-number"><strong><?= htmlspecialchars($type['type_id']) ?></strong></td>
                <td class="cell-id"><?= htmlspecialchars($type['code']) ?></td>
                <td class="cell-text"><?= htmlspecialchars($type['name']) ?></td>
                <td><span class="badge-type"><?= htmlspecialchars($type['finish_type']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="empty-state-row">
                <div class="empty-state-content">
                  <div class="empty-title">No marble types found</div>
                  <div class="empty-desc">Please add marble types first before importing products</div>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 8px;">
      <strong style="color: #1565c0;">💡 Tip:</strong>
      <p style="margin: 8px 0 0 0; color: #1976d2; line-height: 1.6;">
        You can use any of these three methods to specify marble type in your CSV:<br>
        <strong>1.</strong> Type ID (e.g., <code>1</code>, <code>5</code>, <code>12</code>)<br>
        <strong>2.</strong> Code (e.g., <code>EXO-001</code>, <code>GRN-002</code>)<br>
        <strong>3.</strong> Name (e.g., <code>Black Galaxy</code>, <code>Carrara White</code>)
      </p>
    </div>
  </div>

  <!-- CSV Format Guide -->
  <div class="section-container">
    <h3 class="section-title-clean" style="margin-bottom: 15px;">📋 CSV Format Requirements</h3>
    
    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Column</th>
            <th>Field Name</th>
            <th>Required</th>
            <th>Description</th>
            <th>Example</th>
          </tr>
        </thead>
        <tbody>
          <tr class="data-row">
            <td class="cell-number">1</td>
            <td class="cell-text"><strong>Stock ID</strong></td>
            <td><span class="status status-red">Yes</span></td>
            <td class="cell-text">Unique product identifier</td>
            <td class="cell-id">STK-001</td>
          </tr>
          <tr class="data-row">
            <td class="cell-number">2</td>
            <td class="cell-text"><strong>Description</strong></td>
            <td><span class="status status-red">Yes</span></td>
            <td class="cell-text">Product description</td>
            <td class="cell-text">Black Galaxy Granite Premium</td>
          </tr>
          <tr class="data-row" style="background: #fff9e6;">
            <td class="cell-number">3</td>
            <td class="cell-text"><strong>Marble Type</strong></td>
            <td><span class="status status-red">Yes</span></td>
            <td class="cell-text">Type ID, Code, or Name from table above</td>
            <td class="cell-text">1 OR EXO-001 OR Black Galaxy</td>
          </tr>
          <tr class="data-row">
            <td class="cell-number">4</td>
            <td class="cell-text"><strong>Length (cm)</strong></td>
            <td><span class="status status-yellow">Optional</span></td>
            <td class="cell-text">Length in centimeters</td>
            <td class="cell-number">240</td>
          </tr>
          <tr class="data-row">
            <td class="cell-number">5</td>
            <td class="cell-text"><strong>Width (cm)</strong></td>
            <td><span class="status status-yellow">Optional</span></td>
            <td class="cell-text">Width in centimeters</td>
            <td class="cell-number">120</td>
          </tr>
          <tr class="data-row">
            <td class="cell-number">6</td>
            <td class="cell-text"><strong>Quantity</strong></td>
            <td><span class="status status-yellow">Optional</span></td>
            <td class="cell-text">Number of pieces (default: 1)</td>
            <td class="cell-number">5</td>
          </tr>
          <tr class="data-row">
            <td class="cell-number">7</td>
            <td class="cell-text"><strong>Cost per m²</strong></td>
            <td><span class="status status-yellow">Optional</span></td>
            <td class="cell-text">Cost per square meter</td>
            <td class="cell-number">150.50</td>
          </tr>
          <tr class="data-row" style="background: #f0f8ff;">
            <td class="cell-number">8</td>
            <td class="cell-text"><strong>Image URL/Path</strong></td>
            <td><span class="status status-yellow">Optional</span></td>
            <td class="cell-text">Image URL or local file path</td>
            <td class="cell-text" style="font-size: 12px;">https://example.com/img.jpg</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #f39c12; border-radius: 8px;">
      <strong style="color: #856404;">⚠️ Important Notes:</strong>
      <ul style="margin: 10px 0 0 20px; color: #856404; line-height: 1.8;">
        <li>CSV file must use comma (,) as delimiter</li>
        <li>First row must contain column headers</li>
        <li>Stock ID must be unique (will be checked against existing records)</li>
        <li><strong>Marble Type must match existing records in marble_type table</strong></li>
        <li>System will automatically calculate: total_area = (length × width × quantity) / 10000 m²</li>
        <li>System will automatically calculate: total_amount = total_area × cost_per_m2</li>
        <li>All imports will create stock_record entries for audit trail</li>
        <li>Image column can contain URL or local file path</li>
        <li>Supported image formats: JPG, JPEG, PNG, GIF, WEBP</li>
      </ul>
    </div>
  </div>

  <!-- Upload Section -->
  <div class="section-container">
    <div class="section-header-clean">
      <div>
        <h2 class="section-title-clean">Upload CSV File</h2>
        <p class="section-desc">Select your CSV file with product data and image references</p>
      </div>
      <a href="csv-template.php" class="download-template">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        Download Template
      </a>
    </div>

    <form method="POST" enctype="multipart/form-data" id="uploadForm">
      <div class="upload-area" id="uploadArea" tabindex="0" role="button" aria-label="Upload CSV area">
        <input type="file" name="csv_file" id="csvFile" accept=".csv" style="display: none;" required>
        
        <div class="upload-content" id="uploadContent">
          <div class="method-icon" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); margin: 0 auto 20px;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="12" y1="18" x2="12" y2="12"></line>
              <line x1="9" y1="15" x2="15" y2="15"></line>
            </svg>
          </div>
          <h3 style="margin: 0 0 10px 0; color: #2c3e50;">Click or drag & drop CSV file here</h3>
          <p style="margin: 0; color: #6c757d; font-size: 14px;">or drag and drop your file here</p>
          <p style="margin: 10px 0 0 0; color: #95a5a6; font-size: 13px;">Maximum file size: 5MB</p>
        </div>

        <div class="file-selected" id="fileSelected" style="display: none;">
          <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <div class="method-icon" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: #fff;">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </div>
            <div style="flex: 1;">
              <h4 style="margin: 0 0 5px 0; color: #2c3e50;" id="fileName">file.csv</h4>
              <p style="margin: 0; color: #6c757d; font-size: 14px;" id="fileSize">0 KB</p>
            </div>
            <button type="button" class="action-btn action-reject" onclick="clearFile()" title="Remove file">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        </div>

        <div id="clientError" style="display:none; color:#721c24; background:#fff5f5; padding:10px; border-radius:6px; margin-top:10px;"></div>
      </div>

      <div style="display: flex; gap: 15px; margin-top: 25px;">
        <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">
          Cancel
        </button>
        <button type="submit" class="btn btn-main" id="uploadBtn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
          Upload & Import Products
        </button>
      </div>
    </form>
  </div>
</div>

<style>
.upload-area {
  border: 3px dashed #dee2e6;
  border-radius: 12px;
  padding: 60px 40px;
  text-align: center;
  background: #f8f9fa;
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
  outline: none;
}

.upload-area:hover {
  border-color: #3498db;
  background: #f0f8ff;
}

.upload-area.dragover {
  border-color: #2ecc71;
  background: #f0fff4;
  transform: scale(1.02);
}

.file-selected {
  padding: 30px;
}

code {
  background: #f8f9fa;
}

.action-btn {
  background: transparent;
  border: none;
  cursor: pointer;
}
</style>

<script>
  (function(){
    const uploadArea = document.getElementById('uploadArea');
    const csvFileInput = document.getElementById('csvFile');
    const fileSelected = document.getElementById('fileSelected');
    const uploadContent = document.getElementById('uploadContent');
    const fileNameEl = document.getElementById('fileName');
    const fileSizeEl = document.getElementById('fileSize');
    const clientError = document.getElementById('clientError');
    const uploadForm = document.getElementById('uploadForm');

    const MAX_SIZE = 5 * 1024 * 1024; // 5MB

    // Helper to format bytes
    function formatBytes(bytes) {
      if (bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B','KB','MB','GB','TB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Show file info
    function showFile(file) {
      fileNameEl.textContent = file.name;
      fileSizeEl.textContent = formatBytes(file.size);
      fileSelected.style.display = 'block';
      uploadContent.style.display = 'none';
      clientError.style.display = 'none';
    }

    // Clear file selection
    window.clearFile = function() {
      csvFileInput.value = '';
      fileSelected.style.display = 'none';
      uploadContent.style.display = 'block';
      clientError.style.display = 'none';
    }

    // Validate file (client-side)
    function validateFile(file) {
      clientError.style.display = 'none';
      if (!file) return false;
      const name = file.name.toLowerCase();
      if (!name.endsWith('.csv')) {
        clientError.textContent = 'Jenis fail tidak sah. Sila muat naik fail CSV.';
        clientError.style.display = 'block';
        return false;
      }
      if (file.size > MAX_SIZE) {
        clientError.textContent = 'Fail terlalu besar. Saiz maksimum 5MB.';
        clientError.style.display = 'block';
        return false;
      }
      return true;
    }

    // When user clicks the upload area -> open file picker
    uploadArea.addEventListener('click', function(e){
      csvFileInput.click();
    });

    // Keyboard accessibility: Enter or Space triggers file picker
    uploadArea.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        csvFileInput.click();
      }
    });

    // File input change
    csvFileInput.addEventListener('change', function(e){
      const file = e.target.files[0];
      if (validateFile(file)) {
        showFile(file);
      } else {
        // invalid -> reset input
        csvFileInput.value = '';
      }
    });

    // Drag & Drop events
    ['dragenter','dragover'].forEach(ev => {
      uploadArea.addEventListener(ev, function(e){
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.add('dragover');
      });
    });

    ['dragleave','drop'].forEach(ev => {
      uploadArea.addEventListener(ev, function(e){
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.remove('dragover');
      });
    });

    uploadArea.addEventListener('drop', function(e){
      const dt = e.dataTransfer;
      if (dt && dt.files && dt.files.length) {
        const file = dt.files[0];
        if (validateFile(file)) {
          // set the file into the input (works in modern browsers)
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(file);
          csvFileInput.files = dataTransfer.files;
          showFile(file);
        } else {
          csvFileInput.value = '';
        }
      }
    });

    // Form submit: ensure file selected
    uploadForm.addEventListener('submit', function(e){
      if (!csvFileInput.files || !csvFileInput.files.length) {
        e.preventDefault();
        clientError.textContent = 'Sila pilih fail CSV sebelum memuat naik.';
        clientError.style.display = 'block';
        // focus on upload area
        uploadArea.focus();
        return false;
      }
      // All good; allow submit (server-side will re-validate)
    });

  })();
</script>
