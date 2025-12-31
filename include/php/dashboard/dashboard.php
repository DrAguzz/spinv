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
            s.id,
            s.stock_id,
            s.description,
            s.quantity,
            s.total_amount,
            s.length,
            s.width,
            s.total_area,
            s.cost_per_m2,
            s.image,
            mt.name AS finish_name,
            mt.code AS marble_code,
            mt.finish_type,
            sr.action_date,
            sr.note,
            u.username AS requester_name,
            u.email AS requester_email
        FROM stock s
        LEFT JOIN marble_type mt ON s.type_id = mt.type_id
        LEFT JOIN stock_record sr ON s.id = sr.stock_id 
            AND sr.record_id = (
                SELECT MAX(record_id) 
                FROM stock_record 
                WHERE stock_id = s.id
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
 * @param int $id Stock ID (dari stock.id PRIMARY KEY)
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
            sr.record_id,
            sr.qty_change
        FROM stock s
        LEFT JOIN marble_type mt ON s.type_id = mt.type_id
        LEFT JOIN stock_record sr ON s.id = sr.stock_id 
            AND sr.record_id = (
                SELECT MAX(record_id) 
                FROM stock_record 
                WHERE stock_id = s.id
            )
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
 * @param int $id Stock ID (dari stock.id PRIMARY KEY)
 * @param int $accountant_id User ID accountant yang approve
 * @return bool
 */
function approveProduct($conn, $id, $accountant_id) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        // Validate inputs
        if (empty($id) || empty($accountant_id)) {
            error_log("Invalid parameters - id: $id, accountant_id: $accountant_id");
            return false;
        }

        $conn->begin_transaction();
        
        error_log("Approving stock id: " . $id . " by accountant: " . $accountant_id);
        
        // Update stock status to approved (status = 1)
        $stmt1 = $conn->prepare("UPDATE stock SET status = 1, updated_at = NOW() WHERE id = ? AND status = 3");
        $stmt1->bind_param("i", $id);
        $result1 = $stmt1->execute();
        $affected = $stmt1->affected_rows;
        
        error_log("Update affected rows: " . $affected);
        
        if ($affected === 0) {
            throw new Exception("No rows updated - stock might not exist or already processed");
        }

        // Insert approval record in stock_record
        // stock_record.stock_id merujuk kepada stock.id
        $stmt2 = $conn->prepare("
            INSERT INTO stock_record (stock_id, user_id, action_type, qty_change, note, action_date, status) 
            VALUES (?, ?, 'approved', 0, 'Approved by accountant', NOW(), 1)
        ");
        $stmt2->bind_param("ii", $id, $accountant_id);
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
 * @param int $id Stock ID (dari stock.id PRIMARY KEY)
 * @param int $accountant_id User ID accountant yang reject
 * @param string $reason Sebab reject
 * @return bool
 */
function rejectProduct($conn, $id, $accountant_id, $reason = '') {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        // Validate inputs
        if (empty($id) || empty($accountant_id)) {
            error_log("Invalid parameters - id: $id, accountant_id: $accountant_id");
            return false;
        }

        $conn->begin_transaction();
        
        error_log("Rejecting stock id: " . $id . " by accountant: " . $accountant_id);
        
        // Update stock status to rejected (status = 2)
        $sql1 = "UPDATE stock SET status = 2, updated_at = NOW() WHERE id = ? AND status = 3";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $id);
        $result1 = $stmt1->execute();
        $affected = $stmt1->affected_rows;
        
        error_log("Update affected rows: " . $affected);
        
        if ($affected === 0) {
            throw new Exception("No rows updated - stock might not exist or already processed");
        }
        
        // Insert record untuk rejection action
        // stock_record.stock_id merujuk kepada stock.id
        $note = $reason ? "Rejected: $reason" : "Rejected by accountant";
        $sql2 = "
            INSERT INTO stock_record (stock_id, user_id, action_type, qty_change, note, action_date, status) 
            VALUES (?, ?, 'rejected', 0, ?, NOW(), 2)
        ";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("iis", $id, $accountant_id, $note);
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
        'total_quantity' => $totalQty
    ];
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
            mt.finish_type,
            COUNT(s.id) as total_count,
            SUM(s.quantity) as total_qty,
            SUM(s.total_amount) as total_value
        FROM marble_type mt
        LEFT JOIN stock s ON mt.type_id = s.type_id AND s.status = 1
        GROUP BY mt.type_id, mt.name, mt.code, mt.finish_type
        ORDER BY mt.name ASC
    ";
    
    $result = $conn->query($sql);
    $types = [];
    
    while ($row = $result->fetch_assoc()) {
        $types[] = $row;
    }
    
    return $types;
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
            s.description,
            mt.name as marble_name
        FROM stock_record sr
        LEFT JOIN user u ON sr.user_id = u.user_id
        LEFT JOIN stock s ON sr.stock_id = s.id
        LEFT JOIN marble_type mt ON s.type_id = mt.type_id
        ORDER BY sr.action_date DESC
        LIMIT 10
    ";
    
    return $conn->query($sql);
}

/**
 * Get all approved products with filters
 * 
 * @param mysqli $conn Database connection
 * @param int|null $type_id Filter by marble type
 * @param string|null $search Search keyword
 * @return mysqli_result
 */
function getApprovedProducts($conn, $type_id = null, $search = null) {
    $sql = "
        SELECT 
            s.*,
            mt.name AS finish_name,
            mt.code AS marble_code,
            mt.finish_type
        FROM stock s
        LEFT JOIN marble_type mt ON s.type_id = mt.type_id
        WHERE s.status = 1
    ";
    
    $params = [];
    $types = "";
    
    if ($type_id) {
        $sql .= " AND s.type_id = ?";
        $params[] = $type_id;
        $types .= "i";
    }
    
    if ($search) {
        $sql .= " AND (s.stock_id LIKE ? OR s.description LIKE ? OR mt.name LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "sss";
    }
    
    $sql .= " ORDER BY s.created_at DESC";
    
    if (count($params) > 0) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    return $conn->query($sql);
}

/**
 * Get product detail by ID
 * 
 * @param mysqli $conn Database connection
 * @param int $id Stock ID (dari table stock.id)
 * @return array|null
 */
function getProductDetail($conn, $id) {
    $sql = "
        SELECT 
            s.*,
            mt.name AS finish_name,
            mt.code AS marble_code,
            mt.finish_type
        FROM stock s
        LEFT JOIN marble_type mt ON s.type_id = mt.type_id
        WHERE s.id = ?
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get stock records/history by stock ID
 * 
 * @param mysqli $conn Database connection
 * @param int $id Stock ID (dari stock.id PRIMARY KEY)
 * @return mysqli_result
 */
function getStockHistory($conn, $id) {
    $sql = "
        SELECT 
            sr.*,
            u.username
        FROM stock_record sr
        LEFT JOIN user u ON sr.user_id = u.user_id
        WHERE sr.stock_id = ?
        ORDER BY sr.action_date DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    return $stmt->get_result();
}
?>