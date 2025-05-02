<?php
require '../config.php';
require '../db.php';
header('Content-Type: application/json');

$attendance = [];
$zk = connectToDevice();

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
$stmt = $conn->prepare("SELECT * FROM raw_attendance ORDER BY timestamp DESC");
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

    

    $attendance[] = [
        "uid" => $row["attendance_id"],
        "id" => $row["user_id"],
        "timestamp" => $row["timestamp"],
        "state" => $row["verification_method"],
        "type" => $row["type"]
    ];
}
$stmt->close();
$conn->close();
echo json_encode($attendance);
