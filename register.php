<?php

require_once "config/database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form data
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $role = $_POST["role"] ?? "";

    // -----------------------------
    // VALIDATION
    // -----------------------------

    if ($name === "" || $email === "" || $password === "" || $confirm_password === "" || $role === "") {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    } elseif (!in_array($role, ["volunteer", "organization"])) {

        $message = "Please select a valid role.";
        $message_type = "error";

    } else {

        // -----------------------------
        // CHECK EXISTING EMAIL
        // -----------------------------

        $check_sql = "SELECT id FROM users WHERE email = ?";

        $check_stmt = $conn->prepare($check_sql);

        if (!$check_stmt) {

            $message = "Database error: " . $conn->error;
            $message_type = "error";

        } else {

            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {

                $message = "An account with this email already exists.";
                $message_type = "error";

            } else {

                // -----------------------------
                // HASH PASSWORD
                // -----------------------------

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // -----------------------------
                // INSERT USER
                // -----------------------------

                $insert_sql = "
                    INSERT INTO users (name, email, password, role)
                    VALUES (?, ?, ?, ?)
                ";

                $insert_stmt = $conn->prepare($insert_sql);

                if (!$insert_stmt) {

                    $message = "Database error: " . $conn->error;
                    $message_type = "error";

                } else {

                    $insert_stmt->bind_param(
                        "ssss",
                        $name,
                        $email,
                        $hashed_password,
                        $role
                    );

                    if ($insert_stmt->execute()) {

                        // Get newly created user's ID
                        $user_id = $conn->insert_id;

                        // -----------------------------
                        // CREATE PROFILE
                        // -----------------------------

                        if ($role === "volunteer") {

                            $profile_sql = "
                                INSERT INTO volunteer_profiles (user_id)
                                VALUES (?)
                            ";

                            $profile_stmt = $conn->prepare($profile_sql);

                            if ($profile_stmt) {
                                $profile_stmt->bind_param("i", $user_id);
                                $profile_stmt->execute();
                                $profile_stmt->close();
                            }

                        } elseif ($role === "organization") {

                            $organization_name = $name;

                            $organization_sql = "
                                INSERT INTO organizations
                                (user_id, organization_name)
                                VALUES (?, ?)
                            ";

                            $organization_stmt = $conn->prepare($organization_sql);

                            if ($organization_stmt) {

                                $organization_stmt->bind_param(
                                    "is",
                                    $user_id,
                                    $organization_name
                                );

                                $organization_stmt->execute();
                                $organization_stmt->close();
                            }
                        }

                        $insert_stmt->close();
                        $check_stmt->close();

                        // Redirect to login
                        header("Location: login.php?registered=1");
                        exit();

                    } else {

                        $message = "Registration failed. Please try again.";
                        $message_type = "error";

                        $insert_stmt->close();
                    }
                }
            }

            $check_stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Local Volunteer Matching System</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-container {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        h1 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 10px;
            color: #222;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #333;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #333;
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 6px;
            background: #222;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #444;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
        }

        .error {
            background: #ffe5e5;
            color: #b00020;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #222;
            font-weight: bold;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

<div class="register-container">

    <h1>Create Account</h1>

    <p class="subtitle">
        Join the Local Volunteer Matching System
    </p>

    <?php if ($message !== ""): ?>

        <div class="message <?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <form method="POST" action="">

        <!-- Name -->

        <div class="form-group">

            <label for="name">Full Name</label>

            <input
                type="text"
                id="name"
                name="name"
                placeholder="Enter your full name"
                value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                required
            >

        </div>


        <!-- Email -->

        <div class="form-group">

            <label for="email">Email Address</label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                required
            >

        </div>


        <!-- Password -->

        <div class="form-group">

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Minimum 6 characters"
                required
            >

        </div>


        <!-- Confirm Password -->

        <div class="form-group">

            <label for="confirm_password">Confirm Password</label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="Enter password again"
                required
            >

        </div>


        <!-- Role -->

        <div class="form-group">

            <label for="role">Register As</label>

            <select id="role" name="role" required>

                <option value="">-- Select Role --</option>

                <option
                    value="volunteer"
                    <?php echo (($_POST["role"] ?? "") === "volunteer") ? "selected" : ""; ?>
                >
                    Volunteer
                </option>

                <option
                    value="organization"
                    <?php echo (($_POST["role"] ?? "") === "organization") ? "selected" : ""; ?>
                >
                    Organization
                </option>

            </select>

        </div>


        <!-- Submit -->

        <button type="submit">
            Create Account
        </button>

    </form>


    <div class="login-link">

        Already have an account?

        <a href="login.php">
            Login here
        </a>

    </div>

</div>

</body>

</html>