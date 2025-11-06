<?php
session_start();
require '../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $issuer = trim($_POST['issuer']);
    $date_issued = $_POST['date_issued'];
    $date_received = $_POST['date_received'];
    $expiration_date = $_POST['expiration_date'] ?: null;
    $credential_id = trim($_POST['credential_id']);
    $credential_url = trim($_POST['credential_url']);
    $description = trim($_POST['description']);

    if (empty($title)) $errors[] = "Title is required.";
    if (empty($issuer)) $errors[] = "Issuer is required.";
    if (empty($date_issued)) $errors[] = "Date issued is required.";
    if (empty($date_received)) $errors[] = "Date received is required.";

    if (!empty($credential_url) && !filter_var($credential_url, FILTER_VALIDATE_URL)) {
        $errors[] = "Credential URL must be a valid URL.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO certifications
            (user_id, title, issuer, date_issued, date_received, expiration_date, credential_id, credential_url, description)
            VALUES
            (:user_id, :title, :issuer, :date_issued, :date_received, :expiration_date, :credential_id, :credential_url, :description)
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':title' => $title,
            ':issuer' => $issuer,
            ':date_issued' => $date_issued,
            ':date_received' => $date_received,
            ':expiration_date' => $expiration_date,
            ':credential_id' => $credential_id,
            ':credential_url' => $credential_url,
            ':description' => $description
        ]);
        $success = "Certification added successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Certification</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f8f9fa; }
        .form-container { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        .required { color: red; }
        button { background: #007BFF; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
        a.back-btn { text-decoration: none; padding: 8px 15px; border: 1px solid #ccc; background: #eee; border-radius: 4px; color: #333; margin-left: 5px; }
        a.back-btn:hover { background: #ddd; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Add Certification</h2>

    <?php if ($errors): ?>
        <div class="error"><?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Title <span class="required">*</span></label>
        <input type="text" name="title" required>

        <label>Issuer <span class="required">*</span></label>
        <input type="text" name="issuer" required>

        <label>Date Issued <span class="required">*</span></label>
        <input type="date" name="date_issued" required>

        <label>Date Received <span class="required">*</span></label>
        <input type="date" name="date_received" required>

        <label>Expiration Date</label>
        <input type="date" name="expiration_date">

        <label>Credential ID</label>
        <input type="text" name="credential_id">

        <label>Credential URL</label>
        <input type="url" name="credential_url">

        <label>Description</label>
        <textarea name="description" rows="4"></textarea>

        <button type="submit">Add Certification</button>
        <a href="../portfolio.php" class="back-btn">Back</a>
    </form>
</div>
</body>
</html>
