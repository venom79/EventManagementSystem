<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); // Redirect to home or dashboard
    exit;
}

include("../database/databaseConnection.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Check if user exists
    $sql = "SELECT id, username, password, role FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_bind_result($stmt, $user_id, $name, $hashed_password, $role);
            mysqli_stmt_fetch($stmt);
            
            if (password_verify($password, $hashed_password)) {
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = $role;
                
                // Redirect to index.php
                header("Location: /EventManagementSystem/index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EMS</title>
    <link rel="stylesheet" href="../public/styles/header.css">
    <link rel="stylesheet" href="../public/styles/loginRegister.css">
    <link rel="stylesheet" href="../public/styles/style.css">
</head>
<body>
    <?php include("../components/header.php") ?>
    <main class="main-box-home">
        <div class="container loginC">
            <h2>Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form class="form" action="" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Log In</button>
            </form>
            <div class="footer-text">
                Don't have an account? <a href="/EventManagementSystem/pages/register.php">Register here</a>.
            </div>
        </div>
    </main>
    <?php include("../components/footer.php") ?>
</body>
</html>
