
<?php

session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Allow volunteers only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

$volunteer_id = (int) $_SESSION["user_id"];
$volunteer_name = $_SESSION["name"] ?? "Volunteer";

// Count opportunities
$total_opportunities = 0;

$opportunity_query = $conn->query(
    "SELECT COUNT(*) AS total FROM opportunities"
);

if ($opportunity_query) {
    $row = $opportunity_query->fetch_assoc();
    $total_opportunities = (int) $row["total"];
}

// Count applications
$total_applications = 0;

$application_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE volunteer_id = ?"
);

if ($application_stmt) {
    $application_stmt->bind_param("i", $volunteer_id);
    $application_stmt->execute();

    $application_result = $application_stmt->get_result();
    $application_row = $application_result->fetch_assoc();

    $total_applications = (int) $application_row["total"];

    $application_stmt->close();
}

// Count accepted applications
$accepted_applications = 0;

$accepted_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE volunteer_id = ?
     AND status = 'accepted'"
);

if ($accepted_stmt) {
    $accepted_stmt->bind_param("i", $volunteer_id);
    $accepted_stmt->execute();

    $accepted_result = $accepted_stmt->get_result();
    $accepted_row = $accepted_result->fetch_assoc();

    $accepted_applications = (int) $accepted_row["total"];

    $accepted_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Volunteer Dashboard | Local Volunteer Matching System</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .dashboard-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding: 16px 5%;
            background: #ffffff;
            border-bottom: 1px solid #dce6e1;
        }

        .dashboard-brand {
            color: #167d63;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .dashboard-links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        .dashboard-links a {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .dashboard-links .logout-link {
            color: #c0392b;
        }

        .dashboard-container {
            width: 90%;
            max-width: 1100px;
            margin: 35px auto;
        }

        .welcome-panel {
            padding: 35px;
            margin-bottom: 28px;
            background: linear-gradient(135deg, #e3f4eb, #ffffff);
            border: 1px solid #dce6e1;
            border-radius: 16px;
        }

        .welcome-panel h1 {
            margin: 0 0 12px;
            font-size: clamp(1.7rem, 4vw, 2.3rem);
        }

        .welcome-panel p {
            max-width: 700px;
            color: #64756f;
            margin-bottom: 20px;
        }

        .dashboard-button {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 8px;
            background: #167d63;
            color: #ffffff;
            font-weight: 600;
        }

        .dashboard-button:hover {
            background: #105d4a;
            color: #ffffff;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            padding: 25px;
            background: #ffffff;
            border: 1px solid #dce6e1;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(25, 60, 45, 0.06);
        }

        .stat-label {
            margin: 0;
            color: #64756f;
            font-weight: 600;
        }

        .stat-number {
            margin: 12px 0;
            color: #167d63;
            font-size: 2.4rem;
            font-weight: 700;
        }

        .stat-description {
            margin: 0;
            color: #64756f;
            font-size: 0.9rem;
        }

        .quick-actions {
            padding: 28px;
            background: #ffffff;
            border: 1px solid #dce6e1;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(25, 60, 45, 0.06);
        }

        .quick-actions h2 {
            margin-top: 0;
        }

        .action-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .secondary-button {
            display: inline-block;
            padding: 10px 16px;
            border: 1px solid #167d63;
            border-radius: 8px;
            color: #167d63;
            font-weight: 600;
        }

        .secondary-button:hover {
            background: #e8f5ee;
        }

        .dashboard-footer {
            padding: 22px;
            margin-top: 35px;
            text-align: center;
            color: #64756f;
            border-top: 1px solid #dce6e1;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-nav {
                align-items: flex-start;
                flex-direction: column;
            }

            .dashboard-links {
                gap: 12px;
            }

            .welcome-panel,
            .quick-actions {
                padding: 22px;
            }

            .dashboard-container {
                width: 92%;
                margin-top: 22px;
            }
        }
    </style>
</head>

<body>

    <header class="dashboard-nav">

        <a class="dashboard-brand" href="../index.php">
            Volunteer Connect
        </a>

        <nav class="dashboard-links" aria-label="Main navigation">
            <a href="dashboard.php">Dashboard</a>
            <a href="../opportunities.php">Opportunities</a>
            <a href="profile.php">My Profile</a>
            <a href="applications.php">My Applications</a>
            <a class="logout-link" href="../logout.php">Logout</a>
        </nav>

    </header>

    <main class="dashboard-container">

        <section class="welcome-panel">

            <h1>
                Welcome, <?php echo htmlspecialchars($volunteer_name); ?>!
            </h1>

            <p>
                Your time and skills can make a difference.
                Discover local opportunities, apply to activities,
                and track your applications from one place.
            </p>

            <a class="dashboard-button" href="../opportunities.php">
                Explore Opportunities
            </a>

        </section>

        <section class="stats-grid" aria-label="Volunteer statistics">

            <article class="stat-card">
                <p class="stat-label">Total Opportunities</p>

                <p class="stat-number">
                    <?php echo $total_opportunities; ?>
                </p>

                <p class="stat-description">
                    Opportunities available in the system
                </p>
            </article>

            <article class="stat-card">
                <p class="stat-label">My Applications</p>

                <p class="stat-number">
                    <?php echo $total_applications; ?>
                </p>

                <p class="stat-description">
                    Applications you have submitted
                </p>
            </article>

            <article class="stat-card">
                <p class="stat-label">Accepted Applications</p>

                <p class="stat-number">
                    <?php echo $accepted_applications; ?>
                </p>

                <p class="stat-description">
                    Applications accepted by organizations
                </p>
            </article>

        </section>

        <section class="quick-actions">

            <h2>Quick Actions</h2>

            <p>
                Continue managing your volunteering activities.
            </p>

            <div class="action-list">

                <a class="dashboard-button" href="../opportunities.php">
                    Browse Opportunities
                </a>

                <a class="secondary-button" href="profile.php">
                    View My Profile
                </a>

                <a class="secondary-button" href="applications.php">
                    Track My Applications
                </a>

            </div>

        </section>

    </main>

    <footer class="dashboard-footer">
        &copy; <?php echo date('Y'); ?> Local Volunteer Matching System
    </footer>

</body>

</html>
