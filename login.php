
<?php

session_start();

require_once "config/database.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] === "volunteer") {

                    header("Location: volunteer/dashboard.php");
                    exit();

                } elseif ($user["role"] === "organization") {

                    header("Location: organization/dashboard.php");
                    exit();

                } elseif ($user["role"] === "admin") {

                    header("Location: admin/dashboard.php");
                    exit();
                }

                session_unset();
                session_destroy();

                $message = "Invalid account role.";
                $messageType = "error";

            } else {

                $message = "Incorrect password.";
                $messageType = "error";
            }

        } else {

            $message = "No account found with this email.";
            $messageType = "error";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Local Volunteer Matching System</title>

    <link rel="stylesheet" href="assets/style.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .login-header {
            padding: 18px 7%;
            background: #ffffff;
            border-bottom: 1px solid #dce6e1;
        }

        .login-header a {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .login-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 20px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            padding: 35px;
            background: #ffffff;
            border: 1px solid #dce6e1;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(25, 60, 45, 0.09);
        }

        .login-card h1 {
            margin-top: 0;
            margin-bottom: 8px;
            text-align: center;
        }

        .login-subtitle {
            margin-bottom: 25px;
            color: #64756f;
            text-align: center;
        }

        .login-card label {
            display: block;
            margin-top: 10px;
        }

        .login-card input:not([type="checkbox"]):not([type="radio"]) {
            margin-bottom: 12px;
        }

        .login-card button {
            width: 100%;
            margin-top: 12px;
        }

        .login-message {
            padding: 12px;
            border-radius: 8px;
            background: #fff0ef;
            color: #a52822;
            margin-bottom: 18px;
        }

        .login-register {
            margin-top: 22px;
            text-align: center;
        }

        .login-footer {
            padding: 18px;
            text-align: center;
            color: #64756f;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 24px 20px;
            }
        }
    </style>
</head>

<body>

    <header class="login-header">
        <a href="index.php">Volunteer Connect</a>
    </header>

    <main class="login-main">
        <section class="login-card">

            <h1>Welcome Back!</h1>

            <p class="login-subtitle">
                Log in to continue making a difference in your community.
            </p>

            <?php if (!empty($message)): ?>
                <div class="login-message" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">

                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    autocomplete="email"
                    value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                    required
                >

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

                <button type="submit">Login to Your Account</button>

            </form>

            <p class="login-register">
                Don't have an account?
                <a href="register.php">Create an account</a>
            </p>

        </section>
    </main>

    <footer class="login-footer">
        &copy; <?php echo date('Y'); ?> Local Volunteer Matching System
    </footer>

</body>
</html>
