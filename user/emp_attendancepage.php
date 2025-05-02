<?php 
session_start();
require_once '../db.php';

if (!isset($_SESSION['employee_ID'])) {
    echo "Unauthorized. Employee not logged in.";
    exit;
}

$employee_id = $_SESSION['employee_ID'];

// Step 1: Get uid from users table using user_id from session
$uid_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$uid_stmt->bind_param("i", $employee_id);
$uid_stmt->execute();
$uid_result = $uid_stmt->get_result();
$user_row = $uid_result->fetch_assoc();
$uid_stmt->close();

if (!$user_row) {
    echo "<p style='color:red; padding: 20px;'>Employee not found.</p>";
    exit;
}

$uid = $user_row['uid']; // use this to fetch attendance
$employee = $user_row;

// Apply profile picture fallback
if (empty($employee['profile_picture'])) {
    $employee['profile_picture'] = '../images/default-profile.png';
}

// Step 2: Fetch attendance records using UID
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $records_per_page;

// Get date range parameters if they exist
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Prepare the WHERE clause based on date filters
$date_filter = "final_attendance.user_id = ?";
$params = array($uid);
$param_types = "i";

if (!empty($start_date)) {
    $date_filter .= " AND final_attendance.date >= ?";
    $params[] = $start_date;
    $param_types .= "s";
}

if (!empty($end_date)) {
    $date_filter .= " AND final_attendance.date <= ?";
    $params[] = $end_date;
    $param_types .= "s";
}

// Count total records for pagination with date filter
$count_sql = "SELECT COUNT(*) as total FROM final_attendance WHERE " . $date_filter;
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($param_types, ...$params);
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch paginated attendance records with date filter
$query = "
    SELECT final_attendance.*, users.*
    FROM final_attendance
    JOIN users ON users.uid = final_attendance.user_id
    WHERE " . $date_filter . "
    ORDER BY final_attendance.date DESC
    LIMIT ?, ?
";

$att_stmt = $conn->prepare($query);
// Add pagination parameters
$params[] = $offset;
$params[] = $records_per_page;
$param_types .= "ii";
$att_stmt->bind_param($param_types, ...$params);
$att_stmt->execute();
$att_result = $att_stmt->get_result();

// Convert result to array
$attendance_rows = [];
while ($row = $att_result->fetch_assoc()) {
    $attendance_rows[] = $row;
}

// Helper functions
function formatHours($minutes) {
    if (!$minutes || $minutes <= 0) return '-';
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    $formatted = '';
    if ($hours > 0) $formatted .= $hours . ' hour' . ($hours > 1 ? 's' : '');
    if ($mins > 0) $formatted .= ($formatted ? ' ' : '') . $mins . ' minute' . ($mins > 1 ? 's' : '');
    return $formatted;
}

function calculateRenderedMinutes($row) {
    $check_in = isset($row['check_in']) ? strtotime($row['check_in']) : null;
    $check_out = isset($row['check_out']) ? strtotime($row['check_out']) : null;
    if (!$check_in || !$check_out || $check_out <= $check_in) return 0;
    $total_minutes = ($check_out - $check_in) / 60 - 60; // Assuming 1-hour break
    return max(0, $total_minutes);
}

function calculateOTMinutes($ot_in_str, $ot_out_str) {
    if (!$ot_in_str || !$ot_out_str) return 0;
    $in = strtotime($ot_in_str);
    $out = strtotime($ot_out_str);
    if ($in === false || $out === false || $out <= $in) return 0;
    return (int)(($out - $in) / 60);
}

// Totals
$total_minutes_worked = 0;
$total_ot_minutes = 0;
$total_check_in_seconds = 0;
$total_days = count($attendance_rows);
$late_count = 0;

foreach ($attendance_rows as $row) {
    $total_minutes_worked += calculateRenderedMinutes($row);
    $total_ot_minutes += calculateOTMinutes($row['ot_in'] ?? '', $row['ot_out'] ?? '');
    if (!empty($row['check_in'])) {
        $total_check_in_seconds += strtotime($row['check_in']);
    }
    if (!empty($employee['shift_start_time']) && !empty($row['check_in'])) {
        $scheduled_time = strtotime($employee['shift_start_time']);
        $actual_time = strtotime($row['check_in']);
        if ($actual_time > $scheduled_time) {
            $late_count++;
        }
    }
}

