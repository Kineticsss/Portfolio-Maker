<?php
// test_token.php - Use this to debug your token issue
session_start();
require 'dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in first");
}

$user_id = $_SESSION['user_id'];

// Fetch the user's token
$stmt = $pdo->prepare("SELECT id, first_name, last_name, public_token FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

echo "<h2>Token Debug Information</h2>";
echo "<p><strong>User ID:</strong> " . htmlspecialchars($user['id']) . "</p>";
echo "<p><strong>Name:</strong> " . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</p>";
echo "<p><strong>Token in Database:</strong> " . htmlspecialchars($user['public_token']) . "</p>";

// Build the link
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);
$script_path = rtrim($script_path, '/');

echo "<p><strong>Protocol:</strong> " . htmlspecialchars($protocol) . "</p>";
echo "<p><strong>Host:</strong> " . htmlspecialchars($host) . "</p>";
echo "<p><strong>Script Path:</strong> " . htmlspecialchars($script_path) . "</p>";

$public_link = "$protocol://$host$script_path/public_resume.php?token=" . $user['public_token'];

echo "<p><strong>Generated Link:</strong> <a href='" . htmlspecialchars($public_link) . "' target='_blank'>" . htmlspecialchars($public_link) . "</a></p>";

echo "<hr>";
echo "<h3>Test Direct Link</h3>";
echo "<p>Click this link to test: <a href='public_resume.php?token=" . htmlspecialchars($user['public_token']) . "' target='_blank'>Test Public Resume</a></p>";

echo "<hr>";
echo "<h3>Check if Token Works</h3>";

// Test if the token can retrieve the user
$stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE public_token = :token");
$stmt->execute([':token' => $user['public_token']]);
$test_user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($test_user) {
    echo "<p style='color: green;'>✓ Token is valid! Can retrieve user: " . htmlspecialchars($test_user['first_name'] . ' ' . $test_user['last_name']) . "</p>";
} else {
    echo "<p style='color: red;'>✗ Token does not work! Cannot retrieve user from database.</p>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Token Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        p { margin: 10px 0; }
        strong { color: #333; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
    <p><a href="portfolio.php">← Back to Portfolio</a></p>
</body>
</html>