<link rel="stylesheet" href="../crud.css">

<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $skill_name = trim($_POST['skill_name']);
    $proficiency = trim($_POST['proficiency']);

    if ($skill_name === '') {
        $error = "Skill name cannot be empty.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO skills (user_id, skill_name, proficiency)
                               VALUES (:user_id, :skill_name, :proficiency)");
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':skill_name'  => $skill_name,
            ':proficiency' => $proficiency,
        ]);

        $_SESSION['success'] = "✅ Skill added successfully!";
        header("Location: ../portfolio.php");
        exit();
    }
}
?>
<link rel="stylesheet" href="../style.css">
<div class="login-box">
    <h2>Add Skill</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="skill_name" placeholder="Skill name" required><br>
        <input type="text" name="proficiency" placeholder="Proficiency (optional)"><br>
        <div class="button-group">
            <button type="submit">Save</button>
            <button type="button" onclick="window.location.href='../portfolio.php'">Back</button>
        </div>
    </form>
</div>
