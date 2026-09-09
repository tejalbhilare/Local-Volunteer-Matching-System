<?php

session_start();

require_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Check if user is an organization
if ($_SESSION["role"] !== "organization") {
    die("Access denied. Organization account required.");
}

$user_id = $_SESSION["user_id"];

// Get organization information
$sql = "
    SELECT 
        u.name,
        u.email,
        o.id AS organization_id,
        o.organization_name,
        o.description,
        o.city,
        o.phone
    FROM users u
    LEFT JOIN organizations o ON u.id = o.user_id
    WHERE u.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$organization = $result->fetch_assoc();

$stmt->close();

// If organization profile doesn't exist
if (!$organization) {
    die("Organization information not found.");
}

$organization_id = $organization["organization_id"];

// Count opportunities
$opportunity_count = 0;

if ($organization_id) {

    $count_sql = "
        SELECT COUNT(*) AS total
        FROM opportunities
        WHERE organization_id = ?
    ";

    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $organization_id);
    $count_stmt->execute();

    $count_result = $count_stmt->get_result();
    $count_data = $count_result->fetch_assoc();

    $opportunity_count = $count_data["total"];

    $count_stmt->close();
}

// Count applications for this organization's opportunities
$application_count = 0;

if ($organization_id) {

    $application_sql = "
        SELECT COUNT(*) AS total
        FROM applications a
        INNER JOIN opportunities op
            ON a.opportunity_id = op.id
        WHERE op.organization_id = ?
    ";

    $application_stmt = $conn->prepare($application_sql);
    $application_stmt->bind_param("i", $organization_id);
    $application_stmt->execute();

    $application_result = $application_stmt->get_result();
    $application_data = $application_result->fetch_assoc();

    $application_count = $application_data["total"];

    $application_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Organization Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #333;
        }

        .navbar {
            background: #222;
            color: white;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .welcome h1 {
            margin-top: 0;
        }

        .welcome p {
            color: #666;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .card h3 {
            margin-top: 0;
            color: #555;
        }

        .number {
            font-size: 35px;
            font-weight: bold;
            margin: 10px 0;
        }

        .actions {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .actions h2 {
            margin-top: 0;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            margin: 8px 8px 8px 0;
            background: #222;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .button:hover {
            background: #444;
        }

        .logout {
            background: #b00020;
        }

        .logout:hover {
            background: #d00025;
        }

        @media (max-width: 700px) {

            .cards {
                grid-template-columns: 1fr;
            }

            .navbar {
                padding: 15px 20px;
            }

            .container {
                width: 95%;
            }

        }

    </style>

</head>

<body>

    <!-- NAVIGATION -->

    <div class="navbar">

        <h2>Volunteer Match</h2>

        <div>

            <a href="../index.php">Home</a>

            <a href="../opportunities.php">Opportunities</a>

            <a href="../logout.php">Logout</a>

        </div>

    </div>


    <!-- MAIN CONTENT -->

    <div class="container">

        <!-- WELCOME -->

        <div class="welcome">

            <h1>
                Welcome, <?php echo htmlspecialchars($organization["organization_name"]); ?>!
            </h1>

            <p>
                Manage your volunteer opportunities and applications from your dashboard.
            </p>

        </div>


        <!-- STATISTICS -->

        <div class="cards">

            <div class="card">

                <h3>Total Opportunities</h3>

                <div class="number">
                    <?php echo $opportunity_count; ?>
                </div>

                <p>Opportunities created by your organization.</p>

            </div>


            <div class="card">

                <h3>Total Applications</h3>

                <div class="number">
                    <?php echo $application_count; ?>
                </div>

                <p>Volunteer applications received.</p>

            </div>

        </div>


        <!-- ACTIONS -->

        <div class="actions">

            <h2>Organization Actions</h2>

            <p>What would you like to do?</p>

            <a
                href="create-opportunity.php"
                class="button"
            >
                + Create Opportunity
            </a>

            <a
                href="manage-opportunities.php"
                class="button"
            >
                Manage Opportunities
            </a>

            <a
                href="applicants.php"
                class="button"
            >
                View Applicants
            </a>

            <a
                href="../logout.php"
                class="button logout"
            >
                Logout
            </a>

        </div>

    </div>

</body>

</html>