<link rel="stylesheet" href="../crud.css">

<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM skills WHERE id=:id AND user_id=:user_id");
$stmt->execute([':id'=>$id, ':user_id'=>$_SESSION['user_id']]);
$skill = $stmt->fetch();

if (!$skill) {
    die("Skill not found or access denied.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $skill_name = trim($_POST['skill_name']);
    $proficiency = trim($_POST['proficiency']);

    if ($skill_name === '') {
        $error = "Skill name cannot be empty.";
    } else {
        $update = $pdo->prepare("UPDATE skills 
                                 SET skill_name=:skill_name, proficiency=:proficiency 
                                 WHERE id=:id AND user_id=:user_id");
        $update->execute([
            ':skill_name' => $skill_name,
            ':proficiency' => $proficiency,
            ':id' => $id,
            ':user_id' => $_SESSION['user_id']
        ]);

        $_SESSION['success'] = "✅ Skill updated successfully!";
        header("Location: ../portfolio.php");
        exit();
    }
}
?>
<link rel="stylesheet" href="../style.css">
<div class="login-box">
    <h2>Edit Skill</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="skill_name" value="<?= htmlspecialchars($skill['skill_name']) ?>" required><br>
        <input type="text" name="proficiency" value="<?= htmlspecialchars($skill['proficiency']) ?>"><br>
        <div class="button-group">
            <button type="submit">Save</button>
            <button type="button" onclick="window.location.href='../portfolio.php'">Back</button>
        </div>
    </form>
</div>
