<?php 
session_start();

// AUTH 
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}
if (strtolower($_SESSION['role_name']) !== 'accountant') {
    header("Location: ../login.php");
    exit();
}

$link = "../include/";
require($link . "php/config.php");
require_once($link . "php/dashboard/dashboard.php");

// LOAD HTML
$nav = "./";
include($link . "container/head.php");
include($link . "container/nav.php");

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stock_id = isset($_POST['stock_id']) ? intval($_POST['stock_id']) : 0;
    $action = $_POST['action'] ?? '';
    $accountant_id = $_SESSION['user_id'] ?? 0;

    // Debug logging
    error_log("POST Request - stock_id: $stock_id, action: $action, accountant_id: $accountant_id");
    error_log("POST Data: " . print_r($_POST, true));

    if ($stock_id > 0 && $accountant_id > 0) {
        if ($action === 'approve') {
            error_log("Attempting to approve stock_id: $stock_id");
            $result = approveProduct($conn, $stock_id, $accountant_id);
            error_log("Approve result: " . ($result ? "SUCCESS" : "FAILED"));
            
            if ($result) {
                $success_msg = "Product approved successfully!";
            } else {
                $error_msg = "Failed to approve product. Please check the logs.";
            }
        }

        if ($action === 'reject') {
            $reason = $_POST['reason'] ?? '';
            error_log("Attempting to reject stock_id: $stock_id with reason: $reason");
            $result = rejectProduct($conn, $stock_id, $accountant_id, $reason);
            error_log("Reject result: " . ($result ? "SUCCESS" : "FAILED"));
            
            if ($result) {
                $success_msg = "Product rejected successfully!";
            } else {
                $error_msg = "Failed to reject product. Please check the logs.";
            }
        }
    } else {
        $error_msg = "Invalid stock ID or user session.";
        error_log("Invalid data - stock_id: $stock_id, accountant_id: $accountant_id");
    }
}

$stats = getDashboardStats($conn);
$pendingProducts = getPendingProducts($conn);
$marbleTypes = getMarbleTypeCounts($conn);
?>

