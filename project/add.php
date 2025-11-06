<?php
require '../dbconfig.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("User not logged in.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = trim($_POST['project_name']);
    $description = trim($_POST['description']);
    $technologies = trim($_POST['technologies']);
    $project_link = trim($_POST['project_link']);
    $image_data = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (empty($project_name)) {
        $error = "Project Name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO projects (user_id, project_name, description, technologies, project_link, image_path)
                VALUES (:user_id, :project_name, :description, :technologies, :project_link, :image_path)
            ");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':project_name', $project_name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':technologies', $technologies);
            $stmt->bindParam(':project_link', $project_link);
            $stmt->bindParam(':image_path', $image_data, PDO::PARAM_LOB);
            $stmt->execute();
            header("Location: ../portfolio.php");
            exit;
        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Project</title>
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
    <div class ="form-container">
    <form method="post" enctype="multipart/form-data">
        <h2>Add Project</h2>

        <label>Project Name <span class="required">*</span></label>
        <input type="text" name="project_name" required>

        <label>Description</label>
        <textarea name="description"></textarea>

        <label>Technologies</label>
        <input type="text" name="technologies">

        <label>Project Link</label>
        <input type="url" name="project_link">

        <label>Image</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Add Project</button>
        <a href="../portfolio.php" class="back-btn">Back</a>

        <?php if (!empty($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
    </form>
    </div>
</body>
</html>
