<?php
session_start();

require_once "../config/database.php";

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* Check organization role */
if ($_SESSION['role'] !== 'organization') {
    header("Location: ../index.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $required_skills = trim($_POST['required_skills']);
    $event_date = $_POST['event_date'];

    /* Basic validation */
    if (
        empty($title) ||
        empty($description) ||
        empty($location) ||
        empty($required_skills) ||
        empty($event_date)
    ) {
        $error = "Please fill in all fields.";
    } else {

        $organization_id = $_SESSION['user_id'];

        $sql = "INSERT INTO opportunities
                (organization_id, title, description, location, required_skills, event_date)
                VALUES (?, ?, ?, ?, ?, ?)";

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

                /* Clear form values */
                $title = "";
                $description = "";
                $location = "";
                $required_skills = "";
                $event_date = "";

            } else {
                $error = "Error creating opportunity: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);

        } else {
            $error = "Database error: " . mysqli_error($conn);
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

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 600px;
            max-width: 90%;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }

    </style>
</head>

<body>

<div class="container">

    <h2>Create Volunteer Opportunity</h2>

    <?php if (!empty($message)): ?>
        <div class="success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="title">Opportunity Title</label>

        <input
            type="text"
            id="title"
            name="title"
            placeholder="Example: Tree Plantation Drive"
            value="<?php echo htmlspecialchars($title ?? ''); ?>"
            required
        >

        <label for="description">Description</label>

        <textarea
            id="description"
            name="description"
            placeholder="Describe the volunteer opportunity..."
            required
        ><?php echo htmlspecialchars($description ?? ''); ?></textarea>

        <label for="location">Location</label>

        <input
            type="text"
            id="location"
            name="location"
            placeholder="Example: Mumbai, Maharashtra"
            value="<?php echo htmlspecialchars($location ?? ''); ?>"
            required
        >

        <label for="required_skills">Required Skills</label>

        <input
            type="text"
            id="required_skills"
            name="required_skills"
            placeholder="Example: Teamwork, Communication"
            value="<?php echo htmlspecialchars($required_skills ?? ''); ?>"
            required
        >

        <label for="event_date">Event Date</label>

        <input
            type="date"
            id="event_date"
            name="event_date"
            value="<?php echo htmlspecialchars($event_date ?? ''); ?>"
            required
        >

        <button type="submit">
            Create Opportunity
        </button>

    </form>

    <a class="back" href="dashboard.php">
        ← Back to Organization Dashboard
    </a>

</div>

</body>

</html>