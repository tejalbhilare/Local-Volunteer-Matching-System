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


// --------------------------------------------------
// Get organization ID
// --------------------------------------------------

$stmt = $conn->prepare("
    SELECT id
    FROM organizations
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$organization = $result->fetch_assoc();


// Check if organization exists
if (!$organization) {

    die("
        <h2>Organization profile not found.</h2>
        <p>Please complete your organization registration first.</p>
        <a href='dashboard.php'>Back to Dashboard</a>
    ");
}

$organization_id = $organization["id"];


// --------------------------------------------------
// Delete opportunity
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {

    $delete_id = intval($_POST["delete_id"]);

    // Delete only if this opportunity belongs to
    // the currently logged-in organization
    $delete_stmt = $conn->prepare("
        DELETE FROM opportunities
        WHERE id = ?
        AND organization_id = ?
    ");

    $delete_stmt->bind_param(
        "ii",
        $delete_id,
        $organization_id
    );

    if ($delete_stmt->execute()) {

        if ($delete_stmt->affected_rows > 0) {
            $message = "Opportunity deleted successfully.";
        } else {
            $message = "Opportunity not found or you do not have permission to delete it.";
        }

    } else {

        $message = "Error deleting opportunity.";
    }
}


// --------------------------------------------------
// Fetch organization's opportunities
// --------------------------------------------------

$opportunities_stmt = $conn->prepare("
    SELECT
        id,
        title,
        description,
        location,
        required_skills,
        event_date,
        created_at
    FROM opportunities
    WHERE organization_id = ?
    ORDER BY created_at DESC
");

$opportunities_stmt->bind_param(
    "i",
    $organization_id
);

$opportunities_stmt->execute();

$opportunities_result = $opportunities_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Opportunities</title>

</head>

<body>

    <h1>Local Volunteer Matching System</h1>

    <hr>

    <h2>Manage My Opportunities</h2>

    <p>
        View and manage the volunteer opportunities created
        by your organization.
    </p>


    <?php if (!empty($message)): ?>

        <p>
            <strong>
                <?php echo htmlspecialchars($message); ?>
            </strong>
        </p>

    <?php endif; ?>


    <hr>


    <?php if ($opportunities_result->num_rows > 0): ?>

        <?php while ($opportunity = $opportunities_result->fetch_assoc()): ?>

            <article>

                <h3>
                    <?php
                    echo htmlspecialchars($opportunity["title"]);
                    ?>
                </h3>


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
                    <strong>Created:</strong>

                    <?php
                    echo htmlspecialchars(
                        $opportunity["created_at"]
                    );
                    ?>

                </p>


                <!-- Delete button -->

                <form
                    method="POST"
                    onsubmit="return confirm(
                        'Are you sure you want to delete this opportunity?'
                    );"
                >

                    <input
                        type="hidden"
                        name="delete_id"
                        value="<?php
                            echo $opportunity["id"];
                        ?>"
                    >

                    <button type="submit">
                        Delete Opportunity
                    </button>

                </form>


                <hr>

            </article>

        <?php endwhile; ?>

    <?php else: ?>

        <p>
            <strong>
                You have not created any opportunities yet.
            </strong>
        </p>

        <p>
            <a href="create-opportunity.php">
                Create Your First Opportunity
            </a>
        </p>

    <?php endif; ?>


    <hr>


    <p>
        <a href="create-opportunity.php">
            + Create New Opportunity
        </a>
    </p>

    <p>
        <a href="dashboard.php">
            ← Back to Organization Dashboard
        </a>
    </p>

    <p>
        <a href="../logout.php">
            Logout
        </a>
    </p>

</body>

</html>