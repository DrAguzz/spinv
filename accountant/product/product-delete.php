<?php
/**
 * Delete Product Handler
 * HARD DELETE: Delete record + delete image file
 */

session_start();
$nav = "../";
$link = "../../include/";
$imgLink = "../../";
require($link . "php/config.php");
require_once($link . "php/product/product.php");

/* ===============================
   VALIDATION
================================ */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Invalid product ID";
    header("Location: index.php");
    exit();
}

$product_id = intval($_GET['id']);

/* ===============================
   GET PRODUCT
================================ */
$product = getProductById($conn, $product_id);

if (!$product) {
    $_SESSION['error_msg'] = "Product not found";
    header("Location: index.php");
    exit();
}

/* ===============================
   CONFIRM DELETE
================================ */
if (isset($_POST['confirm_delete'])) {

    /* 1️⃣ DELETE IMAGE FILE */
    if (!empty($product['image'])) {
        $imagePath = "../../uploads/" . $product['image']; // adjust jika folder lain

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    /* 2️⃣ HARD DELETE FROM DATABASE */
    $delete = deleteProductId($conn, $product_id);

    if ($delete) {
        $_SESSION['success_msg'] = "Product deleted permanently";
    } else {
        $_SESSION['error_msg'] = "Failed to delete product";
    }

    header("Location: index.php");
    exit();
}

/* ===============================
   CANCEL DELETE
================================ */
if (isset($_POST['cancel_delete'])) {
    header("Location: index.php");
    exit();
}

/* ===============================
   PAGE VIEW
================================ */
include($link . "container/head.php");
include($link . "container/nav.php");
?>

<div class="main">
  <div class="page-header-pro">
    <div>
      <h1 class="page-title-pro">Delete Product</h1>
      <p class="page-subtitle-pro">Confirm product deletion</p>
    </div>
  </div>

  <div class="confirmation-container">
    <div class="confirmation-card">

      <div class="warning-icon-wrapper">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>

      <h2 class="confirmation-title">Are you sure?</h2>
      <p class="confirmation-message">
        This action will permanently delete the product and its image.
        <strong>This cannot be undone.</strong>
      </p>

      <div class="product-preview-card">
        <div class="preview-row">
          <span class="preview-label">Stock ID:</span>
          <span class="preview-value"><?= htmlspecialchars($product['stock_id']) ?></span>
        </div>
        <div class="preview-row">
          <span class="preview-label">Description:</span>
          <span class="preview-value"><?= htmlspecialchars($product['description']) ?></span>
        </div>
        <div class="preview-row">
          <span class="preview-label">Quantity:</span>
          <span class="preview-value"><?= number_format($product['quantity']) ?> pcs</span>
        </div>
        <div class="preview-row">
          <span class="preview-label">Total Value:</span>
          <span class="preview-value">RM <?= number_format($product['total_amount'], 2) ?></span>
        </div>
      </div>

      <div class="warning-note">
        <strong>Warning:</strong> Product data and image will be permanently removed.
      </div>

      <form method="POST" class="confirmation-form">
        <div class="button-group-confirm">
          <button type="submit" name="cancel_delete" class="btn-cancel-delete">
            Cancel
          </button>
          <button type="submit" name="confirm_delete" class="btn-confirm-delete">
            Yes, Delete Permanently
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php
include($link . "container/footer.php");
$conn->close();
?>
