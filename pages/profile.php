<?php
session_start();
include("../database/databaseConnection.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, phone, location, role, profile_picture, created_at FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $username, $email, $phone, $location, $role, $profile_picture, $created_at);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

$extra_details = [];
if ($role === 'organizer') {
    $org_sql = "SELECT company_name, experience, website, instagram FROM organizers WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $org_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $extra_details['company_name'], $extra_details['experience'], $extra_details['website'], $extra_details['instagram']);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
} elseif ($role === 'vendor') {
    $vendor_sql = "SELECT business_name, service, website, instagram, service_locations, price_range FROM vendors WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $vendor_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $extra_details['business_name'], $extra_details['service'], $extra_details['website'], $extra_details['instagram'], $extra_details['service_locations'], $extra_details['price_range']);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
}
// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['updateProfile'])) {
    $username = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];

    $update_sql = "UPDATE users SET username = ?, email = ?, phone = ?, location = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $phone, $location, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($role === 'organizer') {
        $company_name = $_POST['company_name'];
        $experience = (int)$_POST['experience']; // Cast to integer for the experience field
        $website = $_POST['website'];
        $instagram = $_POST['instagram'];

        $update_org_sql = "UPDATE organizers SET company_name = ?, experience = ?, website = ?, instagram = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_org_sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sissi", $company_name, $experience, $website, $instagram, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } elseif ($role === 'vendor') {
        $business_name = $_POST['business_name'];
        $service = $_POST['service'];
        $website = $_POST['website'];
        $instagram = $_POST['instagram'];
        $service_locations = $_POST['service_locations'];
        $price_range = $_POST['price_range'];

        $update_vendor_sql = "UPDATE vendors SET business_name = ?, service = ?, website = ?, instagram = ?, service_locations = ?, price_range = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_vendor_sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssi", $business_name, $service, $website, $instagram, $service_locations, $price_range, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: profile.php");
    exit;
}


mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/styles/style.css">
    <link rel="stylesheet" href="../public/styles/header.css">
    <link rel="stylesheet" href="../public/styles/profilePage.css">
    <title>Profile - EMS</title>
    <style>

    </style>
</head>

