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
            s.stock_id,
            s.description,
            s.quantity,
            s.total_amount,
            mt.name AS finish_name,
            mt.code AS marble_code,
            sr.action_date,
            u.username AS requester_name
        FROM stock s
        LEFT JOIN marble_type mt ON s.type_id = mt.type_id
        LEFT JOIN stock_record sr ON s.stock_id = sr.stock_id 
            AND sr.record_id = (
                SELECT MAX(record_id) 
                FROM stock_record 
                WHERE stock_id = s.stock_id
            )
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
 * @param int $stock_id Stock ID
 * @return array|null
 */
function getPendingProductDetail($conn, $stock_id) {
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
        LEFT JOIN stock_record sr ON s.stock_id = sr.stock_id 
            AND sr.record_id = (
                SELECT MAX(record_id) 
                FROM stock_record 
                WHERE stock_id = s.stock_id
            )
        LEFT JOIN user u ON sr.user_id = u.user_id
        WHERE s.stock_id = ? AND s.status = 3
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stock_id);
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
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $conn->begin_transaction();
        
        // Debug log
        error_log("Approving stock_id: " . $stock_id . " by accountant: " . $accountant_id);
        
        // Update stock status to approved (status = 1)
        $stmt1 = $conn->prepare("UPDATE stock SET status = 1 WHERE stock_id = ? AND status = 3");
        $stmt1->bind_param("i", $stock_id);
        $result1 = $stmt1->execute();
        $affected = $stmt1->affected_rows;
        
        error_log("Update affected rows: " . $affected);
        
        if ($affected === 0) {
            throw new Exception("No rows updated - stock_id might not exist or already processed");
        }

        // Insert approval record in stock_record
        $stmt2 = $conn->prepare("INSERT INTO stock_record (stock_id, user_id, action_type, qty_change, note, action_date)
                                 VALUES (?, ?, 'approved', 0, 'Approved by accountant', NOW())");
        $stmt2->bind_param("ii", $stock_id, $accountant_id);
        $result2 = $stmt2->execute();
        
        error_log("Insert record result: " . ($result2 ? "success" : "failed"));

        $conn->commit();
        error_log("Transaction committed successfully");
        return true;

    } catch(Exception $e) {
        $conn->rollback();
        error_log("Approve failed - Error: " . $e->getMessage());
        error_log("Approve failed - Trace: " . $e->getTraceAsString());
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
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $conn->begin_transaction();
        
        error_log("Rejecting stock_id: " . $stock_id . " by accountant: " . $accountant_id);
        
        // Update stock status to rejected (status = 2)
        $sql1 = "UPDATE stock SET status = 2 WHERE stock_id = ? AND status = 3";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $stock_id);
        $result1 = $stmt1->execute();
        $affected = $stmt1->affected_rows;
        
        error_log("Update affected rows: " . $affected);
        
        if ($affected === 0) {
            throw new Exception("No rows updated - stock_id might not exist or already processed");
        }
        
        // Insert record untuk rejection action
        $note = $reason ? "Rejected: $reason" : "Rejected by accountant";
        $sql2 = "INSERT INTO stock_record (stock_id, user_id, action_type, qty_change, note, action_date) 
                 VALUES (?, ?, 'rejected', 0, ?, NOW())";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("iis", $stock_id, $accountant_id, $note);
        $result2 = $stmt2->execute();
        
        error_log("Insert record result: " . ($result2 ? "success" : "failed"));
        
        $conn->commit();
        error_log("Transaction committed successfully");
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Reject failed - Error: " . $e->getMessage());
        error_log("Reject failed - Trace: " . $e->getTraceAsString());
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
    // Total pending requests (status = 3)
    $sql1 = "SELECT COUNT(*) as total FROM stock WHERE status = 3";
    $result1 = $conn->query($sql1);
    $pending = $result1->fetch_assoc()['total'];
    
    // Total approved products (status = 1)
    $sql2 = "SELECT COUNT(*) as total FROM stock WHERE status = 1";
    $result2 = $conn->query($sql2);
    $approved = $result2->fetch_assoc()['total'];
    
    // Budget calculations (total inventory value - approved only)
    $sql3 = "SELECT SUM(total_amount) as budget FROM stock WHERE status = 1";
    $result3 = $conn->query($sql3);
    $budgetOut = $result3->fetch_assoc()['budget'] ?? 0;
    
    // Total quantity in stock (approved only)
    $sql4 = "SELECT SUM(quantity) as total_qty FROM stock WHERE status = 1";
    $result4 = $conn->query($sql4);
    $totalQty = $result4->fetch_assoc()['total_qty'] ?? 0;
    
    return [
        'pending_requests' => $pending,
        'approved_products' => $approved,
        'budget_out' => $budgetOut,
        'estimate_balance' => $budgetOut * 0.6,
        'profit_loss' => 70,
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
        $sql = "
            SELECT s.*, mt.name AS finish_name
            FROM stock s
            LEFT JOIN marble_type mt ON s.type_id = mt.type_id
            WHERE s.status = 1
            ORDER BY s.stock_id DESC
        ";
        return $conn->query($sql);
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
            COUNT(s.stock_id) as total_count,
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