<?php

session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only admin can access
if ($_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Delete user
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["delete_user"])) {

        $user_id = intval($_POST["user_id"]);

        // Prevent admin from deleting their own account
        if ($user_id === intval($_SESSION["user_id"])) {

            $error = "You cannot delete your own admin account.";

        } else {

            $stmt = $conn->prepare(
                "DELETE FROM users WHERE id = ?"
            );

            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {

                $success = "User deleted successfully.";

            } else {

                $error = "Failed to delete user.";

            }

            $stmt->close();
        }
    }
}

// Get all users
$result = $conn->query(
    "SELECT id, name, email, role
     FROM users
     ORDER BY id ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users - Local Volunteer Matching System</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
        }

        nav {
            background-color: #2c3e50;
            padding: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
        }

        h1 {
            color: #2c3e50;
        }

        .message {
            padding: 12px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th,
        td {
            padding: 14px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .role {
            font-weight: bold;
        }

        .delete-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }

        .protected {
            color: #777;
        }

    </style>

</head>

<body>

<nav>

    <a href="dashboard.php">Admin Dashboard</a>

    <a href="users.php">Manage Users</a>

    <a href="../index.php">Home</a>

    <a href="../logout.php">Logout</a>

</nav>

<div class="container">

    <h1>Manage Users</h1>

    <p>
        View and manage registered users in the system.
    </p>

    <?php if (isset($success)) { ?>

        <div class="message success">
            <?php echo htmlspecialchars($success); ?>
        </div>

    <?php } ?>

    <?php if (isset($error)) { ?>

        <div class="message error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php } ?>

    <table>

        <tr>

            <th>ID</th>

            <th>Name</th>

            <th>Email</th>

            <th>Role</th>

            <th>Action</th>

        </tr>

        <?php while ($user = $result->fetch_assoc()) { ?>

            <tr>

                <td>
                    <?php echo $user["id"]; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($user["name"]); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($user["email"]); ?>
                </td>

                <td class="role">
                    <?php echo htmlspecialchars($user["role"]); ?>
                </td>

                <td>

                    <?php if ($user["id"] == $_SESSION["user_id"]) { ?>

                        <span class="protected">
                            Current Admin
                        </span>

                    <?php } else { ?>

                        <form method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this user? This may also delete related records.');">

                            <input
                                type="hidden"
                                name="user_id"
                                value="<?php echo $user["id"]; ?>"
                            >

                            <button
                                type="submit"
                                name="delete_user"
                                class="delete-button"
                            >
                                Delete
                            </button>

                        </form>

                    <?php } ?>

                </td>

            </tr>

        <?php } ?>

    </table>

</div>

</body>

</html>