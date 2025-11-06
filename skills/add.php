<?php
session_start();
require_once '../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skill_name = trim($_POST['skill_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $proficiency = trim($_POST['proficiency'] ?? '');

    if ($skill_name === '' || $category === '' || $proficiency === '') {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO technical_skills (user_id, skill_name, category, proficiency)
                                   VALUES (:user_id, :skill_name, :category, :proficiency)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':skill_name' => $skill_name,
                ':category' => $category,
                ':proficiency' => $proficiency
            ]);
            $success = "Technical skill added successfully!";
        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Technical Skill</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f8f9fa; }
        .form-container { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
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
    <h2>Add Technical Skill</h2>

    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>

    <form method="post">
        <label>Skill Name <span class="required">*</span></label>
        <input type="text" name="skill_name" required>

        <label>Category <span class="required">*</span></label>
        <select name="category" required>
            <option value="">-- Select Category --</option>
            <option value="Programming Language">Programming Language</option>
            <option value="Framework">Framework</option>
            <option value="Tool">Tool</option>
            <option value="Database">Database</option>
            <option value="Other">Other</option>
        </select>

        <label>Proficiency <span class="required">*</span></label>
        <select name="proficiency" required>
            <option value="">-- Select Proficiency --</option>
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
            <option value="Expert">Expert</option>
        </select>

        <button type="submit">Add Technical Skill</button>
        <a href="../portfolio.php" class="back-btn">Back</a>
    </form>
</div>
</body>
</html>
