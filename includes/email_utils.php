<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationCode($email, $name, $verificationCode) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sndshoppe11@gmail.com'; // Replace with your Gmail
        $mail->Password = 'nmbd uctm myxc pshv'; // Replace with your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('sndshoppe11@gmail.com', 'RCGI WorkPulse');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'RCGI WorkPulse - Your Verification Code';
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h2>RCGI WorkPulse Login Verification</h2>
            </div>
            <div style="margin-bottom: 30px;">
                <p>Hello ' . $name . ',</p>
                <p>Please use the following verification code to complete your login:</p>
                <div style="text-align: center; margin: 30px 0;">
                    <div style="background-color: #f5f5f5; padding: 15px; font-size: 24px; letter-spacing: 5px; font-weight: bold;">
                        ' . $verificationCode . '
                    </div>
                </div>
                <p>If you did not attempt to log in, please ignore this email or contact your administrator.</p>
                <p>This code will expire in 10 minutes for security purposes.</p>
            </div>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #777; font-size: 12px;">
                <p>This is an automated message, please do not reply.</p>
                <p>&copy; ' . date('Y') . ' Restaurant Concepts Group, Inc. All rights reserved.</p>
            </div>
        </div>';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function generateVerificationCode() {
    return sprintf("%06d", mt_rand(100000, 999999)); // Generate 6-digit code
}
?>
