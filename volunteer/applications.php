<?php

session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only volunteers can access this page
if ($_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

$volunteer_id = $_SESSION["user_id"];

// Get volunteer's applications
$sql = "SELECT 
            applications.id,
            applications.status,
            applications.applied_at,
            opportunities.title,
            opportunities.description,
            opportunities.location,
            opportunities.required_skills,
            opportunities.event_date,
            organizations.organization_name
        FROM applications
        INNER JOIN opportunities
            ON applications.opportunity_id = opportunities.id
        INNER JOIN organizations
            ON opportunities.organization_id = organizations.id
        WHERE applications.volunteer_id = ?
        ORDER BY applications.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Applications - Local Volunteer Matching System</title>

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

        .application {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .application h2 {
            margin-top: 0;
        }

        .status {
            font-weight: bold;
        }

        .pending {
            color: #e67e22;
        }

        .accepted {
            color: #27ae60;
        }

        .rejected {
            color: #c0392b;
        }

        .no-applications {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

    </style>

</head>

<body>

<nav>

    <a href="dashboard.php">Dashboard</a>

    <a href="../opportunities.php">Opportunities</a>

    <a href="profile.php">My Profile</a>

    <a href="applications.php">My Applications</a>

    <a href="../logout.php">Logout</a>

</nav>

<div class="container">

    <h1>My Applications</h1>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($application = $result->fetch_assoc()): ?>

            <div class="application">

                <h2>
                    <?php echo htmlspecialchars($application["title"]); ?>
                </h2>

                <p>
                    <strong>Organization:</strong>
                    <?php echo htmlspecialchars($application["organization_name"]); ?>
                </p>

                <p>
                    <strong>Description:</strong>
                    <?php echo htmlspecialchars($application["description"]); ?>
                </p>

                <p>
                    <strong>Location:</strong>
                    <?php echo htmlspecialchars($application["location"]); ?>
                </p>

                <p>
                    <strong>Required Skills:</strong>
                    <?php echo htmlspecialchars($application["required_skills"]); ?>
                </p>

                <p>
                    <strong>Event Date:</strong>
                    <?php echo htmlspecialchars($application["event_date"]); ?>
                </p>

                <p>
                    <strong>Applied On:</strong>
                    <?php echo htmlspecialchars($application["applied_at"]); ?>
                </p>

                <p class="status">

                    Status:

                    <?php

                    $status = $application["status"];

                    if ($status === "pending") {
                        echo '<span class="pending">Pending</span>';
                    } elseif ($status === "accepted") {
                        echo '<span class="accepted">Accepted</span>';
                    } elseif ($status === "rejected") {
                        echo '<span class="rejected">Rejected</span>';
                    }

                    ?>

                </p>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="no-applications">

            <h2>No Applications Yet</h2>

            <p>You have not applied for any volunteer opportunities.</p>

            <a class="button" href="../opportunities.php">
                Browse Opportunities
            </a>

        </div>

    <?php endif; ?>

</div>

</body>

</html>