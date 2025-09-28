<?php
session_start();
require 'dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($_FILES['profile_pic']['name']);
    $targetFilePath = $targetDir . $user_id . "_" . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFilePath)) {
            // Save file path in DB
            $stmt = $pdo->prepare("UPDATE users SET profile_pic = :pic WHERE id = :id");
            $stmt->execute([':pic' => $targetFilePath, ':id' => $user_id]);
            header("Location: portfolio.php");
            exit();
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Only JPG, JPEG, PNG & GIF files are allowed.";
    }
}
