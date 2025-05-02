<?php
session_start();
require_once 'db.php';

// Ensure admin is logged in
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: ../login.php');
//     exit();
// }

// $admin_id = $_SESSION['user_id'];

//  Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 4;
$offset = ($page - 1) * $limit;

//  Get total records for pagination
$totalQuery = "SELECT COUNT(*) as total FROM employee";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
$startRow = $offset + 1;
$endRow = min($offset + $limit, $totalRows);

//  Fetch only paginated employees
$query = "SELECT employee_id, photo, name, fingerprint_id, shift_start_time, shift_end_time, hire_date, org FROM employee LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

//  Handle add-employee form
if ($_SERVER['REQUEST_METHOD']  == "POST" && isset($_POST['submit'])) {
    $employee_id = $_POST['employee_ID'];
    $name = $_POST['name'];
    $fingerprint_id = $_POST['fingerprint_ID'];
    $startshift = $_POST['shift_start_time'];
    $endshift = $_POST['shift_end_time'];
    $hiredate = $_POST['hire_date'];
    $org = $_POST['org'];
    $password = $_POST['password'];

    // Handle image upload
    $employee_image = null;
    if (isset($_FILES['employee_image']) && $_FILES['employee_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "employee_image/";
        $file_name = basename($_FILES['employee_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $unique_name = uniqid() . "_" . $file_name;
            $target_file = $target_dir . $unique_name;

            if (move_uploaded_file($_FILES['employee_image']['tmp_name'], $target_file)) {
                $employee_image = $target_file;
            } else {
                echo "<script>alert('Error uploading the file.');</script>";
                exit;
            }
        } else {
            echo "<script>alert('Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.');</script>";
            exit;
        }
    } else {
        echo "<script>alert('No image uploaded or upload error.');</script>";
        exit;
    }

    //  Insert new employee
    $conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO employee (admin_ID, employee_id, name, fingerprint_id, photo, hire_date, shift_start_time, shift_end_time, org, password) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iissssssss", $admin_id, $employee_id, $name, $fingerprint_id, $employee_image, $hiredate, $startshift, $endshift, $org, $password);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $conn->commit();
    echo "<script>alert('Employee added successfully.'); window.location.href=window.location.href;</script>";
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log($e->getMessage()); // logs to server
    echo "<script>alert('Error adding employee: " . addslashes($e->getMessage()) . "');</script>"; // shows on screen
}
}

// Optional: total employee count (for a summary display)
$employeeCountResult = $conn->query("SELECT COUNT(*) AS total FROM employee");
$totalEmployees = $employeeCountResult->fetch_assoc()['total'];

//Fetch the info of employee
// Fetch one employee's info (if needed for modal)
//$sql = "SELECT employee_id, photo, name, fingerprint_id, shift_start_time, shift_end_time, hire_date, org FROM employee";
//$employeeResult = $conn->query($sql);
//$employee = $employeeResult ? $employeeResult->fetch_assoc() : null;

// Handle edit-employee form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_submit'])) {
  $employee_id = $_POST['employee_ID'];
  $name = $_POST['name2'];
  $fingerprint_id = $_POST['fingerprint_ID'];
  $hiredate = $_POST['hire_date2'];
  $startshift = $_POST['shift_start_time2'];
  $endshift = $_POST['shift_end_time2'];
  $org = $_POST['org2'];

  $updateQuery = "UPDATE employee SET name=?, fingerprint_id=?, hire_date=?, shift_start_time=?, shift_end_time=?, org=? WHERE employee_id=?";
  $stmt = $conn->prepare($updateQuery);

  if (!$stmt) {
    die("Prepare failed: " . $conn->error);
  }

  $stmt->bind_param("sssssss", $name, $fingerprint_id, $hiredate, $startshift, $endshift, $org, $employee_id);

  if ($stmt->execute()) {
    echo "<script>alert('Employee updated successfully.'); window.location.href=window.location.href;</script>";
  } else {
    echo "<script>alert('Error updating employee: " . $stmt->error . "');</script>";
  }
}

// Get total employees
$employeeCountResult = $conn->query("SELECT COUNT(*) AS total FROM employee");
$totalEmployees = $employeeCountResult->fetch_assoc()['total'];

// Get total on leave employees
$onLeaveResult = $conn->query("SELECT COUNT(*) AS total FROM on_leave");
$onLeaveEmployees = $onLeaveResult->fetch_assoc()['total'];

// Calculate active employees
$activeEmployees = $totalEmployees - $onLeaveEmployees;

// Calculate percentages
$onLeavePercent = $totalEmployees > 0 ? ($onLeaveEmployees / $totalEmployees) * 100 : 0;
$activePercent = $totalEmployees > 0 ? ($activeEmployees / $totalEmployees) * 100 : 0;


