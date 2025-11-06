<?php
session_start();
require 'dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$new_token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare("UPDATE users SET public_token = :token WHERE id = :id");
$stmt->execute([':token' => $new_token, ':id' => $_SESSION['user_id']]);

header("Location: edit_profile.php");
exit();
