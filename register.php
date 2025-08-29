<?php
require 'db_connect.php';
session_start();

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['_csrf']) || $_POST['_csrf'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($username)) {
            $errors[] = 'Username is required.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        // Check if email or username already exists
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Email or username already exists.';
            }
        }

        // Insert user into database
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$username, $email, $hashed_password]);
                $success_message = 'Registration successful! Redirecting to login page...';
                header("Refresh: 2; url=login.php");
            } catch (PDOException $e) {
                $errors[] = 'An error occurred during registration. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TravelGuide</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e5f4fd;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar */
        nav {
            background: rgba(31, 41, 55, 0.8);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            color: #fff;
        }

        .logo span {
            color: #f97316;
            padding-left: 0.5rem;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s, transform 0.2s, text-decoration 0.2s;
        }

        .always-underline {
            text-decoration: underline !important;
        }

        .nav-links a:hover {
            background-color: #f97316;
            transform: translateY(-2px);
            text-decoration: underline;
        }

        /* Register Container */
        .register-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin: auto;
            animation: modalFadeIn 0.3s ease-out;
        }

        h2 {
            font-size: 2rem;
            color: #0077b6;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .register-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .register-form label {
            font-size: 1rem;
            color: #546E7A;
            font-weight: 600;
            text-align: left;
        }

        .register-form input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .register-form input:focus {
            border-color: #f97316;
            box-shadow: 0 0 5px rgba(249, 115, 22, 0.3);
            outline: none;
        }

        .register-form button {
            width: 100%;
            padding: 0.8rem;
            background-color: #f97316;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .register-form button:hover {
            background-color: #e55e00;
            transform: translateY(-2px);
        }

        .error-message, .success-message {
            text-align: center;
            margin-bottom: 1rem;
        }

        .error-message {
            color: #f97316;
            font-size: 0.9rem;
            font-weight: 400;
        }

        .success-message {
            color: #8BC34A;
            font-size: 0.9rem;
            font-weight: 400;
        }

      
        /* Animations */
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

       
    </style>
</head>
<body>
    <nav>
        <div class="logo">Travel<span>Guide</span></div>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="adminlogin.php">Admin Login</a>
            <a href="login.php">User Login</a>
        </div>
    </nav>

    <div class="register-container">
        <h2>Create Your Account</h2>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form class="register-form" method="POST" action="register.php">
            <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit">Register</button>
        </form>
    </div>

    
</body>
</html>