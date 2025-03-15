<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection securely
include(__DIR__ . "/../database/databaseConnection.php");

// Default profile picture
$profilePic = "/EventManagementSystem/uploads/profilePics/default.png";

// Fetch user profile picture and role if logged in
$role = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $query = "SELECT profile_picture, role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['profile_picture'])) {
            $profilePic = $row['profile_picture']; // Use image URL from DB
        }
        $role = $row['role']; // Fetch user role
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: #000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .navbar-brand {
            font-size: 1.7rem;
            font-weight: bold;
            color: white;
        }
        .navbar-nav .nav-link {
            color: white;
        }
        .navbar-nav .nav-link:hover {
            color: orange;
        }
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .dropdown-menu {
            background-color: #222245;
            border-radius: 5px;
        }
        .dropdown-menu a {
            color: white;
        }
        .dropdown-menu a:hover {
            background-color: #333365;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/EventManagementSystem/">EMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/aboutus.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/events/events.php">Events</a></li>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/venue/venues.php">Venues</a></li>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/vendors/vendors.php">Vendors</a></li>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/contactUs.php">Contact Us</a></li>
            </ul>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="d-flex">
                    <a href="/EventManagementSystem/pages/login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="/EventManagementSystem/pages/register.php" class="btn btn-warning">Register</a>
                </div>
            <?php else: ?>
                <div class="dropdown">
                    <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Picture" class="profile-pic">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/EventManagementSystem/pages/profile.php">Profile</a></li>
                        <?php if ($_SESSION['role'] === 'vendor'): ?>
                            <li><a class="dropdown-item" href="/EventManagementSystem/dashboard/vendor/vendor_dashboard.php">Dashboard</a></li>
                        <?php elseif ($_SESSION['role'] === 'organizer'): ?>
                            <li><a class="dropdown-item" href="/EventManagementSystem/dashboard/vendor/vendor_dashboard.php">organizer</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="/EventManagementSystem/middleware/logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div style="margin-top: 80px;"></div> <!-- Spacer to prevent content from being hidden behind navbar -->

<!-- Move Bootstrap JS to the bottom for better performance -->


</body>
</html>

