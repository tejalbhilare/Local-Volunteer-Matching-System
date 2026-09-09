<?php

session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only admin can access
if ($_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Count users
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM users"
);

$total_users = $result->fetch_assoc()["total"];

// Count volunteers
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'volunteer'"
);

$total_volunteers = $result->fetch_assoc()["total"];

// Count organizations
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'organization'"
);

$total_organizations = $result->fetch_assoc()["total"];

// Count opportunities
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM opportunities"
);

$total_opportunities = $result->fetch_assoc()["total"];

// Count applications
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications"
);

$total_applications = $result->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Local Volunteer Matching System</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
        }

        nav {
            background-color: #2c3e50;
            padding: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
        }

        h1 {
            color: #2c3e50;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .card h2 {
            font-size: 35px;
            margin: 10px 0;
            color: #3498db;
        }

        .card p {
            color: #555;
        }

    </style>

</head>

<body>

<nav>

    <a href="dashboard.php">Admin Dashboard</a>

    <a href="../index.php">Home</a>

    <a href="../logout.php">Logout</a>

</nav>

<div class="container">

    <h1>
        Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!
    </h1>

    <p>
        This is the administration dashboard.
    </p>

    <div class="cards">

        <div class="card">

            <p>Total Users</p>

            <h2>
                <?php echo $total_users; ?>
            </h2>

        </div>

        <div class="card">

            <p>Volunteers</p>

            <h2>
                <?php echo $total_volunteers; ?>
            </h2>

        </div>

        <div class="card">

            <p>Organizations</p>

            <h2>
                <?php echo $total_organizations; ?>
            </h2>

        </div>

        <div class="card">

            <p>Opportunities</p>

            <h2>
                <?php echo $total_opportunities; ?>
            </h2>

        </div>

        <div class="card">

            <p>Applications</p>

            <h2>
                <?php echo $total_applications; ?>
            </h2>

        </div>

    </div>

</div>

</body>

</html>