<?php
session_start();
require_once 'db.php';
require_once 'includes/email_utils.php';

// Check if user is already verified
if (isset($_SESSION['is_verified']) && $_SESSION['is_verified'] === true) {
    header("Location: admin/emp_attendancepage.php");
    exit;
}

// Check if employee session exists
if (!isset($_SESSION['employee_ID']) || !isset($_SESSION['verification_code']) || !isset($_SESSION['verification_code_timestamp'])) {
    header("Location: rcgi_index.php");
    exit;
}

// Check if verification code has expired (10 minutes)
$codeTimestamp = $_SESSION['verification_code_timestamp'];
$currentTime = time();
$timeDiff = $currentTime - $codeTimestamp;
$timeLimit = 10 * 60; // 10 minutes in seconds

if ($timeDiff > $timeLimit) {
    // Code expired
    session_unset(); // Clear all session variables
    session_destroy();
    header("Location: rcgi_index.php?error=expired");
    exit;
}

$errorMsg = '';
$successMsg = '';

// Check if form was submitted
if (isset($_POST['verify'])) {
    $enteredCode = trim($_POST['verification_code']);
    $storedCode = $_SESSION['verification_code'];
    
    if ($enteredCode === $storedCode) {
        // Code matched
        $_SESSION['is_verified'] = true;
        
        // Redirect to employee attendance page
        header("Location: user/emp_attendancepage.php");
        exit;
    } else {
        // Code did not match
        $errorMsg = "Invalid verification code. Please try again.";
    }
}

// Handle resend verification code
if (isset($_POST['resend'])) {
    // Get employee email
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT email, name FROM employee WHERE employee_ID = ?");
    $stmt->bind_param("s", $_SESSION['employee_ID']);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($email, $name);
        $stmt->fetch();
        
        if (!empty($email)) {
            // Generate a new verification code
            $verificationCode = generateVerificationCode();
            $_SESSION['verification_code'] = $verificationCode;
            $_SESSION['verification_code_timestamp'] = time();
            
            // Send the verification code
            if (sendVerificationCode($email, $name, $verificationCode)) {
                $successMsg = "A new verification code has been sent to your email.";
            } else {
                $errorMsg = "Failed to send verification code. Please try again or contact your administrator.";
            }
        } else {
            $errorMsg = "No email address found for your account. Please contact your administrator.";
        }
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Verification - RCGI WorkPulse</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Font Awesome icons (free version)-->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="rcgi_styles.css" rel="stylesheet" />
    <script src="includes/theme_manager.js" defer></script>

    <style>
        .verification-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .code-input {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
        }
        
        .code-input input {
            width: 100%;
            height: 60px;
            font-size: 24px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            letter-spacing: 12px;
            padding-left: 15px;
        }
        
        .code-input input:focus {
            border-color: #FFC349;
            outline: none;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Dark mode adjustments */
        [data-theme="dark"] .verification-container {
            background-color: rgba(33, 37, 41, 0.9);
            color: #f8f9fa;
        }
        
        [data-theme="dark"] .code-input input {
            background-color: #343a40;
            color: #f8f9fa;
            border-color: #495057;
        }
        
        [data-theme="dark"] .code-input input:focus {
            border-color: #FFC349;
        }
    </style>
</head>

<body id="page-top">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top">
                <h3><span><img src="assets/logo.png" alt="Logo" /></span>RCGI WorkPulse</h3>
            </a>
        </div>
    </nav>
    
    <!-- Verification Section -->
    <section class="page-section" style="background-image: url('pics/admin_main-bg2.jpg'); background-size: cover; height: 100vh; display: flex; align-items: center;">
        <div class="container">
            <div class="verification-container">
                <div class="text-center">
                    <h2 class="section-heading">Verify Your Login</h2>
                    <p>A 6-digit verification code has been sent to your email address. Please enter it below to complete your login.</p>
                </div>
                
                <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger">
                    <?php echo $errorMsg; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success">
                    <?php echo $successMsg; ?>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <div class="code-input">
                        <input type="text" name="verification_code" maxlength="6" pattern="[0-9]{6}" title="Please enter a 6-digit code" required autocomplete="off" placeholder="------">
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" name="verify" class="btn btn-primary">Verify</button>
                        <button type="submit" name="resend" class="btn btn-outline-secondary">Resend Code</button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <p>Didn't receive the code? Check your spam folder or <a href="rcgi_index.php">return to login</a>.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer-->
    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 text-lg-start">Copyright &copy; RCGI WorkPulse <?php echo date("Y"); ?></div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="rcgi_scripts.js"></script>
</body>
</html>
