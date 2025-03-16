<?php
session_start();
include("../../database/databaseConnection.php");

$error = ""; // To store error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Prepare and execute query (case-insensitive username check)
    $query = "SELECT admin_id, password FROM admin WHERE LOWER(userName) = LOWER(?) LIMIT 1";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        // Debugging: Check if admin exists
        if (!$admin) {
            $error = "Invalid username or password!";
        } else {
            // Debugging: Show stored hash
            echo "Stored Hash: " . $admin["password"] . "<br>";
            echo "inside outer else";
            if(password_verify($password,$admin["password"])){
                echo "verifyed";
            }
            else{
                echo "not verified";
            }
            
            // Verify password
            if (password_verify($password, $admin["password"])) {
                session_regenerate_id(true); // Prevent session fixation
                echo "inside if";
                $_SESSION["admin_id"] = $admin["admin_id"];
                $_SESSION["role"] = "admin";

                header("Location: ../index.php");
                exit();
            } else {
                echo password_verify($password,$admin["password"]);
                $error = "Invalid username or password!";
            }
        }
    } else {
        $error = "Database error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .login-container {
            display: flex;
            flex-direction: column;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input {
            display: block;
            width: 90%;
            margin-bottom: 10px;
            padding: 8px;
        }
        button{
            background-color: red;
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: rgb(176, 0, 0);
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    
    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Admin Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