?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body>
    <div class="sidebar">
        <a href="#" class="logo">
            <span class="material-icons">fingerprint</span>
            WorkPulse
        </a>
        <a href="dashboard.php" class="nav-item">
            <span class="material-icons">dashboard</span>
            Dashboard
        </a>
        <a href="employee.php" class="nav-item active">
            <span class="material-icons">people</span>
            Employees
        </a>
        <a href="attendance.php" class="nav-item">
            <span class="material-icons">event_available</span>
            Attendance
        </a>
        <a href="reports.php" class="nav-item">
            <span class="material-icons">assessment</span>
            Reports
        </a>
        <a href="devices.php" class="nav-item">
            <span class="material-icons">devices</span>
            Devices
        </a>
        <a href="notifications.php" class="nav-item">
            <span class="material-icons">notifications</span>
            Notifications
        </a>
        <a href="manage-admins.php" class="nav-item">
            <span class="material-icons">admin_panel_settings</span>
            Manage Admins
        </a>
        <a href="settings.php" class="nav-item">
            <span class="material-icons">settings</span>
            Settings
        </a>
        <a href="../login.php" class="logout">
            <span class="material-icons">logout</span>
            Logout
        </a>
    </div>

    <!--dropdown-->
    <div class="main-content">
        <div class="header-right">
            <div class="account-dropdown">
                <div class="account-dropdown-btn">
                    <span class="material-icons">account_circle</span>
                    My Account
                    <span class="material-icons">expand_more</span>
                </div>
                <div class="account-dropdown-content">
                    <a href="profile.php"><span class="material-icons">person</span>Profile</a>
                    <a href="settings.php"><span class="material-icons">settings</span>Settings</a>
                    <a href="../login.php"><span class="material-icons">logout</span>Logout</a>
                </div>
            </div>
        </div>
        <!-- Remove the duplicate header-right section below -->
        <div class="header">
            <h1>Employee Management</h1>
            <!-- Button to Open Modal -->
            <button class="add-employee-btn" id="openModalBtn">
                <span class="material-icons">person_add</span>
                Add Employee
            </button>
            <div id="employeeModal" class="modal">
              <div class="modal-content">
                <div class="modal-header">
                  <h2>Add New Employee</h2>
                  <span class="close-btn" id="closeModalBtn">&times;</span>
                </div>
                <div class="modal-body">
                  <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-container">
                      <!-- Left Column -->
                      <div class="col-left">
                        <div class="image-upload">
                          <label style="margin-top: 10px; margin-bottom: 15px;">Insert Profile Picture</label>
                          <img src="pics/placeholder.jpg" class="img-thumbnail" alt="Profile Image" id="employee_image">
                          <input type="file" class="form-control" name="employee_image" style="width: 70%;" id="imageUpload" accept="image/*" required>
                          <div class="form-group">
                            <label>Employee ID</label>
                            <input type="text" style="width: 50%;" id="id-employee" name="employee_ID" placeholder="Enter employee ID" required>
                          </div>
                        </div>
                      </div>

                      <!-- Right Column -->
                      <div class="col-right">
                        <div class="form-group">
                          <label>Name</label>
                          <input type="text" name="name" placeholder="Enter name" required>
                        </div>
                        <div class="form-group">
                          <label>Fingerprint ID</label>
                          <input type="text" name="fingerprint_ID" placeholder="Enter fingerprint ID" required>
                        </div>
                        <div class="form-group">
                          <label>Hire Date</label>
                          <input type="date" name="hire_date" required>
                        </div>
                        <div class="form-group">
                          <label>Start Shift</label>
                          <select name="shift_start_time" required>
                            <option selected>Select Shift</option>
                            <option>8:00 AM</option>
                            <option>9:00 AM</option>
                          </select>
                        </div>
                        <div class="form-group">
                          <label>End Shift</label>
                          <select name="shift_end_time" required>
                            <option selected>Select Shift</option>
                            <option>5:00 PM</option>
                            <option>6:00 PM</option>
                          </select>
                        </div>
                        <div class="form-group">
                          <label>Organization</label>
                          <select name="org" required>
                            <option selected>RCGI</option>
                            <option>Tarraco</option>
                          </select>
                        </div>
                        <div class="form-group">
                          <label>Default Password</label>
                          <input type="text" id="employee_password" name="password" readonly>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" name="submit" class="btn btn-primary">Add Employee</button>
                          <a href="admin_manage-employee.php"><button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button></a>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Employees</div>
                <div class="metric-value"><?php echo $totalEmployees; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Active Employees</div>
                <div class="metric-value"><?php echo $activeEmployees; ?></div>
                <div class="metric-label"><?php echo number_format($activePercent, 1); ?>% of total</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">On Leave</div>
                <div class="metric-value"><?php echo $onLeaveEmployees; ?></div>
                <div class="metric-label"><?php echo number_format($onLeavePercent, 1); ?>% of total</div>
            </div>
        </div>

        <!--employee list-->
        <div class="employee-list">
            <div class="list-header">
                <h2>Employee List</h2>
                <p>Manage your employees and their access rights</p>
            </div>

            <div class="search-bar">
                <input type="text" class="search-input" placeholder="Search employees..." id="searchInput" onkeyup="filterProducts()">
                <div id="searchResults"></div>
                <div class="filter-container">
                    <button class="filter-btn" type="button">
                        <span class="material-icons">filter_list</span>
                        Filter
                    </button>

                    <div class="filter-dropdown" id="filterDropdown">
                        <ul>
                            <li onclick="sortTable('name-asc')">Name A–Z</li>
                            <li onclick="sortTable('name-desc')">Name Z–A</li>
                            <li onclick="sortTable('member')">Member Since</li>
                        </ul>
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Fingerprint ID</th>
                        <th>Shift</th>
                        <th>Organization</th>
                        <th>Member Since</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="employeeTable">
                    <?php
                    if ($result && $result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                          echo "<tr>";
                          echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                          echo "<td><img src='" . htmlspecialchars($row['photo']) . "' class='img-thumbnail' style='width: 60px; height: 60px;' onerror=\"this.src='placeholder.jpg'\" /></td>";
                          echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                          echo "<td>" . htmlspecialchars($row['fingerprint_id']) . "</td>";
                          echo "<td>" . date("H:i", strtotime($row['shift_start_time'])) . " AM - " . date("H:i", strtotime($row['shift_end_time'])) . " PM</td>";
                          echo "<td>" . htmlspecialchars($row['org']) . "</td>";
                          echo "<td>" . htmlspecialchars($row['hire_date']) . "</td>";
                          
                          echo "<td>
                              <button
                                  class='action-btn openEditModal'
                                  title='Edit'
                                  data-id='" . htmlspecialchars($row['employee_id']) . "'>
                                  <span class='material-icons'>edit</span>
                              </button>

                              <form action='delete_employee.php' method='get' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this employee?')\">
                                  <input type='hidden' name='id' value='" . htmlspecialchars($row['employee_id']) . "'>
                                  <button type='submit' class='action-btn' title='Delete'>
                                      <span class='material-icons'>delete</span>
                                  </button>
                              </form>
                          </td>";
                          echo "</tr>";
                      }
                  } else {
                      echo "<tr><td colspan='8'>No employees found.</td></tr>";
                  }
                ?>
                    <!-- Add more rows as needed -->
                </tbody>
            </table>
           <!-- Pagination -->
           <div class="pagination-container">
                <span>
                    Showing <?= $startRow ?> to <?= $endRow ?> of <?= $totalRows ?> results
                </span>
                <div>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="action-btn">Previous</a>
                    <?php else: ?>
                        <button class="action-btn" disabled>Previous</button>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="action-btn">Next</a>
                    <?php else: ?>
                        <button class="action-btn" disabled>Next</button>
                    <?php endif; ?>
                </div>
        </div>
    </div>

        <div id="editemployeeModal" class="modal2" tabindex="-1">
        <div class="modal2-content">
            <div class="modal2-header">
                <h2>Edit Employee</h2>
                <span class="close2-btn" id="editcloseModalBtn">&times;</span>
            </div>
            <div class="modal2-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form2-container">
                        <div class="form2group">
                        <input type="hidden" name="employee_ID" id="edit-employee-id">
                            <label>Name</label>
                            <input type="text" name="name2" id="edit-name" required>
                        </div>
                        <div class="form2-group">
                            <label>Fingerprint ID</label>
                            <input type="text" name="fingerprint_ID" id="edit-fingerprint" required readonly>
                        </div>
                        <div class="form2-group">
                            <label>Hire Date</label>
                            <input type="date" name="hire_date2" id="edit-hire-date" value="" required>
                        </div>
                        <div class="form2-group">
                            <label>Start Shift</label>
                            <select name="shift_start_time2" id="edit-shift-start">
                                <option value="08:00:00">8:00 AM</option>
                                <option value="09:00:00">9:00 AM</option>
                            </select>
                        </div>
                        <div class="form2-group">
                            <label>End Shift</label>
                            <select name="shift_end_time2" id="edit-shift-end">
                              <option value="05:00:00">5:00 PM</option>
                              <option value="06:00:00">6:00 PM</option>
                            </select>
                        </div>
                        <div class="form2-group">
                            <label>Organization</label>
                            <select name="org2" id="edit-org">
                                <option>RCGI</option>
                                <option>Tarraco</option>
                            </select>
                        </div>
                        
                        <div class="modal2-footer">
                            <button type="submit" name="edit_submit" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-secondary" id="editcancelBtn">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                // Dropdown click handler
                $('.account-dropdown-btn').click(function(e) {
                    e.stopPropagation();
                    $('.account-dropdown-content').toggleClass('show');
                });
        
                // Close dropdown when clicking outside
                $(document).click(function() {
                    $('.account-dropdown-content').removeClass('show');
                });
        
                // Prevent dropdown from closing when clicking inside it
                $('.account-dropdown-content').click(function(e) {
                    e.stopPropagation();
                });
            });
   
      document.getElementById("openModalBtn").addEventListener("click", function() {
      document.getElementById("employeeModal").style.display = "block";
    });

    document.getElementById("closeModalBtn").addEventListener("click", function() {
      document.getElementById("employeeModal").style.display = "none";
    });

    document.getElementById("cancelBtn").addEventListener("click", function() {
      document.getElementById("employeeModal").style.display = "none";
    });
    
    //edit employee modal
    document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".openEditModal").forEach(button => {
        button.addEventListener("click", async () => {
            const row = button.closest("tr");
            const employeeId = button.getAttribute("data-id");

            try {
                const response = await fetch(`fetch_employee.php?employee_id=${employeeId}`);
                const result = await response.json();

                if (result.status === "success") {
                    const emp = result.data;
                    document.getElementById("edit-name").value = emp.name;
                    document.getElementById("edit-fingerprint").value = emp.fingerprint_id;
                    document.getElementById("edit-hire-date").value = emp.hire_date;
                    document.getElementById("edit-shift-start").value = emp.shift_start_time;
                    document.getElementById("edit-shift-end").value = emp.shift_end_time;
                    document.getElementById("edit-org").value = emp.org;

                    document.getElementById("edit-employee-id").value = employeeId;
                    // Open the modal
                    document.getElementById("editemployeeModal").style.display = "block";
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error("Fetch error:", error);
            }
        });
    });

    // Modal close logic
    document.getElementById("editcloseModalBtn").addEventListener("click", () => {
        document.getElementById("editemployeeModal").style.display = "none";
    });

    document.getElementById("editcancelBtn").addEventListener("click", () => {
        document.getElementById("editemployeeModal").style.display = "none";
    });
});

