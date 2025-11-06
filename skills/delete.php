<?php
session_start();
require_once '../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Skill ID not provided.");
}

$user_id = (int) $_SESSION['user_id'];
$id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM technical_skills WHERE id = :id AND user_id = :user_id");
    $stmt->execute([':id' => $id, ':user_id' => $user_id]);
    header("Location: ../portfolio.php?msg=Skill+deleted+successfully");
    exit();
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>
