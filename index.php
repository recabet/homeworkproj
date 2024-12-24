<?php

session_start();

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Generate a unique code with error handling
try {
    $code = bin2hex(random_bytes(10));  // Generate a random 10-byte string
} catch (Exception $e) {
    // If random_bytes fails, fallback to a default value
    $code = 'defaultcode';
}

// Store the generated code in the session
$_SESSION['code'][$code] = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body {
            min-height: 100vh;
            font-family: 'Verdana', sans-serif;
            font-size: 1em;
            margin: 0;
            padding: 0;
            background-size: cover;
            background-image: url('initial.jpg'); /* Set the background image */
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Hero Section */
        .hero {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100%;
            background-image: url('initial.jpg'); /* Set your background image here */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            padding: 0;
            box-sizing: border-box;
            text-align: center;
        }

        /* Hero Content */
        .hero-content {
            max-width: 600px;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.5); /* Dark overlay for better text visibility */
            border-radius: 10px;
        }

        .hero h1 {
            font-size: 50px;
            margin-bottom: 20px;
        }

        .hero .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .hero .btn {
            background-color: white;
            color: #4caf50;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .hero .btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        /* Media Queries */
        @media (max-width: 1200px) {
            .hero {
                background-size: cover;
            }

            .hero-content {
                max-width: 500px;
            }
        }

        @media (max-width: 768px) {
            .hero {
                height: 80vh;
            }

            .hero h1 {
                font-size: 30px;
            }

            .hero-content {
                max-width: 100%;
                padding: 15px;
            }

            .hero .buttons {
                flex-direction: column;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .hero {
                height: 60vh;
            }

            .hero h1 {
                font-size: 24px;
            }

            .hero-content {
                padding: 10px;
            }

            .hero .btn {
                padding: 12px 25px;
            }
        }
    </style>
</head>

<body>

<div class="hero">
    <div class="hero-content">
        <h1>Welcome</h1>
        <div class="buttons">
            <a href="dashboard.php" class="btn">Dashboard</a>
            <a href="webForm.php?mode=new&code=<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>" class="btn">Web Form</a>
        </div>
    </div>
</div>

</body>

</html>
