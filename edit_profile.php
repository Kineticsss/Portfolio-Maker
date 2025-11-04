<?php
session_start();
require 'dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT public_token FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$token = $stmt->fetchColumn();

if (!$token) {
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE users SET public_token = :token WHERE id = :id");
    $stmt->execute([':token' => $token, ':id' => $user_id]);
}

$public_link = "http://localhost/Portfolio/public_resume.php?token=$token";

$stmt = $pdo->prepare("SELECT first_name, last_name, email, about_me, phone, address, github, linkedin FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("User not found.");

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $about_me   = trim($_POST['about_me']);
    $phone      = trim($_POST['phone']);
    $address    = trim($_POST['address']);
    $github     = trim($_POST['github']);
    $linkedin   = trim($_POST['linkedin']);

    if (!preg_match("/^[A-Za-z\s'-]+$/", $first_name)) $errors[] = "First name can only contain letters, spaces, or hyphens.";
    if (!preg_match("/^[A-Za-z\s'-]+$/", $last_name))  $errors[] = "Last name can only contain letters, spaces, or hyphens.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))     $errors[] = "Invalid email format.";
    if (empty($address))                               $errors[] = "Address is required.";
    if (!preg_match("/^[0-9+\-\s()]+$/", $phone))      $errors[] = "Invalid phone number format.";

    if (!empty($github) && !filter_var($github, FILTER_VALIDATE_URL))   $errors[] = "GitHub link must be a valid URL.";
    if (!empty($linkedin) && !filter_var($linkedin, FILTER_VALIDATE_URL)) $errors[] = "LinkedIn link must be a valid URL.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE users
            SET first_name = :first_name,
                last_name = :last_name,
                email = :email,
                about_me = :about_me,
                phone = :phone,
                address = :address,
                github = :github,
                linkedin = :linkedin
            WHERE id = :id
        ");
        $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':about_me' => $about_me,
            ':phone' => $phone,
            ':address' => $address,
            ':github' => $github,
            ':linkedin' => $linkedin,
            ':id' => $user_id
        ]);
        $success = "Profile updated successfully!";
        $user = array_merge($user, $_POST);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f8f9fa; }
        .form-container { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        button { background: #007BFF; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
        .public-link { margin-bottom: 15px; font-size: 0.9em; color: #555; }
        .public-link a { color: #007BFF; text-decoration: none; }
        .public-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Edit Profile</h2>

    <div class="public-link">
        Public resume link: <a href="<?= htmlspecialchars($public_link) ?>" target="_blank"><?= htmlspecialchars($public_link) ?></a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>First Name</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>

        <label>Last Name</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>

        <label>Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>

        <label>GitHub (optional)</label>
        <input type="url" name="github" value="<?= htmlspecialchars($user['github'] ?? '') ?>">

        <label>LinkedIn (optional)</label>
        <input type="url" name="linkedin" value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>">

        <label>About Me</label>
        <textarea name="about_me" rows="4"><?= htmlspecialchars($user['about_me'] ?? '') ?></textarea>

        <button type="submit">Save Changes</button>
        <button type="button" onclick="window.location.href='portfolio.php'">Back</button>
    </form>
</div>
</body>
</html>
