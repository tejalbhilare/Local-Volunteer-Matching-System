
<?php
session_start();

require_once "../config/database.php";

// Check login.
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Check volunteer role.
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

// Validate opportunity ID.
if (!isset($_GET["id"]) || !ctype_digit((string) $_GET["id"]) || (int) $_GET["id"] < 1) {
    http_response_code(400);
    $page_title = "Invalid Opportunity";
    $heading = "Invalid Opportunity";
    $message = "The opportunity link is invalid. Please return to the opportunities page.";
    $status_label = "";
    $status_class = "";
    $show_browse = true;
    $show_applications = false;
    $show_dashboard = true;
} else {
    $opportunity_id = (int) $_GET["id"];
    $volunteer_id = (int) $_SESSION["user_id"];

    // Find opportunity.
    $stmt = $conn->prepare(
        "SELECT id, title FROM opportunities WHERE id = ?"
    );

    if (!$stmt) {
        error_log("Opportunity query preparation failed: " . $conn->error);
        http_response_code(500);
        die("Unable to process your request right now. Please try again later.");
    }

    $stmt->bind_param("i", $opportunity_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $opportunity = $result->fetch_assoc();
    $stmt->close();

    if (!$opportunity) {
        http_response_code(404);
        $page_title = "Opportunity Not Found";
        $heading = "Opportunity Not Found";
        $message = "This opportunity may have been removed or the link may be incorrect.";
        $status_label = "";
        $status_class = "";
        $show_browse = true;
        $show_applications = false;
        $show_dashboard = true;
    } else {
        // Check whether this volunteer already applied.
        $check_stmt = $conn->prepare(
            "SELECT id FROM applications
             WHERE opportunity_id = ? AND volunteer_id = ?"
        );

        if (!$check_stmt) {
            error_log("Application check preparation failed: " . $conn->error);
            http_response_code(500);
            die("Unable to process your request right now. Please try again later.");
        }

        $check_stmt->bind_param("ii", $opportunity_id, $volunteer_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $already_applied = $check_result->num_rows > 0;
        $check_stmt->close();

        if ($already_applied) {
            $page_title = "Already Applied";
            $heading = "You've Already Applied";
            $message = "You have already submitted an application for this opportunity:";
            $status_label = "";
            $status_class = "";
            $show_browse = true;
            $show_applications = true;
            $show_dashboard = true;
        } else {
            // Submit the application with pending status.
            $insert_stmt = $conn->prepare(
                "INSERT INTO applications
                 (opportunity_id, volunteer_id, status)
                 VALUES (?, ?, 'pending')"
            );

            if (!$insert_stmt) {
                error_log("Application insert preparation failed: " . $conn->error);
                http_response_code(500);
                die("Unable to submit your application right now. Please try again later.");
            }

            $insert_stmt->bind_param("ii", $opportunity_id, $volunteer_id);

            if ($insert_stmt->execute()) {
                $page_title = "Application Submitted";
                $heading = "Application Submitted Successfully!";
                $message = "You've successfully applied for this volunteer opportunity:";
                $status_label = "Pending";
                $status_class = "pending";
                $show_browse = true;
                $show_applications = true;
                $show_dashboard = true;
            } else {
                // A unique constraint can prevent duplicate applications.
                error_log("Application submission failed: " . $insert_stmt->error);

                if ($insert_stmt->errno === 1062) {
                    $page_title = "Already Applied";
                    $heading = "You've Already Applied";
                    $message = "You have already submitted an application for this opportunity:";
                    $status_label = "";
                    $status_class = "";
                    $show_browse = true;
                    $show_applications = true;
                    $show_dashboard = true;
                } else {
                    $page_title = "Application Error";
                    $heading = "Application Could Not Be Submitted";
                    $message = "Something went wrong while submitting your application. Please try again.";
                    $status_label = "";
                    $status_class = "";
                    $show_browse = true;
                    $show_applications = false;
                    $show_dashboard = true;
                }
            }

            $insert_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($page_title); ?> | Local Volunteer Matching System</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .application-page {
            min-height: 75vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 55px 16px;
        }

        .application-card {
            width: 100%;
            max-width: 650px;
            padding: 34px;
            text-align: center;
        }

        .result-icon {
            width: 66px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #e7f6eb;
            color: #176534;
            font-size: 32px;
            font-weight: 700;
        }

        .application-card h1 {
            margin: 0 0 15px;
        }

        .application-message {
            line-height: 1.7;
            color: var(--muted, #667085);
        }

        .opportunity-name {
            margin: 20px 0;
            padding: 16px;
            border: 1px solid var(--border, #dce5dd);
            border-radius: 9px;
            background: var(--background, #f5f8f5);
            font-size: 18px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .status-badge {
            display: inline-block;
            margin: 0 0 20px;
            padding: 7px 14px;
            border-radius: 20px;
            background: #fff4d6;
            color: #805b00;
            font-size: 14px;
            font-weight: 600;
        }

        .application-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 25px;
        }

        .application-actions a {
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .application-page {
                padding: 28px 12px;
            }

            .application-card {
                padding: 24px 18px;
            }

            .application-actions {
                flex-direction: column;
            }

            .application-actions a {
                text-align: center;
            }
        }
    </style>
</head>

<body>

<main class="application-page">
    <section class="card application-card">

        <div class="result-icon" aria-hidden="true">
            <?php
            if (str_contains($page_title, "Submitted")) {
                echo "✓";
            } elseif (str_contains($page_title, "Already")) {
                echo "✓";
            } else {
                echo "!";
            }
            ?>
        </div>

        <h1><?php echo htmlspecialchars($heading); ?></h1>

        <p class="application-message">
            <?php echo htmlspecialchars($message); ?>
        </p>

        <?php if (isset($opportunity) && $opportunity): ?>
            <div class="opportunity-name">
                <?php echo htmlspecialchars($opportunity["title"]); ?>
            </div>
        <?php endif; ?>

        <?php if ($status_label !== ""): ?>
            <div>
                Application Status:
                <span class="status-badge">
                    <?php echo htmlspecialchars($status_label); ?>
                </span>
            </div>
        <?php endif; ?>

        <nav class="application-actions" aria-label="Application actions">

            <?php if ($show_browse): ?>
                <a class="btn" href="../opportunities.php">
                    Browse Opportunities
                </a>
            <?php endif; ?>

            <?php if ($show_applications): ?>
                <a class="btn" href="applications.php">
                    View My Applications
                </a>
            <?php endif; ?>

            <?php if ($show_dashboard): ?>
                <a class="btn" href="dashboard.php">
                    Volunteer Dashboard
                </a>
            <?php endif; ?>

        </nav>

    </section>
</main>

</body>
</html>

<?php
$conn->close();
?>
