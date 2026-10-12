
<?php
session_start();

require_once "../config/database.php";

// Check login.
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only administrators can access this page.
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

// Delete user.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_user"])) {

    $user_id = filter_input(INPUT_POST, "user_id", FILTER_VALIDATE_INT);

    if (!$user_id || $user_id < 1) {
        $error = "Invalid user selected.";
    } elseif ($user_id === (int) $_SESSION["user_id"]) {
        $error = "You cannot delete your own admin account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");

        if ($stmt) {
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "User deleted successfully.";
                } else {
                    $error = "User not found. The list may already be up to date.";
                }
            } else {
                // Related records or database constraints may prevent deletion.
                error_log("User deletion failed: " . $stmt->error);
                $error = "Unable to delete this user. They may have related records.";
            }

            $stmt->close();
        } else {
            error_log("Delete-user query preparation failed: " . $conn->error);
            $error = "A database error occurred. Please try again.";
        }
    }
}

// Get all users.
$result = $conn->query(
    "SELECT id, name, email, role
     FROM users
     ORDER BY id ASC"
);

if (!$result) {
    error_log("Manage users query failed: " . $conn->error);
    die("Unable to load users at this time. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users | Local Volunteer Matching System</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .users-container {
            max-width: 1150px;
            margin: 0 auto;
            padding: 28px 16px 40px;
        }

        .users-header {
            padding: 25px;
            margin-bottom: 22px;
        }

        .users-header h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .users-header p {
            margin-bottom: 0;
            color: var(--muted, #667085);
            line-height: 1.6;
        }

        .users-nav {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 22px;
        }

        .users-nav a {
            text-decoration: none;
        }

        .message {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
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

        .users-table-card {
            padding: 20px;
        }

        .table-heading {
            margin: 0 0 15px;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border, #e0e7e1);
            border-radius: 8px;
        }

        .users-table {
            width: 100%;
            min-width: 720px;
            border-collapse: collapse;
            background: var(--surface, #ffffff);
        }

        .users-table th,
        .users-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid var(--border, #e0e7e1);
            vertical-align: middle;
        }

        .users-table th {
            background: var(--primary-dark, #176534);
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
        }

        .users-table tbody tr:nth-child(even) {
            background: var(--background, #f5f8f5);
        }

        .users-table tbody tr:last-child td {
            border-bottom: none;
        }

        .user-role {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: var(--background, #edf5ee);
            color: var(--primary-dark, #176534);
            font-size: 13px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .delete-button {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            background: #b42318;
            color: #ffffff;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .delete-button:hover {
            background: #8f1c13;
        }

        .protected {
            color: var(--muted, #667085);
            font-size: 13px;
            font-weight: 600;
        }

        .empty-state {
            padding: 25px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .users-container {
                padding: 18px 12px 28px;
            }

            .users-header,
            .users-table-card {
                padding: 17px;
            }

            .users-nav {
                flex-direction: column;
                align-items: stretch;
            }

            .users-nav a {
                text-align: center;
            }
        }
    </style>
</head>

<body>

<main class="users-container">

    <section class="card users-header">
        <h1>Manage Users</h1>
        <p>
            View registered users, check their roles, and manage accounts.
        </p>
    </section>

    <nav class="users-nav" aria-label="Admin navigation">
        <a class="btn" href="dashboard.php">Admin Dashboard</a>
        <a class="btn" href="users.php">Manage Users</a>
        <a class="btn" href="../index.php">Home</a>
        <a class="btn" href="../logout.php">Logout</a>
    </nav>

    <?php if ($success !== ""): ?>
        <div class="message success" role="status">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="message error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <section class="card users-table-card">
        <h2 class="table-heading">Registered Users</h2>

        <?php if ($result->num_rows > 0): ?>

            <div class="table-scroll">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int) $user["id"]; ?></td>

                                <td>
                                    <?php echo htmlspecialchars($user["name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user["email"]); ?>
                                </td>

                                <td>
                                    <span class="user-role">
                                        <?php echo htmlspecialchars($user["role"]); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ((int) $user["id"] === (int) $_SESSION["user_id"]): ?>

                                        <span class="protected">Current Admin</span>

                                    <?php else: ?>

                                        <form
                                            method="POST"
                                            action="users.php"
                                            onsubmit="return confirm('Are you sure you want to delete this user? Related records or database constraints may affect deletion.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?php echo (int) $user["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_user"
                                                value="1"
                                                class="delete-button"
                                            >
                                                Delete
                                            </button>
                                        </form>

                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="empty-state">
                <h3>No Users Found</h3>
                <p>There are currently no registered users to display.</p>
            </div>

        <?php endif; ?>
    </section>

</main>

</body>
</html>

<?php
$result->free();
$conn->close();
?>
