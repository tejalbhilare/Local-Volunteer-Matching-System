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

    <title>Volunteer Opportunities</title>

</head>

<body>

    <h1>Local Volunteer Matching System</h1>

    <hr>

    <h2>Available Volunteer Opportunities</h2>

    <p>
        Find and apply for local volunteer opportunities.
    </p>

    <?php if ($result && $result->num_rows > 0): ?>

        <?php while ($opportunity = $result->fetch_assoc()): ?>

            <hr>

            <h3>
                <?php
                echo htmlspecialchars($opportunity["title"]);
                ?>
            </h3>

            <p>
                <strong>Organization:</strong>

                <?php
                echo htmlspecialchars(
                    $opportunity["organization_name"]
                );
                ?>
            </p>

            <p>
                <strong>Description:</strong>
                <br>

                <?php
                echo nl2br(
                    htmlspecialchars(
                        $opportunity["description"]
                    )
                );
                ?>
            </p>

            <p>
                <strong>Location:</strong>

                <?php
                echo htmlspecialchars(
                    $opportunity["location"]
                );
                ?>
            </p>

            <p>
                <strong>Required Skills:</strong>

                <?php

                if (!empty($opportunity["required_skills"])) {

                    echo htmlspecialchars(
                        $opportunity["required_skills"]
                    );

                } else {

                    echo "Not specified";

                }

                ?>

            </p>

            <p>
                <strong>Event Date:</strong>

                <?php
                echo htmlspecialchars(
                    $opportunity["event_date"]
                );
                ?>
            </p>

            <p>

                <a href="volunteer/apply.php?id=<?php
                    echo $opportunity["id"];
                ?>">
                    Apply for this Opportunity
                </a>

            </p>

        <?php endwhile; ?>

    <?php else: ?>

        <p>
            No volunteer opportunities are currently available.
        </p>

    <?php endif; ?>


    <hr>

    <p>
        <a href="volunteer/dashboard.php">
            Back to Volunteer Dashboard
        </a>
    </p>

    <p>
        <a href="logout.php">
            Logout
        </a>
    </p>

</body>

</html>