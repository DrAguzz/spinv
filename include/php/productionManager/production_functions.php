<?php
// include/php/production/production_functions.php

function getTotalStockValue($conn) {
    // Fixed: use correct table name and column
    $sql = "SELECT SUM(total_amount) as total_value FROM stock WHERE status = 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total_value'] ?? 0;
}

function getTotalStockQuantity($conn) {
    // Fixed: use 'stock' (singular) and correct column name
    $sql = "SELECT SUM(quantity) as total_quantity FROM stock WHERE status = 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total_quantity'] ?? 0;
}

function getTotalStockItems($conn) {
    // Fixed: renamed to reflect stock items instead of products
    $sql = "SELECT COUNT(*) as total FROM stock WHERE status = 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Alias untuk backward compatibility
function getTotalProducts($conn) {
    return getTotalStockItems($conn);
}

function getLowStockItems($conn, $threshold = 10) {
    // Fixed: use stock table and appropriate column
    $sql = "SELECT COUNT(*) as low_stock FROM stock WHERE quantity <= ? AND status = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $threshold); // Changed to 'd' for double
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['low_stock'] ?? 0;
}

// Alias untuk backward compatibility
function getLowStockProducts($conn, $threshold = 10) {
    return getLowStockItems($conn, $threshold);
}

function getAllStockItems($conn, $search = '') {
    // Fixed: query stock table with correct columns
    if (!empty($search)) {
        $search = "%{$search}%";
        $sql = "SELECT s.*, st.name as type_name
                FROM stock s 
                LEFT JOIN marble_type st ON s.type_id = st.type_id 
                WHERE s.status = 1 
                AND (s.stock_id LIKE ? OR s.description LIKE ?)
                ORDER BY s.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        $sql = "SELECT s.*, st.name as type_name
                FROM stock s 
                LEFT JOIN marble_type st ON s.type_id = st.type_id 
                WHERE s.status = 1 
                ORDER BY s.id DESC";
        return $conn->query($sql);
    }
}

// Alias untuk backward compatibility
function getAllProductsForProduction($conn, $search = '') {
    return getAllStockItems($conn, $search);
}

function getStockItemById($conn, $stock_id) {
    // Fixed: query stock table
    $sql = "SELECT s.*, st.name as type_name
            FROM stock s 
            LEFT JOIN marble_type st ON s.type_id = st.type_id 
            WHERE s.id = ? AND s.status = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stock_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Alias untuk backward compatibility
function getProductByIdForProduction($conn, $product_id) {
    return getStockItemById($conn, $product_id);
}

function getStockByType($conn) {
    // Fixed: adapted for stock table structure
    $sql = "SELECT st.name as type_name, 
                   COUNT(s.id) as item_count,
                   SUM(s.quantity) as total_quantity,
                   SUM(s.total_area) as total_area,
                   SUM(s.total_amount) as type_value
            FROM marble_type st
            LEFT JOIN stock s ON st.type_id = s.type_id AND s.status = 1
            GROUP BY st.type_id, st.name
            ORDER BY type_value DESC";
    return $conn->query($sql);
}

// Alias untuk backward compatibility
function getStockByCategory($conn) {
    return getStockByType($conn);
}

function getLowStockList($conn, $threshold = 10) {
    // Fixed: query stock table
    $sql = "SELECT s.*, st.name as type_name
            FROM stock s 
            LEFT JOIN marble_type st ON s.type_id = st.type_id 
            WHERE s.quantity <= ? AND s.status = 1
            ORDER BY s.quantity ASC
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $threshold); // Changed to 'd' for double
    $stmt->execute();
    return $stmt->get_result();
}

// Additional helper function for stock history
function getStockHistory($conn, $stock_id, $limit = 10) {
    $sql = "SELECT sh.*, u.username 
            FROM stock_history sh 
            LEFT JOIN users u ON sh.user_id = u.user_id 
            WHERE sh.stock_id = ? 
            ORDER BY sh.action_date DESC, sh.record_id DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $stock_id, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Additional helper functions
function getTotalStockArea($conn) {
    $sql = "SELECT SUM(total_area) as total_area FROM stock WHERE status = 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total_area'] ?? 0;
}

function getStockByStatus($conn, $status = 1) {
    $sql = "SELECT s.*, st.name as type_name
            FROM stock s 
            LEFT JOIN marble_type st ON s.type_id = st.type_id 
            WHERE s.status = ?
            ORDER BY s.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $status);
    $stmt->execute();
    return $stmt->get_result();
}

function searchStock($conn, $keyword) {
    $keyword = "%{$keyword}%";
    $sql = "SELECT s.*, st.name as type_name
            FROM stock s 
            LEFT JOIN marble_type st ON s.type_id = st.type_id 
            WHERE s.status = 1 
            AND (s.stock_id LIKE ? OR s.description LIKE ? OR st.name LIKE ?)
            ORDER BY s.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
    $stmt->execute();
    return $stmt->get_result();
}
?>