$total_hours_worked = number_format($total_minutes_worked / 60, 2);
$total_extra_hours = number_format($total_ot_minutes / 60, 2);
$average_check_in = '-';

if ($total_days > 0 && $total_check_in_seconds > 0) {
    $average_seconds = $total_check_in_seconds / $total_days;
    $average_check_in = date('h:i A', (int)$average_seconds);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCGI | ATTENDANCE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" href="pics/rcgiph_logo.jpg" type="image/x-icon">
    <style>
        body {
      font-family: 'Inter', sans-serif;
      font-weight: 600;
      font-size: 15px;
      background: #F3F4F6;
      margin: 0;
    }

    .navbar {
      background: #E4C28B;;
      padding: 15px 20px;
      border-bottom: 1px solid #E4C28B;;
      box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1);
    }

    .navbar .left {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 18px;
      font-weight: bold;
    }
    
    .navbar .username {
      font-weight: 600;
      margin-right: 10px;
    }

    .layout {
      display: flex;
      height: calc(100vh - 60px); /* full height minus navbar */
    }

    .sidebar {
      background-color: #f8f9fa;
      width: 250px;
      border-right: 1px solid #ddd;
      padding-top: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .list-group-item {
      background: none;
      border: none;
      padding: 12px 20px;
      font-weight: bold;
      display: flex;
      align-items: center;
      color: #000000;
      transition: background 0.3s ease-in-out;
    }

    .list-group-item:hover {
      background: #e0e0e0;
    }

    .list-group-item.active {
      background-color: #E4C28B;;
      color: #000;
      border-radius: 5px;
    }

    .sidebar-icon {
      width: 20px;
      height: 20px;
      margin-right: 10px;
    }

    .main-content {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }
        

        .col-md-4 h4 {
            margin-left: 30px;
            font-family: 'Inika', serif;
            font-weight: 400;
            font-size: 13px;
            line-height: 17px;
            color: #000000;
        }

        .col-md-4 p {
            margin-top: -10px;
            margin-left: 40px;
            font-family: 'Inika', serif;
            font-weight: 400;
            font-size: 36px;
            color: #000000;
        }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-bar .btn {
            background-color: #374151;
            color: white;
            font-weight: bold;
        }

        .search-bar .btn i {
            margin-right: 5px;
        }

        .section-title {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .card {
      border: none;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

        footer {
      text-align: center;
      padding: 20px;
      font-size: 14px;
      color: #6c757d;
      background: #f8f9fa;
    }

    .statusBtn {
  font-size: 0.85rem;
  font-weight: bold;
  padding: 4px 10px;
  border-radius: 20px;
}

.statusBtn.btn-success {
  background-color: #BCCFB9;
  border: none;
}

.statusBtn.btn-warning {
  background-color: #C29B99;
  border: none;
}
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar d-flex justify-content-between align-items-center">
<div class="left">
    <i class="fas fa-users"></i> 
    <span>MY ATTENDANCE</span>
</div>
    <div class="d-flex align-items-center"><span class="username"><?php echo htmlspecialchars($employee['name']); ?></span>
      
      <img src="<?= htmlspecialchars($employee['profile_picture']) ?>" alt="employee photo" style="width: 50px; height:45px;  border: 2px solid ; border-radius: 50%; ">
    </div>
  </nav>

<div class="container-fluid">
    <div class="row vh-100">
    <!-- Sidebar -->
    <div class="col-2 sidebar">
        <div class="list-group">
            <a href="#" class="list-group-item list-group-item-action active">
                <i class="fas fa-users sidebar-icon"></i> My Attendance
            </a>
            <a href="../rcgi_index.php" class="list-group-item list-group-item-action">
                <i class="fas fa-sign-out-alt sidebar-icon"></i> Logout
            </a>
        </div>
  
    </div>

    <div class="main-content">
      <!-- Employee Info and Stats Card -->
      <div class="card p-4 mb-4 d-flex flex-md-row flex-column align-items-center justify-content-between gap-4" style= "background: #E5E0D8">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= htmlspecialchars($employee['profile_picture']) ?>" alt="employee photo" alt="Profile" style="width: 90px; height: 90px; border-radius: 50%; border: 2px solid #ccc;">
              <div>
                  <h4 class="mb-1"><?= htmlspecialchars($employee['name']) ?></h4>
                  <p class="mb-0 small text-muted"><?= htmlspecialchars($employee['user_id']) ?></p>
                  <p class="mb-0 small"><?= htmlspecialchars($employee['company']) ?></p>
              </div>
          </div>
          <div class="d-flex flex-wrap gap-3 justify-content-center">
              <div class="px-3 py-2 rounded" style="background-color: #E4C28B;">
              <strong><?= $total_hours_worked ?>h</strong><br><small>Total Hours Worked</small>
              </div>
              <div class="px-3 py-2 rounded" style="background-color: #D9D9D9;">
              <strong><?= $total_extra_hours ?>h</strong><br><small>Extra Hours Worked</small>
              </div>
              <div class="px-3 py-2 rounded" style="background-color: #BCCFB9;">
              <strong><?= $average_check_in ?></strong><br><small>Average Check In Time</small>
              </div>
              <div class="px-3 py-2 rounded" style="background-color: #C29B99;">
              <strong><?= $late_count ?> Lates</strong><br><small>Number of Lates</small>
              </div>
          </div>
      </div>

      <!--search field-->
      <div class="card p-4 mb-4">
        <form class="row g-3 align-items-end" method="GET" action="">
          <div class="col-md-4">
          <label for="start_date" class="form-label"><strong>Start Date</strong></label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" />
          </div>
          <div class="col-md-4">
          <label for="end_date" class="form-label"><strong>End Date</strong></label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" />
          </div>
          <div class="col-md-4">
          <button type="submit" class="btn btn-primary btn-sm">Search</button>
          <?php if (!empty($start_date) || !empty($end_date)): ?>
            <a href="emp_attendancepage.php" class="btn btn-secondary btn-sm ms-2">Clear</a>
          <?php endif; ?>
            </div>
          <div>
        </form>
      </div>

      <!-- Attendance Table -->
      <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Attendance Records</h5>
          <button class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-download me-1"></i> Download Report
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered text-center align-middle mb-3">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>SCHEDULE</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>OT</th>
                <th>HRS RENDERED</th>
              </tr>
            </thead>
            <tbody>
            <?php if (count($attendance_rows) > 0): ?>
                <?php foreach ($attendance_rows as $row): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($row['date'])) ?></td>
                        <td><?= date('h:i A', strtotime($employee['shift_start'])) . ' - ' . date('h:i A', strtotime($employee['shift_end'])) ?></td>
                        <td><?= $row['check_in'] ?? '-' ?></td>
                        <td><?= $row['check_out'] ?? '-' ?></td>
                        <td><?= formatHours(calculateOTMinutes($row['ot_in'] ?? '', $row['ot_out'] ?? '')) ?></td>
                        <td><?= formatHours(calculateRenderedMinutes($row)) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="10">No attendance records found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination + Footer text -->
        <div class="d-flex justify-content-between align-items-center">
          <span>Showing <?= ($total_records > 0) ? $offset + 1 : 0 ?> to <?= min($offset + $records_per_page, $total_records) ?> of <?= $total_records ?> entries</span>
          <div>
            <a href="?page=<?= max(1, $page - 1) ?><?= !empty($start_date) ? '&start_date='.htmlspecialchars($start_date) : '' ?><?= !empty($end_date) ? '&end_date='.htmlspecialchars($end_date) : '' ?>" class="btn btn-light btn-sm me-2 <?= ($page <= 1) ? 'disabled' : '' ?>">Previous</a>
            <a href="?page=<?= min($total_pages, $page + 1) ?><?= !empty($start_date) ? '&start_date='.htmlspecialchars($start_date) : '' ?><?= !empty($end_date) ? '&end_date='.htmlspecialchars($end_date) : '' ?>" class="btn btn-light btn-sm <?= ($page >= $total_pages) ? 'disabled' : '' ?>">Next</a>
          </div>
        </div>
      </div>
    </div>
</div>
  <!-- Footer -->
  <footer>
    &copy; 2025 Attendance System. All rights reserved.
  </footer>

</div>

</body>
</html>