<?php
session_start();
require_once __DIR__ . '/dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload' && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Remove old image first
        $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $oldPic = $stmt->fetchColumn();
        if ($oldPic && file_exists($oldPic)) unlink($oldPic);

        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $filename = "profile_" . $user_id . "." . $ext;
        $filepath = $upload_dir . $filename;

        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filepath);

        $stmt = $pdo->prepare("UPDATE users SET profile_pic = :path WHERE id = :id");
        $stmt->execute([':path' => $filepath, ':id' => $user_id]);

    } elseif ($action === 'remove') {
        $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $file = $stmt->fetchColumn();

        if ($file && file_exists($file)) unlink($file);

        $stmt = $pdo->prepare("UPDATE users SET profile_pic = NULL WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
    }
}

header("Location: portfolio.php");
exit();
?>
