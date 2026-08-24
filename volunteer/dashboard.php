<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Check if the logged-in user is a volunteer
if ($_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"];

// Get total number of opportunities
$opportunity_query = "SELECT COUNT(*) AS total FROM opportunities";
$opportunity_result = $conn->query($opportunity_query);
$opportunity_data = $opportunity_result->fetch_assoc();
$total_opportunities = $opportunity_data["total"];

// Get number of applications submitted by this volunteer
$application_query = "
    SELECT COUNT(*) AS total
    FROM applications
    WHERE volunteer_id = ?
";

$stmt = $conn->prepare($application_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$application_result = $stmt->get_result();
$application_data = $application_result->fetch_assoc();

$total_applications = $application_data["total"];

// Get accepted applications
$accepted_query = "
    SELECT COUNT(*) AS total
    FROM applications
    WHERE volunteer_id = ?
    AND status = 'accepted'
";

$stmt = $conn->prepare($accepted_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$accepted_result = $stmt->get_result();
$accepted_data = $accepted_result->fetch_assoc();

$accepted_applications = $accepted_data["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Volunteer Dashboard</title>

</head>

<body>

    <header>

        <h1>Local Volunteer Matching System</h1>

        <p>
            Welcome, <?php echo htmlspecialchars($name); ?>!
        </p>

    </header>

    <hr>

    <main>

        <h2>Volunteer Dashboard</h2>

        <p>
            Manage your profile, discover opportunities,
            and track your applications.
        </p>

        <section>

            <h3>Dashboard Summary</h3>

            <p>
                <strong>Total Opportunities:</strong>
                <?php echo $total_opportunities; ?>
            </p>

            <p>
                <strong>My Applications:</strong>
                <?php echo $total_applications; ?>
            </p>

            <p>
                <strong>Accepted Applications:</strong>
                <?php echo $accepted_applications; ?>
            </p>

        </section>

        <hr>

        <section>

            <h3>Volunteer Menu</h3>

            <p>
                <a href="profile.php">
                    My Profile
                </a>
            </p>

            <p>
                <a href="../opportunities.php">
                    Find Opportunities
                </a>
            </p>

            <p>
                <a href="../logout.php">
                    Logout
                </a>
            </p>

        </section>

    </main>

</body>

</html>