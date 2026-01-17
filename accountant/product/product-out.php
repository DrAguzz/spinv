<?php 
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit();
}
if (strtolower($_SESSION['role_name']) !== 'accountant') {
    header("Location: ../../login.php");
    exit();
}

$nav = "../";
$link = "../../include/";
$imgLink = "../../";
include($link."container/head.php");
include($link."container/nav.php");

require($link."php/config.php");
require_once($link."php/product/product.php");

// Get product ID from URL
$productId = $_GET['id'] ?? 0;
$product = getProductById($conn, $productId);

if (!$product) {
    echo "<script>alert('Produk tidak dijumpai!'); window.location.href='index.php';</script>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = processProductOut($conn, $_POST, $productId);
    
    if ($result === true) {
        echo "<script>alert('Transaksi berjaya direkod!'); window.location.href='index.php';</script>";
    } else {
        $errorMsg = is_string($result) ? $result : 'Gagal proses transaksi!';
        echo "<script>alert('" . addslashes($errorMsg) . "');</script>";
    }
}

// Calculate per piece area
$areaPerPiece = $product['length'] * $product['width'];
?>

<div class="main">
    <div class="header">
        <button class="back-icon" onclick="history.back()">&larr; Back</button>
        <div class="header-title">Product Out - Stock Cut</div>
    </div>

    <div class="product-card">
        <form id="productOutForm" method="POST">
            <div class="product-details">
                
                <!-- Product Image -->
                <div style="flex: 0 0 280px;">
                    <div class="image-section">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= $imgLink ?>uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="Product Image" 
                                 style="width: 100%; max-width: 280px; height: 280px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <?php else: ?>
                            <div style="width: 280px; height: 280px; background: #f0f0f0; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 48px;">📦</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Form Tables -->
                <div class="table-section" style="flex: 1;">
                    
                    <!-- Product Information (Read-only) -->
                    <table>
                        <tr>
                            <th colspan="4" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">📋 Product Information</th>
                        </tr>
                        <tr>
                            <th class="thc">Product ID</th>
                            <td style="font-weight: 700; font-family: 'Courier New', monospace;"><?= htmlspecialchars($product['stock_id']) ?></td>
                            <th class="thc">Finish Type</th>
                            <td><?= htmlspecialchars($product['finish_name']) ?></td>
                        </tr>
                        <tr>
                            <th class="thc">Description</th>
                            <td colspan="3"><?= htmlspecialchars($product['description']) ?></td>
                        </tr>
                        <tr>
                            <th class="thc">Slab Size</th>
                            <td><?= number_format($product['length'], 2) ?> M × <?= number_format($product['width'], 2) ?> M</td>
                            <th class="thc">Area/Piece</th>
                            <td><?= number_format($areaPerPiece, 2) ?> M²</td>
                        </tr>
                    </table>

                    <!-- Current Stock (Read-only) -->
                    <table style="margin-top: 15px;">
                        <tr>
                            <th colspan="4" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">📊 Current Stock</th>
                        </tr>
                        <tr>
                            <th class="thc">Available Qty</th>
                            <td style="font-size: 18px; font-weight: 700; color: #2c3e50;"><?= number_format($product['quantity'], 2) ?> pieces</td>
                            <th class="thc">Total Area</th>
                            <td style="font-size: 18px; font-weight: 700; color: #2c3e50;"><?= number_format($product['total_area'], 2) ?> M²</td>
                        </tr>
                        <tr>
                            <th class="thc">Cost/M²</th>
                            <td>RM <?= number_format($product['cost_per_m2'], 2) ?></td>
                            <th class="thc">Total Value</th>
                            <td>RM <?= number_format($product['total_amount'], 2) ?></td>
                        </tr>
                    </table>

                    <!-- Cut/Sale Details -->
                    <table style="margin-top: 15px;">
                        <tr>
                            <th colspan="4" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">✂️ Cut/Sale Details</th>
                        </tr>
                        <tr>
                            <th class="thc">Qty Cut (Pieces) *</th>
                            <td><input class="input-product" type="number" step="0.01" min="0.01" max="<?= $product['quantity'] ?>" name="qtyCut" id="qtyCut" required /></td>
                            <th class="thc">Area Cut (M²)</th>
                            <td>
                                <input class="input-product" type="number" step="0.01" name="areaCut" id="areaCut" readonly 
                                       style="background-color: #f0f0f0; cursor: not-allowed; font-weight: 600;" />
                            </td>
                        </tr>
                        <tr>
                            <th class="thc">Customer/Job *</th>
                            <td><input class="input-product" type="text" name="customer" id="customer" placeholder="Enter customer name" required /></td>
                            <th class="thc">Transaction Date *</th>
                            <td><input class="input-product" type="date" name="transactionDate" id="transactionDate" value="<?= date('Y-m-d') ?>" required /></td>
                        </tr>
                        <tr>
                            <th class="thc">Purpose *</th>
                            <td colspan="3">
                                <select class="input-product" name="purpose" id="purpose" required style="text-align: left; padding-left: 8px;">
                                    <option value="" disabled selected>-- Select Purpose --</option>
                                    <option value="SALE">Sale/Jualan</option>
                                    <option value="PROJECT">Project Installation</option>
                                    <option value="SAMPLE">Sample</option>
                                    <option value="DAMAGE">Damage/Rosak</option>
                                    <option value="TRANSFER">Transfer</option>
                                    <option value="OTHER">Other</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- Offcut/Baki Details -->
                    <table style="margin-top: 15px;">
                        <tr>
                            <th colspan="4" style="background: linear-gradient(135deg, #16a085 0%, #1abc9c 100%);">♻️ Offcut/Baki Details (Optional)</th>
                        </tr>
                        <tr>
                            <th class="thc">Offcut Pieces</th>
                            <td><input class="input-product" type="number" step="0.01" min="0" name="offcutQty" id="offcutQty" value="0" /></td>
                            <th class="thc">Offcut Area (M²)</th>
                            <td><input class="input-product" type="number" step="0.01" min="0" name="offcutArea" id="offcutArea" value="0" /></td>
                        </tr>
                        <tr>
                            <th class="thc">Usable?</th>
                            <td colspan="3">
                                <select class="input-product" name="offcutUsable" id="offcutUsable" style="text-align: left; padding-left: 8px;">
                                    <option value="YES">Yes - Boleh guna</option>
                                    <option value="NO">No - Cannot be used</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- Remaining Stock (Auto-calculated) -->
                    <table style="margin-top: 15px;">
                        <tr class="">
                            <th colspan="4" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">📈 Remaining Stock (After Transaction)</th>
                        </tr>
                        <tr>
                            <th class="thc" colspan="4">New Quantity</th>
                        </tr>
                        <tr>
                            
                            <td>
                                <input class="input-product" type="number" step="0.01" name="newQuantity" id="newQuantity" readonly 
                                       style="background-color: #d4edda; cursor: not-allowed; font-weight: 700; font-size: 16px; color: #155724;" />
                                <span style="font-size: 12px; color: #27ae60; margin-left: 4px;">pieces</span>
                            </td>
                            <th class="thc">New Total Area</th>
                            <td>
                                <input class="input-product" type="number" step="0.01" name="newTotalArea" id="newTotalArea" readonly 
                                       style="background-color: #d4edda; cursor: not-allowed; font-weight: 700; font-size: 16px; color: #155724;" />
                                <span style="font-size: 12px; color: #27ae60; margin-left: 4px;">M²</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="thc">Value Cut</th>
                            <td>
                                <input class="input-product" type="number" step="0.01" name="valueCut" id="valueCut" readonly 
                                       style="background-color: #fff3cd; cursor: not-allowed; font-weight: 700; color: #856404;" />
                            </td>
                            <th class="thc">Remaining Value</th>
                            <td>
                                <input class="input-product" type="number" step="0.01" name="remainingValue" id="remainingValue" readonly 
                                       style="background-color: #d4edda; cursor: not-allowed; font-weight: 700; color: #155724;" />
                            </td>
                        </tr>
                    </table>

                    <!-- Notes -->
                    <table style="margin-top: 15px;">
                        <tr class="">
                            <th colspan="4" style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);">📝 Additional Notes</th>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <textarea name="notes" id="notes" rows="3" 
                                          style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; font-family: inherit; resize: vertical;"
                                          placeholder="Enter any additional notes or remarks..."></textarea>
                            </td>
                        </tr>
                    </table>

                    <!-- Action Buttons -->
                    <div class="card-buttons" style="margin-top: 25px; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="history.back()" style="padding: 12px 28px;">
                            ✕ Cancel
                        </button>
                        <button type="submit" class="btn btn-main" style="padding: 12px 28px;">
                            ✓ Process Transaction
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Constants
const areaPerPiece = <?= $areaPerPiece ?>;
const currentQty = <?= $product['quantity'] ?>;
const currentArea = <?= $product['total_area'] ?>;
const costPerM2 = <?= $product['cost_per_m2'] ?>;
const currentValue = <?= $product['total_amount'] ?>;

