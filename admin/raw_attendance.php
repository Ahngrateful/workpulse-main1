<?php
require '../config.php';
require '../db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Attendance Records</h2>
        <div class="table-responsive">
            <table class="table table-striped" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Attendance ID</th>
                        <th>User ID</th>
                        <th>Timestamp</th>
                        <th>Verification Method</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function fetchAttendance() {
            $.ajax({
                url: '../api/raw_attendance.php',
                method: 'GET',
                success: function(data) {
                    let tableBody = '';
                    data.forEach(function(record) {
                        tableBody += `
                            <tr>
                                <td>${record.uid}</td>
                                <td>${record.id}</td>
                                <td>${record.timestamp}</td>
                                <td>${record.state}</td>
                                <td>${record.type}</td>
                            </tr>
                        `;
                    });
                    $('#attendanceTable tbody').html(tableBody);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching attendance:', error);
                }
            });
        }

        // Fetch initially
        fetchAttendance();

        // Refresh every 30 seconds
        setInterval(fetchAttendance, 2000);
    </script>
</body>
</html>