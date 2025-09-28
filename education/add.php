<?php
session_start();
require_once __DIR__ . '/../dbconfig.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("INSERT INTO education (user_id, degree, school, start_date, end_date, description)
                           VALUES (:user_id, :degree, :school, :start_date, :end_date, :description)");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':degree'  => $_POST['degree'],
        ':school'  => $_POST['school'],
        ':start_date' => $_POST['start_date'],
        ':end_date'   => $_POST['end_date'],
        ':description'=> $_POST['description'],
    ]);
    header("Location: ../portfolio.php");
    exit();
}
?>
<form method="post">
    Degree: <input type="text" name="degree" required><br>
    School: <input type="text" name="school" required><br>
    Start Date: <input type="date" name="start_date"><br>
    End Date: <input type="date" name="end_date"><br>
    Description:<br>
    <textarea name="description"></textarea><br>
    <button type="submit">Save</button>
</form>
