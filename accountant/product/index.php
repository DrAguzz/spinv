<?php 
  $nav = "../";
  $link = "../../include/";
  include($link."container/head.php");
  include($link."container/nav.php");
  require($link . "php/config.php");
  require_once($link . "php/product/product.php");

  // Ambil semua product
  $products = getAllProducts($conn);
?>

<!-- Main Content -->
<div class="main">
  <!-- Header Section -->
  <div class="header">
    <h2 class="header-title">Product Management</h2>
  </div>

  <!-- Stats Cards -->
  <div class="cards">
    <div class="card">
      <h3>Total Products</h3>
      <p><?= $products->num_rows ?></p>
    </div>
    <div class="card">
      <h3>Exotic</h3>
      <p>0</p>
    </div>
    <div class="card">
      <h3>Granite</h3>
      <p>0</p>
    </div>
    <div class="card">
      <h3>Off Cut Quarts</h3>
      <p>0</p>
    </div>
  </div>

  <!-- Filters & Search Section -->
  <div class="product-controls">
    <!-- Category Filter Tabs -->
    <div class="category-tabs">
      <button class="tab-btn active" data-category="all">
        All Products
      </button>
      <button class="tab-btn" data-category="exotic">
        Exotic
      </button>
      <button class="tab-btn" data-category="granite">
        Granite
      </button>
      <button class="tab-btn" data-category="quarts">
        Off Cut Quarts
      </button>
    </div>

    <!-- Search & Action Buttons -->
    <div class="action-controls">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search product..." />
        <button class="btn-search">Search</button>
      </div>
      
      <!-- Export Button -->
      <button class="btn-export" onclick="window.location.href='export-excel.php'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        Export Excel
      </button>
      
      <!-- Add Product Button - Opens Modal -->
      <button class="btn-add" onclick="openAddModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Add Product
      </button>
    </div>
  </div>

  <!-- Product Table -->
  <div class="table-container">
    <table id="productTable">
      <thead>
        <tr>
          <th>Product ID</th>
          <th>Description</th>
          <th>Finish</th>
          <th>Length (cm)</th>
          <th>Width (cm)</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php if ($products->num_rows === 0): ?>
          <tr>
            <td colspan="6" class="empty-state">
              <div class="empty-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="9" y1="9" x2="15" y2="15"></line>
                  <line x1="15" y1="9" x2="9" y2="15"></line>
                </svg>
              </div>
              <div class="empty-text">No products found</div>
              <div class="empty-subtext">Start by adding your first product</div>
            </td>
          </tr>
        <?php else: ?>
          <?php while ($row = $products->fetch_assoc()): ?>
            <tr class="product-row">
              <td class="product-id">
                <strong><?= htmlspecialchars($row['stock_id']); ?></strong>
              </td>
              <td class="product-desc">
                <?= htmlspecialchars($row['description']); ?>
              </td>
              <td>
                <span class="badge badge-finish">
                  <?= htmlspecialchars($row['finish_name']); ?>
                </span>
              </td>
              <td><?= htmlspecialchars($row['length']); ?></td>
              <td><?= htmlspecialchars($row['width']); ?></td>
              <td class="action-cell">
                <div class="action-buttons">
                  <!-- View Button -->
                  <button 
                    onclick="window.location.href='detail.php?id=<?= $row['id']; ?>'" 
                    class="btn-action btn-view"
                    title="View Details">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                  </button>
                  
                  <!-- Out Button -->
                  <button 
                    onclick="window.location.href='product-out.php?id=<?= $row['id']; ?>'"
                    class="btn-action btn-out"
                    title="Stock Out">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="12" y1="5" x2="12" y2="19"></line>
                      <polyline points="19 12 12 19 5 12"></polyline>
                    </svg>
                  </button>
                  
                  <!-- Delete Button -->
                  <button
                    class="btn-action btn-delete"
                    onclick="if (confirm('Are you sure you want to delete this product?')) window.location.href='product-delete.php?id=<?= $row['id'] ?>';"
                    title="Delete Product">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6"></polyline>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
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

<!-- ============================================
     MODAL: Choose Add Method
     ============================================ -->
<div id="addMethodModal" class="modal-overlay">
  <div class="modal-container">
    <div class="modal-header">
      <h3>Add New Product</h3>
      <button class="modal-close" onclick="closeAddModal()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    
    <div class="modal-body">
      <p class="modal-description">Choose how you want to add products to inventory</p>
      
      <div class="method-options">
        <!-- Manual Entry Option -->
        <div class="method-card" onclick="window.location.href='add.php'">
          <div class="method-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </div>
          <h4>Manual Entry</h4>
          <p>Add products one by one using a form</p>
          <div class="method-badge">Recommended</div>
        </div>
        
        <!-- CSV Upload Option -->
        <div class="method-card" onclick="window.location.href='upload-csv.php'">
          <div class="method-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="12" y1="18" x2="12" y2="12"></line>
              <line x1="9" y1="15" x2="15" y2="15"></line>
            </svg>
          </div>
          <h4>CSV Upload</h4>
          <p>Import multiple products from CSV file</p>
          <div class="method-badge method-badge-alt">Bulk Import</div>
        </div>
      </div>
      
      <div class="modal-footer">
        <a href="csv-template.php" class="download-template">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          Download CSV Template
        </a>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript -->
<script>
// Search Functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
  const searchValue = this.value.toLowerCase();
  const table = document.getElementById('productTable');
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

// Category Filter Tabs
const tabButtons = document.querySelectorAll('.tab-btn');
tabButtons.forEach(btn => {
  btn.addEventListener('click', function() {
    tabButtons.forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    
    const category = this.getAttribute('data-category');
    console.log('Filter by:', category);
  });
});

// Modal Functions
function openAddModal() {
  document.getElementById('addMethodModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeAddModal() {
  document.getElementById('addMethodModal').classList.remove('active');
  document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('addMethodModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeAddModal();
  }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeAddModal();
  }
});
</script>

<?php 
  include($link."container/footer.php");
?>