<div class="main">
  <!-- Professional Header -->
  <div class="dashboard-header-pro">
    <div class="header-left">
      <h2 class="header-title-pro">Dashboard Overview</h2>
      <p class="header-subtitle-pro">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Administrator') ?></p>
    </div>
    <div class="header-right">
      <div class="date-display">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span><?= date('F j, Y') ?></span>
      </div>
    </div>
  </div>

  <!-- Alert Messages -->
  <?php if (isset($success_msg)): ?>
    <div class="alert alert-success">
      <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="20 6 9 17 4 12"></polyline>
      </svg>
      <span><?= htmlspecialchars($success_msg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (isset($error_msg)): ?>
    <div class="alert alert-danger">
      <svg class="alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
      </svg>
      <span><?= htmlspecialchars($error_msg) ?></span>
    </div>
  <?php endif; ?>

  <!-- Statistics Cards -->
  <div class="stats-grid">
    <div class="stat-card stat-primary">
      <div class="stat-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="1" x2="12" y2="23"></line>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Total Inventory Value</div>
        <div class="stat-value">RM <?= number_format($stats['budget_out'], 2) ?></div>
        <div class="stat-change positive">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="18 15 12 9 6 15"></polyline>
          </svg>
          <span>12.5% from last month</span>
        </div>
      </div>
    </div>

    <div class="stat-card stat-secondary">
      <div class="stat-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="3" y1="9" x2="21" y2="9"></line>
          <line x1="9" y1="21" x2="9" y2="9"></line>
        </svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Total Stock Items</div>
        <div class="stat-value"><?= $stats['approved_products'] ?></div>
        <div class="stat-info">Across all categories</div>
      </div>
    </div>

    <div class="stat-card stat-warning">
      <div class="stat-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Pending Approvals</div>
        <div class="stat-value"><?= $stats['pending_requests'] ?></div>
        <div class="stat-info">Requires your attention</div>
      </div>
    </div>

    <div class="stat-card stat-info">
      <div class="stat-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
        </svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Total Quantity</div>
        <div class="stat-value"><?= number_format($stats['total_quantity'], 0) ?></div>
        <div class="stat-info">Pieces in stock</div>
      </div>
    </div>
  </div>

  <!-- Stock by Category Section -->
  <div class="section-container">
    <div class="section-header-clean">
      <div>
        <h3 class="section-title-clean">Stock by Marble Type</h3>
        <p class="section-desc">Inventory distribution across categories</p>
      </div>
      <button class="btn-link" onclick="window.location.href='product/index.php'">
        View All
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </button>
    </div>

    <div class="category-grid">
      <?php foreach ($marbleTypes as $type): ?>
        <div class="category-card">
          <div class="category-header">
            <div class="category-code"><?= htmlspecialchars($type['code']) ?></div>
            <div class="category-badge"><?= intval($type['total_count']) ?> items</div>
          </div>
          <div class="category-name"><?= htmlspecialchars($type['name']) ?></div>
          <div class="category-stats-row">
            <div class="category-stat">
              <span class="stat-number"><?= intval($type['total_qty'] ?? 0) ?></span>
              <span class="stat-text">pieces</span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Pending Section -->
  <div class="section-container">
    <div class="section-header-clean">
      <div>
        <h3 class="section-title-clean">Pending Approvals</h3>
        <p class="section-desc">Stock requests awaiting your review</p>
      </div>
      <div class="search-box-clean">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input type="text" id="searchRequest" placeholder="Search requests..." />
      </div>
    </div>

    <div class="table-wrapper">
      <table class="data-table" id="requestsTable">
        <thead>
          <tr>
            <th>Stock ID</th>
            <th>Description</th>
            <th>Type</th>
            <th>Quantity</th>
            <th>Amount</th>
            <th>Requested By</th>
            <th>Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($pendingProducts->num_rows === 0): ?>
            <tr>
              <td colspan="8" class="empty-state-row">
                <div class="empty-state-content">
                  <svg class="empty-icon" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                  </svg>
                  <div class="empty-title">All Clear!</div>
                  <div class="empty-desc">No pending requests at the moment</div>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php while ($product = $pendingProducts->fetch_assoc()): ?>
              <tr class="data-row">
                <td>
                  <div class="cell-id"><?= htmlspecialchars($product['stock_id']) ?></div>
                </td>
                <td>
                  <div class="cell-text"><?= htmlspecialchars($product['description']) ?></div>
                </td>
                <td>
                  <span class="badge-type"><?= htmlspecialchars($product['finish_name']) ?></span>
                </td>
                <td>
                  <div class="cell-number"><?= number_format($product['quantity'], 0) ?></div>
                </td>
                <td>
                  <div class="cell-amount">RM <?= number_format($product['total_amount'], 2) ?></div>
                </td>
                <td>
                  <div class="cell-user">
                    <div class="user-avatar">
                      <?= strtoupper(substr($product['requester_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span><?= htmlspecialchars($product['requester_name'] ?? 'Unknown') ?></span>
                  </div>
                </td>
                <td>
                  <div class="cell-date"><?= date('M d, Y', strtotime($product['action_date'])) ?></div>
                </td>
                <td>
                  <div class="action-group">
                    <button 
                      onclick="window.location.href='request-detail.php?id=<?= intval($product['stock_id']) ?>'"
                      class="action-btn action-view"
                      title="View Details">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                    </button>
                    
                    <form method="POST" style="display: inline;" onsubmit="return confirmApprove(<?= intval($product['stock_id']) ?>)">
                      <input type="hidden" name="stock_id" value="<?= intval($product['stock_id']) ?>">
                      <input type="hidden" name="action" value="approve">
                      <button type="submit" class="action-btn action-approve" title="Approve">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                      </button>
                    </form>
                    
                    <button 
                      onclick="showRejectModal(<?= intval($product['stock_id']) ?>)"
                      class="action-btn action-reject" 
                      title="Reject">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="quick-access">
    <h3 class="section-title-clean">Quick Access</h3>
    <div class="quick-grid">
      <a href="product/index.php" class="quick-card">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="3" y1="9" x2="21" y2="9"></line>
            <line x1="9" y1="21" x2="9" y2="9"></line>
          </svg>
        </div>
        <div class="quick-title">Manage Products</div>
        <div class="quick-desc">View and manage inventory</div>
      </a>

      <a href="product/index.php" class="quick-card">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
        </div>
        <div class="quick-title">Add Product</div>
        <div class="quick-desc">Add new stock items</div>
      </a>

      <a href="user/index.php" class="quick-card">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <div class="quick-title">Manage Users</div>
        <div class="quick-desc">User account management</div>
      </a>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay">
  <div class="modal-container">
    <div class="modal-header">
      <h3>Reject Request</h3>
      <button class="modal-close" onclick="closeRejectModal()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    
    <div class="modal-body">
      <form method="POST" id="rejectForm">
        <input type="hidden" name="stock_id" id="rejectStockId">
        <input type="hidden" name="action" value="reject">
        
        <label for="reason" class="form-label">Reason for rejection:</label>
        <textarea 
          name="reason" 
          id="reason" 
          rows="4" 
          class="form-textarea" 
          placeholder="Please provide a detailed reason for rejection..."
          required></textarea>
        
        <div class="btn-umContainer">
          <button type="button" class="cancel" onclick="closeRejectModal()">Cancel</button>
          <button type="submit" class="btn btn-red">Submit Rejection</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Search functionality
document.getElementById('searchRequest').addEventListener('keyup', function() {
  const searchValue = this.value.toLowerCase();
  const rows = document.querySelectorAll('#requestsTable tbody .data-row');
  
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchValue) ? '' : 'none';
  });
});

// Confirm approve
function confirmApprove(stockId) {
  console.log('Confirming approve for stock_id:', stockId);
  return confirm('Are you sure you want to approve this request?');
}

// Reject Modal
function showRejectModal(stockId) {
  console.log('Showing reject modal for stock_id:', stockId);
  document.getElementById('rejectStockId').value = stockId;
  document.getElementById('rejectModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeRejectModal() {
  document.getElementById('rejectModal').classList.remove('active');
  document.body.style.overflow = 'auto';
  document.getElementById('rejectForm').reset();
}

// Close modal on outside click
document.getElementById('rejectModal').addEventListener('click', function(e) {
  if (e.target === this) closeRejectModal();
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeRejectModal();
});

// Auto-hide alerts
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(alert => {
    alert.style.opacity = '0';
    setTimeout(() => alert.remove(), 300);
  });
}, 5000);
</script>

<?php include($link."container/footer.php"); ?>