<?php
require_once '../db.php';
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 14) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

$userId = $_GET['user_id'];

try {
    $stmt = $conn->prepare("SELECT user_id, name, username, email, contact, role, status, 
                           company, hire_date, shift_start, shift_end, profile_picture 
                           FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Convert profile picture path to URL if exists
        if ($row['profile_picture']) {
            $row['profile_picture'] = '../' . $row['profile_picture'];
        }
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>