// Get form elements
const qtyCutInput = document.getElementById('qtyCut');
const areaCutInput = document.getElementById('areaCut');
const offcutQtyInput = document.getElementById('offcutQty');
const offcutAreaInput = document.getElementById('offcutArea');
const newQuantityInput = document.getElementById('newQuantity');
const newTotalAreaInput = document.getElementById('newTotalArea');
const valueCutInput = document.getElementById('valueCut');
const remainingValueInput = document.getElementById('remainingValue');

// Initialize displays
newQuantityInput.value = currentQty.toFixed(2);
newTotalAreaInput.value = currentArea.toFixed(2);
remainingValueInput.value = currentValue.toFixed(2);

// Calculate Area Cut when Qty Cut changes
function calculateAreaCut() {
    const qtyCut = parseFloat(qtyCutInput.value) || 0;
    const areaCut = qtyCut * areaPerPiece;
    areaCutInput.value = areaCut.toFixed(2);
    calculateRemaining();
}

// Calculate Remaining Stock
function calculateRemaining() {
    const qtyCut = parseFloat(qtyCutInput.value) || 0;
    const areaCut = parseFloat(areaCutInput.value) || 0;
    const offcutQty = parseFloat(offcutQtyInput.value) || 0;
    const offcutArea = parseFloat(offcutAreaInput.value) || 0;
    
    // Calculate new quantity (subtract cut, add back offcut if usable)
    const newQty = currentQty - qtyCut;
    newQuantityInput.value = newQty.toFixed(2);
    
    // Calculate new area
    const newArea = currentArea - areaCut;
    newTotalAreaInput.value = newArea.toFixed(2);
    
    // Calculate value cut
    const valCut = areaCut * costPerM2;
    valueCutInput.value = valCut.toFixed(2);
    
    // Calculate remaining value
    const remValue = newArea * costPerM2;
    remainingValueInput.value = remValue.toFixed(2);
}

