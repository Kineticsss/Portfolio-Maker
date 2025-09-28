<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';


if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM skills WHERE id=:id AND user_id=:user_id");
$stmt->execute([':id'=>$id, ':user_id'=>$_SESSION['user_id']]);
$skill = $stmt->fetch();
if (!$skill) { die("Not found"); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $update = $pdo->prepare("UPDATE skills SET skill_name=:skill_name, proficiency=:proficiency WHERE id=:id AND user_id=:user_id");
    $update->execute([
        ':skill_name' => $_POST['skill_name'],
        ':proficiency' => $_POST['proficiency'],
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    header("Location: ../portfolio.php");
    exit();
}
?>
<form method="post">
    Skill: <input type="text" name="skill_name" value="<?= htmlspecialchars($skill['skill_name']) ?>" required><br>
    Proficiency: <input type="text" name="proficiency" value="<?= htmlspecialchars($skill['proficiency']) ?>"><br>
    <button type="submit">Update</button>
</form>
