<?php
session_start();
require_once '../db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add CORS headers if needed
header('Content-Type: application/json');
// header('Access-Control-Allow-Origin: *');

try {
    // Validate input parameters
    if (!isset($_GET['user_id']) || !isset($_GET['date'])) {
        throw new Exception('Missing required parameters');
    }

    $user_id = $_GET['user_id'];
    $date = $_GET['date'];

    // Validate date format
    if (!DateTime::createFromFormat('Y-m-d', $date)) {
        throw new Exception('Invalid date format');
    }

    // Prepare and execute query
    $sql = "SELECT * FROM final_attendance WHERE user_id = ? AND date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        throw new Exception('No attendance record found');
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
