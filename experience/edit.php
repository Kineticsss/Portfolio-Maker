<?php
session_start();
require '../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;
if (!$id) die("Invalid request.");

$stmt = $pdo->prepare("SELECT * FROM experience WHERE id = :id AND user_id = :user_id");
$stmt->execute([':id' => $id, ':user_id' => $user_id]);
$exp = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$exp) die("Experience not found.");

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_title = trim($_POST['job_title']);
    $company_name = trim($_POST['company_name']);
    $location = trim($_POST['location']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = trim($_POST['description']);

    if (empty($job_title)) $errors[] = "Job title is required.";
    if (empty($company_name)) $errors[] = "Company name is required.";
    if (empty($start_date)) $errors[] = "Start date is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE experience SET
                job_title = :job_title,
                company_name = :company_name,
                location = :location,
                start_date = :start_date,
                end_date = :end_date,
                description = :description
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute([
            ':job_title' => $job_title,
            ':company_name' => $company_name,
            ':location' => $location,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':description' => $description,
            ':id' => $id,
            ':user_id' => $user_id
        ]);
        $success = "Experience updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Experience</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f8f9fa; }
        .form-container { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
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
    <h2>Edit Experience</h2>

    <?php if ($errors): ?>
        <div class="error"><?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Job Title <span class="required">*</span></label>
        <input type="text" name="job_title" value="<?= htmlspecialchars($exp['job_title']) ?>" required>

        <label>Company Name <span class="required">*</span></label>
        <input type="text" name="company_name" value="<?= htmlspecialchars($exp['company_name']) ?>" required>

        <label>Location</label>
        <input type="text" name="location" value="<?= htmlspecialchars($exp['location']) ?>">

        <label>Start Date <span class="required">*</span></label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($exp['start_date']) ?>" required>

        <label>End Date</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($exp['end_date']) ?>">

        <label>Description</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($exp['description']) ?></textarea>

        <button type="submit">Save Changes</button>
        <a href="../portfolio.php" class="back-btn">Cancel</a>
    </form>
</div>
</body>
</html>
