<?php
require 'db_connect.php';
$username = 'admin';
$email = 'admin@example.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
$stmt->execute([$username, $email, $password]);
echo "Admin user created.";
?>