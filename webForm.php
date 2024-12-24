<?php

include("connectDB.inc.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['mode']) || !isset($_GET['code']) || !isset($_SESSION['code'][$_GET['code']])) {
    exit("Invalid or missing mode or code.");
}

$mode = $_GET['mode'];
$code = $_GET['code'];
$experience_id = $_SESSION['code'][$code];

$db = Database::getInstance();
$pdo = $db->getConnection();

$weatherOptions = $pdo->query("SELECT * FROM Weather")->fetchAll(PDO::FETCH_ASSOC);
$roadOptions = $pdo->query("SELECT * FROM Road")->fetchAll(PDO::FETCH_ASSOC);
$trafficOptions = $pdo->query("SELECT * FROM Traffic")->fetchAll(PDO::FETCH_ASSOC);
$maneuverOptions = $pdo->query("SELECT * FROM Maneuvers")->fetchAll(PDO::FETCH_ASSOC);

$startTime = $endTime = $distance = $weather_id = $road_id = $traffic_id = '';
$selectedManeuvers = [];

if ($mode === 'edit' && $experience_id != 0) {
    $query = "
        SELECT 
            de.start_time, 
            de.end_time, 
            de.distance, 
            de.weather_id, 
            de.road_id, 
            de.traffic_id, 
            GROUP_CONCAT(dm.maneuver_id) AS maneuvers
        FROM 
            Driving_Experience de
        LEFT JOIN 
            DrivingExperience_Maneuvers dm ON de.experience_id = dm.experience_id
        WHERE 
            de.experience_id = :experience_id
        GROUP BY 
            de.experience_id;
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':experience_id' => $experience_id]);
    $experienceData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($experienceData) {
        $startTime = $experienceData['start_time'];
        $endTime = $experienceData['end_time'];
        $distance = $experienceData['distance'];
        $weather_id = $experienceData['weather_id'];
        $road_id = $experienceData['road_id'];
        $traffic_id = $experienceData['traffic_id'];
        $selectedManeuvers = explode(',', $experienceData['maneuvers']);
    }
} elseif ($mode === 'new') {
    $experience_id = 0;
} else {
    exit("Invalid mode.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mode === 'new' ? "Add New Driving Experience" : "Edit Driving Experience"; ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('initial.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            height: 100vh;
        }

        .form-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-header h1 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 10px;
        }

        label {
            font-size: 14px;
            color: #555;
        }

        input[type="time"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            background-color: #f9f9f9;
        }

        select[multiple="multiple"] {
            height: auto;
            max-height: 200px;
        }

        button[type="submit"] {
            grid-column: span 2;
            padding: 15px;
            font-size: 16px;
            color: white;
            background-color: #4caf50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr;
            }

            button[type="submit"] {
                grid-column: span 1;
            }
        }
    </style>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

</head>
<body>

<div class="form-container">
    <div class="form-header">
        <h1><?php echo $mode === 'new' ? "Add New Driving Experience" : "Edit Driving Experience"; ?></h1>
    </div>

    <form action="webFormUpdate.php" method="POST">
        <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">

        <label for="startTime">Start Time:</label>
        <label>
            <input type="time" name="startTime" value="<?php echo htmlspecialchars($startTime); ?>" required>
        </label>

        <label for="endTime">End Time:</label>
        <label>
            <input type="time" name="endTime" value="<?php echo htmlspecialchars($endTime); ?>" required>
        </label>

        <label for="distance">Distance (km):</label>
        <label>
            <input type="number" name="distance" value="<?php echo htmlspecialchars($distance); ?>" required>
        </label>

        <label for="weather">Weather:</label>
        <label>
            <select name="weather_id" required>
                <?php foreach ($weatherOptions as $weather): ?>
                    <option value="<?php echo $weather['weather_id']; ?>" <?php echo $weather['weather_id'] == $weather_id ? 'selected' : ''; ?>>
                        <?php echo $weather['weather_condition']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label for="road">Road Condition:</label>
        <label>
            <select name="road_id" required>
                <?php foreach ($roadOptions as $road): ?>
                    <option value="<?php echo $road['road_id']; ?>" <?php echo $road['road_id'] == $road_id ? 'selected' : ''; ?>>
                        <?php echo $road['road_condition']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label for="traffic">Traffic:</label>
        <label>
            <select name="traffic_id" required>
                <?php foreach ($trafficOptions as $traffic): ?>
                    <option value="<?php echo $traffic['traffic_id']; ?>" <?php echo $traffic['traffic_id'] == $traffic_id ? 'selected' : ''; ?>>
                        <?php echo $traffic['traffic_condition']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label for="maneuvers">Maneuvers:</label>
        <label>
            <select name="maneuvers[]" multiple="multiple">
                <?php foreach ($maneuverOptions as $maneuver): ?>
                    <option value="<?php echo $maneuver['maneuver_id']; ?>" <?php echo in_array($maneuver['maneuver_id'], $selectedManeuvers) ? 'selected' : ''; ?>>
                        <?php echo $maneuver['maneuver_type']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit">Submit</button>
    </form>

</div>

<script>
    $(document).ready(function() {
        $('select[multiple="multiple"]').select2({
            placeholder: "Select maneuvers"
        });
    });
</script>

</body>
</html>
