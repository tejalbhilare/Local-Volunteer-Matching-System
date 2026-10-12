
<?php
session_start();

require_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Check volunteer role
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "volunteer") {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$message = "";
$error = "";

// UPDATE PROFILE
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $phone = trim($_POST["phone"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $skills = trim($_POST["skills"] ?? "");
    $interests = trim($_POST["interests"] ?? "");
    $availability = trim($_POST["availability"] ?? "");

    $check_stmt = $conn->prepare(
        "SELECT id FROM volunteer_profiles WHERE user_id = ?"
    );

    if (!$check_stmt) {
        $error = "Unable to check your profile. Please try again.";
    } else {
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

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
                    $message = "Your profile has been updated successfully!";
                } else {
                    $error = "Unable to update your profile. Please try again.";
                }

                $update_stmt->close();
            } else {
                $error = "Unable to prepare the profile update.";
            }

        } else {

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
                    $message = "Your profile has been created successfully!";
                } else {
                    $error = "Unable to create your profile. Please try again.";
                }

                $insert_stmt->close();
            } else {
                $error = "Unable to prepare the profile creation.";
            }
        }

        $check_stmt->close();
    }
}

// GET USER INFORMATION
$user_stmt = $conn->prepare(
    "SELECT name, email FROM users WHERE id = ?"
);

$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();

$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$user_stmt->close();

if (!$user) {
    die("User information not found.");
}

// GET VOLUNTEER PROFILE
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

    <title>My Volunteer Profile | Volunteer Match</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .profile-nav {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 16px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }

        .profile-brand {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
        }

        .profile-nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }

        .profile-nav-links a {
            font-weight: 600;
        }

        .profile-container {
            width: 90%;
            max-width: 760px;
            margin: 35px auto;
        }

        .profile-heading {
            background: linear-gradient(135deg, #e4f4ec, #fff);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 22px;
        }

        .profile-heading h1 {
            margin: 0 0 8px;
        }

        .profile-heading p {
            margin: 0;
            color: var(--muted);
        }

        .profile-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 28px;
            margin-bottom: 22px;
        }

        .profile-panel h2 {
            font-size: 20px;
            padding-bottom: 12px;
            margin: 0 0 18px;
            border-bottom: 1px solid var(--border);
        }

        .personal-info {
            display: grid;
            gap: 12px;
        }

        .info-item {
            padding: 13px 15px;
            border-radius: 8px;
            background: #f4f8f6;
            overflow-wrap: anywhere;
        }

        .info-label {
            display: block;
            font-size: 13px;
            color: var(--muted);
            font-weight: 600;
            margin-bottom: 3px;
        }

        .info-value {
            font-weight: 600;
        }

        .profile-form label {
            display: block;
            margin: 15px 0 5px;
            font-weight: 600;
        }

        .profile-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-help {
            color: var(--muted);
            font-size: 13px;
            margin-top: -8px;
        }

        .profile-form button {
            width: 100%;
            margin-top: 20px;
        }

        .profile-message {
            padding: 13px 15px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .profile-message.success {
            background: #e6f5eb;
            color: #176b3a;
            border: 1px solid #c4e8d0;
        }

        .profile-message.error {
            background: #fff0ee;
            color: #a52a20;
            border: 1px solid #f0cbc6;
        }

        .back-link {
            display: inline-block;
            margin: 4px 0 25px;
            font-weight: 600;
        }

        .profile-footer {
            text-align: center;
            padding: 24px 12px;
            color: var(--muted);
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .profile-container {
                width: 92%;
                margin-top: 22px;
            }

            .profile-panel {
                padding: 20px;
            }

            .profile-nav-links {
                gap: 12px;
            }
        }
    </style>
</head>

<body>

    <nav class="profile-nav">
        <a href="../index.php" class="profile-brand">Volunteer Match</a>

        <div class="profile-nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="../opportunities.php">Opportunities</a>
            <a href="applications.php">My Applications</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>

    <main class="profile-container">

        <header class="profile-heading">
            <h1>My Volunteer Profile</h1>
            <p>
                Keep your information updated so you can better connect
                with community opportunities.
            </p>
        </header>

        <?php if ($message !== ""): ?>
            <div class="profile-message success" role="status">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="profile-message error" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <section class="profile-panel">
            <h2>Personal Information</h2>

            <div class="personal-info">
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars($user["name"]); ?>
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">Email Address</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars($user["email"]); ?>
                    </span>
                </div>
            </div>
        </section>

        <section class="profile-panel">
            <h2>Volunteer Information</h2>

            <form method="POST" action="profile.php" class="profile-form">

                <label for="phone">Phone Number</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?php echo htmlspecialchars($profile["phone"] ?? ""); ?>"
                    placeholder="Enter your phone number"
                    autocomplete="tel"
                >

                <label for="city">City</label>
                <input
                    type="text"
                    id="city"
                    name="city"
                    value="<?php echo htmlspecialchars($profile["city"] ?? ""); ?>"
                    placeholder="Enter your city"
                    autocomplete="address-level2"
                >

                <label for="skills">Skills</label>
                <textarea
                    id="skills"
                    name="skills"
                    rows="4"
                    placeholder="Example: HTML, CSS, PHP, Web Development"
                ><?php echo htmlspecialchars($profile["skills"] ?? ""); ?></textarea>
                <p class="form-help">
                    List the skills you can contribute to volunteer activities.
                </p>

                <label for="interests">Interests</label>
                <textarea
                    id="interests"
                    name="interests"
                    rows="4"
                    placeholder="Example: Education, Environment, Community Service"
                ><?php echo htmlspecialchars($profile["interests"] ?? ""); ?></textarea>
                <p class="form-help">
                    Tell organizations which causes you are interested in.
                </p>

                <label for="availability">Availability</label>
                <input
                    type="text"
                    id="availability"
                    name="availability"
                    value="<?php echo htmlspecialchars($profile["availability"] ?? ""); ?>"
                    placeholder="Example: Weekends or weekday evenings"
                >

                <button type="submit">Save Profile Changes</button>

            </form>
        </section>

        <a class="back-link" href="dashboard.php">
            ← Back to Volunteer Dashboard
        </a>

    </main>

    <footer class="profile-footer">
        Local Volunteer Matching System &copy; <?php echo date("Y"); ?>
    </footer>

</body>
</html>
