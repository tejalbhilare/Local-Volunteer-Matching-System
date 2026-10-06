<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'organization') {
    header("Location: ../index.php");
    exit();
}

$organization_id = $_SESSION['user_id'];

$sql = "SELECT * FROM opportunities WHERE organization_id = ? ORDER BY event_date ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $organization_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Opportunities - Local Volunteer Matching System</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .opportunity {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .opportunity h2 {
            margin-top: 0;
            color: #2c3e50;
        }

        .opportunity p {
            margin: 8px 0;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }

        .no-data {
            background: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Manage Volunteer Opportunities</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($opportunity = mysqli_fetch_assoc($result)): ?>

            <div class="opportunity">

                <h2>
                    <?php echo htmlspecialchars($opportunity['title']); ?>
                </h2>

                <p>
                    <strong>Description:</strong>
                    <?php echo htmlspecialchars($opportunity['description']); ?>
                </p>

                <p>
                    <strong>Location:</strong>
                    <?php echo htmlspecialchars($opportunity['location']); ?>
                </p>

                <p>
                    <strong>Required Skills:</strong>
                    <?php echo htmlspecialchars($opportunity['required_skills']); ?>
                </p>

                <p>
                    <strong>Event Date:</strong>
                    <?php echo htmlspecialchars($opportunity['event_date']); ?>
                </p>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="no-data">
            <p>You have not created any volunteer opportunities yet.</p>
        </div>

    <?php endif; ?>

    <a class="back-link" href="dashboard.php">
        ← Back to Organization Dashboard
    </a>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>