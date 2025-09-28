<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("INSERT INTO skills (user_id, skill_name, proficiency)
                           VALUES (:user_id, :skill_name, :proficiency)");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':skill_name'  => $_POST['skill_name'],
        ':proficiency' => $_POST['proficiency'],
    ]);
    header("Location: ../portfolio.php");
    exit();
}
?>
<form method="post">
    Skill: <input type="text" name="skill_name" required><br>
    Proficiency: <input type="text" name="proficiency"><br>
    <button type="submit">Save</button>
</form>
