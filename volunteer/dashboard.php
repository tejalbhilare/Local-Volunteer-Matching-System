<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only volunteers can access this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

$volunteer_id = $_SESSION["user_id"];
$volunteer_name = $_SESSION["name"];

// Count total opportunities
$opportunity_query = $conn->query(
    "SELECT COUNT(*) AS total FROM opportunities"
);

$total_opportunities = 0;

if ($opportunity_query) {
    $row = $opportunity_query->fetch_assoc();
    $total_opportunities = $row["total"];
}

// Count volunteer applications
$application_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE volunteer_id = ?"
);

$application_stmt->bind_param("i", $volunteer_id);
$application_stmt->execute();

$application_result = $application_stmt->get_result();
$application_row = $application_result->fetch_assoc();

$total_applications = $application_row["total"];

// Count accepted applications
$accepted_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE volunteer_id = ?
     AND status = 'accepted'"
);

$accepted_stmt->bind_param("i", $volunteer_id);
$accepted_stmt->execute();

$accepted_result = $accepted_stmt->get_result();
$accepted_row = $accepted_result->fetch_assoc();

$accepted_applications = $accepted_row["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Volunteer Dashboard - Local Volunteer Matching System</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            color: #333;
        }

        nav {
            background-color: #2c3e50;
            padding: 16px 30px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 25px;
            font-size: 15px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .welcome {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .welcome h1 {
            margin-top: 0;
            color: #2c3e50;
        }

        .welcome p {
            color: #666;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .card h2 {
            font-size: 36px;
            margin: 10px 0;
            color: #3498db;
        }

        .card p {
            margin: 0;
            color: #666;
        }

        .actions {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .actions h2 {
            margin-top: 0;
            color: #2c3e50;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            margin: 8px 8px 8px 0;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background-color: #2980b9;
        }

        .logout {
            float: right;
        }

    </style>

</head>

<body>

<nav>

    <a href="dashboard.php">Dashboard</a>

    <a href="../opportunities.php">Opportunities</a>

    <a href="profile.php">My Profile</a>

    <a href="applications.php">My Applications</a>

    <a href="../index.php">Home</a>

    <a class="logout" href="../logout.php">Logout</a>

</nav>

<div class="container">

    <div class="welcome">

        <h1>
            Welcome, <?php echo htmlspecialchars($volunteer_name); ?>! 👋
        </h1>

        <p>
            Welcome to your Volunteer Dashboard.
        </p>

        <p>
            Find volunteer opportunities and apply for activities
            that match your skills and interests.
        </p>

    </div>

    <div class="cards">

        <div class="card">

            <p>Total Opportunities</p>

            <h2>
                <?php echo $total_opportunities; ?>
            </h2>

        </div>

        <div class="card">

            <p>My Applications</p>

            <h2>
                <?php echo $total_applications; ?>
            </h2>

        </div>

        <div class="card">

            <p>Accepted Applications</p>

            <h2>
                <?php echo $accepted_applications; ?>
            </h2>

        </div>

    </div>

    <div class="actions">

        <h2>Quick Actions</h2>

        <a class="button" href="../opportunities.php">
            Browse Opportunities
        </a>

        <a class="button" href="profile.php">
            My Profile
        </a>

        <a class="button" href="applications.php">
            My Applications
        </a>

    </div>

</div>

</body>

</html>