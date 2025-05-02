<?php
require_once '../db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 14) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $conn->begin_transaction();

    $userId = $_POST['user_id'];
    $updateFields = [];
    $params = [];
    $types = "";

    // Handle profile picture upload
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

        $profile_picture_path = 'uploads/profile_pictures/' . uniqid('profile_') . '.' . $file_extension;
        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], '../' . $profile_picture_path)) {
            throw new Exception('Failed to upload profile picture.');
        }

        $updateFields[] = "profile_picture = ?";
        $params[] = $profile_picture_path;
        $types .= "s";
    }

    // Add other fields to update
    $fields = [
        'name' => 's',
        'username' => 's',
        'email' => 's',
        'contact' => 's',
        'role' => 'i',
        'status' => 's',
        'company' => 's',
        'hire_date' => 's',
        'shift_start' => 's',
        'shift_end' => 's'
    ];

    foreach ($fields as $field => $type) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $updateFields[] = "$field = ?";
            $params[] = $_POST[$field];
            $types .= $type;
        }
    }

    // Handle password update if provided
    if (isset($_POST['password']) && $_POST['password'] !== '') {
        $updateFields[] = "password = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $types .= "s";
    }

    // Add user_id to parameters
    $params[] = $userId;
    $types .= "s";

    $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
