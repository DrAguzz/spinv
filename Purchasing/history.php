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

$user_id = $_SESSION['user_id'];

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build SQL based on filter
$sql = "
    SELECT 
        s.id,
        s.stock_id,
        s.description,
        s.quantity,
        s.total_amount,
        s.total_area,
        s.cost_per_m2,
        s.status,
        mt.name AS marble_name,
        mt.code AS marble_code,
        sr.action_date,
        sr.note
    FROM stock s
    LEFT JOIN marble_type mt ON s.type_id = mt.type_id
    LEFT JOIN stock_record sr ON s.id = sr.stock_id 
        AND sr.action_type = 'PURCHASE'
    WHERE sr.user_id = ?
";

$params = [$user_id];
$types = "i";

if ($filter === 'pending') {
    $sql .= " AND s.status = 3";
} elseif ($filter === 'approved') {
    $sql .= " AND s.status = 1";
} elseif ($filter === 'rejected') {
    $sql .= " AND s.status = 2";
}

$sql .= " ORDER BY sr.action_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get statistics for this user
$stats_sql = "
    SELECT 
        COUNT(CASE WHEN s.status = 3 THEN 1 END) as pending,
        COUNT(CASE WHEN s.status = 1 THEN 1 END) as approved,
        COUNT(CASE WHEN s.status = 2 THEN 1 END) as rejected,
        SUM(CASE WHEN s.status = 1 THEN s.total_amount ELSE 0 END) as approved_value
    FROM stock s
    LEFT JOIN stock_record sr ON s.id = sr.stock_id AND sr.action_type = 'PURCHASE'
    WHERE sr.user_id = ?
