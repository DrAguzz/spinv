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

// Get all pending purchases (status = 3)
$sql = "
    SELECT 
        s.id,
        s.stock_id,
        s.description,
        s.quantity,
        s.total_amount,
        s.total_area,
        s.cost_per_m2,
        mt.name AS marble_name,
        mt.code AS marble_code,
        mt.finish_type,
        sr.action_date,
        u.username AS requester_name
    FROM stock s
    LEFT JOIN marble_type mt ON s.type_id = mt.type_id
    LEFT JOIN stock_record sr ON s.id = sr.stock_id 
        AND sr.record_id = (
            SELECT MAX(record_id) 
            FROM stock_record 
            WHERE stock_id = s.id
        )
    LEFT JOIN user u ON sr.user_id = u.user_id
    WHERE s.status = 3
    ORDER BY sr.action_date DESC
";

$result = $conn->query($sql);

// Get statistics
$stats_sql = "
    SELECT 
        COUNT(*) as total_pending,
        SUM(quantity) as total_qty,
        SUM(total_amount) as total_value
    FROM stock 
    WHERE status = 3
";
$stats = $conn->query($stats_sql)->fetch_assoc();
?>

<!-- Main Content -->
<div class="main">
  <!-- Header -->
  <div class="dashboard-header-pro">
    <div class="header-left">
      <h1 class="header-title-pro">Pending Requests</h1>
      <p class="header-subtitle-pro">View all pending purchase requests awaiting accountant approval</p>
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
        <div class="stat-label">Pending Requests</div>
        <div class="stat-value"><?= number_format($stats['total_pending'] ?? 0) ?></div>
        <div class="stat-info">Awaiting approval</div>
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
        <div class="stat-value"><?= number_format($stats['total_qty'] ?? 0) ?></div>
        <div class="stat-info">Pieces pending</div>
      </div>
    </div>

    <div class="stat-card stat-primary">
      <div class="stat-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="1" x2="12" y2="23"></line>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Total Value</div>
        <div class="stat-value">RM <?= number_format($stats['total_value'] ?? 0, 2) ?></div>
        <div class="stat-info">Pending approval</div>
      </div>
    </div>
  </div>

  <!-- Pending Requests Table -->
  <div class="section-container">
    <div class="section-header-clean">
      <div>
        <h2 class="section-title-clean">All Pending Requests</h2>
        <p class="section-desc">Read-only view of all pending purchases</p>
      </div>
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
            <th>Cost/m²</th>
            <th>Total Amount</th>
            <th>Requested By</th>
            <th>Date</th>
            <th class="text-center">Status</th>
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
                <td class="cell-amount">RM <?= number_format($row['cost_per_m2'], 2) ?></td>
                <td class="cell-amount"><strong>RM <?= number_format($row['total_amount'], 2) ?></strong></td>
                <td class="cell-user">
                  <div class="user-avatar">
                    <?= strtoupper(substr($row['requester_name'], 0, 1)) ?>
                  </div>
                  <span><?= htmlspecialchars($row['requester_name']) ?></span>
                </td>
                <td class="cell-date"><?= date('M d, Y', strtotime($row['action_date'])) ?></td>
                <td class="text-center">
                  <span class="status status-yellow">Pending</span>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="empty-state-row">
                <div class="empty-state-content">
                  <div class="empty-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="12" y1="8" x2="12" y2="12"></line>
                      <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                  </div>
                  <div class="empty-title">No Pending Requests</div>
                  <div class="empty-desc">There are currently no pending purchase requests</div>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Info Box -->
  <div class="section-container" style="margin-top: 20px;">
    <div style="display: flex; align-items: center; gap: 12px; padding: 16px; background: #fff3cd; border-left: 4px solid #f39c12; border-radius: 8px;">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f39c12" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="16" x2="12" y2="12"></line>
        <line x1="12" y1="8" x2="12.01" y2="8"></line>
      </svg>
      <div>
        <strong style="color: #856404;">Information:</strong>
        <span style="color: #856404;"> This is a read-only view. Only accountants can approve or reject purchase requests.</span>
      </div>
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
}
</style>

<?php 
  include($link."container/footer.php");
?>