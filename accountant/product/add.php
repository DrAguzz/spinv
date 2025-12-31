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
include($link."container/head.php");
include($link."container/nav.php");

require($link."php/config.php");
require_once($link."php/product/product.php");

// Dapatkan finish dari DB
$marbleTypes = getFinishList($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = addProduct($conn, $_POST, $_FILES);

    if ($result === true) {
        echo "<script>alert('Produk berjaya disimpan!'); window.location.href='index.php';</script>";
    } else {
        // $result contains error message
        $errorMsg = is_string($result) ? $result : 'Gagal simpan produk!';
        echo "<script>alert('" . addslashes($errorMsg) . "');</script>";
    }
}
?>

<div class="main">
    <div class="header">
        <button class="back-icon" onclick="history.back()">&larr; Back</button>
        <div class="header-title">Add Product</div>
    </div>

    <div class="product-card">
        <form id="productForm" method="POST" enctype="multipart/form-data">
            <div class="product-details">

                <!-- Image -->
                <div>
                    <div class="image-section" id="imageBox">
                        <img id="previewImage" src="data:image/svg+xml;utf8,
                        <svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'>
                            <rect width='100%' height='100%' fill='%23f0f0f0'/>
                            <text x='50%' y='50%' font-size='60' fill='%23999' text-anchor='middle' dominant-baseline='middle'>+</text>
                        </svg>" alt="Preview">
                    </div>

                    <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;">

                    <div class="card-buttons" style="width: 100%; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" onclick="location.reload()" style="width: 50%; padding: 10px;">Close</button>
                        <button type="submit" class="btn btn-main" style="width: 50%; padding: 10px;">Save</button>
                    </div>
                </div>

                <!-- Table inputs -->
                <div class="table-section product-out-tables">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Product Id</th>
                                <th>Description</th>
                                <th>Finish</th>
                                <th>Slab Length (M)</th>
                                <th>Slab Width (M)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input class="input-product" type="text" name="productId" id="productId" required/></td>
                                <td><input class="input-product" type="text" name="description" id="description" required/></td>
                                <td>
                                    <select class="input-product" name="finish" id="finish" required>
                                        <option value="" disabled selected hidden>-- Pilih Finish --</option>
                                        <?php while($row = $marbleTypes->fetch_assoc()) : ?>
                                            <option value="<?= $row['type_id']; ?>"><?= $row['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </td>
                                <td><input class="input-product" type="number" step="0.01" min="0" name="slabLength" id="slabLength" required/></td>
                                <td><input class="input-product" type="number" step="0.01" min="0" name="slabWidth" id="slabWidth" required/></td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="detail-table" style="margin-top: 10px;">
                        <tr class="section-header section-existing">
                            <th colspan="4">Existing Stock</th>
                        </tr>
                        <tr>
                            <th class="thc">Qty (Piece)</th>
                            <td><input class="input-product" type="number" step="0.01" min="0" name="qty" id="qty" required/></td>
                            <th class="thc">Total Area (M²)</th>
                            <td>
                                <input class="input-product" type="number" step="0.01" name="totalArea" id="totalArea" readonly 
                                       style="background-color: #f0f0f0; cursor: not-allowed;" required/>
                            </td>
                        </tr>
                        <tr>
                            <th class="thc">Cost /M² (RM)</th>
                            <td><input class="input-product" type="number" step="0.01" min="0" name="costPerM2" id="costPerM2" required/></td>
                            <th class="thc">Total Amount (RM)</th>
                            <td>
                                <input class="input-product" type="number" step="0.01" name="totalAmount" id="totalAmount" readonly 
                                       style="background-color: #f0f0f0; cursor: not-allowed;" required/>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
// Image Preview
const imageBox = document.getElementById('imageBox');
const imageInput = document.getElementById('imageInput');
const previewImage = document.getElementById('previewImage');

imageBox.addEventListener('click', () => {
    imageInput.click();
});

imageInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        previewImage.src = URL.createObjectURL(file);
    }
});

// Auto Calculate Functions
const slabLength = document.getElementById('slabLength');
const slabWidth = document.getElementById('slabWidth');
const qty = document.getElementById('qty');
const totalArea = document.getElementById('totalArea');
const costPerM2 = document.getElementById('costPerM2');
const totalAmount = document.getElementById('totalAmount');

// Function to calculate Total Area
function calculateTotalArea() {
    const length = parseFloat(slabLength.value) || 0;
    const width = parseFloat(slabWidth.value) || 0;
    const quantity = parseFloat(qty.value) || 0;
    
    const area = length * width * quantity;
    totalArea.value = area.toFixed(2);
    
    // After calculating area, recalculate total amount
    calculateTotalAmount();
}

// Function to calculate Total Amount
function calculateTotalAmount() {
    const area = parseFloat(totalArea.value) || 0;
    const cost = parseFloat(costPerM2.value) || 0;
    
    const amount = area * cost;
    totalAmount.value = amount.toFixed(2);
}

// Add event listeners for auto calculation
slabLength.addEventListener('input', calculateTotalArea);
slabWidth.addEventListener('input', calculateTotalArea);
qty.addEventListener('input', calculateTotalArea);
costPerM2.addEventListener('input', calculateTotalAmount);

// Form validation before submit
document.getElementById('productForm').addEventListener('submit', function(e) {
    const length = parseFloat(slabLength.value);
    const width = parseFloat(slabWidth.value);
    const quantity = parseFloat(qty.value);
    const cost = parseFloat(costPerM2.value);
    
    if (length <= 0 || width <= 0 || quantity <= 0 || cost <= 0) {
        e.preventDefault();
        alert('Sila pastikan semua nilai adalah lebih besar dari 0!');
        return false;
    }
    
    if (!totalArea.value || parseFloat(totalArea.value) <= 0) {
        e.preventDefault();
        alert('Total Area tidak boleh kosong atau 0!');
        return false;
    }
    
    if (!totalAmount.value || parseFloat(totalAmount.value) <= 0) {
        e.preventDefault();
        alert('Total Amount tidak boleh kosong atau 0!');
        return false;
    }
    
    return true;
});
</script>

<?php include($link."container/footer.php"); ?>