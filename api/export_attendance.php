<?php
session_start();
require_once '../config.php';
require_once '../db.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 14) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized access');
}

// Get date range parameters
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');

// Fetch attendance data
$stmt = $conn->prepare("
    SELECT u.name, fa.* 
    FROM final_attendance fa 
    JOIN users u ON fa.user_id = u.user_id 
    WHERE fa.date BETWEEN ? AND ?
    ORDER BY fa.date DESC, u.name ASC
");
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, [
    'Name',
    'Date',
    'Check In',
    'Check Out',
    'Break In',
    'Break Out',
    'OT In',
    'OT Out',
    'Status'
]);

// Add data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['name'],
        $row['date'],
        $row['check_in'],
        $row['check_out'],
        $row['break_in'],
        $row['break_out'],
        $row['ot_in'],
        $row['ot_out'],
        $row['status']
    ]);
}

fclose($output);
$conn->close();