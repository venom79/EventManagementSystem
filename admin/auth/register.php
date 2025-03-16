<?php
session_start();
include("../../database/databaseConnection.php");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if the username already exists
        $query = "SELECT admin_id FROM admin WHERE LOWER(userName) = LOWER(?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert new admin
            $insert_query = "INSERT INTO admin (userName, password) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ss", $username, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $success = "Admin registered successfully! <a href='login.php'>Login here</a>";
            } else {
                $error = "Error: " . $conn->error;
            }
            $insert_stmt->close();
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
    <title>Admin Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .register-container {
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
            background-color: green;
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: darkgreen;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Admin Registration</h2>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>
    
    <?php if (!empty($success)) { ?>
        <p class="success"><?= $success ?></p>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Admin Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>
