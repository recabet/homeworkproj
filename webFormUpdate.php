<?php

ini_set('display_errors', 1);
include("connectDB.inc.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance();
$pdo = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? null;

    if (!$code || !isset($_SESSION['code'][$code])) {
        exit("Invalid or expired code.");
    }

    $experience_id = $_SESSION['code'][$code];
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';
    $distance = $_POST['distance'] ?? 0;
    $weather_id = $_POST['weather_id'] ?? 0;
    $road_id = $_POST['road_id'] ?? 0;
    $traffic_id = $_POST['traffic_id'] ?? 0;
    $maneuvers = $_POST['maneuvers'] ?? [];

    try {
        $pdo->beginTransaction();

        if ($experience_id == 0) {
            $stmt = $pdo->prepare("
                INSERT INTO Driving_Experience (start_time, end_time, distance, weather_id, road_id, traffic_id)
                VALUES (:start_time, :end_time, :distance, :weather_id, :road_id, :traffic_id)
            ");
            $stmt->execute([
                ':start_time' => $startTime,
                ':end_time' => $endTime,
                ':distance' => $distance,
                ':weather_id' => $weather_id,
                ':road_id' => $road_id,
                ':traffic_id' => $traffic_id
            ]);

            $experience_id = $pdo->lastInsertId();
        } else {
            $stmt = $pdo->prepare("
                UPDATE Driving_Experience
                SET start_time = :start_time, end_time = :end_time, distance = :distance, 
                    weather_id = :weather_id, road_id = :road_id, traffic_id = :traffic_id
                WHERE experience_id = :experience_id
            ");
            $stmt->execute([
                ':start_time' => $startTime,
                ':end_time' => $endTime,
                ':distance' => $distance,
                ':weather_id' => $weather_id,
                ':road_id' => $road_id,
                ':traffic_id' => $traffic_id,
                ':experience_id' => $experience_id
            ]);
        }

        $pdo->prepare("DELETE FROM Driving_Experience WHERE experience_id = :experience_id")
            ->execute([':experience_id' => $experience_id]);

        if (!empty($maneuvers)) {
            $maneuverStmt = $pdo->prepare("
                INSERT INTO DrivingExperience_Maneuvers (experience_id, maneuver_id)
                VALUES (:experience_id, :maneuver_id)
            ");

            foreach ($maneuvers as $maneuver_id) {
                $maneuverStmt->execute([
                    ':experience_id' => $experience_id,
                    ':maneuver_id' => $maneuver_id
                ]);
            }
        }

        $pdo->commit();

        header("Location: success.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        exit("An error occurred: " . $e->getMessage());
    }
}

