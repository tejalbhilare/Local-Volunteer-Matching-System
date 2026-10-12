
<?php
session_start();

require_once "../config/database.php";

// Check login.
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// Check organization role.
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "organization") {
    $current_role = $_SESSION["role"] ?? "not set";
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Check</title>
        <link rel="stylesheet" href="../assets/style.css">
    </head>
    <body>
        <main class="container">
            <section class="card">
                <h2>Organization Access Check</h2>
                <p>Your current session role is:
                    <strong><?php echo htmlspecialchars($current_role); ?></strong>
                </p>
                <p>Please log out and sign in again with your organization account.</p>
                <a class="btn" href="../logout.php">Log Out</a>
            </section>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$user_id = (int) $_SESSION["user_id"];

// Find the organization's profile.
$organization_sql = "
    SELECT id, organization_name
    FROM organizations
    WHERE user_id = ?
";

$organization_stmt = $conn->prepare($organization_sql);

if (!$organization_stmt) {
    die("Unable to prepare organization query.");
}

$organization_stmt->bind_param("i", $user_id);
$organization_stmt->execute();

$organization_result = $organization_stmt->get_result();
$organization = $organization_result->fetch_assoc();

$organization_stmt->close();

if (!$organization) {
    die("Organization profile not found for your account. Please check your organization profile.");
}

$organization_id = (int) $organization["id"];

// Fetch only this organization's opportunities.
$opportunities_sql = "
    SELECT id, title, description, location,
           required_skills, event_date, created_at
    FROM opportunities
    WHERE organization_id = ?
    ORDER BY id DESC
";

$opportunities_stmt = $conn->prepare($opportunities_sql);

if (!$opportunities_stmt) {
    die("Unable to prepare opportunities query.");
}

$opportunities_stmt->bind_param("i", $organization_id);
$opportunities_stmt->execute();

$opportunities_result = $opportunities_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Opportunities | Local Volunteer Matching System</title>
    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .manage-container {
            max-width: 1050px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        .manage-header {
            padding: 28px;
            margin-bottom: 26px;
        }

        .manage-header h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .organization-name {
            display: inline-block;
            margin-top: 5px;
            font-weight: 600;
        }

        .page-description {
            margin-bottom: 0;
        }

        .manage-navigation {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .manage-navigation a {
            text-decoration: none;
        }

        .opportunity-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
            gap: 20px;
        }

        .opportunity-card {
            padding: 24px;
            min-width: 0;
        }

        .opportunity-card h2 {
            margin-top: 0;
            margin-bottom: 18px;
            overflow-wrap: anywhere;
        }

        .opportunity-detail {
            margin: 13px 0;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .detail-label {
            display: block;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .opportunity-id {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 10px;
            border-radius: 20px;
            background: var(--background, #f0f7f1);
            font-size: 13px;
        }

        .empty-state {
            padding: 35px 24px;
            text-align: center;
        }

        .empty-state h2 {
            margin-top: 0;
        }

        @media (max-width: 600px) {
            .manage-container {
                padding: 16px 12px 28px;
            }

            .manage-header,
            .opportunity-card {
                padding: 20px;
            }

            .manage-navigation {
                flex-direction: column;
            }

            .manage-navigation a {
                text-align: center;
                width: 100%;
            }
        }
    </style>
</head>

<body>

<main class="manage-container">

    <section class="card manage-header">
        <h1>Manage Opportunities</h1>

        <p>
            Organization:
            <span class="organization-name">
                <?php
                echo htmlspecialchars($organization["organization_name"]);
                ?>
            </span>
        </p>

        <p class="page-description">
            View the volunteer opportunities created by your organization.
        </p>

        <nav class="manage-navigation" aria-label="Organization navigation">
            <a class="btn" href="dashboard.php">Dashboard</a>
            <a class="btn" href="create-opportunity.php">+ Create Opportunity</a>
            <a class="btn" href="applicants.php">View Applicants</a>
            <a class="btn" href="../logout.php">Logout</a>
        </nav>
    </section>

    <?php if ($opportunities_result->num_rows > 0): ?>

        <section class="opportunity-list" aria-label="Your opportunities">

            <?php while ($opportunity = $opportunities_result->fetch_assoc()): ?>

                <article class="card opportunity-card">

                    <h2>
                        <?php echo htmlspecialchars($opportunity["title"]); ?>
                    </h2>

                    <div class="opportunity-detail">
                        <span class="detail-label">Description</span>
                        <?php
                        echo nl2br(
                            htmlspecialchars($opportunity["description"] ?? "")
                        );
                        ?>
                    </div>

                    <div class="opportunity-detail">
                        <span class="detail-label">Location</span>
                        <?php
                        echo htmlspecialchars(
                            $opportunity["location"] ?: "Not specified"
                        );
                        ?>
                    </div>

                    <div class="opportunity-detail">
                        <span class="detail-label">Required Skills</span>
                        <?php
                        echo htmlspecialchars(
                            $opportunity["required_skills"] ?: "Not specified"
                        );
                        ?>
                    </div>

                    <div class="opportunity-detail">
                        <span class="detail-label">Event Date</span>
                        <?php
                        echo htmlspecialchars(
                            $opportunity["event_date"] ?: "Not specified"
                        );
                        ?>
                    </div>

                    <span class="opportunity-id">
                        Opportunity ID: <?php echo (int) $opportunity["id"]; ?>
                    </span>

                </article>

            <?php endwhile; ?>

        </section>

    <?php else: ?>

        <section class="card empty-state">
            <h2>No Opportunities Found</h2>
            <p>
                Your organization has not listed any opportunities yet.
                Create one to start connecting with volunteers.
            </p>

            <a class="btn" href="create-opportunity.php">
                Create Your First Opportunity
            </a>
        </section>

    <?php endif; ?>

</main>

</body>
</html>

<?php
$opportunities_stmt->close();
$conn->close();
?>
