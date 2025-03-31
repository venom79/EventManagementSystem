<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection securely
include(__DIR__ . "/../database/databaseConnection.php");

// Default profile picture
$profilePic = "/EventManagementSystem/uploads/profilePics/default.png";

// Initialize role and notification count
$role = null;
$unreadNotifications = 0;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user profile picture and role
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

    // Fetch unread notification count
    $notifQuery = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread'";
    $notifStmt = mysqli_prepare($conn, $notifQuery);
    mysqli_stmt_bind_param($notifStmt, "i", $user_id);
    mysqli_stmt_execute($notifStmt);
    $notifResult = mysqli_stmt_get_result($notifStmt);
    
    if ($notifRow = mysqli_fetch_assoc($notifResult)) {
        $unreadNotifications = $notifRow['unread_count'];
    }
    mysqli_stmt_close($notifStmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
        .notification-icon {
            position: relative;
            display: inline-block;
            color: white;
            font-size: 20px;
            margin-right: 15px;
            cursor: pointer;
        }
        .notif-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            font-size: 12px;
            font-weight: bold;
            border-radius: 50%;
            padding: 3px 6px;
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
        <a class="navbar-brand" href="/EventManagementSystem/">EventHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/aboutus.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/organizers/organizers.php">Organizers</a></li>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/venue/venues.php">Venues</a></li>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/vendors/vendors.php">Vendors</a></li>
                <?php if (isset($_SESSION['user_id'])){ ?>
                    <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/myBooking.php">My Booking</a></li>
                <?php } ?>
                <li class="nav-item"><a class="nav-link" href="/EventManagementSystem/pages/contactUs.php">Contact Us</a></li>
            </ul>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="d-flex">
                    <a href="/EventManagementSystem/pages/login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="/EventManagementSystem/pages/register.php" class="btn btn-warning">Register</a>
                </div>
            <?php else: ?>
                <div class="d-flex align-items-center">
                    <!-- Notification Icon -->
                    <a href="/EventManagementSystem/pages/notifications.php" class="notification-icon">
                        <i class="bi bi-bell"></i>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="notif-badge"><?php echo $unreadNotifications; ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Picture" class="profile-pic">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/EventManagementSystem/pages/profile.php">Profile</a></li>
                            <?php if ($role === 'vendor'): ?>
                                <li><a class="dropdown-item" href="/EventManagementSystem/dashboard/vendor/vendor_dashboard.php">Dashboard</a></li>
                            <?php elseif ($role === 'organizer'): ?>
                                <li><a class="dropdown-item" href="/EventManagementSystem/dashboard/organizer/organizer_dashboard.php">Dashboard</a></li>
                            <?php elseif ($role === 'venue_owner'): ?>
                                <li><a class="dropdown-item" href="/EventManagementSystem/dashboard/venueOwner/venues.php">Dashboard</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="/EventManagementSystem/middleware/logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div style="margin-top: 80px;"></div> <!-- Spacer to prevent content from being hidden behind navbar -->

</body>
</html> 
