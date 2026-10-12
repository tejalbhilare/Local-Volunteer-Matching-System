
<?php
session_start();

require_once "config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get all opportunities
$sql = "
    SELECT
        opportunities.id,
        opportunities.title,
        opportunities.description,
        opportunities.location,
        opportunities.required_skills,
        opportunities.event_date,
        organizations.organization_name
    FROM opportunities
    INNER JOIN organizations
        ON opportunities.organization_id = organizations.id
    ORDER BY opportunities.event_date ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Volunteer Opportunities | Volunteer Match</title>

    <link rel="stylesheet" href="assets/style.css">

    <style>
        .opportunities-nav {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 16px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }

        .opportunities-brand {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
        }

        .opportunities-nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .opportunities-nav-links a {
            font-weight: 600;
        }

        .opportunities-container {
            width: 90%;
            max-width: 1150px;
            margin: 35px auto;
        }

        .opportunities-hero {
            background: linear-gradient(135deg, #e2f3e9, #fff);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 28px;
        }

        .opportunities-hero h1 {
            margin: 0 0 12px;
            font-size: clamp(26px, 4vw, 36px);
        }

        .opportunities-hero p {
            margin: 0;
            color: var(--muted);
        }

        .opportunity-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .opportunity-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .opportunity-card h2 {
            color: var(--primary);
            font-size: 22px;
            margin: 0 0 14px;
            overflow-wrap: anywhere;
        }

        .organization-name {
            color: var(--muted);
            margin: 0 0 16px;
        }

        .opportunity-description {
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .opportunity-details {
            border-top: 1px solid var(--border);
            margin-top: 12px;
            padding-top: 12px;
        }

        .opportunity-details p {
            margin: 9px 0;
            overflow-wrap: anywhere;
        }

        .detail-label {
            font-weight: 700;
        }

        .apply-button {
            display: inline-block;
            margin-top: auto;
            padding: 11px 18px;
            background: var(--primary);
            color: #fff;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
        }

        .apply-button:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        .empty-state {
            text-align: center;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 35px 20px;
        }

        .opportunities-footer {
            text-align: center;
            color: var(--muted);
            padding: 24px 12px;
            font-size: 14px;
        }

        @media (max-width: 700px) {
            .opportunities-container {
                width: 92%;
                margin-top: 22px;
            }

            .opportunity-grid {
                grid-template-columns: 1fr;
            }

            .opportunities-hero {
                padding: 24px;
            }

            .opportunities-nav-links {
                gap: 12px;
            }
        }
    </style>
</head>

<body>

    <nav class="opportunities-nav">
        <a class="opportunities-brand" href="index.php">
            Volunteer Match
        </a>

        <div class="opportunities-nav-links">
            <a href="index.php">Home</a>
            <a href="volunteer/dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="opportunities-container">

        <section class="opportunities-hero">
            <h1>Discover Volunteer Opportunities</h1>
            <p>
                Explore local community activities, connect with organizations,
                and take the next step toward making a difference.
            </p>
        </section>

        <?php if ($result && $result->num_rows > 0): ?>

            <div class="opportunity-grid">

                <?php while ($opportunity = $result->fetch_assoc()): ?>

                    <article class="opportunity-card">

                        <h2>
                            <?php
                            echo htmlspecialchars($opportunity["title"]);
                            ?>
                        </h2>

                        <p class="organization-name">
                            <strong>Organized by:</strong>
                            <?php
                            echo htmlspecialchars(
                                $opportunity["organization_name"]
                            );
                            ?>
                        </p>

                        <p class="opportunity-description">
                            <strong>Description</strong><br>
                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $opportunity["description"] ?? ""
                                )
                            );
                            ?>
                        </p>

                        <div class="opportunity-details">

                            <p>
                                <span class="detail-label">Location:</span>
                                <?php
                                echo htmlspecialchars(
                                    $opportunity["location"] ?? "Not specified"
                                );
                                ?>
                            </p>

                            <p>
                                <span class="detail-label">Required Skills:</span>
                                <?php
                                echo !empty($opportunity["required_skills"])
                                    ? htmlspecialchars($opportunity["required_skills"])
                                    : "Not specified";
                                ?>
                            </p>

                            <p>
                                <span class="detail-label">Event Date:</span>
                                <?php
                                echo !empty($opportunity["event_date"])
                                    ? htmlspecialchars($opportunity["event_date"])
                                    : "Not specified";
                                ?>
                            </p>

                        </div>

                        <a
                            class="apply-button"
                            href="volunteer/apply.php?id=<?php
                                echo (int) $opportunity["id"];
                            ?>"
                        >
                            Apply for This Opportunity →
                        </a>

                    </article>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <section class="empty-state">
                <h2>No Opportunities Available Yet</h2>
                <p>
                    There are currently no volunteer opportunities to display.
                    Please check again later.
                </p>

                <a class="btn" href="volunteer/dashboard.php">
                    Back to Dashboard
                </a>
            </section>

        <?php endif; ?>

    </main>

    <footer class="opportunities-footer">
        Local Volunteer Matching System &copy; <?php echo date("Y"); ?>
    </footer>

</body>
</html>
