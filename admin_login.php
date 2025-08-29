<?php
session_start();

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
$host = 'localhost';
$user = 'root'; // Update with your MySQL username
$password = ''; // Update with your MySQL password
$dbname = 'travelers';

$conn = new mysqli($host, $user, $password, $dbname);
$error = '';

if ($conn->connect_error) {
    $error = "Connection failed: " . $conn->connect_error;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['_csrf']) || $_POST['_csrf'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Input validation
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (empty($password)) {
            $error = "Password is required.";
        } else {
            // Sanitize inputs
            $email = $conn->real_escape_string($email);

            // Query the admins table
            $sql = "SELECT * FROM admins WHERE email = '$email'";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                // Verify password
                if (password_verify($password, $admin['password'])) {
                    // Successful login
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate CSRF token
                    header('Location: homeadmin.php');
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
        }
    }

    // Redirect back on failure with error
    if ($error) {
        header('Location: adminlogin.php?error=' . urlencode($error));
        exit();
    }
}

$conn->close();
?>