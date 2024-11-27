<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include PHPMailer via Composer

$mail = new PHPMailer(true);
session_start();
$error = '';
function loadEnv($file)
{
    if (!file_exists($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignore comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Split by '=' sign
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1]));
        }
    }
}

// Load the .env file
loadEnv(__DIR__ . '/.env');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Database connection
    $conn = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), getenv('DB_NAME'));

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Input validation
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // SQL query using prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['user_email_temp'] = $email;
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'chilukuridileepreddy7@gmail.com'; // Your email
                $mail->Password = 'ztokshssvrfwarvz';   // Your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
                $mail->Port = 587; // Port for TLS
            
                // Email Content
                $mail->isHTML(true);
                $mail->setFrom('chilukuridileepreddy7@gmail.com', 'Dileep');
                $mail->addAddress($email); // Recipient's email
                $mail->Subject = 'Your Two-Factor Authentication (2FA) Code';
                $mail->Body = "<html>
<head>
    <title>2FA Code</title>
</head>
<body>
    <h2>Your Two-Factor Authentication Code</h2>
    <p>Use the code below to complete your login or verification:</p>
    <h1 style='color: #007bff;'>{$otp}</h1>
    <p>This code is valid for 10 minutes. If you did not request this code, please ignore this email or contact support.</p>
    <p>Thank you,</p>
    <p>Coventry</p>
</body>
</html>";
$mail->send();

                echo "<script>
                        alert('OTP sent to your email. Please check!');
                        document.location.href='otp.php';
                      </script>";
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .form-container h2 {
            margin-bottom: 20px;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            background-color: #28a745;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }

        .form-container .register-btn {
            background-color: #007bff;
        }

        .form-container button:hover {
            opacity: 0.9;
        }

        .form-container .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <form action="register.php" method="GET">
            <button type="submit" class="register-btn">Register</button>
        </form>
    </div>
</body>

</html>