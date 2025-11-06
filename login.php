<?php
session_start();
require_once __DIR__ . '/dbconfig.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifier = trim($_POST['email'] ?? '');
    $password   = trim($_POST['password'] ?? '');

    if (!empty($identifier) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id OR first_name = :id LIMIT 1");
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $success = "Login successful! Redirecting...";
            echo "<meta http-equiv='refresh' content='2;url=portfolio.php'>";
        } else {
            $error = "Invalid email/username or password!";
        }
    } else {
        $error = "Please fill in both fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="login_style.css">
</head>
<body>
<div class="login-box">
    <img src="logo/flowerforher.png" alt="Logo" class="logo">
    <h2>Login</h2>
    <form method="post">
        <input type="text" name="email" placeholder="Email or Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>
</div>
</body>
</html>