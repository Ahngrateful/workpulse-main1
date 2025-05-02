<?php
session_start();
require_once '../db.php';
require_once '../config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 14) {
    header('Location: ../login.php');
    exit();
}

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    // Start transaction
    $conn->begin_transaction();

    // Handle profile picture upload
    $profile_picture_path = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profile_pictures/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, and PNG files are allowed.');
        }

        $profile_picture_path = $upload_dir . uniqid('profile_') . '.' . $file_extension;
        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture_path)) {
            throw new Exception('Failed to upload profile picture.');
        }
    }

    // Hash the password
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepare the SQL statement
    $stmt = $conn->prepare("
        INSERT INTO users (
            user_id, name, username, email, contact, 
            card_number, pin, password, role, status,
            company, hire_date, shift_start, shift_end,
            profile_picture, created_at, created_by
        ) VALUES (
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, 
            ?, NOW(), ?
        )
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "sssssssssssssssi",
        $_POST['user_id'],
        $_POST['name'],
        $_POST['username'],
        $_POST['email'],
        $_POST['contact'],
        $_POST['card_number'],
        $_POST['pin'],
        $hashed_password,
        $_POST['role'],
        $_POST['status'],
        $_POST['company'],
        $_POST['hire_date'],
        $_POST['shift_start'],
        $_POST['shift_end'],
        $profile_picture_path,
        $_SESSION['user_id']
    );

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'User added successfully';

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Delete uploaded file if exists
    if ($profile_picture_path && file_exists($profile_picture_path)) {
        unlink($profile_picture_path);
    }

    $response['message'] = 'Error: ' . $e->getMessage();
} finally {
    // Close statement if it exists
    if (isset($stmt)) {
        $stmt->close();
    }
}

// Send response
echo json_encode($response);
exit();

// Update the JavaScript in users.php to handle the form submission
?>
