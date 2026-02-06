<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendPasswordResetEmail($to_email, $to_name, $reset_link)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your CropManage Password';

        // Email body
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f5f7fa;
                }
                .container {
                    max-width: 600px;
                    margin: 30px auto;
                    background: white;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #2d5016 0%, #4a7c27 100%);
                    color: white;
                    padding: 30px 20px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                }
                .content {
                    padding: 40px 30px;
                }
                .content h2 {
                    color: #2d5016;
                    margin-top: 0;
                }
                .button {
                    display: inline-block;
                    padding: 15px 40px;
                    background: linear-gradient(135deg, #2d5016 0%, #4a7c27 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    margin: 20px 0;
                    box-shadow: 0 4px 12px rgba(45, 80, 22, 0.3);
                }
                .button:hover {
                    opacity: 0.9;
                }
                .info-box {
                    background: #f8faf9;
                    border-left: 4px solid #2d5016;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .footer {
                    background: #f5f5f5;
                    padding: 20px;
                    text-align: center;
                    font-size: 14px;
                    color: #666;
                }
                .footer a {
                    color: #2d5016;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🌱 CropManage</h1>
                </div>
                <div class="content">
                    <h2>Hello ' . htmlspecialchars($to_name) . ',</h2>
                    <p>We received a request to reset your password for your CropManage account.</p>
                    <p>Click the button below to reset your password:</p>
                    
                    <center>
                        <a href="' . $reset_link . '" class="button">Reset Password</a>
                    </center>
                    
                    <div class="info-box">
                        <strong>⏰ This link will expire in 1 hour</strong><br>
                        If you didn\'t request a password reset, you can safely ignore this email.
                    </div>
                    
                    <p>If the button doesn\'t work, copy and paste this link into your browser:</p>
                    <p style="word-break: break-all; color: #666; font-size: 14px;">' . $reset_link . '</p>
                    
                    <p style="margin-top: 30px;">
                        <strong>Security tip:</strong> Never share this link with anyone. CropManage will never ask for your password via email.
                    </p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' CropManage. All rights reserved.</p>
                    <p>
                        <a href="mailto:' . SMTP_FROM_EMAIL . '">Contact Support</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ';

        // Plain text version for email clients that don't support HTML
        $mail->AltBody = "Hello $to_name,\n\n"
            . "We received a request to reset your password for your CropManage account.\n\n"
            . "Click the link below to reset your password:\n"
            . "$reset_link\n\n"
            . "This link will expire in 1 hour.\n\n"
            . "If you didn't request a password reset, you can safely ignore this email.\n\n"
            . "Best regards,\n"
            . "CropManage Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

function sendWelcomeEmail($to_email, $to_name)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to CropManage!';

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 30px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #2d5016 0%, #4a7c27 100%); color: white; padding: 30px 20px; text-align: center; }
                .content { padding: 40px 30px; }
                .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 14px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🌱 Welcome to CropManage!</h1>
                </div>
                <div class="content">
                    <h2>Hello ' . htmlspecialchars($to_name) . ',</h2>
                    <p>Thank you for registering with CropManage! We\'re excited to have you on board.</p>
                    <p>With CropManage, you can:</p>
                    <ul>
                        <li>Track your crops and their growth stages</li>
                        <li>Manage expenses and sales</li>
                        <li>Generate detailed reports</li>
                        <li>Monitor your farming profitability</li>
                    </ul>
                    <p>Get started by logging in to your account and adding your first crop!</p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' CropManage. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Welcome email failed: {$mail->ErrorInfo}");
        return false;
    }
}
