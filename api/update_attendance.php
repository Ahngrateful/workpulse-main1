<?php
require_once '../db.php';

header('Content-Type: application/json');

try {
    $user_id = $_POST['user_id'];
    $date = $_POST['date'];
    $check_in = $_POST['check_in'] ?: null;
    $check_out = $_POST['check_out'] ?: null;
    $break_in = $_POST['break_in'] ?: null;
    $break_out = $_POST['break_out'] ?: null;
    $ot_in = $_POST['ot_in'] ?: null;
    $ot_out = $_POST['ot_out'] ?: null;
    $status = $_POST['status'];

    // Changed table name from 'attendance' to 'final_attendance'
    $sql = "UPDATE final_attendance SET 
            check_in = ?, 
            check_out = ?,
            break_in = ?,
            break_out = ?,
            ot_in = ?,
            ot_out = ?,
            status = ?,
            updated_at = NOW()
            WHERE user_id = ? AND date = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssss",
        $check_in,
        $check_out,
        $break_in,
        $break_out,
        $ot_in,
        $ot_out,
        $status,
        $user_id,
        $date
    );

    $success = $stmt->execute();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Attendance updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update attendance: ' . $stmt->error]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
