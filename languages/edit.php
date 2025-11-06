<?php
session_start();
require __DIR__ . '/../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../portfolio.php");
    exit();
}

$error = $success = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM languages WHERE id = :id AND user_id = :user_id");
    $stmt->execute([':id' => $id, ':user_id' => $user_id]);
    $language = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$language) {
        header("Location: ../portfolio.php");
        exit();
    }
} catch (PDOException $e) {
    $error = "Database error: " . htmlspecialchars($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language_name = trim($_POST['language_name'] ?? '');
    $proficiency = trim($_POST['proficiency'] ?? '');

    if (empty($language_name) || empty($proficiency)) {
        $error = "Please fill out all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE languages SET language_name = :language_name, proficiency = :proficiency WHERE id = :id AND user_id = :user_id");
            $stmt->execute([
                ':language_name' => $language_name,
                ':proficiency' => $proficiency,
                ':id' => $id,
                ':user_id' => $user_id
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
    <title>Edit Language</title>
    <link rel="stylesheet" href="../form_style.css">
</head>
<body>
<div class="form-container">
    <h2>Edit Language</h2>

    <?php if ($error): ?><p class="error"><?= $error; ?></p><?php endif; ?>

    <form method="post">
        <label>Language Name <span style="color:red">*</span></label>
        <input type="text" name="language_name" value="<?= htmlspecialchars($language['language_name']); ?>" required>

        <label>Proficiency <span style="color:red">*</span></label>
        <select name="proficiency" required>
            <?php
            $levels = ["Beginner", "Intermediate", "Advanced", "Fluent", "Native"];
            foreach ($levels as $level):
                $selected = ($language['proficiency'] === $level) ? 'selected' : '';
                echo "<option value=\"$level\" $selected>$level</option>";
            endforeach;
            ?>
        </select>

        <div class="form-buttons">
            <button type="submit" class="btn-submit">Save Changes</button>
            <a href="../portfolio.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
