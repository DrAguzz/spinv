<?php
// ========================================
// FILE: include/php/dashboard/dashboard.php
// Dashboard functions untuk Accountant
// ========================================

/**
 * Get pending products yang perlu approval (status = 3)
 * 
 * @param mysqli $conn Database connection
 * @return mysqli_result
 */
function getPendingProducts($conn) {
    $sql = "
        SELECT 
            s.*,
            mt.name AS finish_name,
            mt.code AS marble_code,
            u.username AS requester_name,
            sr.action_type,
            sr.action_date,
            sr.note
        FROM stock s
        LEFT JOIN marble_type mt ON s.type_id = mt.type_id
        LEFT JOIN stock_record sr ON s.stock_id = sr.stock_id AND s.status = 3
        LEFT JOIN user u ON sr.user_id = u.user_id
        WHERE s.status = 3
        ORDER BY sr.action_date DESC
    ";
    
    return $conn->query($sql);
}

/**
 * Get pending product detail by ID
 * 
 * @param mysqli $conn Database connection
 * @param int $id Stock ID
 * @return array|null
 */
function getPendingProductDetail($conn, $id) {
    $sql = "
        SELECT 
            s.*,
            mt.name AS finish_name,
            mt.code AS marble_code,
            mt.finish_type,
            u.username AS requester_name,
            u.email AS requester_email,
            sr.action_type,
            sr.action_date,
            sr.note,
            sr.record_id
        FROM stock s
        LEFT JOIN marble_type mt ON s.type_id = mt.type_id
        LEFT JOIN stock_record sr ON s.stock_id = sr.stock_id AND sr.status = 3
        LEFT JOIN user u ON sr.user_id = u.user_id
        WHERE s.id = ? AND s.status = 3
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Approve pending product
 * 
 * @param mysqli $conn Database connection
 * @param int $stock_id Stock ID
 * @param int $accountant_id User ID accountant yang approve
 * @return bool
 */
function approveProduct($conn, $stock_id, $accountant_id) {
    $conn->begin_transaction();
    
    try {
        // 1. Update stock status ke 1 (approved)
        $sql1 = "UPDATE stock SET status = 1 WHERE id = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $stock_id);
        $stmt1->execute();
        
        // 2. Update stock_record status ke 1 (approved)
        $sql2 = "UPDATE stock_record SET status = 1 WHERE stock_id = (SELECT stock_id FROM stock WHERE id = ?) AND status = 3";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $stock_id);
        $stmt2->execute();
        
        // 3. Insert record untuk approval action
        $sql3 = "INSERT INTO stock_record (stock_id, user_id, action_type, qty_change, note) 
                 VALUES ((SELECT stock_id FROM stock WHERE id = ?), ?, 'approved', 0, 'Approved by accountant')";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("ii", $stock_id, $accountant_id);
        $stmt3->execute();
        
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Reject pending product
 * 
 * @param mysqli $conn Database connection
 * @param int $stock_id Stock ID
 * @param int $accountant_id User ID accountant yang reject
 * @param string $reason Sebab reject
 * @return bool
 */
function rejectProduct($conn, $stock_id, $accountant_id, $reason = '') {
    $conn->begin_transaction();
    
    try {
        // 1. Update stock status ke 2 (rejected)
        $sql1 = "UPDATE stock SET status = 2 WHERE id = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $stock_id);
        $stmt1->execute();
        
        // 2. Update stock_record status ke 2 (rejected)
        $sql2 = "UPDATE stock_record SET status = 2 WHERE stock_id = (SELECT stock_id FROM stock WHERE id = ?) AND status = 3";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $stock_id);
        $stmt2->execute();
        
        // 3. Insert record untuk rejection action
        $note = $reason ? "Rejected: $reason" : "Rejected by accountant";
        $sql3 = "INSERT INTO stock_record (stock_id, user_id, action_type, qty_change, note) 
                 VALUES ((SELECT stock_id FROM stock WHERE id = ?), ?, 'rejected', 0, ?)";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("iis", $stock_id, $accountant_id, $note);
        $stmt3->execute();
        
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Get dashboard statistics
 * 
 * @param mysqli $conn Database connection
 * @return array
 */
function getDashboardStats($conn) {
    // Total pending requests
    $sql1 = "SELECT COUNT(*) as total FROM stock WHERE status = 3";
    $result1 = $conn->query($sql1);
    $pending = $result1->fetch_assoc()['total'];
    
    // Total approved products
    $sql2 = "SELECT COUNT(*) as total FROM stock WHERE status = 1";
    $result2 = $conn->query($sql2);
    $approved = $result2->fetch_assoc()['total'];
    
    // Budget calculations (total inventory value)
    $sql3 = "SELECT SUM(total_amount) as budget FROM stock WHERE status = 1";
    $result3 = $conn->query($sql3);
    $budgetOut = $result3->fetch_assoc()['budget'] ?? 0;
    
    // Total quantity in stock
    $sql4 = "SELECT SUM(quantity) as total_qty FROM stock WHERE status = 1";
    $result4 = $conn->query($sql4);
    $totalQty = $result4->fetch_assoc()['total_qty'] ?? 0;
    
    return [
        'pending_requests' => $pending,
        'approved_products' => $approved,
        'budget_out' => $budgetOut,
        'estimate_balance' => $budgetOut * 0.6, // Example calculation
        'profit_loss' => 70, // Example - calculate based on your logic
        'total_quantity' => $totalQty
    ];
}

/**
 * Get recent activities (last 10)
 * 
 * @param mysqli $conn Database connection
 * @return mysqli_result
 */
function getRecentActivities($conn) {
    $sql = "
        SELECT 
            sr.*,
            u.username,
            s.stock_id,
            s.description
        FROM stock_record sr
        LEFT JOIN user u ON sr.user_id = u.user_id
        LEFT JOIN stock s ON sr.stock_id = s.stock_id
        ORDER BY sr.action_date DESC
        LIMIT 10
    ";
    
    return $conn->query($sql);
}

/**
 * Get products by marble type
 * 
 * @param mysqli $conn Database connection
 * @param int $type_id Marble Type ID
 * @return mysqli_result
 */
function getProductsByType($conn, $type_id = null) {
    if ($type_id) {
        $sql = "
            SELECT s.*, mt.name AS finish_name
            FROM stock s
            LEFT JOIN marble_type mt ON s.type_id = mt.type_id
            WHERE s.type_id = ? AND s.status = 1
            ORDER BY s.stock_id DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $type_id);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        return getAllProducts($conn);
    }
}

/**
 * Get marble type counts for dashboard
 * 
 * @param mysqli $conn Database connection
 * @return array
 */
function getMarbleTypeCounts($conn) {
    $sql = "
        SELECT 
            mt.type_id,
            mt.name,
            mt.code,
            COUNT(s.id) as total_count,
            SUM(s.quantity) as total_qty
        FROM marble_type mt
        LEFT JOIN stock s ON mt.type_id = s.type_id AND s.status = 1
        GROUP BY mt.type_id, mt.name, mt.code
        ORDER BY mt.name ASC
    ";
    
    $result = $conn->query($sql);
    $types = [];
    
    while ($row = $result->fetch_assoc()) {
        $types[] = $row;
    }
    
    return $types;
}
?>