<link rel="stylesheet" href="../crud.css">

<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM education WHERE id = :id AND user_id = :user_id");
$stmt->execute([':id' => $id, ':user_id' => $_SESSION['user_id']]);
$edu = $stmt->fetch();

if (!$edu) { die("Not found"); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $update = $pdo->prepare("UPDATE education SET degree=:degree, school=:school, start_date=:start_date, end_date=:end_date, description=:description WHERE id=:id AND user_id=:user_id");
    $update->execute([
        ':degree' => $_POST['degree'],
        ':school' => $_POST['school'],
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
    Degree: <input type="text" name="degree" value="<?= htmlspecialchars($edu['degree']) ?>" required><br>
    School: <input type="text" name="school" value="<?= htmlspecialchars($edu['school']) ?>" required><br>
    Start Date: <input type="date" name="start_date" value="<?= $edu['start_date'] ?>"><br>
    End Date: <input type="date" name="end_date" value="<?= $edu['end_date'] ?>"><br>
    Description:<br>
    <textarea name="description"><?= htmlspecialchars($edu['description']) ?></textarea><br>
    <button type="submit">Update</button>
    <button type="button" onclick="window.location.href='../portfolio.php'"> Back</button>
</form>
