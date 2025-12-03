<?php

function getFinishList($conn) {
    $sql = "SELECT * FROM marble_type";
    return $conn->query($sql);
}

function addProduct($conn, $data, $file){
    $productId   = $data['productId'];
    $description = $data['description'];
    $finish      = $data['finish'];
    $slabLength  = $data['slabLength'];
    $slabWidth   = $data['slabWidth'];
    $qty         = $data['qty'];
    $totalArea   = $data['totalArea'];
    $costPerM2   = $data['costPerM2'];
    $totalAmount = $data['totalAmount'];

    // upload gambar
    $imagePath = null;

    if (!empty($file['image']['name'])) {

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $fileType = mime_content_type($file['image']['tmp_name']);
        $fileExt = strtolower(pathinfo($file['image']['name'], PATHINFO_EXTENSION));
        $fileSize = $file['image']['size'];

        if (!in_array($fileType, $allowedTypes) || !in_array($fileExt, ['jpg','jpeg','png'])) {
            throw new Exception("Format fail tidak dibenarkan! JPG/PNG sahaja.");
        }

        if ($fileSize > $maxSize) {
            throw new Exception("Saiz fail melebihi 2MB.");
        }

        $folder = "../../include/uploads/product/";
        if (!file_exists($folder)) mkdir($folder, 0777, true);

        $imageName = time() . "_" . preg_replace("/[^a-zA-Z0-9_\-\.]/", "", basename($file['image']['name']));
        $targetFile = $folder . $imageName;

        if (move_uploaded_file($file['image']['tmp_name'], $targetFile)) {
            $imagePath = $imageName;
        }
    }

    $sql = "INSERT INTO stock
            VALUES ( '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

    $stmt = $conn->prepare($sql);
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

    return $stmt->execute();
}

function getAllProducts($conn) {
    $sql = "
        SELECT p.*, f.name AS finish_name 
        FROM stock p
        LEFT JOIN marble_type f ON p.type_id = f.type_id
        ORDER BY p.stock_id DESC
    ";

    return $conn->query($sql);
}


/**
 * Dapatkan detail product berdasarkan ID
 * 
 * @param mysqli $conn  Koneksi database
 * @param int $id       ID product
 * @return array|null   Array associative product atau null jika tiada
 */
function getProductDetail($conn, $id) {
    $sql = "
        SELECT p.*, f.name AS finish_name 
        FROM stock p
        LEFT JOIN marble_type f ON p.type_id = f.type_id
        WHERE p.id = ?
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


function getProductById($conn, $id) {
    $sql = "SELECT * FROM stock WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateProduct($conn, $data, $file, $id) {
    // Gunakan null coalescing untuk elak warning
    $productId = $data['productId'] ?? null;
    $description = $data['description'] ?? null;
    $finish = $data['finish'] ?? null;
    $slabLength = $data['slabLength'] ?? null;
    $slabWidth = $data['slabWidth'] ?? null;
    $qty = $data['qty'] ?? null;
    $totalArea = $data['totalArea'] ?? null;
    $costPerM2 = $data['costPerM2'] ?? null;
    $totalAmount = $data['totalAmount'] ?? null;

    // Upload gambar jika ada
    $imagePath = null;
    if (!empty($file['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $fileType = mime_content_type($file['image']['tmp_name']);
        $fileExt = strtolower(pathinfo($file['image']['name'], PATHINFO_EXTENSION));
        $fileSize = $file['image']['size'];

        if (in_array($fileType, $allowedTypes) && in_array($fileExt, ['jpg','jpeg','png']) && $fileSize <= $maxSize) {
            $folder = "../../include/uploads/product/";
            if (!file_exists($folder)) mkdir($folder, 0777, true);
            $imageName = time() . "_" . preg_replace("/[^a-zA-Z0-9_\-\.]/", "", basename($file["image"]["name"]));
            $targetFile = $folder . $imageName;
            if (move_uploaded_file($file["image"]["tmp_name"], $targetFile)) {
                $imagePath = $imageName;
            }
        }
    }

    // Jika ada gambar baru, update field image juga
    if ($imagePath) {
        $sql = "UPDATE stock SET stock_id=?, description=?, type_id=?, length=?, width=?, quantity=?, total_area=?, cost_per_m2=?, total_amount=?, image=? WHERE stock_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssiididddss",
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
    } else {
        $sql = "UPDATE stock SET stock_id=?, description=?, type_id=?, length=?, width=?, quantity=?, total_area=?, cost_per_m2=?, total_amount=? WHERE stock_id=?";
        $stmt = $conn->prepare($sql);
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
            $id
        );
    }

    return $stmt->execute();
}

function outProduct(){
    
}