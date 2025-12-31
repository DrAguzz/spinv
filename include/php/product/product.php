<?php

/**
 * Dapatkan senarai jenis marmar
 */
function getFinishList($conn) {
    $sql = "SELECT * FROM marble_type ORDER BY name ASC";
    return $conn->query($sql);
}

/**
 * Tambah produk baru dengan auto-calculation di backend
 */
function addProduct($conn, $data, $file) {
    try {
        // Validate dan sanitize input
        $productId   = trim($data['productId'] ?? '');
        $description = trim($data['description'] ?? '');
        $finish      = (int)($data['finish'] ?? 0);
        $slabLength  = (float)($data['slabLength'] ?? 0);
        $slabWidth   = (float)($data['slabWidth'] ?? 0);
        $qty         = (float)($data['qty'] ?? 0);
        $costPerM2   = (float)($data['costPerM2'] ?? 0);

        // Validation
        if (empty($productId)) {
            throw new Exception("Product ID tidak boleh kosong!");
        }
        if (empty($description)) {
            throw new Exception("Description tidak boleh kosong!");
        }
        if ($finish <= 0) {
            throw new Exception("Sila pilih Finish!");
        }
        if ($slabLength <= 0 || $slabWidth <= 0 || $qty <= 0 || $costPerM2 <= 0) {
            throw new Exception("Semua nilai mesti lebih besar dari 0!");
        }

        // Check duplicate Product ID
        $checkSql = "SELECT id FROM stock WHERE stock_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $productId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            throw new Exception("Product ID '{$productId}' sudah wujud! Sila guna ID lain.");
        }

        // AUTO-CALCULATE di backend (SECURITY - jangan percaya frontend sahaja)
        $totalArea   = $slabLength * $slabWidth * $qty;
        $totalAmount = $totalArea * $costPerM2;

        // Round to 2 decimal places
        $totalArea   = round($totalArea, 2);
        $totalAmount = round($totalAmount, 2);

        // Upload gambar dengan proper validation
        $imagePath = uploadProductImage($file);

        // Insert ke database
        $sql = "INSERT INTO stock 
                (stock_id, description, type_id, length, width, quantity, total_area, cost_per_m2, total_amount, image, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param(
            "ssiididdds",
            $productId,
            $description,
            $finish,
            $slabLength,
            $slabWidth,
            $qty,
            $totalArea,
            $costPerM2,
            $totalAmount,
            $imagePath
        );

        if (!$stmt->execute()) {
            throw new Exception("Gagal simpan produk: " . $stmt->error);
        }

        return true;

    } catch (Exception $e) {
        // Jika ada error, buang gambar yang dah upload (if any)
        if (isset($imagePath) && !empty($imagePath)) {
            $filePath = "../../include/uploads/product/" . $imagePath;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Log error untuk debugging
        error_log("addProduct Error: " . $e->getMessage());
        
        // Return error message
        return $e->getMessage();
    }
}

/**
 * Upload gambar produk dengan validation lengkap
 */
function uploadProductImage($file) {
    // Jika tiada gambar, return null
    if (empty($file['image']['name'])) {
        return null;
    }

    // Check upload errors
    if ($file['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error upload gambar: " . getUploadErrorMessage($file['image']['error']));
    }

    // Validation settings
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Get file info
    $fileType = mime_content_type($file['image']['tmp_name']);
    $fileExt = strtolower(pathinfo($file['image']['name'], PATHINFO_EXTENSION));
    $fileSize = $file['image']['size'];

    // Validate MIME type
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("Format fail tidak sah! Hanya JPG, PNG, WEBP dibenarkan.");
    }

    // Validate extension
    if (!in_array($fileExt, $allowedExtensions)) {
        throw new Exception("Extension fail tidak sah!");
    }

    // Validate file size
    if ($fileSize > $maxSize) {
        throw new Exception("Saiz gambar melebihi 5MB!");
    }

    // Validate image dimensions (optional - prevent oversized images)
    $imageInfo = getimagesize($file['image']['tmp_name']);
    if ($imageInfo === false) {
        throw new Exception("Fail bukan gambar yang sah!");
    }

    // Create upload folder if not exists
    $folder = "../../include/uploads/product/";
    if (!file_exists($folder)) {
        if (!mkdir($folder, 0755, true)) {
            throw new Exception("Gagal cipta folder upload!");
        }
    }

    // Generate unique filename
    $imageName = uniqid('product_', true) . '_' . time() . '.' . $fileExt;
    $targetFile = $folder . $imageName;

    // Move uploaded file
    if (!move_uploaded_file($file['image']['tmp_name'], $targetFile)) {
        throw new Exception("Gagal upload gambar!");
    }

    // Set proper permissions
    chmod($targetFile, 0644);

    return $imageName;
}

/**
 * Helper function untuk upload error messages
 */
function getUploadErrorMessage($errorCode) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => 'Saiz fail melebihi had PHP',
        UPLOAD_ERR_FORM_SIZE  => 'Saiz fail melebihi had form',
        UPLOAD_ERR_PARTIAL    => 'Fail hanya sebahagian diupload',
        UPLOAD_ERR_NO_FILE    => 'Tiada fail diupload',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak wujud',
        UPLOAD_ERR_CANT_WRITE => 'Gagal tulis fail ke disk',
        UPLOAD_ERR_EXTENSION  => 'PHP extension block upload',
    ];
    return $errors[$errorCode] ?? 'Unknown upload error';
}

