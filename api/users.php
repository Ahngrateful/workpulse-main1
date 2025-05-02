<?php
// Add these at the very top of the file
error_reporting(E_ALL);
ini_set('display_errors', 0);

require '../config.php';
require '../db.php';

// Set headers
header('Content-Type: application/json');

try {
    $users = [];
    $message = '';
    $zk = connectToDevice();
    if ($zk){
        $stmt = $conn->prepare("SELECT uid, user_id, name,pin, role, card_number FROM users");
        $stmt->execute();
        $result = $stmt->get_result();
        foreach ($result as $row) {
            $zk->setUser($row['uid'], $row['user_id'], $row['name'], $row['pin'], $row['role'], $row['card_number']);
        }
        
    }


    // // Handle ZK device sync (keeping existing code)
    // // Insert data from ZK device to users table workpulse database
    // $z_users = $zk->getUser();
    // foreach ($z_users as $z_user) {
    //     // Check for existing record
    //     $checkStmt = $conn->prepare("SELECT * FROM users WHERE uid = ? OR user_id = ?");
    //     $checkStmt->bind_param("is", $z_user['uid'], $z_user['userid']);
    //     $checkStmt->execute();
    //     $result = $checkStmt->get_result();
    //     $existingUser = $result->fetch_assoc();
    //     $checkStmt->close();

    //     if ($result->num_rows === 0) {
    //         // Insert new record if no match found
    //         $insertStmt = $conn->prepare("INSERT INTO users (uid, user_id, name, role, card_number, pin) VALUES (?, ?, ?, ?, ?, ?)");
    //         $insertStmt->bind_param(
    //             "issiii",
    //             $z_user['uid'],
    //             $z_user['userid'],
    //             $z_user['name'],
    //             $z_user['role'],
    //             $z_user['cardno'],
    //             $z_user['password']
    //         );
    //         $insertStmt->execute();
    //         $insertStmt->close();
    //     } else {
    //         // Update existing record if any field has changed
    //         if (
    //             $existingUser['user_id'] !== $z_user['userid'] ||
    //             $existingUser['name'] !== $z_user['name'] ||
    //             $existingUser['role'] !== $z_user['role'] ||
    //             $existingUser['card_number'] !== $z_user['cardno'] ||
    //             $existingUser['pin'] !== $z_user['password']
    //         ) {

    //             $updateStmt = $conn->prepare("UPDATE users SET user_id = ?, name = ?, role = ?, card_number = ?, pin = ? WHERE uid = ?");
    //             $updateStmt->bind_param(
    //                 "ssiiii",
    //                 $z_user['userid'],
    //                 $z_user['name'],
    //                 $z_user['role'],
    //                 $z_user['cardno'],
    //                 $z_user['password'],
    //                 $z_user['uid']
    //             );
    //             $updateStmt->execute();
    //             $updateStmt->close();
    //         }
    //     }
    // }


    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $perPage;

    // Get search and filter parameters
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $roleFilter = isset($_GET['role']) ? $_GET['role'] : '';
    $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
    $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
    $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

    // Build the WHERE clause
    $whereClause = "WHERE 1=1";
    if ($search) {
        $whereClause .= " AND (name LIKE ? OR email LIKE ? OR username LIKE ?)";
    }
    if ($roleFilter !== '') {
        $roleFilter = (int)$roleFilter;
        $whereClause .= " AND role = ?";
    }
    if ($statusFilter) {
        $whereClause .= " AND status = ?";
    }

    // Count total records
    $countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
    $countStmt = $conn->prepare($countQuery);

    // Bind parameters for count query
    $params = [];
    $types = '';
    if ($search) {
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        $types .= 'sss';
    }
    if ($roleFilter !== '') {
        $params[] = $roleFilter;
        $types .= 'i';
    }
    if ($statusFilter) {
        $params[] = $statusFilter;
        $types .= 's';
    }

    if ($params) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
    $totalPages = ceil($totalRecords / $perPage);

    // Fetch users with pagination, search, and sorting
    $query = "SELECT user_id, name, username, email, role, status, hire_date, company 
              FROM users $whereClause 
              ORDER BY $sortBy $sortOrder 
              LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);

    // Add pagination parameters
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $row['role'] = (int)$row['role'] === 14 ? 'Admin' : 'User';
        $users[] = $row;
    }

    echo json_encode([
        'data' => $users,
        'total' => $totalRecords,
        'current_page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages
    ]);

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
