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
<header class="flex-r header">
    <div class="brand flex-r">
        <div class="brandName">EMS</div>
    </div>
    <div class="menu">
        <li class="flex-r">
            <ul><a href="/EventManagementSystem/">Home</a></ul>
            <ul><a href="/EventManagementSystem/pages/aboutus.php">About Us</a></ul>
            <ul><a href="/EventManagementSystem/pages/events/events.php">Events</a></ul>
            <ul><a href="/EventManagementSystem/pages/venue/venues.php">Venues</a></ul>
            <ul><a href="/EventManagementSystem/pages/vendors.php">Vendors</a></ul>
            <ul><a href="/EventManagementSystem/pages/contactUs.php">Contact Us</a></ul>
        </li>
    </div>
    <?php
    // Check if user is logged in
     if (!isset($_SESSION['user_id'])) {
         echo '
             <div class="logReg flex-r">
                 <div class="btnConatiner">
                     <a href="/EventManagementSystem/pages/login.php" class="btn btn-primary">Login</a>
                     <a href="/EventManagementSystem/pages/register.php" class="btn btn-primary">Register</a>
                 </div>
             </div>
         ';
     } else {
         echo '
             <div class="logReg flex-r">
                 <div class="profilePic">
                     <img src="' . htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8') . '" alt="Profile Picture">  
                 </div>
                 <div class="dropdown">
                     <button class="dropbtn">▼</button>
                     <div class="dropdown-content">
                         <a href="/EventManagementSystem/pages/profile.php">Profile</a>';

         // Show Mentor Dashboard option if user is a mentor
        //  if ($role === 'mentor') {
        //      echo '<a href="/EventManagementSystem/pages/dashboard.php">Dashboard</a>';
        //  }

         echo '
                         <a href="/EventManagementSystem/middleware/logout.php">Logout</a>
                     </div>
                 </div>
             </div>
         ';
     }
     ?>
</header>