// Event listeners
qtyCutInput.addEventListener('input', calculateAreaCut);
offcutQtyInput.addEventListener('input', calculateRemaining);
offcutAreaInput.addEventListener('input', calculateRemaining);

// Form validation
document.getElementById('productOutForm').addEventListener('submit', function(e) {
    const qtyCut = parseFloat(qtyCutInput.value) || 0;
    const customer = document.getElementById('customer').value.trim();
    const purpose = document.getElementById('purpose').value;
    
    // Validate qty cut
    if (qtyCut <= 0) {
        e.preventDefault();
        alert('Quantity Cut mesti lebih besar dari 0!');
        return false;
    }
    
    if (qtyCut > currentQty) {
        e.preventDefault();
        alert(`Quantity Cut (${qtyCut}) tidak boleh melebihi stock tersedia (${currentQty})!`);
        return false;
    }
    
    // Validate customer
    if (customer === '') {
        e.preventDefault();
        alert('Sila masukkan Customer/Job name!');
        return false;
    }
    
    // Validate purpose
    if (purpose === '') {
        e.preventDefault();
        alert('Sila pilih Purpose!');
        return false;
    }
    
    // Validate remaining stock
    const newQty = parseFloat(newQuantityInput.value);
    if (newQty < 0) {
        e.preventDefault();
        alert('Remaining stock tidak boleh negatif!');
        return false;
    }
    
    // Confirmation
    const confirmMsg = `Confirm transaction?\n\n` +
                      `Cut: ${qtyCut} pieces (${areaCutInput.value} M²)\n` +
                      `Remaining: ${newQuantityInput.value} pieces (${newTotalAreaInput.value} M²)\n` +
                      `Customer: ${customer}\n` +
                      `Purpose: ${purpose}`;
    
    if (!confirm(confirmMsg)) {
        e.preventDefault();
        return false;
    }
    
    return true;
});
</script>

<?php include($link."container/footer.php"); ?>