<body>
    <main class="main-box-home">
        <?php include("../components/header.php") ?>
        <div class="profileContainer container flex-c">
            <div class="userDetails flex-c">
                <div class="updateAction">
                    <button type="button" class="btn pPUpdateBtn" id="updateProfileBtn">Update profile</button>
                    <button type="button" class="btn saveUpdate" id="saveChangesBtn">Save changes</button>
                </div>
                <form id="profileForm" action="profile.php" method="post">
                    <input type="hidden" name="updateProfile" value="1">
                    <div class="flex-r">
                        <div class="userImg">
                            <img src="<?= htmlspecialchars($profile_picture ?? '../public/images/default-profile.jpg') ?>" alt="ProfilePic">
                        </div>
                        <div class="flex-c">
                            <div class="detailGroup flex-r">
                                <label for="name">Name: </label><input type="text" name="name" value="<?= htmlspecialchars($username) ?>" readonly>
                            </div>
                            <div class="detailGroup flex-r">
                                <label for="email">Email: </label><input type="email" name="email" value="<?= htmlspecialchars($email) ?>" readonly>
                            </div>
                            <div class="detailGroup flex-r">
                                <label for="phone">Phone: </label><input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" readonly>
                            </div>
                            <div class="detailGroup flex-r">
                                <label for="role">Role: </label><input type="text" id="roleField" value="<?= ucfirst(htmlspecialchars($role)) ?>" readonly>
                            </div>
                            <div class="detailGroup flex-r">
                                <label for="location">Location: </label><input type="text" name="location" value="<?= htmlspecialchars($location) ?>" readonly>
                            </div>
                            <div class="detailGroup flex-r">
                                <label for="joinedDate">Joined on: </label><input type="text" id="joinedDateField" value="<?= date('d M Y', strtotime($created_at)) ?>" readonly>
                            </div>

                            <?php if ($role === 'organizer'): ?>
                                <div class="detailGroup flex-r">
                                    <label for="company_name">Company Name: </label><input type="text" name="company_name" value="<?= htmlspecialchars($extra_details['company_name'] ?? '') ?>" readonly>
                                </div>
                                <div class="detailGroup flex-r">
                                    <label for="experience">Experience: </label><input type="number" name="experience" value="<?= htmlspecialchars($extra_details['experience'] ?? '') ?>" readonly><span> years</span>
                                </div>
                                <div class="detailGroup flex-r">
                                    <label for="website">Website: </label><input type="text" name="website" value="<?= htmlspecialchars($extra_details['website'] ?? '') ?>" readonly>
                                </div>
                                <div class="detailGroup flex-r">
                                    <label for="instagram">Instagram: </label><input type="text" name="instagram" value="<?= htmlspecialchars($extra_details['instagram'] ?? '') ?>" readonly>
                                </div>
                            <?php elseif ($role === 'vendor'): ?>
                                <div class="detailGroup flex-r">
                                    <label for="business_name">Business Name: </label><input type="text" name="business_name" value="<?= htmlspecialchars($extra_details['business_name'] ?? '') ?>" readonly>
                                </div>
                                <div class="detailGroup flex-r">
                                    <label for="service">Service: </label>
                                    <select name="service" disabled>
                                        <option value="catering" <?= ($extra_details['service'] ?? '') === 'catering' ? 'selected' : '' ?>>Catering</option>
                                        <option value="photography" <?= ($extra_details['service'] ?? '') === 'photography' ? 'selected' : '' ?>>Photography</option>
                                        <option value="decor" <?= ($extra_details['service'] ?? '') === 'decor' ? 'selected' : '' ?>>Decor</option>
                                    </select>
                                </div>
                                <div class="detailGroup flex-r">
                                    <label for="website">Website: </label><input type="text" name="website" value="<?= htmlspecialchars($extra_details['website'] ?? '') ?>" readonly>
                                </div>
                                <div class="detailGroup flex-r">
                                    <label for="instagram">Instagram: </label><input type="text" name="instagram" value="<?= htmlspecialchars($extra_details['instagram'] ?? '') ?>" readonly>
                                </div>
                                <div class="detailGroup flex-r">
                                    <label for="service_locations">Service Locations: </label>
                                    <input name="service_locations"  value="<?= htmlspecialchars($extra_details['service_locations'] ?? '') ?>" readonly>
                                </div>
                                <div class="detailGroup flex-r">
                                    <label for="price_range">Price Range: </label><input type="text" name="price_range" value="<?= htmlspecialchars($extra_details['price_range'] ?? '') ?>" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="submit" id="submitProfileBtn" style="display: none;">Submit</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.getElementById("updateProfileBtn").addEventListener("click", function() {
            // Get all editable inputs, selects, and textareas
            const inputs = document.querySelectorAll("#profileForm input:not([type=hidden]):not(#roleField):not(#joinedDateField)");
            const selects = document.querySelectorAll("#profileForm select");
            const textareas = document.querySelectorAll("#profileForm textarea");
            
            // Make input fields editable and add border
            inputs.forEach(input => {
                input.removeAttribute("readonly");
                input.classList.add("editable");
            });
            
            // Make select fields editable
            selects.forEach(select => {
                select.removeAttribute("disabled");
                select.classList.add("editable");
            });
            
            // Make textarea fields editable
            textareas.forEach(textarea => {
                textarea.removeAttribute("readonly");
                textarea.classList.add("editable");
            });
            
            // Hide update button and show save button
            document.getElementById("updateProfileBtn").style.display = "none";
            document.getElementById("saveChangesBtn").style.display = "block";
        });

        document.getElementById("saveChangesBtn").addEventListener("click", function() {
            document.getElementById("profileForm").submit();
        });
    </script>
</body>

</html>