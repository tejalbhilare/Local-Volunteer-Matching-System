<?php

session_start();

require_once "../config/database.php";


// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}


// Only volunteers can apply
if ($_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}


$volunteer_id = $_SESSION["user_id"];


// Check opportunity ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid opportunity.");
}


$opportunity_id = intval($_GET["id"]);


// Check if opportunity exists
$stmt = $conn->prepare("
    SELECT
        o.id,
        o.title,
        o.description,
        o.location,
        o.required_skills,
        o.event_date,
        org.organization_name
    FROM opportunities o
    INNER JOIN organizations org
        ON o.organization_id = org.id
    WHERE o.id = ?
");

$stmt->bind_param("i", $opportunity_id);
$stmt->execute();

$result = $stmt->get_result();

$opportunity = $result->fetch_assoc();


// Opportunity not found
if (!$opportunity) {
    die("Opportunity not found.");
}


// Check if volunteer already applied
$check = $conn->prepare("
    SELECT id
    FROM applications
    WHERE opportunity_id = ?
    AND volunteer_id = ?
");

$check->bind_param(
    "ii",
    $opportunity_id,
    $volunteer_id
);

$check->execute();

$existing = $check->get_result();


// If already applied
if ($existing->num_rows > 0) {

    $message = "You have already applied for this opportunity.";

} else {

    // Insert application
    $apply = $conn->prepare("
        INSERT INTO applications
        (opportunity_id, volunteer_id, status)
        VALUES (?, ?, 'pending')
    ");

    $apply->bind_param(
        "ii",
        $opportunity_id,
        $volunteer_id
    );


    if ($apply->execute()) {

        $message = "Application submitted successfully!";

    } else {

        $message = "Failed to submit application.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Apply for Opportunity</title>

</head>

<body>

    <h1>Local Volunteer Matching System</h1>

    <hr>

    <h2>Volunteer Application</h2>


    <h3>
        <?php
        echo htmlspecialchars(
            $opportunity["title"]
        );
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


    <hr>


    <p>
        <strong>
            <?php echo htmlspecialchars($message); ?>
        </strong>
    </p>


    <hr>


    <p>
        <a href="../opportunities.php">
            ← Back to Opportunities
        </a>
    </p>


    <p>
        <a href="dashboard.php">
            ← Volunteer Dashboard
        </a>
    </p>


    <p>
        <a href="../logout.php">
            Logout
        </a>
    </p>

</body>

</html>