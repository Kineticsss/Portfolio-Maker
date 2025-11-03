<?php
session_start();
require_once __DIR__ . '/dbconfig.php';

$first_name = $last_name = $email = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name       = trim($_POST['first_name'] ?? '');
    $last_name        = trim($_POST['last_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate first name
    if (!preg_match("/^[a-zA-Z\s\-]+$/", $first_name)) {
        $errors['first_name'] = "❌ First name can only contain letters, spaces, and hyphens.";
        $first_name = '';
    }

    // Validate last name
    if (!preg_match("/^[a-zA-Z\s\-]+$/", $last_name)) {
        $errors['last_name'] = "❌ Last name can only contain letters, spaces, and hyphens.";
        $last_name = '';
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "❌ Invalid email format.";
        $email = '';
    }

    // Validate password
    if (strlen($password) < 6) {
        $errors['password'] = "❌ Password must be at least 6 characters.";
        $password = '';
    }

    // Validate confirm password
    if ($password !== $confirm_password || empty($confirm_password)) {
        $errors['confirm_password'] = "❌ Passwords do not match.";
        $confirm_password = '';
    }

    // If no errors → save user
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) 
                               VALUES (:first_name, :last_name, :email, :password)");
        try {
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':email'      => $email,
                ':password'   => $hashed
            ]);
            $success = "✅ Account created successfully!";
            // clear all fields on success
            $first_name = $last_name = $email = '';
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') { // unique violation
                $errors['email'] = "❌ Email is already registered.";
            } else {
                $errors['general'] = "❌ Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-box">
    <img src="logo/flowerforher.png" alt="Logo" class="logo">

    <h2>Register</h2>

    <?php if ($success): ?>
        <div class="notification success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="notification error"><?= $errors['general'] ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="text" name="first_name" placeholder="First Name" 
               value="<?= htmlspecialchars($first_name) ?>" required>
        <?php if (!empty($errors['first_name'])): ?>
            <div class="error"><?= $errors['first_name'] ?></div>
        <?php endif; ?>

        <input type="text" name="last_name" placeholder="Last Name" 
               value="<?= htmlspecialchars($last_name) ?>" required>
        <?php if (!empty($errors['last_name'])): ?>
            <div class="error"><?= $errors['last_name'] ?></div>
        <?php endif; ?>

        <input type="email" name="email" placeholder="Email" 
               value="<?= htmlspecialchars($email) ?>" required>
        <?php if (!empty($errors['email'])): ?>
            <div class="error"><?= $errors['email'] ?></div>
        <?php endif; ?>

        <input type="password" name="password" placeholder="Password" required>
        <?php if (!empty($errors['password'])): ?>
            <div class="error"><?= $errors['password'] ?></div>
        <?php endif; ?>

        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <?php if (!empty($errors['confirm_password'])): ?>
            <div class="error"><?= $errors['confirm_password'] ?></div>
        <?php endif; ?>

        <button type="submit">Register</button>
    </form>

    <p class="register-link">Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
