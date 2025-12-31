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
  include($link."container/head.php");
  include($link."container/nav.php");
  require($link . "php/config.php");
  require_once($link . "php/product/product.php");

  // Ambil semua product dan kira mengikut kategori
  $products = getAllProducts($conn);
  
  // Kira kategori (sesuaikan dengan struktur database anda)
  $category_counts = [
    'exotic' => 0,
    'granite' => 0,
    'quarts' => 0,
];

$products_array = [];
while ($row = $products->fetch_assoc()) {
    $products_array[] = $row;

    $cat = strtolower($row['finish_name']); // guna finish_name
    if (isset($category_counts[$cat])) {
        $category_counts[$cat]++;
    }
}



foreach ($products_array as $row) {
    if (!empty($row['category'])) {
        $cat = strtolower($row['category']);
        if (isset($category_counts[$cat])) {
            $category_counts[$cat]++;
        }
    }
}

$total_products = count($products_array);
?>


<!-- Main Content -->
<div class="main">
  <!-- Professional Header -->
  <div class="dashboard-header-pro">
    <div class="header-left">
      <h1 class="header-title-pro">Product Management</h1>
      <p class="header-subtitle-pro">Manage and track your stone inventory</p>
    </div>
    <div class="header-right">
      <div class="date-display">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span><?= date('d M Y') ?></span>
      </div>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-content">
            <div class="stat-label">Total Products</div>
            <div class="stat-value"><?= $total_products ?></div>
            <div class="stat-info">All inventory items</div>
        </div>
    </div>

    <div class="stat-card stat-secondary">
        <div class="stat-content">
            <div class="stat-label">Exotic</div>
            <div class="stat-value"><?= $category_counts['exotic'] ?></div>
            <div class="stat-info">Premium category</div>
        </div>
    </div>

    <div class="stat-card stat-warning">
        <div class="stat-content">
            <div class="stat-label">Granite</div>
            <div class="stat-value"><?= $category_counts['granite'] ?></div>
            <div class="stat-info">Natural stone</div>
        </div>
    </div>

    <div class="stat-card stat-info">
        <div class="stat-content">
            <div class="stat-label">Off Cut Quarts</div>
            <div class="stat-value"><?= $category_counts['quarts'] ?></div>
            <div class="stat-info">Engineered stone</div>
        </div>
    </div>
</div>


  <!-- Product Controls Section -->
  <div class="section-container">
    <div class="section-header-clean">
      <div>
        <h2 class="section-title-clean">Product Inventory</h2>
        <p class="section-desc">Browse and manage your stone products</p>
      </div>
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <button class="btn-export" onclick="window.location.href='export-excel.php'">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          Export Excel
        </button>
        <button class="btn-add" onclick="openAddModal()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Add Product
        </button>
      </div>
    </div>

    <!-- Category Tabs -->
   <div class="category-tabs"> 
    <button class="tab-btn active" data-category="all"> 
      <span class="tab-icon">📦</span> 
      All Products 
    </button> 
    <button class="tab-btn" data-category="exotic"> 
      <span class="tab-icon">💎</span> 
        Exotic 
    </button> 
    <button class="tab-btn" data-category="granite"> 
      <span class="tab-icon">🪨</span> 
      Granite 
    </button> 
    <button class="tab-btn" data-category="quarts"> 
      <span class="tab-icon">✨</span> 
      Off Cut Quarts </button> 
    </div>


    <!-- Search Box -->
    <div class="search-box-clean" style="margin-top: 20px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <path d="m21 21-4.35-4.35"></path>
      </svg>
      <input type="text" id="searchInput" placeholder="Search products by ID, description, finish..." />
    </div>
  </div>

  <!-- Product Table -->
  <div class="section-container">
    <div class="table-wrapper">
      <table class="data-table" id="productTable">
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
          <?php if (count($products_array) === 0): ?>
            <tr>
              <td colspan="6" class="empty-state-row">
                <div class="empty-state-content">
                  <div class="empty-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                      <line x1="9" y1="9" x2="15" y2="15"></line>
                      <line x1="15" y1="9" x2="9" y2="15"></line>
                    </svg>
                  </div>
                  <div class="empty-title">No products found</div>
                  <div class="empty-desc">Start by adding your first product to the inventory</div>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($products_array as $row): ?>
<tr class="data-row product-row" data-category="<?= strtolower($row['finish_name']); ?>">
    <td class="cell-id"><?= htmlspecialchars($row['stock_id']); ?></td>
    <td class="cell-text"><?= htmlspecialchars($row['description']); ?></td>
    <td>
        <span class="badge-type"><?= htmlspecialchars($row['finish_name']); ?></span>
    </td>
    <td class="cell-number"><?= htmlspecialchars($row['length']); ?></td>
    <td class="cell-number"><?= htmlspecialchars($row['width']); ?></td>
    <td>
        <div class="action-group">
            <button onclick="window.location.href='detail.php?id=<?= $row['id']; ?>'" class="action-btn action-view" title="View Details"> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"> <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path> <circle cx="12" cy="12" r="3"></circle> </svg> </button>
            <button onclick="window.location.href='product-out.php?id=<?= $row['id']; ?>'" class="action-btn action-approve" title="Stock Out"> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"> <line x1="12" y1="5" x2="12" y2="19"></line> <polyline points="19 12 12 19 5 12"></polyline> </svg> </button>
            <button class="action-btn action-reject" onclick="if (confirm('Are you sure you want to delete this product?')) window.location.href='product-delete.php?id=<?= $row['id'] ?>';" title="Delete Product"> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"> <polyline points="3 6 5 6 21 6"></polyline> <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path> </svg> </button>
        </div>
    </td>
</tr>
<?php endforeach; ?>

          <?php endif; ?>
        </tbody>
      </table>
    </div>
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
    if (row.classList.contains('empty-state-row')) continue;
    
    const text = row.textContent.toLowerCase();
    
    if (text.includes(searchValue)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  }
});

// Category Filter


// Category Filter Tabs
const tabButtons = document.querySelectorAll('.tab-btn');
tabButtons.forEach(btn => {
  btn.addEventListener('click', function() {
    // Remove active class from all buttons
    tabButtons.forEach(b => b.classList.remove('active'));
    
    // Add active class to clicked button
    this.classList.add('active');
    
    // Get selected category
    const category = this.getAttribute('data-category');
    
    // Filter rows
    const rows = document.querySelectorAll('.product-row');
    rows.forEach(row => {
      const rowCategory = row.getAttribute('data-category');
      
      if (category === 'all' || rowCategory === category) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
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