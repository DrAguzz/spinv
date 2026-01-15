<?php 
session_start();

// 🔒 AUTH CHECK
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}
if (strtolower($_SESSION['role_name']) !== 'purchasing') {
    header("Location: ../login.php");
    exit();
}

$nav = "./";
$link = "../include/";

include($link."container/head.php");
include($link."container/nav.php");
require($link . "php/config.php");

// Get all marble types for dropdown
$marble_types_query = "SELECT * FROM marble_type ORDER BY name ASC";
$marble_types_result = $conn->query($marble_types_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_purchase'])) {
    $user_id = $_SESSION['user_id'];
    $type_id = $_POST['type_id'];
    $description = $_POST['description'];
    $length = floatval($_POST['length']);
    $width = floatval($_POST['width']);
    $quantity = floatval($_POST['quantity']);
    $cost_per_m2 = floatval($_POST['cost_per_m2']);
    
    // Calculate total area and amount
    $total_area = ($length * $width * $quantity) / 10000; // Convert cm² to m²
    $total_amount = $total_area * $cost_per_m2;
    
    // Handle image upload
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $image_name = uniqid() . '.' . $ext;
            $upload_path = "../../uploads/products/" . $image_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
        }
    }
    
    // Generate stock_id
    $year = date('Y');
    $count_query = "SELECT COUNT(*) as total FROM stock WHERE stock_id LIKE 'STK-$year-%'";
    $count_result = $conn->query($count_query);
    $count = $count_result->fetch_assoc()['total'] + 1;
    $stock_id = "STK-$year-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $conn->begin_transaction();
        
        // Insert into stock table with status = 3 (pending)
        $sql_stock = "INSERT INTO stock (stock_id, description, type_id, length, width, quantity, total_area, cost_per_m2, total_amount, image, status, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 3, NOW(), NOW())";
        $stmt_stock = $conn->prepare($sql_stock);
        $stmt_stock->bind_param("ssidddddds", $stock_id, $description, $type_id, $length, $width, $quantity, $total_area, $cost_per_m2, $total_amount, $image_name);
        $stmt_stock->execute();
        
        $inserted_id = $conn->insert_id;
        
        // Insert into stock_record
        $sql_record = "INSERT INTO stock_record (stock_id, user_id, action_type, action_date, qty_change, note, status) 
                       VALUES (?, ?, 'PURCHASE', NOW(), ?, 'Purchase request submitted', 3)";
        $stmt_record = $conn->prepare($sql_record);
        $stmt_record->bind_param("iid", $inserted_id, $user_id, $quantity);
        $stmt_record->execute();
        
        $conn->commit();
        
        $success_msg = "Purchase request submitted successfully! Stock ID: $stock_id";
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = "Failed to submit purchase request: " . $e->getMessage();
    }
}
?>