//image upload
    document.getElementById("imageUpload").addEventListener("change", function(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById("employee_image").src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    //search
document.getElementById('searchInput').addEventListener('keyup', function () {
    let searchValue = this.value;

    fetch('search_employees.php?query=' + encodeURIComponent(searchValue))
        .then(response => response.text())
        .then(data => {
            document.getElementById('employeeTable').innerHTML = data;
        });
});

//generate pass
function generatePassword(length = 12) {
  const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$!";
  let password = "";
  for (let i = 0; i < length; i++) {
    password += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return password;
}

//getting employee id
function fetchNextEmployeeID() {
  fetch('get_latest_employee_id.php?_=' + new Date().getTime())
    .then(response => response.json())
    .then(data => {
      console.log("Fetched next ID:", data); // DEBUG LINE
      if (data.next_id) {
        document.getElementById("id-employee").value = data.next_id;
      } else {
        alert("Failed to fetch employee ID.");
      }
    })
    .catch(error => {
      console.error("Error fetching employee ID:", error);
    });
}

document.getElementById("openModalBtn").addEventListener("click", function() {
  document.getElementById("employeeModal").style.display = "block";
  fetchNextEmployeeID();
  document.getElementById("employee_password").value = generatePassword(); // 🟢 This was missing
});

//dropdown filter
document.querySelector('.filter-btn').addEventListener('click', function () {
        const dropdown = document.querySelector('.filter-dropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });

    // Sort function
    function sortTable(type) {
        const table = document.getElementById('employeeTable');
        const rows = Array.from(table.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const nameA = a.children[2]?.textContent.trim().toLowerCase();
            const nameB = b.children[2]?.textContent.trim().toLowerCase();
            const dateA = new Date(a.children[6]?.textContent.trim());
            const dateB = new Date(b.children[6]?.textContent.trim());

            if (type === 'name-asc') return nameA.localeCompare(nameB);
            if (type === 'name-desc') return nameB.localeCompare(nameA);
            if (type === 'member') return dateB - dateA;
        });

        rows.forEach(row => table.appendChild(row)); // Re-insert sorted rows

        document.querySelector('.filter-dropdown').style.display = 'none'; // hide after select
    }

  
  </script>
</body> 
</html>