<?php

session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only organizations can access this page
if ($_SESSION["role"] !== "organization") {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get organization ID
$stmt = $conn->prepare(
    "SELECT id, organization_name
     FROM organizations
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$organization_result = $stmt->get_result();

if ($organization_result->num_rows !== 1) {
    die("Organization information not found.");
}

$organization = $organization_result->fetch_assoc();

$organization_id = $organization["id"];

// Get applicants
$sql = "SELECT
            applications.id AS application_id,
            applications.status,
            applications.applied_at,
            users.name,
            users.email,
            opportunities.title,
            opportunities.event_date,
            opportunities.location
        FROM applications
        INNER JOIN users
            ON applications.volunteer_id = users.id
        INNER JOIN opportunities
            ON applications.opportunity_id = opportunities.id
        WHERE opportunities.organization_id = ?
        ORDER BY applications.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organization_id);
$stmt->execute();

$applicants = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Applicants - Local Volunteer Matching System</title>

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
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }

        h1 {
            color: #2c3e50;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #34495e;
            color: white;
        }

        .pending {
            color: #e67e22;
            font-weight: bold;
        }

        .accepted {
            color: #27ae60;
            font-weight: bold;
        }

        .rejected {
            color: #c0392b;
            font-weight: bold;
        }

        .no-applicants {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
        }

    </style>

</head>

<body>

<nav>

    <a href="dashboard.php">Dashboard</a>

    <a href="create-opportunity.php">Create Opportunity</a>

    <a href="manage-opportunities.php">Manage Opportunities</a>

    <a href="applicants.php">Applicants</a>

    <a href="../logout.php">Logout</a>

</nav>

<div class="container">

    <h1>Volunteer Applicants</h1>

    <p>
        Organization:
        <strong>
            <?php echo htmlspecialchars($organization["organization_name"]); ?>
        </strong>
    </p>

    <?php if ($applicants->num_rows > 0): ?>

        <table>

            <tr>

                <th>Volunteer</th>
                <th>Email</th>
                <th>Opportunity</th>
                <th>Event Date</th>
                <th>Location</th>
                <th>Applied On</th>
                <th>Status</th>

            </tr>

            <?php while ($applicant = $applicants->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($applicant["name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($applicant["email"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($applicant["title"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($applicant["event_date"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($applicant["location"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($applicant["applied_at"]); ?>
                    </td>

                    <td>

                        <?php

                        $status = $applicant["status"];

                        if ($status === "pending") {
                            echo '<span class="pending">Pending</span>';
                        } elseif ($status === "accepted") {
                            echo '<span class="accepted">Accepted</span>';
                        } elseif ($status === "rejected") {
                            echo '<span class="rejected">Rejected</span>';
                        }

                        ?>

                    </td>

                </tr>

            <?php endwhile; ?>

        </table>

    <?php else: ?>

        <div class="no-applicants">

            <h2>No Applicants Yet</h2>

            <p>
                Volunteers have not applied to your opportunities yet.
            </p>

        </div>

    <?php endif; ?>

</div>

</body>

</html>