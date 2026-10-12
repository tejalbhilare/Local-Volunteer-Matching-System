
<?php

require_once "config/database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $role = $_POST["role"] ?? "";

    if (
        $name === "" ||
        $email === "" ||
        $password === "" ||
        $confirm_password === "" ||
        $role === ""
    ) {

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

    } elseif (!in_array($role, ["volunteer", "organization"], true)) {

        $message = "Please select a valid role.";
        $message_type = "error";

    } else {

        $check_stmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        if (!$check_stmt) {

            $message = "Unable to check the email. Please try again.";
            $message_type = "error";

        } else {

            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {

                $message = "An account with this email already exists.";
                $message_type = "error";

            } else {

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $insert_stmt = $conn->prepare(
                    "INSERT INTO users (name, email, password, role)
                     VALUES (?, ?, ?, ?)"
                );

                if (!$insert_stmt) {

                    $message = "Unable to create the account. Please try again.";
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

                        $user_id = $conn->insert_id;
                        $profile_created = false;

                        if ($role === "volunteer") {

                            $profile_stmt = $conn->prepare(
                                "INSERT INTO volunteer_profiles (user_id)
                                 VALUES (?)"
                            );

                            if ($profile_stmt) {
                                $profile_stmt->bind_param("i", $user_id);
                                $profile_created = $profile_stmt->execute();
                                $profile_stmt->close();
                            }

                        } elseif ($role === "organization") {

                            $organization_stmt = $conn->prepare(
                                "INSERT INTO organizations
                                 (user_id, organization_name)
                                 VALUES (?, ?)"
                            );

                            if ($organization_stmt) {
                                $organization_stmt->bind_param(
                                    "is",
                                    $user_id,
                                    $name
                                );

                                $profile_created = $organization_stmt->execute();
                                $organization_stmt->close();
                            }
                        }

                        $insert_stmt->close();
                        $check_stmt->close();

                        if ($profile_created) {
                            header("Location: login.php?registered=1");
                            exit();
                        }

                        $message = "Account creation was incomplete. Please contact the administrator.";
                        $message_type = "error";

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

    <title>Create Account | Local Volunteer Matching System</title>

    <link rel="stylesheet" href="assets/style.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .register-header {
            padding: 18px 7%;
            background: #ffffff;
            border-bottom: 1px solid #dce6e1;
        }

        .register-header a {
            color: #167d63;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .register-main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 35px 20px;
        }

        .register-container {
            width: 100%;
            max-width: 480px;
            padding: 32px;
            background: #ffffff;
            border: 1px solid #dce6e1;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(25, 60, 45, 0.09);
        }

        .register-container h1 {
            margin: 0 0 8px;
            text-align: center;
        }

        .subtitle {
            margin: 0 0 25px;
            color: #64756f;
            text-align: center;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            margin-top: 4px;
            margin-bottom: 5px;
        }

        .register-container button {
            width: 100%;
            margin-top: 10px;
        }

        .message {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 8px;
        }

        .message.error {
            background: #fff0ef;
            color: #a52822;
        }

        .login-link {
            margin-top: 22px;
            text-align: center;
        }

        .register-footer {
            padding: 18px;
            text-align: center;
            color: #64756f;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 24px 20px;
            }
        }
    </style>
</head>

<body>

    <header class="register-header">
        <a href="index.php">Volunteer Connect</a>
    </header>

    <main class="register-main">
        <section class="register-container">

            <h1>Create Account</h1>

            <p class="subtitle">
                Join the Local Volunteer Matching System
            </p>

            <?php if ($message !== ""): ?>
                <div
                    class="message <?php echo htmlspecialchars($message_type); ?>"
                    role="alert"
                >
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Minimum 6 characters"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Enter password again"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >
                </div>

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

                <button type="submit">Create Account</button>

            </form>

            <p class="login-link">
                Already have an account?
                <a href="login.php">Login here</a>
            </p>

        </section>
    </main>

    <footer class="register-footer">
        &copy; <?php echo date('Y'); ?> Local Volunteer Matching System
    </footer>

</body>
</html>
