<?php
require '../config.php';
require '../db.php';
header('Content-Type: application/json');

$attendance = [];
$zk = connectToDevice();

// Move function definition outside the loop
function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    
    if (count($words) >= 2) {
        // Get first letter of first and last word
        $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
    } else {
        // If only one word, get first two letters
        $initials = strtoupper(substr($name, 0, 2));
    }
    
    return $initials;
}

if ($zk) {
    // Fetch attendance
    $rawAttendance = $zk->getAttendance();

    // Format the attendance data
    foreach ($rawAttendance as $record) {
        // Check if attendance_id already exists
        $checkStmt = $conn->prepare("SELECT attendance_id FROM raw_attendance WHERE attendance_id = ?");
        $checkStmt->bind_param("i", $record["uid"]);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $checkStmt->close();

        // Only insert if attendance_id doesn't exist
        if ($result->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO raw_attendance (attendance_id, user_id, timestamp, verification_method, type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisii", $record["uid"], $record["id"], $record["timestamp"], $record["state"], $record["type"]);
            $stmt->execute();
            $stmt->close();
        }
    }
    disconnectFromDevice($zk);
}

// Fetch from database
$stmt = $conn->prepare("SELECT * FROM raw_attendance ORDER BY timestamp DESC LIMIT 8");
$stmt->execute();
$result = $stmt->get_result();
foreach ($result as $row) {

    if ($row["verification_method"] == 1) {
        $row["verification_method"] = "Fingerprint"; 
    } elseif ($row["verification_method"] == 2) {
        $row["verification_method"] = "RFID"; 
    } elseif ($row["verification_method"] == 3) {
        $row["verification_method"] = "PIN"; 
    }

    if ($row["type"] == 0) {
        $row["type"] = "Check In"; 
    }elseif ($row["type"] == 1) {
        $row["type"] = "Check Out"; 
    }elseif ($row["type"] == 2) {
        $row["type"] = "Break In"; 
    }elseif ($row["type"] == 3) {
        $row["type"] = "Break Out";
    }elseif ($row["type"] == 4) {
        $row["type"] = "Overtime In"; 
    }elseif ($row["type"] == 5) {
        $row["type"] = "Overtime Out"; 
    }

    $row["timestamp"] = date('h:i:A', strtotime($row["timestamp"]));
    

    $readUserStmt = $conn->prepare("SELECT name, company FROM users WHERE uid = ?");
    $readUserStmt->bind_param("s", $row["user_id"]);
    $readUserStmt->execute();
    $userResult = $readUserStmt->get_result();
    $user = $userResult->fetch_assoc();
    $readUserStmt->close();

    $userName = $user ? $user["name"] : "Unknown";
    $initials = $user ? getInitials($user["name"]) : "UN";

    $attendance[] = [
        "name" => $userName,
        "initials" => $initials,
        "company" => $user ? $user["company"] : "Unknown",
        "uid" => $row["attendance_id"],
        "id" => $row["user_id"],
        "time" => $row["timestamp"],
        "state" => $row["verification_method"],
        "type" => $row["type"]
    ];
}
$stmt->close();
$conn->close();
echo json_encode($attendance, JSON_PRETTY_PRINT);
