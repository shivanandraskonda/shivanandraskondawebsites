<?php
// No session_start needed for .html frontends
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Honeypot check
    if (!empty($_POST['website_url'])) {
        die("Bot detected."); 
    }

    // 2. Aligning names with your HTML Newsletter form
    // We check 'subscriber_name' and 'subscriber_email' to match your form
    $name  = filter_input(INPUT_POST, 'subscriber_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'subscriber_email', FILTER_VALIDATE_EMAIL);

    if (!$name || !$email) {
        echo "Invalid input. Please check your name and email.<br>";
        echo "<a href='index.html'>Go Back</a>";
        exit;
    }

    // 3. Secure File Writing
    $file = 'subscribers.csv';
    // We add the user's IP address for security tracking
    $data = [$name, $email, date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']];

    // 
    if ($fp = fopen($file, 'a')) {
        flock($fp, LOCK_EX); // Prevents file corruption
        fputcsv($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
        
        // Success Message
        echo "<h1>Success!</h1><p>You have been added to our newsletter.</p>";
        echo "<a href='index.html'>Return to Website</a>";
    } else {
        error_log("Failed to write to subscribers.csv");
        echo "A server error occurred. Please try again later.";
    }
} else {
    header("Location: index.html");
    exit;
}
?>