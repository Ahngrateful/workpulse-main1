<?php
require '../config.php';
require '../db.php';
header('Content-Type: application/json');

// Get raw attendance records
$stmt = $conn->prepare("SELECT user_id, timestamp, type FROM raw_attendance ORDER BY timestamp ASC");
$stmt->execute();
$result = $stmt->get_result();

// Process raw attendance records
foreach ($result as $row) {
    $date = date('Y-m-d', strtotime($row['timestamp']));
    $time = date('H:i:s', strtotime($row['timestamp']));
    $user_id = $row['user_id'];
    
    // Check if record exists
    $checkStmt = $conn->prepare("SELECT * FROM final_attendance WHERE user_id = ? AND date = ?");
    $checkStmt->bind_param("is", $user_id, $date);
    $checkStmt->execute();
    $existingRecord = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($existingRecord) {
        $needsUpdate = false;
        $updates = [];

        switch ($row['type']) {
            case 0: // check in
                if (empty($existingRecord['check_in']) || 
                    $existingRecord['check_in'] === '00:00:00' ||
                    strtotime($time) < strtotime($existingRecord['check_in'])) {
                    $updates['check_in'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 1: // check out
                if (empty($existingRecord['check_out']) || 
                    $existingRecord['check_out'] === '00:00:00' ||
                    strtotime($time) > strtotime($existingRecord['check_out'])) {
                    $updates['check_out'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 2: // break in
                if (empty($existingRecord['break_in']) || 
                    $existingRecord['break_in'] === '00:00:00' ||
                    strtotime($time) < strtotime($existingRecord['break_in'])) {
                    $updates['break_in'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 3: // break out
                if (empty($existingRecord['break_out']) || 
                    $existingRecord['break_out'] === '00:00:00' ||
                    strtotime($time) > strtotime($existingRecord['break_out'])) {
                    $updates['break_out'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 4: // overtime in
                if (empty($existingRecord['ot_in']) || 
                    $existingRecord['ot_in'] === '00:00:00' ||
                    strtotime($time) < strtotime($existingRecord['ot_in'])) {
                    $updates['ot_in'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 5: // overtime out
                if (empty($existingRecord['ot_out']) || 
                    $existingRecord['ot_out'] === '00:00:00' ||
                    strtotime($time) > strtotime($existingRecord['ot_out'])) {
                    $updates['ot_out'] = $time;
                    $needsUpdate = true;
                }
                break;
        }

        if ($needsUpdate) {
            $updateFields = [];
            $updateValues = [];
            $types = "";

            foreach ($updates as $field => $value) {
                $updateFields[] = "$field = ?";
                $updateValues[] = $value;
                $types .= "s";
            }

            $updateValues[] = $user_id;
            $updateValues[] = $date;
            $types .= "is";

            $sql = "UPDATE final_attendance SET " . implode(", ", $updateFields) . 
                   " WHERE user_id = ? AND date = ?";
            
            $updateStmt = $conn->prepare($sql);
            $updateStmt->bind_param($types, ...$updateValues);
            $updateStmt->execute();
            $updateStmt->close();
        }
    } else {
        // Insert new record
        $insertStmt = $conn->prepare("INSERT INTO final_attendance 
            (user_id, date, check_in, check_out, break_in, break_out, ot_in, ot_out, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'present')");
        
        $fields = ['check_in' => '', 'check_out' => '', 'break_in' => '', 
                  'break_out' => '', 'ot_in' => '', 'ot_out' => ''];
        $fields[$row['type'] == 0 ? 'check_in' : 
               ($row['type'] == 1 ? 'check_out' : 
               ($row['type'] == 2 ? 'break_in' : 
               ($row['type'] == 3 ? 'break_out' : 
               ($row['type'] == 4 ? 'ot_in' : 'ot_out'))))] = $time;

        $insertStmt->bind_param("isssssss", 
            $user_id, $date, 
            $fields['check_in'], $fields['check_out'],
            $fields['break_in'], $fields['break_out'],
            $fields['ot_in'], $fields['ot_out']
        );
        $insertStmt->execute();
        $insertStmt->close();
    }
}

$stmt->close();
$conn->close();