/**
 * Dapatkan semua produk
 */
function getAllProducts($conn) {
    $sql = "
        SELECT 
            s.*, 
            m.name AS finish_name,
            m.code AS finish_code
        FROM stock s
        LEFT JOIN marble_type m ON s.type_id = m.type_id
        WHERE s.status = 1
        ORDER BY s.created_at DESC
    ";
    return $conn->query($sql);
}

/**
 * Dapatkan detail produk berdasarkan ID
 */
function getProductDetail($conn, $id) {
    $sql = "
        SELECT 
            s.*, 
            m.name AS finish_name,
            m.code AS finish_code,
            m.finish_type
        FROM stock s
        LEFT JOIN marble_type m ON s.type_id = m.type_id
        WHERE s.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return null;
    }

    return $result->fetch_assoc();
}

/**
 * Dapatkan produk berdasarkan ID (integer primary key)
 */
function getProductById($conn, $id) {
    $sql = "
        SELECT 
            s.*, 
            m.name AS finish_name,
            m.code AS finish_code,
            m.finish_type
        FROM stock s
        LEFT JOIN marble_type m ON s.type_id = m.type_id
        WHERE s.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Dapatkan produk berdasarkan stock_id (string)
 */
function getProductByStockId($conn, $stockId) {
    $sql = "
        SELECT 
            s.*, 
            m.name AS finish_name
        FROM stock s
        LEFT JOIN marble_type m ON s.type_id = m.type_id
        WHERE s.stock_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $stockId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Delete produk (soft delete)
 */
function deleteProduct($conn, $id) {
    try {
        // Get product data first
        $product = getProductDetail($conn, $id);
        
        if (!$product) {
            throw new Exception("Produk tidak dijumpai!");
        }

        // Soft delete - set status to 0
        $sql = "UPDATE stock SET status = 0, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal delete produk!");
        }

        // Optional: Delete physical image file
        if (!empty($product['image'])) {
            $imagePath = "../../include/uploads/product/" . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        return true;

    } catch (Exception $e) {
        error_log("deleteProduct Error: " . $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Delete produk by ID (alias function for backward compatibility)
 */
function deleteProductId($conn, $id) {
    return deleteProduct($conn, $id);
}

/**
 * Update produk dengan auto-calculation
 */
function updateProduct($conn, $data, $file, $id) {
    try {
        // Validate dan sanitize input
        $productId   = trim($data['productId'] ?? '');
        $description = trim($data['description'] ?? '');
        $finish      = (int)($data['finish'] ?? 0);
        $slabLength  = (float)($data['slabLength'] ?? 0);
        $slabWidth   = (float)($data['slabWidth'] ?? 0);
        $qty         = (float)($data['qty'] ?? 0);
        $costPerM2   = (float)($data['costPerM2'] ?? 0);

        // Validation
        if (empty($productId) || empty($description) || $finish <= 0) {
            throw new Exception("Semua field wajib diisi!");
        }
        if ($slabLength <= 0 || $slabWidth <= 0 || $qty <= 0 || $costPerM2 <= 0) {
            throw new Exception("Semua nilai mesti lebih besar dari 0!");
        }

        // Check duplicate Product ID (exclude current record)
        $checkSql = "SELECT id FROM stock WHERE stock_id = ? AND id != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $productId, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            throw new Exception("Product ID '{$productId}' sudah digunakan produk lain!");
        }

        // AUTO-CALCULATE di backend
        $totalArea   = round($slabLength * $slabWidth * $qty, 2);
        $totalAmount = round($totalArea * $costPerM2, 2);

        // Get existing product data
        $existingProduct = getProductDetail($conn, $id);
        if (!$existingProduct) {
            throw new Exception("Produk tidak dijumpai!");
        }

        // Handle image upload
        $imagePath = $existingProduct['image']; // Keep existing image by default
        
        if (!empty($file['image']['name'])) {
            // Delete old image if exists
            if (!empty($existingProduct['image'])) {
                $oldImagePath = "../../include/uploads/product/" . $existingProduct['image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            // Upload new image
            $imagePath = uploadProductImage($file);
        }

        // Update database
        $sql = "UPDATE stock SET 
                stock_id = ?, 
                description = ?, 
                type_id = ?, 
                length = ?, 
                width = ?, 
                quantity = ?, 
                total_area = ?, 
                cost_per_m2 = ?, 
                total_amount = ?, 
                image = ?,
                updated_at = NOW()
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param(
            "ssiididdsi",
            $productId,
            $description,
            $finish,
            $slabLength,
            $slabWidth,
            $qty,
            $totalArea,
            $costPerM2,
            $totalAmount,
            $imagePath,
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception("Gagal update produk: " . $stmt->error);
        }

        return true;

    } catch (Exception $e) {
        error_log("updateProduct Error: " . $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Search products
 */
function searchProducts($conn, $keyword) {
    $searchTerm = "%{$keyword}%";
    $sql = "
        SELECT 
            s.*, 
            m.name AS finish_name
        FROM stock s
        LEFT JOIN marble_type m ON s.type_id = m.type_id
        WHERE s.status = 1 
        AND (
            s.stock_id LIKE ? OR 
            s.description LIKE ? OR 
            m.name LIKE ?
        )
        ORDER BY s.created_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get low stock products (quantity below threshold)
 */
function getLowStockProducts($conn, $threshold = 5) {
    $sql = "
        SELECT 
            s.*, 
            m.name AS finish_name
        FROM stock s
        LEFT JOIN marble_type m ON s.type_id = m.type_id
        WHERE s.status = 1 AND s.quantity <= ?
        ORDER BY s.quantity ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $threshold);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get stock statistics
 */
function getStockStatistics($conn) {
    $sql = "
        SELECT 
            COUNT(*) as total_products,
            SUM(quantity) as total_quantity,
            SUM(total_area) as total_area,
            SUM(total_amount) as total_value,
            AVG(cost_per_m2) as avg_cost_per_m2
        FROM stock 
        WHERE status = 1
    ";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

/**
 * Process Product Out (Stock Cut/Sale)
 */
function processProductOut($conn, $data, $productId) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get current product data
        $product = getProductById($conn, $productId);
        if (!$product) {
            throw new Exception("Produk tidak dijumpai!");
        }
        
        // Validate and sanitize input
        $qtyCut = (float)($data['qtyCut'] ?? 0);
        $areaCut = (float)($data['areaCut'] ?? 0);
        $customer = trim($data['customer'] ?? '');
        $transactionDate = $data['transactionDate'] ?? date('Y-m-d');
        $purpose = $data['purpose'] ?? '';
        $offcutQty = (float)($data['offcutQty'] ?? 0);
        $offcutArea = (float)($data['offcutArea'] ?? 0);
        $offcutUsable = $data['offcutUsable'] ?? 'NO';
        $notes = trim($data['notes'] ?? '');
        
        // Validation
        if ($qtyCut <= 0) {
            throw new Exception("Quantity Cut mesti lebih besar dari 0!");
        }
        
        if ($qtyCut > $product['quantity']) {
            throw new Exception("Quantity Cut ({$qtyCut}) melebihi stock tersedia ({$product['quantity']})!");
        }
        
        if (empty($customer)) {
            throw new Exception("Customer/Job name wajib diisi!");
        }
        
        if (empty($purpose)) {
            throw new Exception("Purpose wajib dipilih!");
        }
        
        // Calculate new values
        $newQuantity = $product['quantity'] - $qtyCut;
        $newTotalArea = $product['total_area'] - $areaCut;
        $newTotalAmount = $newTotalArea * $product['cost_per_m2'];
        
        // Ensure no negative values
        if ($newQuantity < 0 || $newTotalArea < 0) {
            throw new Exception("Stock remaining tidak boleh negatif!");
        }
        
        // Round values
        $newQuantity = round($newQuantity, 2);
        $newTotalArea = round($newTotalArea, 2);
        $newTotalAmount = round($newTotalAmount, 2);
        
        // Update stock table
        $updateSql = "UPDATE stock SET 
                      quantity = ?, 
                      total_area = ?, 
                      total_amount = ?,
                      updated_at = NOW()
                      WHERE id = ?";
        
        $updateStmt = $conn->prepare($updateSql);
        if (!$updateStmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $updateStmt->bind_param("dddi", $newQuantity, $newTotalArea, $newTotalAmount, $productId);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Gagal update stock: " . $updateStmt->error);
        }
        
        // Prepare note for stock_record
        $recordNote = "OUT - {$purpose}\n";
        $recordNote .= "Customer/Job: {$customer}\n";
        $recordNote .= "Cut: {$qtyCut} pcs ({$areaCut} M²)\n";
        if ($offcutQty > 0) {
            $recordNote .= "Offcut: {$offcutQty} pcs ({$offcutArea} M²) - " . ($offcutUsable === 'YES' ? 'Usable' : 'Not Usable') . "\n";
        }
        if (!empty($notes)) {
            $recordNote .= "Notes: {$notes}";
        }
        
        // Insert into stock_record table
        $recordSql = "INSERT INTO stock_record 
                      (stock_id, user_id, action_type, action_date, qty_change, note, status) 
                      VALUES (?, ?, 'OUT', ?, ?, ?, 1)";
        
        $recordStmt = $conn->prepare($recordSql);
        if (!$recordStmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        $qtyChangeNegative = -$qtyCut; // Negative because it's going out
        
        $recordStmt->bind_param("iisds", 
            $productId, 
            $userId, 
            $transactionDate, 
            $qtyChangeNegative, 
            $recordNote
        );
        
        if (!$recordStmt->execute()) {
            throw new Exception("Gagal rekod transaksi: " . $recordStmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        return true;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("processProductOut Error: " . $e->getMessage());
        return $e->getMessage();
    }
}