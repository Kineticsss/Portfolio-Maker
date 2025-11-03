<link rel="stylesheet" href="../crud.css">

<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM skills WHERE id=:id AND user_id=:user_id");
$stmt->execute([':id' => $id, ':user_id' => $_SESSION['user_id']]);

$_SESSION['success'] = "🗑️ Skill deleted successfully!";
header("Location: ../portfolio.php");
exit();


