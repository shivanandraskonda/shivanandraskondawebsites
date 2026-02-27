<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Honeypot check (Add this field to your HTML!)
    if (!empty($_POST['website_url'])) {
        die("Bot detected."); 
    }

    // 2. Rate limiting
    if(isset($_SESSION['last_submit']) && (time() - $_SESSION['last_submit']) < 10){
        die("Please wait 10 seconds before sending again.");
    }
    $_SESSION['last_submit'] = time();

    // 3. Capture & Clean
    $name    = strip_tags(trim($_POST['name'] ?? ''));
    $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST['subject'] ?? ''));
    $message = strip_tags(trim($_POST['message'] ?? ''));

    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($message)) {
        die("Please fill in all fields correctly.");
    }

    // 4. Header Injection Check
    $pattern = "/[\r\n]|content-type:|bcc:|cc:/i";
    if (preg_match($pattern, $name) || preg_match($pattern, $email) || preg_match($pattern, $subject)) {
        die("Header injection detected.");
    }

    // 5. SETTINGS (Change these to your real domain)
    $to = "your-real-email@gmail.com"; 
    $from_email = "no-reply@yourdomain.com"; // MUST be your domain name

    $headers = "From: Website Contact <$from_email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $body = "New message from contact form:\n\n";
    $body .= "Name: $name\n";
    $body .= "Email: $email\n";
    $body .= "Message:\n$message\n";

    if (mail($to, "Contact: $subject", $body, $headers)) {
        // Redirect back to a 'thank you' page or show success
        echo "Message sent successfully!";
    } else {
        echo "Server failed to send. Check your Linux mail logs.";
    }

} else {
    header("Location: index.html");
    exit;
}
?>