<?php
require '../config.php';
require '../db.php';
header('Content-Type: application/json');

// Attendance Type Constants and Order
// Check In - Must be first action of the day
// Check Out - Must be last action of the day
// Break In - Must occur after check in, before check out
// Break Out - Must occur after break in, before check out
// Overtime In - Must occur after check in, before check out
// Overtime Out - Must occur after OT in, before check out

// Valid Sequence Rules:
// 1. Day must start with Check In (0)
// 2. Break cycle: Break In (2) must be followed by Break Out (3)
// 3. OT cycle: OT In (4) must be followed by OT Out (5)
// 4. Day must end with Check Out (1)

// Invalid Sequences:
// - Cannot Check Out (1) before Check In (0)
// - Cannot Break Out (3) before Break In (2)
// - Cannot OT Out (5) before OT In (4)
// - Cannot start Break or OT if another cycle is ongoing
// - Cannot Check Out while Break or OT is ongoing

// Get date range parameters from GET request
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01'); // First day of current month
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');  // Last day of current month

// Get raw attendance records with date filter
$stmt = $conn->prepare("
    SELECT user_id, timestamp, type 
    FROM raw_attendance 
    WHERE DATE(timestamp) BETWEEN ? AND ? 
    ORDER BY timestamp ASC
");
$stmt->bind_param("ss", $startDate, $endDate);
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

        // Get all relevant times for comparison
        $checkInTime = !empty($existingRecord['check_in']) && $existingRecord['check_in'] !== '00:00:00'
            ? strtotime($existingRecord['check_in']) : null;
        $checkOutTime = !empty($existingRecord['check_out']) && $existingRecord['check_out'] !== '00:00:00'
            ? strtotime($existingRecord['check_out']) : null;
        $breakInTime = !empty($existingRecord['break_in']) && $existingRecord['break_in'] !== '00:00:00'
            ? strtotime($existingRecord['break_in']) : null;
        $breakOutTime = !empty($existingRecord['break_out']) && $existingRecord['break_out'] !== '00:00:00'
            ? strtotime($existingRecord['break_out']) : null;
        $otInTime = !empty($existingRecord['ot_in']) && $existingRecord['ot_in'] !== '00:00:00'
            ? strtotime($existingRecord['ot_in']) : null;
        $otOutTime = !empty($existingRecord['ot_out']) && $existingRecord['ot_out'] !== '00:00:00'
            ? strtotime($existingRecord['ot_out']) : null;

        $currentTime = strtotime($time);

        // Prevent duplicate timestamps
        $existingTimes = array_filter([
            $checkInTime,
            $checkOutTime,
            $breakInTime,
            $breakOutTime,
            $otInTime,
            $otOutTime
        ]);
        if (in_array($currentTime, $existingTimes)) {
            continue; // Skip this record as it has the same timestamp as another record
        }

        switch ($row['type']) {
            case 0: // check in
                if (
                    empty($existingRecord['check_in']) ||
                    $existingRecord['check_in'] === '00:00:00' ||
                    $currentTime < $checkInTime
                ) {
                    $updates['check_in'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 1: // check out
                // Only allow check out if:
                // 1. Check-in exists
                // 2. New time is after check-in time
                // 3. Break is not ongoing (break_in exists but break_out doesn't)
                // 4. OT is not ongoing (ot_in exists but ot_out doesn't)
                // 5. Either no previous check-out or new time is later than existing check-out
                if (
                    $checkInTime &&
                    $currentTime > $checkInTime &&
                    !($breakInTime && !$breakOutTime) && // Not during break
                    !($otInTime && !$otOutTime) && // Not during OT
                    (!$checkOutTime || $currentTime > $checkOutTime)
                ) {
                    $updates['check_out'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 2: // break in
                // Only allow break in if:
                // 1. Check-in exists
                // 2. New time is after check-in time
                // 3. Not during OT period
                // 4. Previous break is completed (if any)
                // 5. Check-out hasn't occurred yet
                if (
                    $checkInTime &&
                    $currentTime > $checkInTime &&
                    !($otInTime && !$otOutTime) && // Not during OT
                    (!$breakInTime || ($breakOutTime && $currentTime > $breakOutTime)) && // Previous break completed
                    !$checkOutTime
                ) { // Before check-out
                    $updates['break_in'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 3: // break out
                // Only allow break out if:
                // 1. Break-in exists
                // 2. New time is after check-in and break-in times
                // 3. Not during OT period
                // 4. Check-out hasn't occurred yet
                if (
                    $breakInTime &&
                    $currentTime > $checkInTime &&
                    $currentTime > $breakInTime &&
                    !($otInTime && !$otOutTime) && // Not during OT
                    !$checkOutTime && // Before check-out
                    (!$breakOutTime || $currentTime > $breakOutTime)
                ) {
                    $updates['break_out'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 4: // overtime in
                // Only allow OT in if:
                // 1. Check-in exists
                // 2. New time is after check-in time
                // 3. Not during break period
                // 4. Previous OT is completed (if any)
                // 5. Check-out hasn't occurred yet
                if (
                    $checkInTime &&
                    $currentTime > $checkInTime &&
                    !($breakInTime && !$breakOutTime) && // Not during break
                    (!$otInTime || ($otOutTime && $currentTime > $otOutTime)) && // Previous OT completed
                    !$checkOutTime
                ) { // Before check-out
                    $updates['ot_in'] = $time;
                    $needsUpdate = true;
                }
                break;

            case 5: // overtime out
                // Only allow OT out if:
                // 1. OT-in exists
                // 2. New time is after check-in and OT-in times
                // 3. Not during break period
                // 4. Check-out hasn't occurred yet
                if (
                    $otInTime &&
                    $currentTime > $checkInTime &&
                    $currentTime > $otInTime &&
                    !($breakInTime && !$breakOutTime) && // Not during break
                    !$checkOutTime && // Before check-out
                    (!$otOutTime || $currentTime > $otOutTime)
                ) {
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
        // For new records, only allow check-in
        if ($row['type'] == 0) {
            $insertStmt = $conn->prepare("INSERT INTO final_attendance 
                (user_id, date, check_in, check_out, break_in, break_out, ot_in, ot_out, status) 
                VALUES (?, ?, ?, '', '', '', '', '', 'present')");

            $insertStmt->bind_param(
                "iss",
                $user_id,
                $date,
                $time
            );
            $insertStmt->execute();
            $insertStmt->close();
        }
    }
}
$stmt->close();

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 10;
$offset = ($page - 1) * $perPage;

// Get date range parameters
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');

// First, get total count of records
$countStmt = $conn->prepare("
    SELECT COUNT(DISTINCT fa.user_id, fa.date) as total
    FROM final_attendance fa 
    WHERE fa.date BETWEEN ? AND ?
");
$countStmt->bind_param("ss", $startDate, $endDate);
$countStmt->execute();
$totalResult = $countStmt->get_result()->fetch_assoc();
$totalRecords = $totalResult['total'];
$countStmt->close();

// Get paginated attendance records
$attStmt = $conn->prepare("
    SELECT fa.user_id, fa.date, fa.check_in, fa.check_out, 
           fa.break_in, fa.break_out, fa.ot_in, fa.ot_out, 
           fa.status, u.name 
    FROM final_attendance fa 
    JOIN users u ON fa.user_id = u.uid 
    WHERE fa.date BETWEEN ? AND ?
    ORDER BY fa.date DESC, u.name ASC
    LIMIT ? OFFSET ?
");
$attStmt->bind_param("ssii", $startDate, $endDate, $perPage, $offset);
$attStmt->execute();
$attResult = $attStmt->get_result();
$attendance = [];

while ($record = $attResult->fetch_assoc()) {
    $attendance[] = [
        "name" => $record["name"],
        "user_id" => $record["user_id"],
        "date" => $record["date"],
        "check_in" => $record["check_in"],
        "check_out" => $record["check_out"],
        "break_in" => $record["break_in"],
        "break_out" => $record["break_out"],
        "ot_in" => $record["ot_in"],
        "ot_out" => $record["ot_out"],
        "status" => $record["status"]
    ];
}

$attStmt->close();
$conn->close();

// Return paginated response
echo json_encode([
    'data' => $attendance,
    'total' => $totalRecords,
    'page' => $page,
    'perPage' => $perPage,
    'totalPages' => ceil($totalRecords / $perPage)
], JSON_PRETTY_PRINT);
