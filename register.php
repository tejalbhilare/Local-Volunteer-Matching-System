<?php

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = $_POST["role"];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (!in_array($role, ["volunteer", "organization"])) {
        $message = "Invalid role selected.";
    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "An account with this email already exists.";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $hashed_password,
                $role
            );

            if ($stmt->execute()) {

                $user_id = $stmt->insert_id;

                if ($role === "volunteer") {

                    $profile = $conn->prepare(
                        "INSERT INTO volunteer_profiles (user_id) VALUES (?)"
                    );

                    $profile->bind_param("i", $user_id);
                    $profile->execute();

                } elseif ($role === "organization") {

                    $organization = $conn->prepare(
                        "INSERT INTO organizations (user_id, organization_name) VALUES (?, ?)"
                    );

                    $organization->bind_param("is", $user_id, $name);
                    $organization->execute();
                }

                $message = "Registration successful! You can now login.";

            } else {

                $message = "Registration failed. Please try again.";
            }
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
</head>

<body>

    <h1>Create an Account</h1>

    <?php if (!empty($message)): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST" action="">

        <label for="name">Name:</label><br>

        <input
            type="text"
            id="name"
            name="name"
            required
        >

        <br><br>

        <label for="email">Email:</label><br>

        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <br><br>

        <label for="password">Password:</label><br>

        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <br><br>

        <label for="confirm_password">Confirm Password:</label><br>

        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            required
        >

        <br><br>

        <label for="role">Register as:</label><br>

        <select id="role" name="role" required>

            <option value="">-- Select Role --</option>

            <option value="volunteer">
                Volunteer
            </option>

            <option value="organization">
                Organization
            </option>

        </select>

        <br><br>

        <button type="submit">
            Register
        </button>

    </form>

    <p>
        Already have an account?
        <a href="login.php">Login</a>
    </p>

</body>

</html>