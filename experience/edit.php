<link rel="stylesheet" href="../crud.css">

<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM experience WHERE id = :id AND user_id = :user_id");
$stmt->execute([':id' => $id, ':user_id' => $_SESSION['user_id']]);
$exp = $stmt->fetch();
if (!$exp) { die("Not found"); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $update = $pdo->prepare("UPDATE experience SET title=:title, company=:company, start_date=:start_date, end_date=:end_date, description=:description WHERE id=:id AND user_id=:user_id");
    $update->execute([
        ':title' => $_POST['title'],
        ':company' => $_POST['company'],
        ':start_date' => $_POST['start_date'],
        ':end_date' => $_POST['end_date'],
        ':description' => $_POST['description'],
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    header("Location: ../portfolio.php");
    exit();
}
?>
<form method="post">
    Title: <input type="text" name="title" value="<?= htmlspecialchars($exp['title']) ?>" required><br>
    Company: <input type="text" name="company" value="<?= htmlspecialchars($exp['company']) ?>" required><br>
    Start Date: <input type="date" name="start_date" value="<?= $exp['start_date'] ?>"><br>
    End Date: <input type="date" name="end_date" value="<?= $exp['end_date'] ?>"><br>
    Description:<br>
    <textarea name="description"><?= htmlspecialchars($exp['description']) ?></textarea><br>
    <button type="submit">Update</button>
    <button type="button" onclick="window.location.href='../portfolio.php'"> Back</button>
</form>
