
<?php
session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only organizations can access this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "organization") {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

// Get organization ID
$stmt = $conn->prepare(
    "SELECT id, organization_name
     FROM organizations
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$organization_result = $stmt->get_result();

if ($organization_result->num_rows !== 1) {
    die("Organization information not found.");
}

$organization = $organization_result->fetch_assoc();
$organization_id = (int) $organization["id"];

$stmt->close();

// Handle Accept / Reject
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $application_id = filter_input(
        INPUT_POST,
        "application_id",
        FILTER_VALIDATE_INT
    );

    $new_status = $_POST["status"] ?? "";

    if (
        $application_id &&
        in_array($new_status, ["accepted", "rejected"], true)
    ) {
        // Ensure the application belongs to this organization
        $check_sql = "SELECT applications.id
                      FROM applications
                      INNER JOIN opportunities
                        ON applications.opportunity_id = opportunities.id
                      WHERE applications.id = ?
                        AND opportunities.organization_id = ?";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param(
            "ii",
            $application_id,
            $organization_id
        );
        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 1) {
            $update_sql = "UPDATE applications
                           SET status = ?
                           WHERE id = ?";

            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param(
                "si",
                $new_status,
                $application_id
            );
            $update_stmt->execute();
            $update_stmt->close();
        }

        $check_stmt->close();
    }

    header("Location: applicants.php");
    exit();
}

// Get applicants
$sql = "SELECT
            applications.id AS application_id,
            applications.status,
            applications.applied_at,
            users.name,
            users.email,
            opportunities.title,
            opportunities.event_date,
            opportunities.location
        FROM applications
        INNER JOIN users
            ON applications.volunteer_id = users.id
        INNER JOIN opportunities
            ON applications.opportunity_id = opportunities.id
        WHERE opportunities.organization_id = ?
        ORDER BY applications.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organization_id);
$stmt->execute();

$applicants = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Volunteer Applicants | Volunteer Match</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .applicants-nav {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 16px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }

        .applicants-brand {
            color: var(--primary);
            font-size: 22px;
            font-weight: 700;
        }

        .applicants-nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }

        .applicants-nav-links a {
            font-weight: 600;
        }

        .applicants-container {
            width: 95%;
            max-width: 1250px;
            margin: 35px auto;
        }

        .applicants-heading {
            background: linear-gradient(135deg, #e4f4ec, #fff);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 26px;
            margin-bottom: 24px;
        }

        .applicants-heading h1 {
            margin: 0 0 10px;
            font-size: clamp(25px, 4vw, 34px);
        }

        .applicants-heading p {
            color: var(--muted);
            margin: 0;
            overflow-wrap: anywhere;
        }

        .table-panel {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        .applicants-table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }

        .applicants-table th {
            background: #e8f2ed;
            color: var(--text);
            font-size: 13px;
            white-space: nowrap;
        }

        .applicants-table td {
            vertical-align: middle;
            font-size: 14px;
        }

        .applicants-table tbody tr:hover td {
            background: #f7fbf8;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-pending {
            color: #895500;
            background: #fff1ce;
        }

        .status-accepted {
            color: #176b3a;
            background: #e0f4e7;
        }

        .status-rejected {
            color: #a52a20;
            background: #fde8e6;
        }

        .status-unknown {
            color: #4b5563;
            background: #edf0f2;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .action-buttons form {
            margin: 0;
        }

        .action-buttons button {
            padding: 7px 11px;
            font-size: 13px;
        }

        .action-buttons .reject-button {
            background: #fff0ee;
            color: var(--danger);
            border: 1px solid #f0cbc6;
        }

        .action-buttons .reject-button:hover {
            background: #fbe0dc;
        }

        .no-applicants {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 38px 22px;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .no-applicants h2 {
            margin-top: 0;
        }

        .no-applicants p {
            color: var(--muted);
        }

        .applicants-footer {
            text-align: center;
            padding: 24px 12px;
            color: var(--muted);
            font-size: 14px;
        }

        @media (max-width: 650px) {
            .applicants-container {
                margin-top: 22px;
            }

            .applicants-heading {
                padding: 21px;
            }

            .applicants-nav-links {
                gap: 12px;
            }
        }
    </style>
</head>

<body>

    <nav class="applicants-nav">
        <a href="../index.php" class="applicants-brand">
            Volunteer Match
        </a>

        <div class="applicants-nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="create-opportunity.php">Create Opportunity</a>
            <a href="manage-opportunities.php">Manage Opportunities</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>

    <main class="applicants-container">

        <header class="applicants-heading">
            <h1>Volunteer Applicants</h1>
            <p>
                Organization:
                <strong>
                    <?php
                    echo htmlspecialchars($organization["organization_name"]);
                    ?>
                </strong>
            </p>
            <p>
                Review applications and update each volunteer's application status.
            </p>
        </header>

        <?php if ($applicants->num_rows > 0): ?>

            <section class="table-panel">
                <div class="table-scroll">

                    <table class="applicants-table">
                        <thead>
                            <tr>
                                <th>Volunteer</th>
                                <th>Email</th>
                                <th>Opportunity</th>
                                <th>Event Date</th>
                                <th>Location</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($applicant = $applicants->fetch_assoc()): ?>

                                <?php
                                $status = strtolower(
                                    trim($applicant["status"] ?? "")
                                );

                                $status_class = "status-unknown";

                                if ($status === "pending") {
                                    $status_class = "status-pending";
                                } elseif ($status === "accepted") {
                                    $status_class = "status-accepted";
                                } elseif ($status === "rejected") {
                                    $status_class = "status-rejected";
                                }
                                ?>

                                <tr>
                                    <td>
                                        <?php
                                        echo htmlspecialchars($applicant["name"]);
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars($applicant["email"]);
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars($applicant["title"]);
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $applicant["event_date"] ?? "Not specified"
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $applicant["location"] ?? "Not specified"
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $applicant["applied_at"] ?? "Not available"
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php
                                            echo htmlspecialchars(
                                                $status !== ""
                                                    ? ucfirst($status)
                                                    : "Unknown"
                                            );
                                            ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if ($status === "pending"): ?>

                                            <div class="action-buttons">

                                                <form method="POST" action="applicants.php">
                                                    <input
                                                        type="hidden"
                                                        name="application_id"
                                                        value="<?php
                                                            echo (int) $applicant["application_id"];
                                                        ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="status"
                                                        value="accepted"
                                                    >

                                                    <button type="submit">
                                                        Accept
                                                    </button>
                                                </form>

                                                <form
                                                    method="POST"
                                                    action="applicants.php"
                                                    onsubmit="return confirm('Are you sure you want to reject this application?');"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="application_id"
                                                        value="<?php
                                                            echo (int) $applicant["application_id"];
                                                        ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="status"
                                                        value="rejected"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="reject-button"
                                                    >
                                                        Reject
                                                    </button>
                                                </form>

                                            </div>

                                        <?php else: ?>

                                            <span class="status-badge status-unknown">
                                                Reviewed
                                            </span>

                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endwhile; ?>
                        </tbody>
                    </table>

                </div>
            </section>

        <?php else: ?>

            <section class="no-applicants">
                <h2>No Applicants Yet</h2>
                <p>
                    Volunteers have not applied to your opportunities yet.
                    New applications will appear here.
                </p>

                <a class="btn" href="manage-opportunities.php">
                    Manage Opportunities
                </a>
            </section>

        <?php endif; ?>

        <?php $stmt->close(); ?>

    </main>

    <footer class="applicants-footer">
        Local Volunteer Matching System &copy; <?php echo date("Y"); ?>
    </footer>

</body>
</html>
