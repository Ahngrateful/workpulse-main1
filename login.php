<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];  
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            if ((int)$user['role'] === 14) {
                header('Location: admin/dashboard.php');
            } elseif ((int)$user['role'] === 0) {
                header('Location: user/dashboard.php');
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - WorkPulse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Import professional fonts */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #8B4513;
            --dark-bg: #2C3639;
            --card-bg: #ffffff;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --accent-orange: #F4A460;
            --accent-green: #8B8B6E;
            --gradient-start: #F4A460;
            --gradient-end: #8B4513;

            /* Typography variables */
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;

            /* Font weights */
            --font-light: 300;
            --font-regular: 400;
            --font-medium: 500;
            --font-semibold: 600;
            --font-bold: 700;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-primary);
        }

        body {
            background: var(--dark-bg);
            color: var(--text-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .login-container {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: var(--font-size-2xl);
            font-weight: var(--font-bold);
        }

        .subtitle {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: var(--font-size-sm);
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-size: var(--font-size-sm);
            font-weight: var(--font-medium);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            color: var(--text-primary);
            font-size: var(--font-size-sm);
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(244, 164, 96, 0.1);
        }

        .forgot-password {
            text-align: right;
            margin-top: -0.5rem;
            margin-bottom: 1.5rem;
        }

        .forgot-password a {
            color: var(--accent-orange);
            text-decoration: none;
            font-size: var(--font-size-sm);
            font-weight: var(--font-medium);
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: var(--primary-color);
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: var(--font-semibold);
            font-size: var(--font-size-sm);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .error-message {
            background: #FEE2E2;
            color: #DC2626;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: var(--font-size-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .error-message i {
            font-size: var(--font-size-base);
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .login-container {
                background: #1a1a1a;
            }

            input[type="text"],
            input[type="password"] {
                background: #2d2d2d;
                border-color: #3d3d3d;
                color: #fff;
            }

            label {
                color: #fff;
            }

            h1 {
                color: var(--accent-orange);
            }

            .subtitle {
                color: #888;
            }

            .error-message {
                background: rgba(220, 38, 38, 0.1);
                color: #ef4444;
            }
        }

        /* Responsive adjustments */
        @media screen and (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .logo {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="assets/logo.png" alt="WorkPulse Logo" class="logo">
        <h1>WorkPulse</h1>
        <p class="subtitle">Enter your credentials to access your account</p>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="forgot-password">
                <a href="#"><i class="fas fa-lock"></i> Forgot password?</a>
            </div>
            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> Sign in
            </button>
        </form>
    </div>
</body>
</html>
