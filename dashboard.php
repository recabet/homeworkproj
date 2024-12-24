
<?php

use Random\RandomException;

include("connectDB.inc.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['code'] = [];

$db = Database::getInstance();
$pdo = $db->getConnection();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driving Experience List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-bottom: 30px;
        }

        #experienceTable {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        #experienceTable th, #experienceTable td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        #experienceTable th {
            background-color: #4CAF50;
            color: white;
        }

        #experienceTable tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        #experienceTable tr:hover {
            background-color: #f1f1f1;
        }

        .text-center {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #45a049;
        }

        #experienceTable a {
            text-decoration: none;
            color: #4CAF50;
            margin-right: 10px;
            font-weight: bold;
            transition: color 0.3s;
        }

        #experienceTable a:hover {
            color: #45a049;
        }

        @media (max-width: 768px) {
            #experienceTable th, #experienceTable td {
                font-size: 14px;
                padding: 8px;
            }

            .container {
                width: 100%;
                padding: 10px;
            }

            h1 {
                font-size: 2em;
            }

            .btn {
                padding: 8px 16px;
                font-size: 1em;
            }
        }
    </style>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
</head>
<body>
<div class="container">
    <h1>Driving Experience List</h1>
    <div class="table-wrapper">
        <table id="experienceTable">
            <thead>
            <tr>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Distance</th>
                <th>Weather</th>
                <th>Road</th>
                <th>Traffic</th>
                <th>Maneuvers</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $query = "
                    SELECT 
                        de.experience_id,
                        de.start_time,
                        de.end_time,
                        de.distance,
                        w.weather_condition AS weather_name,
                        r.road_condition AS road_name,
                        t.traffic_condition AS traffic_name,
                        GROUP_CONCAT(m.maneuver_type ORDER BY m.maneuver_id) AS maneuvers
                    FROM 
                        Driving_Experience de
                    JOIN 
                        Weather w ON de.weather_id = w.weather_id
                    JOIN 
                        Road r ON de.road_id = r.road_id
                    JOIN 
                        Traffic t ON de.traffic_id = t.traffic_id
                    LEFT JOIN 
                        DrivingExperience_Maneuvers dm ON de.experience_id = dm.experience_id
                    LEFT JOIN 
                        Maneuvers m ON dm.maneuver_id = m.maneuver_id
                    GROUP BY 
                        de.experience_id
                    ORDER BY 
                        de.experience_id;
                ";

            try {
                $stmt = $pdo->query($query);
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch()) {
                        $code = bin2hex(random_bytes(10));
                        $_SESSION['code'][$code] = $row['experience_id'];

                        // Sanitize output to prevent XSS
                        echo "<tr>
                                <td>" . htmlspecialchars($row['start_time'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['end_time'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['distance'], ENT_QUOTES, 'UTF-8') . " km</td>
                                <td>" . htmlspecialchars($row['weather_name'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['road_name'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['traffic_name'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>" . htmlspecialchars($row['maneuvers'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td>
                                    <a href=\"webForm.php?mode=edit&code=$code\">Edit</a>
                                    <a href=\"deleteHandler.php?code=$code\" onclick=\"return confirm('Are you sure you want to delete this entry?');\">Delete</a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No driving experiences found.</td></tr>";
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                exit();
            } catch (RandomException $e) {
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="text-center">
        <?php
        try {
            $code = bin2hex(random_bytes(10));
        } catch (RandomException $e) {

        }
        $_SESSION['code'][$code] = 0;
        ?>
        <a href="webForm.php?mode=new&code=<?php echo $code; ?>" class="btn">Add New Driving Experience</a>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#experienceTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true
        });
    });
</script>
</body>
</html>
