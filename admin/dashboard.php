
<?php
session_start();

require_once "../config/database.php";

// Check login.
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only administrators can access this page.
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Count users.
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM users"
);
$total_users = (int) $result->fetch_assoc()["total"];

// Count volunteers.
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'volunteer'"
);
$total_volunteers = (int) $result->fetch_assoc()["total"];

// Count organizations.
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'organization'"
);
$total_organizations = (int) $result->fetch_assoc()["total"];

// Count opportunities.
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM opportunities"
);
$total_opportunities = (int) $result->fetch_assoc()["total"];

// Count applications.
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM applications"
);
$total_applications = (int) $result->fetch_assoc()["total"];

$admin_name = $_SESSION["name"] ?? "Administrator";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Local Volunteer Matching System</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .admin-container {
            max-width: 1150px;
            margin: 0 auto;
            padding: 30px 18px 45px;
        }

        .admin-nav {
            background: var(--surface, #ffffff);
            border-bottom: 1px solid var(--border, #e0e7e1);
            padding: 16px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        .admin-brand {
            font-weight: 700;
            font-size: 18px;
            color: var(--primary-dark, #176534);
            text-decoration: none;
        }

        .admin-links {
            display: flex;
            gap: 18px;
            align-items: center;
            flex-wrap: wrap;
        }

        .admin-links a {
            color: var(--text, #26332a);
            text-decoration: none;
            font-weight: 500;
        }

        .admin-links a:hover {
            color: var(--primary, #26834a);
        }

        .admin-welcome {
            padding: 28px;
            margin-bottom: 25px;
        }

        .admin-welcome h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .admin-welcome p {
            margin-bottom: 0;
            color: var(--muted, #667085);
            line-height: 1.6;
        }

        .stats-heading {
            margin: 0 0 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 20px;
        }

        .stat-card {
            padding: 24px;
            text-align: left;
            border-top: 4px solid var(--primary, #26834a);
        }

        .stat-label {
            margin: 0;
            color: var(--muted, #667085);
            font-weight: 600;
            font-size: 14px;
        }

        .stat-number {
            margin: 12px 0 0;
            color: var(--primary-dark, #176534);
            font-size: clamp(30px, 4vw, 38px);
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .admin-footer {
            text-align: center;
            padding: 22px 16px;
            color: var(--muted, #667085);
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .admin-nav {
                padding: 16px;
            }

            .admin-links {
                gap: 14px;
            }

            .admin-container {
                padding: 20px 12px 30px;
            }

            .admin-welcome,
            .stat-card {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
        }
    </style>
</head>

<body>

<nav class="admin-nav" aria-label="Main navigation">
    <a class="admin-brand" href="dashboard.php">
        Volunteer System | Admin
    </a>

    <div class="admin-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="../index.php">Home</a>
        <a href="../logout.php">Logout</a>
    </div>
</nav>

<main class="admin-container">

    <section class="card admin-welcome">
        <h1>
            Welcome, <?php echo htmlspecialchars($admin_name); ?>!
        </h1>

        <p>
            Monitor your volunteer matching platform from one place.
            Here is a summary of the current system activity.
        </p>
    </section>

    <section aria-labelledby="stats-heading">
        <h2 class="stats-heading" id="stats-heading">System Overview</h2>

        <div class="stats-grid">

            <article class="card stat-card">
                <p class="stat-label">Total Users</p>
                <h3 class="stat-number">
                    <?php echo $total_users; ?>
                </h3>
            </article>

            <article class="card stat-card">
                <p class="stat-label">Volunteers</p>
                <h3 class="stat-number">
                    <?php echo $total_volunteers; ?>
                </h3>
            </article>

            <article class="card stat-card">
                <p class="stat-label">Organizations</p>
                <h3 class="stat-number">
                    <?php echo $total_organizations; ?>
                </h3>
            </article>

            <article class="card stat-card">
                <p class="stat-label">Opportunities</p>
                <h3 class="stat-number">
                    <?php echo $total_opportunities; ?>
                </h3>
            </article>

            <article class="card stat-card">
                <p class="stat-label">Applications</p>
                <h3 class="stat-number">
                    <?php echo $total_applications; ?>
                </h3>
            </article>

        </div>
    </section>

</main>

<footer class="admin-footer">
    Local Volunteer Matching System &copy; <?php echo date("Y"); ?>
</footer>

</body>
</html>

<?php
$conn->close();
?>
