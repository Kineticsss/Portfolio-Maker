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

$stmt = $pdo->prepare("SELECT * FROM education WHERE id = :id AND user_id = :user_id");
$stmt->execute([':id' => $id, ':user_id' => $user_id]);
$edu = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$edu) die("Education record not found.");

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_name = trim($_POST['school_name']);
    $degree = trim($_POST['degree']);
    $field_of_study = trim($_POST['field_of_study']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = trim($_POST['description']);

    if (empty($school_name)) $errors[] = "School name is required.";
    if (empty($degree)) $errors[] = "Degree is required.";
    if (empty($start_date)) $errors[] = "Start date is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE education SET
                school_name = :school_name,
                degree = :degree,
                field_of_study = :field_of_study,
                start_date = :start_date,
                end_date = :end_date,
                description = :description
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute([
            ':school_name' => $school_name,
            ':degree' => $degree,
            ':field_of_study' => $field_of_study,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':description' => $description,
            ':id' => $id,
            ':user_id' => $user_id
        ]);
        $success = "Education updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Education</title>
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
    <h2>Edit Education</h2>

    <?php if ($errors): ?>
        <div class="error"><?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>School Name <span class="required">*</span></label>
        <input type="text" name="school_name" value="<?= htmlspecialchars($edu['school_name']) ?>" required>

        <label>Degree <span class="required">*</span></label>
        <input type="text" name="degree" value="<?= htmlspecialchars($edu['degree']) ?>" required>

        <label>Field of Study</label>
        <input type="text" name="field_of_study" value="<?= htmlspecialchars($edu['field_of_study']) ?>">

        <label>Start Date <span class="required">*</span></label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($edu['start_date']) ?>" required>

        <label>End Date</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($edu['end_date']) ?>">

        <label>Description</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($edu['description']) ?></textarea>

        <button type="submit">Save Changes</button>
        <a href="../portfolio.php" class="back-btn">Cancel</a>
    </form>
</div>
</body>
</html>
