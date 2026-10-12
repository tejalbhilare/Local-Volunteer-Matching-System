
<?php
session_start();

require_once "../config/database.php";

// Check whether the user is logged in.
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// Check the user's role.
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "organization") {
    $current_role = $_SESSION["role"] ?? "not set";
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Check</title>
    </head>
    <body>
        <h2>Organization Access Check</h2>
        <p>Your current session role is:
            <strong><?php echo htmlspecialchars($current_role); ?></strong>
        </p>
        <p>Please log out and sign in again with your organization account.</p>
        <a href="../logout.php">Log Out</a>
    </body>
    </html>
    <?php
    exit;
}

// Get the logged-in user's ID.
$user_id = (int) $_SESSION["user_id"];

// Find this user's organization profile.
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
    die(
        "Organization profile not found for your account. " .
        "Please check the organizations table in your database."
    );
}

$organization_id = (int) $organization["id"];

// Get only opportunities belonging to this organization.
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

    <title>Manage Opportunities</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #222;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            background: #fff;
            padding: 22px;
            border-radius: 10px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .header h1 {
            margin-top: 0;
            color: #2457a7;
        }

        .navigation {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            background: #2457a7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background: #173d7a;
        }

        .opportunity {
            background: white;
            padding: 22px;
            margin-bottom: 18px;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .opportunity h2 {
            margin-top: 0;
            color: #2457a7;
        }

        .detail {
            margin: 10px 0;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .empty {
            background: white;
            padding: 25px;
            border-radius: 9px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h1>Manage Opportunities</h1>

        <p>
            Organization:
            <strong>
                <?php
                echo htmlspecialchars(
                    $organization["organization_name"]
                );
                ?>
            </strong>
        </p>

        <p>View the volunteer opportunities created by your organization.</p>

        <div class="navigation">
            <a class="button" href="dashboard.php">
                Organization Dashboard
            </a>

            <a class="button" href="create-opportunity.php">
                Create Opportunity
            </a>

            <a class="button" href="applicants.php">
                View Applicants
            </a>

            <a class="button" href="../logout.php">
                Logout
            </a>
        </div>
    </div>

    <?php if ($opportunities_result->num_rows > 0): ?>

        <?php while ($opportunity = $opportunities_result->fetch_assoc()): ?>

            <div class="opportunity">

                <h2>
                    <?php echo htmlspecialchars($opportunity["title"]); ?>
                </h2>

                <p class="detail">
                    <strong>Description:</strong><br>
                    <?php
                    echo nl2br(
                        htmlspecialchars($opportunity["description"] ?? "")
                    );
                    ?>
                </p>

                <p class="detail">
                    <strong>Location:</strong>
                    <?php
                    echo htmlspecialchars($opportunity["location"] ?? "");
                    ?>
                </p>

                <p class="detail">
                    <strong>Required Skills:</strong>
                    <?php
                    echo htmlspecialchars(
                        $opportunity["required_skills"] ?? "Not specified"
                    );
                    ?>
                </p>

                <p class="detail">
                    <strong>Event Date:</strong>
                    <?php
                    echo htmlspecialchars($opportunity["event_date"] ?? "");
                    ?>
                </p>

                <p class="detail">
                    <strong>Opportunity ID:</strong>
                    <?php echo (int) $opportunity["id"]; ?>
                </p>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">
            <h2>No Opportunities Found</h2>
            <p>
                Your organization does not have any opportunities listed yet.
            </p>

            <a class="button" href="create-opportunity.php">
                Create Your First Opportunity
            </a>
        </div>

    <?php endif; ?>

</div>

</body>
</html>

<?php
$opportunities_stmt->close();
$conn->close();
?>
