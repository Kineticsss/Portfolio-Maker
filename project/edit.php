<?php
require '../dbconfig.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("User not logged in.");
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid ID.");

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $id, ':uid' => $user_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) die("Project not found.");

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
            if ($image_data) {
                $stmt = $pdo->prepare("
                    UPDATE projects
                    SET project_name = :project_name, description = :description, technologies = :technologies,
                        project_link = :project_link, image_path = :image_path
                    WHERE id = :id AND user_id = :uid
                ");
                $stmt->bindParam(':image_path', $image_data, PDO::PARAM_LOB);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE projects
                    SET project_name = :project_name, description = :description, technologies = :technologies,
                        project_link = :project_link
                    WHERE id = :id AND user_id = :uid
                ");
            }

            $stmt->bindParam(':project_name', $project_name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':technologies', $technologies);
            $stmt->bindParam(':project_link', $project_link);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
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
    <title>Edit Project</title>
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
        <h2>Edit Project</h2>

        <label>Project Name <span class="required">*</span></label>
        <input type="text" name="project_name" value="<?= htmlspecialchars($project['project_name']) ?>" required>

        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($project['description']) ?></textarea>

        <label>Technologies</label>
        <input type="text" name="technologies" value="<?= htmlspecialchars($project['technologies']) ?>">

        <label>Project Link</label>
        <input type="url" name="project_link" value="<?= htmlspecialchars($project['project_link']) ?>">

        <label>Replace Image</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Save Changes</button>
        <a href="../portfolio.php" class="back-btn">Cancel</a>

        <?php if (!empty($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
    </form>
    </div>
</body>
</html>
