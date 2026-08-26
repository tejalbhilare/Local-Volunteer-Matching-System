<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only volunteers can access this page
if ($_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";

// Fetch volunteer's existing profile
$stmt = $conn->prepare("
    SELECT phone, city, skills, interests, availability
    FROM volunteer_profiles
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$profile = $result->fetch_assoc();

// Set default values
$phone = $profile["phone"] ?? "";
$city = $profile["city"] ?? "";
$skills = $profile["skills"] ?? "";
$interests = $profile["interests"] ?? "";
$availability = $profile["availability"] ?? "";


// Handle profile submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $phone = trim($_POST["phone"]);
    $city = trim($_POST["city"]);
    $skills = trim($_POST["skills"]);
    $interests = trim($_POST["interests"]);
    $availability = trim($_POST["availability"]);


    // Check if profile already exists
    $check_stmt = $conn->prepare("
        SELECT id
        FROM volunteer_profiles
        WHERE user_id = ?
    ");

    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();

    $check_result = $check_stmt->get_result();


    if ($check_result->num_rows > 0) {

        // Update existing profile
        $update_stmt = $conn->prepare("
            UPDATE volunteer_profiles
            SET phone = ?,
                city = ?,
                skills = ?,
                interests = ?,
                availability = ?
            WHERE user_id = ?
        ");

        $update_stmt->bind_param(
            "sssssi",
            $phone,
            $city,
            $skills,
            $interests,
            $availability,
            $user_id
        );

        if ($update_stmt->execute()) {
            $message = "Profile updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating profile.";
            $message_type = "error";
        }

    } else {

        // Create new profile
        $insert_stmt = $conn->prepare("
            INSERT INTO volunteer_profiles
            (user_id, phone, city, skills, interests, availability)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $insert_stmt->bind_param(
            "isssss",
            $user_id,
            $phone,
            $city,
            $skills,
            $interests,
            $availability
        );

        if ($insert_stmt->execute()) {
            $message = "Profile created successfully!";
            $message_type = "success";
        } else {
            $message = "Error creating profile.";
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

    <title>My Profile - Local Volunteer Matching System</title>

</head>

<body>

    <h1>Local Volunteer Matching System</h1>

    <hr>

    <h2>My Volunteer Profile</h2>

    <p>
        Welcome,
        <strong><?php echo htmlspecialchars($_SESSION["name"]); ?></strong>!
    </p>


    <?php if (!empty($message)): ?>

        <p>
            <strong>
                <?php echo htmlspecialchars($message); ?>
            </strong>
        </p>

    <?php endif; ?>


    <form method="POST">

        <label for="phone">Phone Number:</label>
        <br>

        <input
            type="text"
            id="phone"
            name="phone"
            value="<?php echo htmlspecialchars($phone); ?>"
            maxlength="20"
        >

        <br><br>


        <label for="city">City:</label>
        <br>

        <input
            type="text"
            id="city"
            name="city"
            value="<?php echo htmlspecialchars($city); ?>"
            maxlength="100"
        >

        <br><br>


        <label for="skills">Skills:</label>
        <br>

        <textarea
            id="skills"
            name="skills"
            rows="4"
            cols="40"
            placeholder="Example: Teaching, Web Development, Event Management"
        ><?php echo htmlspecialchars($skills); ?></textarea>

        <br><br>


        <label for="interests">Interests:</label>
        <br>

        <textarea
            id="interests"
            name="interests"
            rows="4"
            cols="40"
            placeholder="Example: Education, Environment, Community Service"
        ><?php echo htmlspecialchars($interests); ?></textarea>

        <br><br>


        <label for="availability">Availability:</label>
        <br>

        <input
            type="text"
            id="availability"
            name="availability"
            value="<?php echo htmlspecialchars($availability); ?>"
            maxlength="100"
            placeholder="Example: Weekends, Evenings"
        >

        <br><br>


        <button type="submit">
            Save Profile
        </button>

    </form>

    <hr>

    <p>
        <a href="dashboard.php">
            ← Back to Dashboard
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

</body>

</html>