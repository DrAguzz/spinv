<?php 
  session_start();
  $nav = "./";
  $link = "../include/";
  include($link."container/head.php");
  include($link."container/nav.php");
  require($link . "php/config.php");
  require_once($link . "php/dashboard/dashboard.php");

  // Check if user is logged in (optional - implement nanti)
  // if (!isset($_SESSION['user_id'])) {
  //     header("Location: login.php");
  //     exit();
  // }

  // Handle approve/reject actions
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $stock_id = $_POST['stock_id'] ?? 0;
      $action = $_POST['action'] ?? '';
      $accountant_id = $_SESSION['user_id'] ?? 1; // Default ke 1 untuk testing
      
      if ($action === 'approve') {
          if (approveProduct($conn, $stock_id, $accountant_id)) {
              $success_msg = "Product approved successfully!";
          } else {
              $error_msg = "Failed to approve product.";
          }
      } elseif ($action === 'reject') {
          $reason = $_POST['reason'] ?? '';
          if (rejectProduct($conn, $stock_id, $accountant_id, $reason)) {
              $success_msg = "Product rejected successfully!";
          } else {
              $error_msg = "Failed to reject product.";
          }
      }
  }

  // Get dashboard data
  $stats = getDashboardStats($conn);
  $pendingProducts = getPendingProducts($conn);
  $marbleTypes = getMarbleTypeCounts($conn);
?>

