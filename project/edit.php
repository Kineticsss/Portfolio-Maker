<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id AND user_id = :user_id");
$stmt->execute([':id' => $id, ':user_id' => $_SESSION['user_id']]);
$proj = $stmt->fetch();
if (!$proj) { die("Not found"); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $update = $pdo->prepare("UPDATE projects SET title=:title, start_date=:start_date, end_date=:end_date, description=:description, link=:link WHERE id=:id AND user_id=:user_id");
    $update->execute([
        ':title' => $_POST['title'],
        ':start_date' => $_POST['start_date'],
        ':end_date' => $_POST['end_date'],
        ':description' => $_POST['description'],
        ':link' => $_POST['link'],
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    header("Location: ../portfolio.php");
    exit();
}
?>
<form method="post">
    Title: <input type="text" name="title" value="<?= htmlspecialchars($proj['title']) ?>" required><br>
    Start Date: <input type="date" name="start_date" value="<?= $proj['start_date'] ?>"><br>
    End Date: <input type="date" name="end_date" value="<?= $proj['end_date'] ?>"><br>
    Description:<br>
    <textarea name="description"><?= htmlspecialchars($proj['description']) ?></textarea><br>
    Link: <input type="url" name="link" value="<?= htmlspecialchars($proj['link']) ?>"><br>
    <button type="submit">Update</button>
</form>
