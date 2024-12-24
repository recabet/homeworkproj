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

    // Validate the code to ensure it exists in the session
    if (!$code || !isset($_SESSION['code'][$code])) {
        exit("Invalid or expired code.");
    }

    // Get the experience ID associated with the code
    $experience_id = $_SESSION['code'][$code];
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';
    $distance = $_POST['distance'] ?? 0;
    $weather_id = $_POST['weather_id'] ?? 0;
    $road_id = $_POST['road_id'] ?? 0;
    $traffic_id = $_POST['traffic_id'] ?? 0;
    $maneuvers = $_POST['maneuvers'] ?? [];

    // Check if any required fields are empty
    if (empty($startTime) || empty($endTime) || empty($distance) || empty($weather_id) || empty($road_id) || empty($traffic_id)) {
        echo "Please fill in all required fields.";
        exit();
    }

    // Ensure at least one maneuver is selected
    if (empty($maneuvers)) {
        echo "Please select at least one maneuver.";
        exit();
    }

    try {
        // Insert or update the Driving Experience
        if (!$experience_id) {
            $query = "INSERT INTO Driving_Experience (start_time, end_time, distance, weather_id, road_id, traffic_id)
                      VALUES (:startTime, :endTime, :distance, :weather_id, :road_id, :traffic_id)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':startTime', $startTime);
            $stmt->bindParam(':endTime', $endTime);
            $stmt->bindParam(':distance', $distance, PDO::PARAM_INT);
            $stmt->bindParam(':weather_id', $weather_id, PDO::PARAM_INT);
            $stmt->bindParam(':road_id', $road_id, PDO::PARAM_INT);
            $stmt->bindParam(':traffic_id', $traffic_id, PDO::PARAM_INT);
            $stmt->execute();

            // Get the last inserted ID for the experience
            $experience_id = $pdo->lastInsertId();
        } else {
            $query = "UPDATE Driving_Experience 
                      SET start_time = :startTime, end_time = :endTime, distance = :distance, 
                          weather_id = :weather_id, road_id = :road_id, traffic_id = :traffic_id
                      WHERE experience_id = :experience_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':startTime', $startTime);
            $stmt->bindParam(':endTime', $endTime);
            $stmt->bindParam(':distance', $distance, PDO::PARAM_INT);
            $stmt->bindParam(':weather_id', $weather_id, PDO::PARAM_INT);
            $stmt->bindParam(':road_id', $road_id, PDO::PARAM_INT);
            $stmt->bindParam(':traffic_id', $traffic_id, PDO::PARAM_INT);
            $stmt->bindParam(':experience_id', $experience_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        // Clear existing maneuvers
        $query_clear_maneuvers = "DELETE FROM DrivingExperience_Maneuvers WHERE experience_id = :experience_id";
        $stmt_clear = $pdo->prepare($query_clear_maneuvers);
        $stmt_clear->execute([':experience_id' => $experience_id]);

        // Insert the selected maneuvers
        $query_maneuvers = "INSERT INTO DrivingExperience_Maneuvers (experience_id, maneuver_id) VALUES (:experience_id, :maneuver_id)";
        $stmt_maneuvers = $pdo->prepare($query_maneuvers);
        foreach ($maneuvers as $maneuver_id) {
            $stmt_maneuvers->execute([':experience_id' => $experience_id, ':maneuver_id' => $maneuver_id]);
        }

        // Redirect to the dashboard with a success status
        header("Location: dashboard.php?status=success");
        exit();
    } catch (PDOException $e) {
        // Log the error and redirect to an error page
        error_log("Error saving data: " . $e->getMessage());
        header("Location: errorPage.php?error=SavingFailed");
        exit();
    }
}
