<?php

session_start();

require_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Check if user is a volunteer
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

// Check if opportunity ID is provided
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid opportunity.");
}

$opportunity_id = (int) $_GET["id"];
$volunteer_id = $_SESSION["user_id"];

// Check whether the opportunity exists
$stmt = $conn->prepare(
    "SELECT id, title
     FROM opportunities
     WHERE id = ?"
);

$stmt->bind_param("i", $opportunity_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Opportunity not found.");
}

$opportunity = $result->fetch_assoc();


// Check whether the volunteer has already applied
$check_stmt = $conn->prepare(
    "SELECT id
     FROM applications
     WHERE opportunity_id = ?
     AND volunteer_id = ?"
);

$check_stmt->bind_param("ii", $opportunity_id, $volunteer_id);
$check_stmt->execute();

$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Already Applied</title>
    </head>
    <body>

        <h1>Already Applied</h1>

        <p>
            You have already applied for:
            <strong><?php echo htmlspecialchars($opportunity["title"]); ?></strong>
        </p>

        <p>
            <a href="../opportunities.php">Back to Opportunities</a>
        </p>

        <p>
            <a href="applications.php">View My Applications</a>
        </p>

    </body>
    </html>
    <?php
    exit();
}


// Submit application
$insert_stmt = $conn->prepare(
    "INSERT INTO applications
     (opportunity_id, volunteer_id, status)
     VALUES (?, ?, 'pending')"
);

$insert_stmt->bind_param("ii", $opportunity_id, $volunteer_id);

if ($insert_stmt->execute()) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Application Submitted</title>
    </head>
    <body>

        <h1>Application Submitted Successfully!</h1>

        <p>
            You have successfully applied for:
            <strong><?php echo htmlspecialchars($opportunity["title"]); ?></strong>
        </p>

        <p>
            Your application status is:
            <strong>Pending</strong>
        </p>

        <p>
            <a href="../opportunities.php">Browse More Opportunities</a>
        </p>

        <p>
            <a href="applications.php">View My Applications</a>
        </p>

        <p>
            <a href="dashboard.php">Back to Dashboard</a>
        </p>

    </body>
    </html>
    <?php
} else {
    echo "Error submitting application: " . htmlspecialchars($conn->error);
}
?>