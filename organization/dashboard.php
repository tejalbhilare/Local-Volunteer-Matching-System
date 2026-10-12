
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
    http_response_code(403);
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

    $opportunity_count = (int) $count_data["total"];

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

    $application_count = (int) $application_data["total"];

    $application_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Organization Dashboard | Volunteer Match</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .dashboard-nav {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 16px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }

        .dashboard-brand {
            color: var(--primary);
            font-size: 22px;
            font-weight: 700;
        }

        .dashboard-links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .dashboard-links a {
            font-weight: 600;
        }

        .dashboard-container {
            width: 90%;
            max-width: 1100px;
            margin: 35px auto;
        }

        .welcome-panel {
            background: linear-gradient(135deg, #e4f4ec, #ffffff);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 26px;
        }

        .welcome-panel h1 {
            margin: 0 0 12px;
            font-size: clamp(25px, 4vw, 34px);
        }

        .welcome-panel p {
            margin-bottom: 0;
            color: var(--muted);
        }

        .section-heading {
            margin: 28px 0 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--surface);
            padding: 25px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border-top: 4px solid var(--primary);
        }

        .stat-card h3 {
            font-size: 16px;
            margin: 0 0 12px;
            color: var(--muted);
        }

        .stat-number {
            font-size: 38px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .stat-card p {
            color: var(--muted);
            margin-bottom: 0;
        }

        .actions-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 26px;
        }

        .actions-panel h2 {
            margin-top: 0;
        }

        .actions-panel p {
            color: var(--muted);
        }

        .action-links {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .action-links .btn {
            text-align: center;
        }

        .logout-link {
            background: #fff0ee;
            color: var(--danger);
        }

        .logout-link:hover {
            background: #fbe0dc;
            color: var(--danger);
        }

        .dashboard-footer {
            text-align: center;
            color: var(--muted);
            font-size: 14px;
            padding: 25px 15px;
        }

        @media (max-width: 650px) {
            .dashboard-nav {
                padding: 16px 5%;
            }

            .dashboard-links {
                gap: 12px;
            }

            .dashboard-container {
                width: 92%;
                margin-top: 22px;
            }

            .welcome-panel,
            .actions-panel {
                padding: 22px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .action-links {
                flex-direction: column;
            }

            .action-links .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <nav class="dashboard-nav">
        <a class="dashboard-brand" href="../index.php">
            Volunteer Match
        </a>

        <div class="dashboard-links">
            <a href="../index.php">Home</a>
            <a href="../opportunities.php">Opportunities</a>
            <a href="create-opportunity.php">Create Opportunity</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>

    <main class="dashboard-container">

        <section class="welcome-panel">
            <h1>
                Welcome,
                <?php
                echo htmlspecialchars(
                    $organization["organization_name"] ?: $organization["name"]
                );
                ?>!
            </h1>

            <p>
                Manage your volunteer opportunities, connect with volunteers,
                and review applications from one place.
            </p>
        </section>

        <h2 class="section-heading">Your Organization Overview</h2>

        <section class="stats-grid">

            <div class="stat-card">
                <h3>Total Opportunities</h3>

                <div class="stat-number">
                    <?php echo $opportunity_count; ?>
                </div>

                <p>Opportunities created by your organization.</p>
            </div>

            <div class="stat-card">
                <h3>Total Applications</h3>

                <div class="stat-number">
                    <?php echo $application_count; ?>
                </div>

                <p>Volunteer applications received for your opportunities.</p>
            </div>

        </section>

        <section class="actions-panel">
            <h2>Organization Actions</h2>
            <p>Choose what you would like to do next.</p>

            <div class="action-links">
                <a href="create-opportunity.php" class="btn">
                    + Create Opportunity
                </a>

                <a href="manage-opportunities.php" class="btn">
                    Manage Opportunities
                </a>

                <a href="applicants.php" class="btn">
                    View Applicants
                </a>

                <a href="../logout.php" class="btn logout-link">
                    Logout
                </a>
            </div>
        </section>

    </main>

    <footer class="dashboard-footer">
        Local Volunteer Matching System &copy; <?php echo date("Y"); ?>
    </footer>

</body>
</html>
