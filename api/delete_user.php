<?php
session_start();
require_once '../db.php';
require_once '../config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 14) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if user_id is provided
if (!isset($_POST['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

$userId = $_POST['user_id'];

try {
    // Start transaction
    $conn->begin_transaction();
    $zk = connectToDevice();
    if ($zk){
        $zk->removeUser($userId);
        
    }

    // Delete user from all related tables
    $stmt = $conn->prepare("DELETE FROM final_attendance WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()]);
}

$conn->close();
?>