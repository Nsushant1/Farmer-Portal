<?php
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com'); // Gmail SMTP server
define('SMTP_PORT', 587); // TLS port
define('SMTP_USERNAME', 'nsushaant72@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'uyin prpc rnji xasb '); // Your Gmail App Password (NOT your regular password)
define('SMTP_FROM_EMAIL', 'nsushaant72@gmail.com'); // From email
define('SMTP_FROM_NAME', 'CropManage');

// For Gmail, you need to:
// 1. Enable 2-factor authentication
// 2. Generate an "App Password" from your Google Account settings
// 3. Use that App Password here (not your regular Gmail password)

// Alternative SMTP services:
// - SendGrid: smtp.sendgrid.net, port 587
// - Mailgun: smtp.mailgun.org, port 587
// - Mailtrap (for testing): smtp.mailtrap.io, port 2525
