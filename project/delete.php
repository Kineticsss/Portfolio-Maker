<?php
require '../dbconfig.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("User not logged in.");

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid ID.");

$stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $id, ':uid' => $user_id]);

header("Location: ../portfolio.php");
exit;
?>