<div class="main">
  <!-- Header -->
  <div class="dashboard-header">
    <div>
      <h2 class="header-title">Dashboard</h2>
      <p class="header-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Accountant') ?>!</p>
    </div>
    <div class="dashboard-date">
      <span class="date-icon">📅</span>
      <span><?= date('l, F j, Y') ?></span>
    </div>
  </div>

  <!-- Alert Messages -->
  <?php if (isset($success_msg)): ?>
    <div class="alert alert-success">
      <span class="alert-icon"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg></span>
      <span><?= htmlspecialchars($success_msg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (isset($error_msg)): ?>
    <div class="alert alert-danger">
      <span class="alert-icon"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M330-120 120-330v-300l210-210h300l210 210v300L630-120H330Zm36-190 114-114 114 114 56-56-114-114 114-114-56-56-114 114-114-114-56 56 114 114-114 114 56 56Zm-2 110h232l164-164v-232L596-760H364L200-596v232l164 164Zm116-280Z"/></svg></span>
      <span><?= htmlspecialchars($error_msg) ?></span>
    </div>
  <?php endif; ?>

  <!-- Statistics Cards -->
  <div class="cards">
    <div class="card card-budget">
      <div class="card-icon"></div>
      <div class="card-content">
        <div class="card-label">Category Stock</div>
        <div class="card-value">RM <?= number_format($stats['budget_out'], 2) ?></div>
      </div>
    </div>
    <div class="card card-balance">
      <div class="card-icon"></div>
      <div class="card-content">
        <div class="card-label">Total Stock</div>
        <div class="card-value">RM <?= number_format($stats['estimate_balance'], 2) ?></div>
      </div>
    </div>
    <div class="card card-pending">
      <div class="card-icon">⏳</div>
      <div class="card-content">
        <div class="card-label">Pending Requests</div>
        <div class="card-value"><?= $stats['pending_requests'] ?></div>
      </div>
    </div>
  </div>

  <!-- Marble Type Summary -->
  <div class="marble-summary">
    <h3 class="section-title">Stock by Marble Type</h3>
    <div class="marble-cards">
      <?php foreach ($marbleTypes as $type): ?>
        <div class="marble-card">
          <div class="marble-info">
            <div class="marble-code"><?= htmlspecialchars($type['code']) ?></div>
            <div class="marble-name"><?= htmlspecialchars($type['name']) ?></div>
          </div>
          <div class="marble-stats">
            <div class="marble-count"><?= $type['total_count'] ?> items</div>
            <div class="marble-qty"><?= $type['total_qty'] ?? 0 ?> pcs</div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Pending Requests Section -->
  <div class="section-header">
    <div>
      <h3 class="section-title">Pending Approvals</h3>
      <p class="section-subtitle">Review and approve stock requests from Purchasing team</p>
    </div>
    <div class="search-box">
      <input type="text" id="searchRequest" placeholder="Search requests..." />
      <button class="btn-search">
        <span>🔍</span>
      </button>
    </div>
  </div>

  <!-- Requests Table -->
  <div class="table-container">
    <table id="requestsTable">
      <thead>
        <tr>
          <th>Stock ID</th>
          <th>Description</th>
          <th>Marble Type</th>
          <th>Quantity</th>
          <th>Total Amount</th>
          <th>Requested By</th>
          <th>Date</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($pendingProducts->num_rows === 0): ?>
          <tr>
            <td colspan="8" class="empty-state">
              <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="m644-448-56-58 122-94-230-178-94 72-56-58 150-116 360 280-196 152Zm115 114-58-58 73-56 66 50-81 64Zm33 258L632-236 480-118 120-398l66-50 294 228 94-73-57-56-37 29-360-280 83-65L55-811l57-57 736 736-56 56ZM487-606Z"/></svg></div>
              <div class="empty-text">No pending requests</div>
              <div class="empty-subtext">All requests have been processed</div>
            </td>
          </tr>
        <?php else: ?>
          <?php while ($product = $pendingProducts->fetch_assoc()): ?>
            <tr class="request-row">
              <td class="request-id">
                <strong><?= htmlspecialchars($product['stock_id']) ?></strong>
              </td>
              <td class="product-desc"><?= htmlspecialchars($product['description']) ?></td>
              <td>
                <span class="badge badge-finish">
                  <?= htmlspecialchars($product['finish_name']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($product['quantity']) ?></td>
              <td>RM <?= number_format($product['total_amount'], 2) ?></td>
              <td><?= htmlspecialchars($product['requester_name'] ?? 'Unknown') ?></td>
              <td><?= date('M d, Y', strtotime($product['action_date'])) ?></td>
              <td class="action-cell">
                <div class="action-buttons">
                  <!-- Detail Button -->
                  <button 
                    onclick="window.location.href='request-detail.php?id=<?= $product['id'] ?>'"
                    class="btn-action btn-view"
                    title="View Details">
                    <span>👁️</span>
                  </button>
                  
                  <!-- Approve Button -->
                  <form method="POST" style="display: inline;" onsubmit="return confirm('Approve this product?')">
                    <input type="hidden" name="stock_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn-action btn-approve" title="Approve">
                      <span>✅</span>
                    </button>
                  </form>
                  
                  <!-- Reject Button -->
                  <button 
                    onclick="showRejectModal(<?= $product['id'] ?>)"
                    class="btn-action btn-reject" 
                    title="Reject">
                    <span>❌</span>
                  </button>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Quick Actions -->
  <div class="quick-actions">
    <h3 class="section-title">Quick Actions</h3>
    <div class="action-cards">
      <div class="action-card" onclick="window.location.href='product/index.php'">
        <div class="action-card-icon">📦</div>
        <div class="action-card-title">Manage Products</div>
        <div class="action-card-desc">View and manage inventory</div>
      </div>
      <div class="action-card" onclick="window.location.href='product/add.php'">
        <div class="action-card-icon">➕</div>
        <div class="action-card-title">Add Product</div>
        <div class="action-card-desc">Add new product to inventory</div>
      </div>
      <div class="action-card" onclick="window.location.href='user/index.php'">
        <div class="action-card-icon">👥</div>
        <div class="action-card-title">Manage Users</div>
        <div class="action-card-desc">Add or edit user accounts</div>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal (Hidden by default) -->
<div id="rejectModal" class="modal">
  <div class="modal-content">
    <h3>Reject Product</h3>
    <form method="POST" id="rejectForm">
      <input type="hidden" name="stock_id" id="rejectStockId">
      <input type="hidden" name="action" value="reject">
      
      <label for="reason">Reason for rejection:</label>
      <textarea name="reason" id="reason" rows="4" class="input-um" required></textarea>
      
      <div class="btn-umContainer">
        <button type="button" class="cancel" onclick="closeRejectModal()">Cancel</button>
        <button type="submit" class="btn btn-red">Reject</button>
      </div>
    </form>
  </div>
</div>

<!-- JavaScript -->
<script>
// Search functionality
document.getElementById('searchRequest').addEventListener('keyup', function() {
  const searchValue = this.value.toLowerCase();
  const table = document.getElementById('requestsTable');
  const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
  
  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    const text = row.textContent.toLowerCase();
    
    if (text.includes(searchValue)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  }
});

// Reject Modal
function showRejectModal(stockId) {
  document.getElementById('rejectStockId').value = stockId;
  document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
  document.getElementById('rejectModal').style.display = 'none';
  document.getElementById('rejectForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('rejectModal');
  if (event.target === modal) {
    closeRejectModal();
  }
}

// Auto-hide alert after 5 seconds
setTimeout(function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    alert.style.opacity = '0';
    setTimeout(() => alert.remove(), 300);
  });
}, 5000);
</script>

<?php 
  include($link."container/footer.php");
?>