";
$stmt_stats = $conn->prepare($stats_sql);
$stmt_stats->bind_param("i", $user_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>

<!-- Main Content -->
<div class="main">
  <!-- Header -->
  <div class="dashboard-header-pro">
    <div class="header-left">
      <h1 class="header-title-pro">My Purchase History</h1>
      <p class="header-subtitle-pro">View all your submitted purchase requests</p>
    </div>
    <div class="header-right">
      <a href="index.php" class="btn btn-primary">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        New Purchase
      </a>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card stat-warning">
      <div class="stat-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Pending</div>
        <div class="stat-value"><?= number_format($stats['pending'] ?? 0) ?></div>
        <div class="stat-info">Awaiting approval</div>
      </div>
    </div>

    <div class="stat-card stat-primary">
      <div class="stat-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Approved</div>
        <div class="stat-value"><?= number_format($stats['approved'] ?? 0) ?></div>
        <div class="stat-info">RM <?= number_format($stats['approved_value'] ?? 0, 2) ?></div>
      </div>
    </div>

    <div class="stat-card stat-secondary">
      <div class="stat-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Rejected</div>
        <div class="stat-value"><?= number_format($stats['rejected'] ?? 0) ?></div>
        <div class="stat-info">Not approved</div>
      </div>
    </div>
  </div>

  <!-- History Table -->
  <div class="section-container">
    <div class="section-header-clean">
      <div>
        <h2 class="section-title-clean">Purchase History</h2>
        <p class="section-desc">All your purchase request submissions</p>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="category-tabs" style="margin-bottom: 20px;">
      <a href="?filter=all" class="tab-btn <?= $filter === 'all' ? 'active' : '' ?>">
        <span class="tab-icon">📋</span>
        All Requests
      </a>
      <a href="?filter=pending" class="tab-btn <?= $filter === 'pending' ? 'active' : '' ?>">
        <span class="tab-icon">⏳</span>
        Pending
      </a>
      <a href="?filter=approved" class="tab-btn <?= $filter === 'approved' ? 'active' : '' ?>">
        <span class="tab-icon">✅</span>
        Approved
      </a>
      <a href="?filter=rejected" class="tab-btn <?= $filter === 'rejected' ? 'active' : '' ?>">
        <span class="tab-icon">❌</span>
        Rejected
      </a>
    </div>

    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Stock ID</th>
            <th>Marble Type</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Total Area</th>
            <th>Total Amount</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr class="data-row">
                <td class="cell-id"><?= htmlspecialchars($row['stock_id']) ?></td>
                <td>
                  <span class="badge-type"><?= htmlspecialchars($row['marble_name']) ?></span>
                  <div style="font-size: 11px; color: #6c757d; margin-top: 4px;"><?= htmlspecialchars($row['marble_code']) ?></div>
                </td>
                <td class="cell-text" title="<?= htmlspecialchars($row['description']) ?>">
                  <?= htmlspecialchars($row['description']) ?>
                </td>
                <td class="cell-number"><?= number_format($row['quantity']) ?> pcs</td>
                <td class="cell-number"><?= number_format($row['total_area'], 2) ?> m²</td>
                <td class="cell-amount"><strong>RM <?= number_format($row['total_amount'], 2) ?></strong></td>
                <td class="cell-date"><?= date('M d, Y', strtotime($row['action_date'])) ?></td>
                <td class="text-center">
                  <?php if ($row['status'] == 1): ?>
                    <span class="status status-green">Approved</span>
                  <?php elseif ($row['status'] == 2): ?>
                    <span class="status status-red">Rejected</span>
                  <?php else: ?>
                    <span class="status status-yellow">Pending</span>
                  <?php endif; ?>
                </td>
                <td class="cell-text" title="<?= htmlspecialchars($row['note']) ?>">
                  <?= htmlspecialchars($row['note']) ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="empty-state-row">
                <div class="empty-state-content">
                  <div class="empty-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                      <polyline points="14 2 14 8 20 8"></polyline>
                      <line x1="12" y1="18" x2="12" y2="12"></line>
                      <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                  </div>
                  <div class="empty-title">No Purchase History</div>
                  <div class="empty-desc">
                    <?php if ($filter === 'all'): ?>
                      You haven't submitted any purchase requests yet
                    <?php elseif ($filter === 'pending'): ?>
                      You have no pending purchase requests
                    <?php elseif ($filter === 'approved'): ?>
                      You have no approved purchase requests
                    <?php else: ?>
                      You have no rejected purchase requests
                    <?php endif; ?>
                  </div>
                  <a href="index.php" class="btn btn-primary" style="margin-top: 16px; text-decoration: none;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                      <line x1="12" y1="5" x2="12" y2="19"></line>
                      <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Create New Purchase
                  </a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="section-container" style="margin-top: 24px;">
    <h3 class="section-title-clean" style="margin-bottom: 16px;">Quick Actions</h3>
    <div class="quick-grid">
      <a href="index.php" class="quick-card">
        <div class="quick-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
        </div>
        <div class="quick-title">New Purchase</div>
        <div class="quick-desc">Submit a new purchase request</div>
      </a>

      <a href="pending.php" class="quick-card">
        <div class="quick-icon" style="background: rgba(243, 156, 18, 0.1); color: #f39c12;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </div>
        <div class="quick-title">View Pending</div>
        <div class="quick-desc">See all pending requests</div>
      </a>

      <a href="?filter=approved" class="quick-card">
        <div class="quick-icon" style="background: rgba(39, 174, 96, 0.1); color: #27ae60;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </div>
        <div class="quick-title">Approved</div>
        <div class="quick-desc">View approved purchases</div>
      </a>
    </div>
  </div>
</div>

<style>
/* Additional responsive styles */
@media (max-width: 768px) {
  .table-wrapper {
    overflow-x: auto;
  }
  
  .data-table {
    min-width: 1000px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .category-tabs {
    flex-direction: column;
  }
  
  .tab-btn {
    width: 100%;
    justify-content: center;
  }
}

.tab-btn {
  text-decoration: none;
}
</style>

<?php 
  include($link."container/footer.php");
?>