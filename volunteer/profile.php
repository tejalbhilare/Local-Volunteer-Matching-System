<?php

session_start();

require_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Check if user is a volunteer
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$message = "";
$error = "";

/* -----------------------------
   UPDATE PROFILE
----------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $phone = trim($_POST["phone"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $skills = trim($_POST["skills"] ?? "");
    $interests = trim($_POST["interests"] ?? "");
    $availability = trim($_POST["availability"] ?? "");

    // Check if profile already exists
    $check_stmt = $conn->prepare(
        "SELECT id FROM volunteer_profiles WHERE user_id = ?"
    );

    if (!$check_stmt) {
        $error = "Database error: " . $conn->error;
    } else {

        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            // Update existing profile
            $update_stmt = $conn->prepare(
                "UPDATE volunteer_profiles
                 SET phone = ?, city = ?, skills = ?, interests = ?, availability = ?
                 WHERE user_id = ?"
            );

            if ($update_stmt) {

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
                } else {
                    $error = "Error updating profile: " . $update_stmt->error;
                }

                $update_stmt->close();

            } else {
                $error = "Database error: " . $conn->error;
            }

        } else {

            // Create new profile
            $insert_stmt = $conn->prepare(
                "INSERT INTO volunteer_profiles
                (user_id, phone, city, skills, interests, availability)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            if ($insert_stmt) {

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
                } else {
                    $error = "Error creating profile: " . $insert_stmt->error;
                }

                $insert_stmt->close();

            } else {
                $error = "Database error: " . $conn->error;
            }
        }

        $check_stmt->close();
    }
}

/* -----------------------------
   GET USER INFORMATION
----------------------------- */

$user_stmt = $conn->prepare(
    "SELECT name, email FROM users WHERE id = ?"
);

$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();

$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$user_stmt->close();

/* -----------------------------
   GET VOLUNTEER PROFILE
----------------------------- */

$profile_stmt = $conn->prepare(
    "SELECT phone, city, skills, interests, availability
     FROM volunteer_profiles
     WHERE user_id = ?"
);

$profile_stmt->bind_param("i", $user_id);
$profile_stmt->execute();

$profile_result = $profile_stmt->get_result();

if ($profile_result->num_rows > 0) {

    $profile = $profile_result->fetch_assoc();

} else {

    $profile = [
        "phone" => "",
        "city" => "",
        "skills" => "",
        "interests" => "",
        "availability" => ""
    ];
}

$profile_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Volunteer Profile</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #333;
            padding: 15px;
            text-align: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .container {
            width: 650px;
            max-width: 90%;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            margin-top: 25px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }

        .personal-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
        }

        .personal-info p {
            margin: 8px 0;
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
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
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
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
        }

    </style>

</head>

<body>

    <div class="navbar">

        <a href="dashboard.php">Dashboard</a>

        <a href="../opportunities.php">Opportunities</a>

        <a href="applications.php">My Applications</a>

        <a href="profile.php">Profile</a>

        <a href="../logout.php">Logout</a>

    </div>


    <div class="container">

        <h1>Volunteer Profile</h1>


        <?php if ($message != "") { ?>

            <div class="success">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>


        <?php if ($error != "") { ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <h2>Personal Information</h2>

        <div class="personal-info">

            <p>
                <strong>Name:</strong>
                <?php echo htmlspecialchars($user["name"]); ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?php echo htmlspecialchars($user["email"]); ?>
            </p>

        </div>


        <h2>Volunteer Information</h2>


        <form method="POST" action="profile.php">

            <label for="phone">Phone</label>

            <input
                type="text"
                id="phone"
                name="phone"
                value="<?php echo htmlspecialchars($profile["phone"]); ?>"
                placeholder="Enter phone number"
            >


            <label for="city">City</label>

            <input
                type="text"
                id="city"
                name="city"
                value="<?php echo htmlspecialchars($profile["city"]); ?>"
                placeholder="Enter city"
            >


            <label for="skills">Skills</label>

            <textarea
                id="skills"
                name="skills"
                rows="4"
                placeholder="Example: Web Development, HTML, CSS, PHP"
            ><?php echo htmlspecialchars($profile["skills"]); ?></textarea>


            <label for="interests">Interests</label>

            <textarea
                id="interests"
                name="interests"
                rows="4"
                placeholder="Example: Education, Environment, Community Service"
            ><?php echo htmlspecialchars($profile["interests"]); ?></textarea>


            <label for="availability">Availability</label>

            <input
                type="text"
                id="availability"
                name="availability"
                value="<?php echo htmlspecialchars($profile["availability"]); ?>"
                placeholder="Example: Weekends"
            >


            <button type="submit">
                Update Profile
            </button>

        </form>


        <a class="back" href="dashboard.php">
            ← Back to Dashboard
        </a>

    </div>

</body>

</html>