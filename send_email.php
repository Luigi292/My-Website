<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Import PHPMailer classes
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$success = false;
$message = '';
$name = $email = $subject = $messageContent = $phone = '';

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ================= SPAM PROTECTION =================
    // 1. Honeypot field check (bots will fill this hidden field)
    if (!empty($_POST['website'])) {
        // Silent fail for bots
        echoResponse(false, "Thank you for your message!");
        exit;
    }
    
    // 2. Time-based check (form filled too quickly - less than 5 seconds)
    if (isset($_POST['submit_time']) && time() - (int)$_POST['submit_time'] < 5) {
        echoResponse(false, "Please take your time to fill out the form.");
        exit;
    }
    
    // 3. Required fields check
    $required = ['name', 'email', 'subject', 'message'];
    $missingFields = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        echoResponse(false, "Please fill in all required fields: " . implode(', ', $missingFields));
        exit;
    }
    
    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject']));
    $messageContent = htmlspecialchars(trim($_POST['message']));
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    
    // 4. Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echoResponse(false, "Please provide a valid email address");
        exit;
    }
    
    // 5. Check if email domain exists and has MX records
    $emailDomain = substr(strrchr($email, "@"), 1);
    if (!checkdnsrr($emailDomain, "MX")) {
        echoResponse(false, "Please provide a valid email address with a real domain");
        exit;
    }
    
    // 6. Block disposable email addresses
    $disposableDomains = [
        'mailinator.com', 'guerrillamail.com', 'temp-mail.org', 
        '10minutemail.com', 'throwawaymail.com', 'yopmail.com',
        'fakeinbox.com', 'trashmail.com', 'maildrop.cc'
    ];
    
    if (in_array(strtolower($emailDomain), $disposableDomains)) {
        echoResponse(false, "Disposable email addresses are not accepted");
        exit;
    }
    
    // 7. Keyword spam filtering
    $spamKeywords = [
        'viagra', 'cialis', 'loan', 'mortgage', 'casino', 'porn',
        'seo', 'marketing', 'traffic', 'bitcoin', 'crypto', 'forex',
        'hack', 'password', 'compromised', 'virus', 'free', 'win',
        'promotion', 'advertise', 'backlink', 'ranking', 'google'
    ];
    
    $combinedContent = strtolower($subject . ' ' . $messageContent);
    foreach ($spamKeywords as $keyword) {
        if (strpos($combinedContent, $keyword) !== false) {
            echoResponse(false, "Your message contains content that appears to be spam");
            exit;
        }
    }
    
    // 8. Validate name (no numbers or special chars)
    if (!preg_match('/^[a-zA-Z\s\-\.\']+$/', $name)) {
        echoResponse(false, "Please provide a valid name without numbers or special characters");
        exit;
    }
    
    // 9. Message length check
    if (strlen($messageContent) < 10 || strlen($messageContent) > 2000) {
        echoResponse(false, "Message should be between 10 and 2000 characters");
        exit;
    }
    
    // 10. Validate phone number if provided
    if (!empty($phone) && !preg_match('/^[0-9\s\+\-\(\)]{6,20}$/', $phone)) {
        echoResponse(false, "Please provide a valid phone number");
        exit;
    }
    
    // ================= EMAIL SENDING =================
    $mail = new PHPMailer(true);

    try {
        // First try sending the confirmation email to the user
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'luigimaretto292@gmail.com';
        $mail->Password = 'bbfd brrw eaem efsq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        
        // Send confirmation to user first
        $mail->setFrom('luigimaretto292@gmail.com', 'Luigi Maretto');
        $mail->addAddress($email, $name);
        $mail->Subject = "Thank you for contacting me";
        
        $userEmailBody = "<div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; color: #333;'>
            <h2 style='color: #2563eb;'>Thank you for your message, $name!</h2>
            <p style='line-height: 1.6;'>This is a confirmation that I've received your message and will get back to you as soon as possible.</p>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 3px solid #2563eb;'>
                <p style='margin: 0 0 10px 0;'><strong>Subject:</strong> $subject</p>
                <div style='white-space: pre-line;'>$messageContent</div>
            </div>
            
            <p style='line-height: 1.6;'>If you have any additional information to add, please reply to this email.</p>
            
            <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
            
            <div style='font-size: 0.9em; color: #6b7280;'>
                <p><strong>Your contact details:</strong></p>
                <p>Name: $name</p>
                <p>Email: $email</p>
                " . (!empty($phone) ? "<p>Phone: $phone</p>" : "") . "
                <p>Message sent: " . date('Y-m-d H:i:s') . "</p>
            </div>
            
            <p style='margin-top: 30px;'>Best regards,</p>
            <p style='font-weight: bold;'>Luigi Maretto</p>
            <p>Full-Stack Developer</p>
        </div>";
        
        $mail->Body = $userEmailBody;
        $mail->AltBody = "Thank you for your message, $name!\n\n" .
                        "This is a confirmation that I've received your message:\n\n" .
                        "Subject: $subject\n\n" .
                        "$messageContent\n\n" .
                        "I'll review your message and get back to you as soon as possible.\n\n" .
                        "Your contact details:\n" .
                        "Name: $name\n" .
                        "Email: $email\n" .
                        (!empty($phone) ? "Phone: $phone\n" : "") . "\n" .
                        "Message sent: " . date('Y-m-d H:i:s') . "\n\n" .
                        "Best regards,\n" .
                        "Luigi Maretto\n" .
                        "Full-Stack Developer";
        
        // Try to send confirmation to user first
        $mail->send();
        
        // If we get here, the user email was sent successfully
        // Now prepare the email to be sent to you
        $mail->clearAddresses();
        $mail->clearReplyTos();
        
        $mail->setFrom('luigimaretto292@gmail.com', 'Website Contact Form');
        $mail->addAddress('luigimaretto292@gmail.com', 'Luigi Maretto');
        $mail->addReplyTo($email, $name);
        $mail->Subject = "Website Contact: $subject";
        
        $emailBody = "<h2 style='color: #2563eb;'>New Contact Form Submission</h2>
                     <p><strong>From:</strong> $name &lt;$email&gt;</p>";
        
        if (!empty($phone)) {
            $emailBody .= "<p><strong>Phone:</strong> $phone</p>";
        }
        
        $emailBody .= "<p><strong>Subject:</strong> $subject</p>
                      <p><strong>Message:</strong></p>
                      <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 3px solid #2563eb;'>
                        $messageContent
                      </div>
                      <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                      <p style='font-size: 0.8em; color: #6b7280;'>
                        <strong>IP:</strong> {$_SERVER['REMOTE_ADDR']} | 
                        <strong>Time:</strong> " . date('Y-m-d H:i:s') . " | 
                        <strong>Browser:</strong> {$_SERVER['HTTP_USER_AGENT']}
                      </p>";
        
        $mail->Body = $emailBody;
        $mail->AltBody = "Name: $name\nEmail: $email\n" . 
                        (!empty($phone) ? "Phone: $phone\n" : "") .
                        "Subject: $subject\n\nMessage:\n$messageContent\n\n" .
                        "IP: {$_SERVER['REMOTE_ADDR']} | Time: " . date('Y-m-d H:i:s') . 
                        " | Browser: {$_SERVER['HTTP_USER_AGENT']}";
        
        // Send the email to yourself
        $mail->send();
        
        $message = "Thank you for your message! I've received it and will get back to you soon. A confirmation email has been sent to $email.";
        echoResponse(true, $message);
        exit;
        
    } catch (Exception $e) {
        $message = "There was an error sending your message. The email address you provided might be invalid. Please try again with a valid email address or contact me directly at luigimaretto292@gmail.com.";
        error_log("Email sending error: " . $e->getMessage());
        echoResponse(false, $message);
        exit;
    }
    
} else {
    // If not a POST request, show the HTML response
    echoResponse($success, $message);
}

