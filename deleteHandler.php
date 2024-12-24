<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include("connectDB.inc.php");

if (!isset($_GET['code']) || !preg_match('/^[a-f0-9]{20}$/', $_GET['code'])) {
    exit("Invalid or missing code.");
}

$code = $_GET['code'];
$experience_id = $_SESSION['code'][$code] ?? null;

if ($experience_id == 0) {
    exit("Invalid operation: Cannot delete a new or non-existent entry.");
}

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    $pdo->beginTransaction();

    $query_delete_maneuvers = "DELETE FROM DrivingExperience_Maneuvers WHERE experience_id = :experience_id";
    $stmt_delete_maneuvers = $pdo->prepare($query_delete_maneuvers);
    $stmt_delete_maneuvers->execute([':experience_id' => $experience_id]);

    $query_delete_experience = "DELETE FROM Driving_Experience WHERE experience_id = :experience_id";
    $stmt_delete_experience = $pdo->prepare($query_delete_experience);
    $stmt_delete_experience->execute([':experience_id' => $experience_id]);

    $pdo->commit();

    header("Location: dashboard.php?status=deleted");
    exit();
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error deleting data: " . $e->getMessage());
    header("Location: dashboard.php?status=error");
    exit();
}