<!-- Main Content -->
<div class="main">
  <!-- Header -->
  <div class="dashboard-header-pro">
    <div class="header-left">
      <h1 class="header-title-pro">Add Purchase Request</h1>
      <p class="header-subtitle-pro">Submit new marble purchase for accountant approval</p>
    </div>
    <div class="header-right">
      <div class="date-display">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span><?= date('M d, Y') ?></span>
      </div>
    </div>
  </div>

  <!-- Alert Messages -->
  <?php if (isset($success_msg)): ?>
    <div class="alert alert-success">
      <span class="alert-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </span>
      <span><?= htmlspecialchars($success_msg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (isset($error_msg)): ?>
    <div class="alert alert-danger">
      <span class="alert-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
      </span>
      <span><?= htmlspecialchars($error_msg) ?></span>
    </div>
  <?php endif; ?>

  <!-- Purchase Form -->
  <div class="section-container">
    <form method="POST" enctype="multipart/form-data" id="purchaseForm">
      <div class="detail-grid">
        
        <!-- Product Information -->
        <div class="detail-card">
          <div class="detail-card-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="3" y1="9" x2="21" y2="9"></line>
              <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            <h3>Product Information</h3>
          </div>
          <div class="detail-card-body">
            <label class="form-label">Marble Type *</label>
            <select name="type_id" required style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; margin-bottom: 15px;">
              <option value="">Select Marble Type</option>
              <?php while($type = $marble_types_result->fetch_assoc()): ?>
                <option value="<?= $type['type_id'] ?>"><?= htmlspecialchars($type['name']) ?> (<?= htmlspecialchars($type['code']) ?>)</option>
              <?php endwhile; ?>
            </select>

            <label class="form-label">Description *</label>
            <textarea name="description" required rows="3" class="form-textarea" placeholder="Enter product description..."></textarea>
          </div>
        </div>

        <!-- Dimensions & Stock -->
        <div class="detail-card">
          <div class="detail-card-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
            <h3>Dimensions & Stock</h3>
          </div>
          <div class="detail-card-body">
            <label class="form-label">Length (cm) *</label>
            <input type="number" name="length" id="length" step="0.01" required class="form-textarea" style="margin-bottom: 15px;" placeholder="0.00">

            <label class="form-label">Width (cm) *</label>
            <input type="number" name="width" id="width" step="0.01" required class="form-textarea" style="margin-bottom: 15px;" placeholder="0.00">

            <label class="form-label">Quantity (pieces) *</label>
            <input type="number" name="quantity" id="quantity" step="0.01" required class="form-textarea" style="margin-bottom: 15px;" placeholder="0">

            <div class="detail-row highlight-row" style="margin-top: 10px;">
              <span class="detail-label">Total Area</span>
              <span class="detail-value" id="display_area">0.00 m²</span>
            </div>
          </div>
        </div>

        <!-- Pricing Information -->
        <div class="detail-card">
          <div class="detail-card-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="1" x2="12" y2="23"></line>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <h3>Pricing Information</h3>
          </div>
          <div class="detail-card-body">
            <label class="form-label">Cost per m² (RM) *</label>
            <input type="number" name="cost_per_m2" id="cost_per_m2" step="0.01" required class="form-textarea" style="margin-bottom: 15px;" placeholder="0.00">

            <div class="detail-row highlight-row">
              <span class="detail-label">Total Amount</span>
              <span class="detail-value highlight-value" id="display_total">RM 0.00</span>
            </div>
          </div>
        </div>

        <!-- Product Image -->
        <div class="detail-card">
          <div class="detail-card-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <circle cx="8.5" cy="8.5" r="1.5"></circle>
              <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
            <h3>Product Image</h3>
          </div>
          <div class="detail-card-body">
            <label class="form-label">Upload Image (Optional)</label>
            <input type="file" name="image" accept="image/*" class="form-textarea" style="padding: 10px;">
            <p style="font-size: 12px; color: #6c757d; margin-top: 8px;">Accepted: JPG, JPEG, PNG, GIF</p>
          </div>
        </div>

      </div>

      <!-- Submit Buttons -->
      <div class="btn-umContainer" style="margin-top: 30px;">
        <a href="history.php" class="cancel">Cancel</a>
        <button type="submit" name="submit_purchase" class="btn btn-primary">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          Submit Purchase Request
        </button>
      </div>
    </form>
  </div>
</div>

<!-- JavaScript for Auto-Calculate -->
<script>
// Auto-calculate total area and amount
function calculateTotals() {
  const length = parseFloat(document.getElementById('length').value) || 0;
  const width = parseFloat(document.getElementById('width').value) || 0;
  const quantity = parseFloat(document.getElementById('quantity').value) || 0;
  const costPerM2 = parseFloat(document.getElementById('cost_per_m2').value) || 0;
  
  // Calculate total area (convert cm² to m²)
  const totalArea = (length * width * quantity) / 10000;
  
  // Calculate total amount
  const totalAmount = totalArea * costPerM2;
  
  // Update display
  document.getElementById('display_area').textContent = totalArea.toFixed(2) + ' m²';
  document.getElementById('display_total').textContent = 'RM ' + totalAmount.toFixed(2);
}

// Add event listeners
document.getElementById('length').addEventListener('input', calculateTotals);
document.getElementById('width').addEventListener('input', calculateTotals);
document.getElementById('quantity').addEventListener('input', calculateTotals);
document.getElementById('cost_per_m2').addEventListener('input', calculateTotals);

// Form validation
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
  const length = parseFloat(document.getElementById('length').value);
  const width = parseFloat(document.getElementById('width').value);
  const quantity = parseFloat(document.getElementById('quantity').value);
  const costPerM2 = parseFloat(document.getElementById('cost_per_m2').value);
  
  if (length <= 0 || width <= 0 || quantity <= 0 || costPerM2 <= 0) {
    e.preventDefault();
    alert('Please enter valid positive values for all fields!');
    return false;
  }
  
  return confirm('Are you sure you want to submit this purchase request?');
});

// Auto-hide alerts
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(alert => {
    alert.style.opacity = '0';
    setTimeout(() => alert.remove(), 300);
  });
}, 5000);
</script>

<?php 
  include($link."container/footer.php");
?>