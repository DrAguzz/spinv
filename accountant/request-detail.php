<?php 
  $nav = "./";
  $link = "../include/";

  session_start();

// 🔒 AUTH CHECK PALING ATAS
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}
if (strtolower($_SESSION['role_name']) !== 'accountant') {
    header("Location: ../login.php"); // pastikan path betul
    exit();
}

  include($link."container/head.php");
  include($link."container/nav.php");
  require($link . "php/config.php");
  require_once($link . "php/dashboard/dashboard.php");

  // Get stock ID from URL
  $stock_id = $_GET['id'] ?? 0;

  // Get product detail
  $product = getPendingProductDetail($conn, $stock_id);

  // If product not found, redirect back
  if (!$product) {
      echo "<script>alert('Product not found!'); window.location.href='index.php';</script>";
      exit();
  }

  // Handle approve/reject actions
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $action = $_POST['action'] ?? '';
      $accountant_id = $_SESSION['user_id'] ?? 1;
      
      if ($action === 'approve') {
          if (approveProduct($conn, $stock_id, $accountant_id)) {
              echo "<script>alert('Product approved successfully!'); window.location.href='index.php';</script>";
              exit();
          } else {
              $error_msg = "Failed to approve product.";
          }
      } elseif ($action === 'reject') {
          $reason = $_POST['reason'] ?? '';
          if (rejectProduct($conn, $stock_id, $accountant_id, $reason)) {
              echo "<script>alert('Product rejected successfully!'); window.location.href='index.php';</script>";
              exit();
          } else {
              $error_msg = "Failed to reject product.";
          }
      }
  }
?>

<!-- Main Content -->
<div class="main">
  <!-- Header with Back Button -->
  <div class="header">
    <button class="back-icon" onclick="window.location.href='index.php'">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back
    </button>
    <div class="header-title">Request Detail</div>
  </div>

  <!-- Error Message -->
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

  <!-- Detail Cards Grid -->
  <div class="detail-grid">
    <!-- Product Detail Card -->
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
        <div class="detail-row">
          <span class="detail-label">Stock ID</span>
          <span class="detail-value"><?= htmlspecialchars($product['stock_id']) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Description</span>
          <span class="detail-value"><?= htmlspecialchars($product['description']) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Marble Type</span>
          <span class="detail-value">
            <span class="badge badge-finish"><?= htmlspecialchars($product['finish_name']) ?></span>
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Marble Code</span>
          <span class="detail-value"><?= htmlspecialchars($product['marble_code']) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Finish Type</span>
          <span class="detail-value"><?= htmlspecialchars($product['finish_type']) ?></span>
        </div>
      </div>
    </div>

    <!-- Dimensions & Stock Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
        </svg>
        <h3>Dimensions & Stock</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <span class="detail-label">Length</span>
          <span class="detail-value"><?= number_format($product['length'], 2) ?> cm</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Width</span>
          <span class="detail-value"><?= number_format($product['width'], 2) ?> cm</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Quantity</span>
          <span class="detail-value"><?= number_format($product['quantity'], 0) ?> pcs</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Total Area</span>
          <span class="detail-value"><?= number_format($product['total_area'], 2) ?> m²</span>
        </div>
      </div>
    </div>

    <!-- Pricing Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="1" x2="12" y2="23"></line>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
        <h3>Pricing Information</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <span class="detail-label">Cost per m²</span>
          <span class="detail-value">RM <?= number_format($product['cost_per_m2'], 2) ?></span>
        </div>
        <div class="detail-row highlight-row">
          <span class="detail-label">Total Amount</span>
          <span class="detail-value highlight-value">RM <?= number_format($product['total_amount'], 2) ?></span>
        </div>
      </div>
    </div>

    <!-- Request Detail Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        <h3>Request Information</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <span class="detail-label">Request Type</span>
          <span class="detail-value">
            <span class="badge badge-<?= $product['action_type'] ?>">
              <?= ucfirst($product['action_type']) ?>
            </span>
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Requested By</span>
          <span class="detail-value"><?= htmlspecialchars($product['requester_name']) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Email</span>
          <span class="detail-value"><?= htmlspecialchars($product['requester_email']) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Request Date</span>
          <span class="detail-value"><?= date('M d, Y H:i', strtotime($product['action_date'])) ?></span>
        </div>
        <?php if ($product['note']): ?>
        <div class="detail-row">
          <span class="detail-label">Note</span>
          <span class="detail-value"><?= htmlspecialchars($product['note']) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Product Image -->
  <?php if ($product['image']): ?>
  <div class="image-preview-section">
    <h3>Product Image</h3>
    <div class="image-preview-container">
      <img src="<?= $link ?>upload/product/<?= htmlspecialchars($product['image']) ?>" 
           alt="Product Image"
           class="product-preview-image">
    </div>
  </div>
  <?php endif; ?>

  <!-- Action Buttons -->
  <div class="action-section">
    <h3>Review Actions</h3>
    <div class="action-button-group">
      <!-- Reject Button -->
      <button class="btn-action-large btn-reject-large" onclick="showRejectModal()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        Reject Request
      </button>

      <!-- Approve Button -->
      <form method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to approve this product?')">
        <input type="hidden" name="action" value="approve">
        <button type="submit" class="btn-action-large btn-approve-large">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          Approve Request
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay">
  <div class="modal-container">
    <div class="modal-header">
      <h3>Reject Product Request</h3>
      <button class="modal-close" onclick="closeRejectModal()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    
    <div class="modal-body">
      <form method="POST" id="rejectForm">
        <input type="hidden" name="action" value="reject">
        
        <label for="reason" class="form-label">Reason for rejection:</label>
        <textarea 
          name="reason" 
          id="reason" 
          rows="4" 
          class="form-textarea" 
          placeholder="Please provide a reason for rejection..."
          required></textarea>
        
        <div class="btn-umContainer">
          <button type="button" class="cancel" onclick="closeRejectModal()">Cancel</button>
          <button type="submit" class="btn btn-red">Submit Rejection</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JavaScript -->
<script>
// Reject Modal Functions
function showRejectModal() {
  document.getElementById('rejectModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeRejectModal() {
  document.getElementById('rejectModal').classList.remove('active');
  document.body.style.overflow = 'auto';
  document.getElementById('rejectForm').reset();
}

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeRejectModal();
  }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeRejectModal();
  }
});
</script>

<?php 
  include($link."container/footer.php");
?>