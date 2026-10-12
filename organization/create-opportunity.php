
<?php
session_start();

require_once "../config/database.php";

// Check login.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check organization role.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organization') {
    header("Location: ../index.php");
    exit();
}

$message = "";
$error = "";

$title = "";
$description = "";
$location = "";
$required_skills = "";
$event_date = "";

// Handle form submission.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $required_skills = trim($_POST['required_skills'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');

    if (
        $title === '' ||
        $description === '' ||
        $location === '' ||
        $required_skills === '' ||
        $event_date === ''
    ) {
        $error = "Please fill in all fields.";
    } else {

        $user_id = (int) $_SESSION['user_id'];

        // Find the organization profile for this user.
        $organization_sql = "
            SELECT id
            FROM organizations
            WHERE user_id = ?
        ";

        $organization_stmt = mysqli_prepare($conn, $organization_sql);

        if ($organization_stmt) {

            mysqli_stmt_bind_param($organization_stmt, "i", $user_id);
            mysqli_stmt_execute($organization_stmt);

            $organization_result = mysqli_stmt_get_result($organization_stmt);
            $organization = mysqli_fetch_assoc($organization_result);

            mysqli_stmt_close($organization_stmt);

            if (!$organization) {
                $error = "Organization information not found. Please contact the administrator.";
            } else {

                $organization_id = (int) $organization['id'];

                $sql = "
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
                ";

                $stmt = mysqli_prepare($conn, $sql);

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "isssss",
                        $organization_id,
                        $title,
                        $description,
                        $location,
                        $required_skills,
                        $event_date
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        $message = "Opportunity created successfully!";

                        // Clear fields after successful creation.
                        $title = "";
                        $description = "";
                        $location = "";
                        $required_skills = "";
                        $event_date = "";

                    } else {
                        // Log technical details without showing database errors to visitors.
                        error_log("Create opportunity failed: " . mysqli_stmt_error($stmt));
                        $error = "Unable to create the opportunity. Please try again.";
                    }

                    mysqli_stmt_close($stmt);

                } else {
                    error_log("Opportunity insert preparation failed: " . mysqli_error($conn));
                    $error = "A database error occurred. Please try again.";
                }
            }

        } else {
            error_log("Organization query preparation failed: " . mysqli_error($conn));
            $error = "A database error occurred. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Opportunity | Local Volunteer Matching System</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .create-page {
            max-width: 760px;
            margin: 0 auto;
            padding: 28px 16px 40px;
        }

        .create-card {
            padding: 30px;
        }

        .create-heading {
            text-align: center;
            margin-bottom: 8px;
        }

        .create-description {
            text-align: center;
            margin: 0 0 25px;
            color: var(--muted, #667085);
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            display: block;
            width: 100%;
            padding: 12px 13px;
            border: 1px solid var(--border, #d5ded7);
            border-radius: var(--radius, 8px);
            background: var(--surface, #fff);
            color: var(--text, #202820);
            font: inherit;
            box-sizing: border-box;
        }

        .form-group input {
            min-height: 46px;
        }

        .form-group textarea {
            min-height: 125px;
            resize: vertical;
            line-height: 1.6;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: 2px solid var(--primary, #26834a);
            outline-offset: 2px;
        }

        .field-hint {
            display: block;
            margin-top: 6px;
            color: var(--muted, #667085);
            font-size: 13px;
        }

        .form-message {
            padding: 13px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .success {
            background: #e7f6eb;
            color: #176534;
            border: 1px solid #b7e2c3;
        }

        .error {
            background: #fff0f0;
            color: #a12626;
            border: 1px solid #f1c2c2;
        }

        .form-actions {
            display: grid;
            gap: 12px;
            margin-top: 25px;
        }

        .submit-button {
            width: 100%;
            min-height: 48px;
            cursor: pointer;
            font: inherit;
            font-weight: 600;
        }

        .back-link {
            text-align: center;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .create-page {
                padding: 16px 12px 28px;
            }

            .create-card {
                padding: 21px 17px;
            }
        }
    </style>
</head>

<body>

<main class="create-page">
    <section class="card create-card">

        <h1 class="create-heading">Create Volunteer Opportunity</h1>

        <p class="create-description">
            Share your organization's volunteering event and connect with people
            who want to make a difference.
        </p>

        <?php if ($message !== ''): ?>
            <div class="form-message success" role="status">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="form-message error" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="title">Opportunity Title *</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Example: Tree Plantation Drive"
                    value="<?php echo htmlspecialchars($title); ?>"
                    maxlength="255"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Explain the event, activities, and how volunteers can help..."
                    required
                ><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div class="form-group">
                <label for="location">Location *</label>
                <input
                    type="text"
                    id="location"
                    name="location"
                    placeholder="Example: Mumbai, Maharashtra"
                    value="<?php echo htmlspecialchars($location); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="required_skills">Required Skills *</label>
                <input
                    type="text"
                    id="required_skills"
                    name="required_skills"
                    placeholder="Example: Teamwork, Communication"
                    value="<?php echo htmlspecialchars($required_skills); ?>"
                    required
                >
                <span class="field-hint">
                    Separate multiple skills with commas.
                </span>
            </div>

            <div class="form-group">
                <label for="event_date">Event Date *</label>
                <input
                    type="date"
                    id="event_date"
                    name="event_date"
                    value="<?php echo htmlspecialchars($event_date); ?>"
                    required
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn submit-button">
                    Create Opportunity
                </button>

                <a class="back-link" href="dashboard.php">
                    &larr; Back to Organization Dashboard
                </a>
            </div>

        </form>

    </section>
</main>

</body>
</html>

<?php
mysqli_close($conn);
?>