function echoResponse($success, $message) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Message Status | Luigi Maretto</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --primary: #4361ee;
                --primary-dark: #3a56d4;
                --success: #4cc9f0;
                --text: #2b2d42;
                --text-light: #8d99ae;
                --bg: #f8f9fa;
                --card-bg: #ffffff;
                --border-radius: 12px;
                --box-shadow: 0 10px 30px rgba(0,0,0,0.08);
                --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Inter', sans-serif;
                background-color: var(--bg);
                color: var(--text);
                line-height: 1.6;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                padding: 20px;
                background-image: radial-gradient(circle at 10% 20%, rgba(67, 97, 238, 0.05) 0%, rgba(76, 201, 240, 0.05) 90%);
            }
            
            .confirmation-card {
                background: var(--card-bg);
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow);
                padding: 3rem 2.5rem;
                max-width: 580px;
                width: 100%;
                text-align: center;
                transition: var(--transition);
                position: relative;
                overflow: hidden;
            }
            
            .confirmation-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 5px;
                background: linear-gradient(90deg, var(--primary), var(--success));
            }
            
            .confirmation-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, var(--primary), var(--success));
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1.5rem;
                box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            }
            
            .confirmation-icon i {
                color: white;
                font-size: 2.5rem;
            }
            
            .confirmation-title {
                font-size: 1.8rem;
                font-weight: 700;
                margin-bottom: 1rem;
                color: var(--primary);
                background: linear-gradient(90deg, var(--primary), var(--success));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            
            .confirmation-message {
                font-size: 1.1rem;
                color: var(--text-light);
                margin-bottom: 2rem;
            }
            
            .confirmation-email {
                display: inline-block;
                background: rgba(67, 97, 238, 0.1);
                color: var(--primary);
                padding: 8px 16px;
                border-radius: 50px;
                font-weight: 600;
                margin: 10px 0;
            }
            
            .confirmation-btn {
                display: inline-flex;
                align-items: center;
                background: linear-gradient(90deg, var(--primary), var(--primary-dark));
                color: white;
                padding: 12px 28px;
                border-radius: 50px;
                text-decoration: none;
                font-weight: 600;
                transition: var(--transition);
                box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
                margin-top: 1.5rem;
                border: none;
                cursor: pointer;
            }
            
            .confirmation-btn:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
            }
            
            .confirmation-btn i {
                margin-left: 8px;
                transition: var(--transition);
            }
            
            .confirmation-btn:hover i {
                transform: translateX(3px);
            }
            
            @media (max-width: 768px) {
                .confirmation-card {
                    padding: 2.5rem 1.5rem;
                }
                
                .confirmation-title {
                    font-size: 1.5rem;
                }
                
                .confirmation-message {
                    font-size: 1rem;
                }
            }
        </style>
    </head>
     <body>
        <div class="confirmation-card">
            <div class="confirmation-icon">
                <i class="fas <?php echo $success ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            </div>
            <h1 class="confirmation-title"><?php echo $success ? 'Message Received!' : 'Oops!'; ?></h1>
            <p class="confirmation-message"><?php echo htmlspecialchars($message); ?></p>
            <?php if (!empty($email) && strpos($message, $email) !== false): ?>
                <div class="confirmation-email"><?php echo htmlspecialchars($email); ?></div>
            <?php endif; ?>
            <a href="index.html#contact" class="confirmation-btn">
                <?php echo $success ? 'Return to Website' : 'Try Again'; ?> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </body>
    </html>
    <?php
}