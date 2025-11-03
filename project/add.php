<link rel="stylesheet" href="../crud.css">

<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("INSERT INTO projects (user_id, title, description, start_date, end_date, link)
                           VALUES (:user_id, :title, :description, :start_date, :end_date, :link)");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':title'   => $_POST['title'],
        ':description' => $_POST['description'],
        ':start_date' => $_POST['start_date'],
        ':end_date'   => $_POST['end_date'],
        ':link'    => $_POST['link'],
    ]);
    header("Location: ../portfolio.php");
    exit();
}
?>
<form method="post">
    Project Title: <input type="text" name="title" required><br>
    Description:<br>
    <textarea name="description"></textarea><br>
    Start Date: <input type="date" name="start_date"><br>
    End Date: <input type="date" name="end_date"><br>
    Link: <input type="url" name="link"><br>
    <button type="submit">Save</button>
    <button type="button" onclick="window.location.href='../portfolio.php'"> Back</button>
</form>
