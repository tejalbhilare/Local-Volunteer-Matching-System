<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only organizations can access this dashboard
if ($_SESSION["role"] !== "organization") {
    header("Location: ../login.php");
    exit();
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"];

// Get organization ID
$stmt = $conn->prepare("
    SELECT id
    FROM organizations
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$organization = $result->fetch_assoc();

$organization_id = $organization["id"] ?? 0;


// Count opportunities created by this organization
$total_opportunities = 0;

if ($organization_id > 0) {

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM opportunities
        WHERE organization_id = ?
    ");

    $stmt->bind_param("i", $organization_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $total_opportunities = $data["total"];
}


// Count applications received by this organization
$total_applications = 0;

if ($organization_id > 0) {

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM applications a
        INNER JOIN opportunities o
            ON a.opportunity_id = o.id
        WHERE o.organization_id = ?
    ");

    $stmt->bind_param("i", $organization_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $total_applications = $data["total"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Organization Dashboard</title>

</head>

<body>

    <header>

        <h1>Local Volunteer Matching System</h1>

        <p>
            Welcome,
            <strong>
                <?php echo htmlspecialchars($name); ?>
            </strong>!
        </p>

    </header>

    <hr>

    <main>

        <h2>Organization Dashboard</h2>

        <p>
            Create volunteer opportunities and manage applications
            from volunteers.
        </p>


        <section>

            <h3>Dashboard Summary</h3>

            <p>
                <strong>Total Opportunities:</strong>
                <?php echo $total_opportunities; ?>
            </p>

            <p>
                <strong>Applications Received:</strong>
                <?php echo $total_applications; ?>
            </p>

        </section>


        <hr>


        <section>

            <h3>Organization Menu</h3>

            <p>
                <a href="create-opportunity.php">
                    Create Opportunity
                </a>
            </p>

            <p>
                <a href="../opportunities.php">
                    View Opportunities
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