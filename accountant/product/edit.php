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
$imgLink = "../../";
include($link."container/head.php");
include($link."container/nav.php");

require($link."php/config.php");
require_once($link."php/product/product.php");

// Dapatkan finish dari DB
$marbleTypes = getFinishList($conn);

// 1. Dapatkan ID product dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Product ID tidak diberikan!'); history.back();</script>";
    exit;
}
$id = intval($_GET['id']);

// 2. Dapatkan data product
$product = getProductById($conn, $id);
if (!$product) {
    echo "<script>alert('Product tidak ditemui!'); history.back();</script>";
    exit;
}

// 3. Handle POST untuk update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = updateProduct($conn, $id, $_POST, $_FILES);

    if ($result) {
        echo "<script>alert('Produk berjaya dikemaskini!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal kemaskini produk!');</script>";
    }
}
?>

<div class="main">
    <div class="header">
        <button class="back-icon" onclick="history.back()">&larr; Back</button>
        <div class="header-title">Edit Product</div>
    </div>

    <div class="product-card">
        <form id="productForm" method="POST" enctype="multipart/form-data">
            <div class="product-details">

                <!-- Image -->
                <div>
                    <div class="image-section" id="imageBox">
                        <?php if(!empty($product['image']) && file_exists("../../uploads/product/".$product['image'])): ?>
                            <img id="previewImage" src="../../uploads/product/<?= $product['image']; ?>" alt="Product Image">
                        <?php else: ?>
                            <img id="previewImage" src="data:image/svg+xml;utf8,
                            <svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'>
                                <rect width='100%' height='100%' fill='%23f0f0f0'/>
                                <text x='50%' y='50%' font-size='60' fill='%23999' text-anchor='middle' dominant-baseline='middle'>+</text>
                            </svg>" alt="Preview">
                        <?php endif; ?>
                    </div>

                    <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;">

                    <div class="card-buttons" style="width: 100%; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" onclick="history.back()" style="width: 50%; padding: 10px;">Close</button>
                        <button type="submit" class="btn btn-main" style="width: 50%; padding: 10px;">Save</button>
                    </div>
                </div>

                <!-- Table inputs -->
                <div class="table-section">
                    <table>
                        <thead>
                            <tr>
                                <th>Product Id</th>
                                <th>Description</th>
                                <th>Finish</th>
                                <th>Slab Length</th>
                                <th>Slab Width</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input class="input-product" type="text" name="productId" value="<?= htmlspecialchars($product['id']); ?>" required/></td>
                                <td><input class="input-product" type="text" name="description" value="<?= htmlspecialchars($product['description']); ?>" required/></td>
                                <td>
                                    <select class="input-product" name="finish" required>
                                        <option value="" disabled hidden>-- Pilih Finish --</option>
                                        <?php while($row = $marbleTypes->fetch_assoc()) : ?>
                                            <option value="<?= $row['type_id']; ?>" <?= $row['type_id']==$product['finish']?'selected':'' ?>>
                                                <?= $row['name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </td>
                                <td><input class="input-product" type="text" name="slabLength" value="<?= htmlspecialchars($product['length']); ?>" required/></td>
                                <td><input class="input-product" type="text" name="slabWidth" value="<?= htmlspecialchars($product['width']); ?>" required/></td>
                            </tr>
                        </tbody>
                    </table>

                    <table style="margin-top: 10px;">
                        <tr>
                            <th colspan="4">Existing Stock</th>
                        </tr>
                        <tr>
                            <th class="thc">Qty (Piece)</th>
                            <td><input class="input-product" type="text" name="qty" value="<?= htmlspecialchars($product['quantity']); ?>" required/></td>
                            <th class="thc">Total Area (M2)</th>
                            <td><input class="input-product" type="text" name="totalArea" value="<?= htmlspecialchars($product['total_area']); ?>" required/></td>
                        </tr>
                        <tr>
                            <th class="thc">Cost /M2 (RM)</th>
                            <td><input class="input-product" type="text" name="costPerM2" value="<?= htmlspecialchars($product['cost_per_m2']); ?>" required/></td>
                            <th class="thc">Total Amount (RM)</th>
                            <td><input class="input-product" type="text" name="totalAmount" value="<?= htmlspecialchars($product['total_amount']); ?>" required/></td>
                        </tr>
                    </table>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
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
</script>

<?php include($link."container/footer.php"); ?>
