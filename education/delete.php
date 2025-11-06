<?php
session_start();
require '../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Missing education ID.");
}

$user_id = $_SESSION['user_id'];
$edu_id = $_GET['id'];

// Delete only if the record belongs to the user
$stmt = $pdo->prepare("DELETE FROM education WHERE id = :id AND user_id = :user_id");
$stmt->execute([':id' => $edu_id, ':user_id' => $user_id]);

header("Location: ../portfolio.php?msg=deleted");
exit();
?>
