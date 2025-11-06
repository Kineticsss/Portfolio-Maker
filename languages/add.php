<?php
session_start();
require __DIR__ . '/../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language_name = trim($_POST['language_name'] ?? '');
    $proficiency = trim($_POST['proficiency'] ?? '');

    if (empty($language_name) || empty($proficiency)) {
        $error = "Please fill out all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO languages (user_id, language_name, proficiency) VALUES (:user_id, :language_name, :proficiency)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':language_name' => $language_name,
                ':proficiency' => $proficiency
            ]);
            header("Location: ../portfolio.php");
            exit();
        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Language</title>
    <link rel="stylesheet" href="../form_style.css">
</head>
<body>
<div class="form-container">
    <h2>Add Language</h2>
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

    <?php if ($error): ?><p class="error"><?= $error; ?></p><?php endif; ?>

    <form method="post">
        <label>Langugae<span class="required">*</span></label>
        <input type="text" name="Language" required>

        <label>Proficiency <span style="color:red">*</span></label>
        <select name="proficiency" required>
            <option value="">Select proficiency</option>
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
            <option value="Fluent">Fluent</option>
            <option value="Native">Native</option>
        </select>

        <div class="form-buttons">
            <button type="submit">Add Language</button>
            <a href="../portfolio.php" class="back-btn">Back</a>
        </div>
    </form>
</div>
</body>
</html>
