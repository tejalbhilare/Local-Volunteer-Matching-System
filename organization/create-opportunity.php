<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only organizations can access this page
if ($_SESSION["role"] !== "organization") {
    header("Location: ../login.php");
    exit();
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


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


// Check if organization profile exists
if (!$organization) {

    die("
        <h2>Organization profile not found.</h2>
        <p>Please complete your organization registration first.</p>
        <a href='dashboard.php'>Back to Dashboard</a>
    ");
}

$organization_id = $organization["id"];


// Handle opportunity form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $location = trim($_POST["location"]);
    $required_skills = trim($_POST["required_skills"]);
    $event_date = $_POST["event_date"];


    // Validate required fields
    if (
        empty($title) ||
        empty($description) ||
        empty($location) ||
        empty($event_date)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {

        // Insert opportunity
        $insert_stmt = $conn->prepare("
            INSERT INTO opportunities
            (
                organization_id,
                title,
                description,
                location,
                required_skills,
                event_date
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $insert_stmt->bind_param(
            "isssss",
            $organization_id,
            $title,
            $description,
            $location,
            $required_skills,
            $event_date
        );


        if ($insert_stmt->execute()) {

            $message = "Opportunity created successfully!";
            $message_type = "success";

            // Clear form after successful submission
            $title = "";
            $description = "";
            $location = "";
            $required_skills = "";
            $event_date = "";

        } else {

            $message = "Error creating opportunity.";
            $message_type = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Opportunity</title>

</head>

<body>

    <h1>Local Volunteer Matching System</h1>

    <hr>

    <h2>Create Volunteer Opportunity</h2>

    <p>
        Create an opportunity for volunteers in your local community.
    </p>


    <?php if (!empty($message)): ?>

        <p>
            <strong>
                <?php echo htmlspecialchars($message); ?>
            </strong>
        </p>

    <?php endif; ?>


    <form method="POST">

        <!-- Opportunity Title -->

        <label for="title">
            Opportunity Title:
        </label>

        <br>

        <input
            type="text"
            id="title"
            name="title"
            value="<?php echo htmlspecialchars($title ?? ''); ?>"
            maxlength="150"
            required
        >

        <br><br>


        <!-- Description -->

        <label for="description">
            Description:
        </label>

        <br>

        <textarea
            id="description"
            name="description"
            rows="6"
            cols="50"
            required
        ><?php echo htmlspecialchars($description ?? ''); ?></textarea>

        <br><br>


        <!-- Location -->

        <label for="location">
            Location:
        </label>

        <br>

        <input
            type="text"
            id="location"
            name="location"
            value="<?php echo htmlspecialchars($location ?? ''); ?>"
            maxlength="150"
            placeholder="Example: Mumbai"
            required
        >

        <br><br>


        <!-- Required Skills -->

        <label for="required_skills">
            Required Skills:
        </label>

        <br>

        <textarea
            id="required_skills"
            name="required_skills"
            rows="4"
            cols="50"
            placeholder="Example: Teaching, Communication, Event Management"
        ><?php echo htmlspecialchars($required_skills ?? ''); ?></textarea>

        <br><br>


        <!-- Event Date -->

        <label for="event_date">
            Event Date:
        </label>

        <br>

        <input
            type="date"
            id="event_date"
            name="event_date"
            value="<?php echo htmlspecialchars($event_date ?? ''); ?>"
            required
        >

        <br><br>


        <button type="submit">
            Create Opportunity
        </button>

    </form>


    <hr>


    <p>
        <a href="dashboard.php">
            ← Back to Organization Dashboard
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

</body>

</html>