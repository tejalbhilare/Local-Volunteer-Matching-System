
<?php
session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only volunteers can access this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

$volunteer_id = (int) $_SESSION["user_id"];

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

if (!$stmt) {
    die("Unable to load applications. Please try again later.");
}

$stmt->bind_param("i", $volunteer_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Applications | Volunteer Match</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .applications-nav {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 16px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }

        .applications-brand {
            color: var(--primary);
            font-size: 22px;
            font-weight: 700;
        }

        .applications-nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }

        .applications-nav-links a {
            font-weight: 600;
        }

        .applications-container {
            width: 90%;
            max-width: 1050px;
            margin: 35px auto;
        }

        .applications-heading {
            padding: 28px;
            margin-bottom: 25px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: linear-gradient(135deg, #e4f4ec, #fff);
        }

        .applications-heading h1 {
            margin: 0 0 8px;
            font-size: clamp(26px, 4vw, 34px);
        }

        .applications-heading p {
            color: var(--muted);
            margin: 0;
        }

        .application-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 22px;
        }

        .application-card h2 {
            margin: 0 0 12px;
            color: var(--primary);
            font-size: 23px;
            overflow-wrap: anywhere;
        }

        .application-organization {
            color: var(--muted);
            margin-bottom: 20px;
        }

        .application-description {
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .application-details {
            border-top: 1px solid var(--border);
            margin-top: 18px;
            padding-top: 12px;
        }

        .application-details p {
            margin: 10px 0;
            overflow-wrap: anywhere;
        }

        .status-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            border-top: 1px solid var(--border);
            padding-top: 18px;
            margin-top: 18px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-pending {
            color: #895500;
            background: #fff1ce;
        }

        .status-accepted {
            color: #176b3a;
            background: #e0f4e7;
        }

        .status-rejected {
            color: #a52a20;
            background: #fde8e6;
        }

        .status-unknown {
            color: #4b5563;
            background: #edf0f2;
        }

        .empty-applications {
            text-align: center;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 38px 22px;
            box-shadow: var(--shadow);
        }

        .empty-applications h2 {
            margin-top: 0;
        }

        .empty-applications p {
            color: var(--muted);
            margin-bottom: 22px;
        }

        .applications-footer {
            text-align: center;
            color: var(--muted);
            font-size: 14px;
            padding: 24px 12px;
        }

        @media (max-width: 650px) {
            .applications-container {
                width: 92%;
                margin-top: 22px;
            }

            .applications-heading,
            .application-card {
                padding: 20px;
            }

            .applications-nav-links {
                gap: 12px;
            }
        }
    </style>
</head>

<body>

    <nav class="applications-nav">
        <a href="../index.php" class="applications-brand">
            Volunteer Match
        </a>

        <div class="applications-nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="../opportunities.php">Opportunities</a>
            <a href="profile.php">My Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>

    <main class="applications-container">

        <header class="applications-heading">
            <h1>My Applications</h1>
            <p>
                Track the volunteer opportunities you have applied for
                and check the latest status of each application.
            </p>
        </header>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($application = $result->fetch_assoc()): ?>

                <?php
                $status = strtolower(trim($application["status"] ?? ""));

                $status_class = "status-unknown";

                if ($status === "pending") {
                    $status_class = "status-pending";
                } elseif ($status === "accepted") {
                    $status_class = "status-accepted";
                } elseif ($status === "rejected") {
                    $status_class = "status-rejected";
                }
                ?>

                <article class="application-card">

                    <h2>
                        <?php
                        echo htmlspecialchars($application["title"]);
                        ?>
                    </h2>

                    <p class="application-organization">
                        <strong>Organization:</strong>
                        <?php
                        echo htmlspecialchars($application["organization_name"]);
                        ?>
                    </p>

                    <p class="application-description">
                        <strong>Description</strong><br>
                        <?php
                        echo nl2br(
                            htmlspecialchars($application["description"] ?? "")
                        );
                        ?>
                    </p>

                    <div class="application-details">

                        <p>
                            <strong>Location:</strong>
                            <?php
                            echo htmlspecialchars(
                                $application["location"] ?? "Not specified"
                            );
                            ?>
                        </p>

                        <p>
                            <strong>Required Skills:</strong>
                            <?php
                            echo !empty($application["required_skills"])
                                ? htmlspecialchars($application["required_skills"])
                                : "Not specified";
                            ?>
                        </p>

                        <p>
                            <strong>Event Date:</strong>
                            <?php
                            echo !empty($application["event_date"])
                                ? htmlspecialchars($application["event_date"])
                                : "Not specified";
                            ?>
                        </p>

                        <p>
                            <strong>Applied On:</strong>
                            <?php
                            echo !empty($application["applied_at"])
                                ? htmlspecialchars($application["applied_at"])
                                : "Not available";
                            ?>
                        </p>

                    </div>

                    <div class="status-row">
                        <strong>Application Status:</strong>

                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php
                            echo htmlspecialchars(
                                $status !== "" ? ucfirst($status) : "Unknown"
                            );
                            ?>
                        </span>
                    </div>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <section class="empty-applications">
                <h2>No Applications Yet</h2>

                <p>
                    You haven't applied for any volunteer opportunities yet.
                    Browse available opportunities and find a cause you care about.
                </p>

                <a class="btn" href="../opportunities.php">
                    Browse Opportunities
                </a>
            </section>

        <?php endif; ?>

        <?php $stmt->close(); ?>

    </main>

    <footer class="applications-footer">
        Local Volunteer Matching System &copy; <?php echo date("Y"); ?>
    </footer>

</body>
</html>
