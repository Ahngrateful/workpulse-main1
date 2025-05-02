<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    echo "Username: " . $username . "<br>";
    echo "Hashed Password: " . $hashed_password . "<br>";
    echo "Role: " . $role . "<br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Registration</title>
</head>
<body>
    <form method="POST">
        <input type="text" name="username" placeholder="Username"><br><br>
        <input type="password" name="password" placeholder="Password"><br><br>
        <select name="role">
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select><br><br>
        <button type="submit">Generate Hash</button>
    </form>
</body>
</html>