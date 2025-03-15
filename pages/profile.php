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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/styles/style.css">
    <link rel="stylesheet" href="../public/styles/profilePage.css">
    <title>Profile - EMS</title>
</head>

<body>
    <?php include("../components/header.php") ?>

    <div class="container profile-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Profile Header -->
                <div class="profile-header d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div class="text-center text-md-start mb-3 mb-md-0">
                        <h1 class="mb-1"><?= htmlspecialchars($username) ?></h1>
                        <span class="badge <?= 'badge-' . htmlspecialchars($role) ?> badge-role"><?= htmlspecialchars($role) ?></span>

                        <p class="joined-date mt-2">
                            <i class="fas fa-calendar-alt me-2"></i>Member since <?= date('d M Y', strtotime($created_at)) ?>
                        </p>
                    </div>
                    <div class="text-center">
                        <img src="<?= htmlspecialchars($profile_picture ?? '../public/images/default-profile.jpg') ?>" alt="Profile Picture" class="profile-picP">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end mb-4">
                    <button type="button" class="btn btn-update me-2" id="updateProfileBtn">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </button>
                    <button type="button" class="btn btn-save" id="saveChangesBtn" style="display: none;">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>

                <!-- Profile Form -->
                <div class="profile-form">
                    <form id="profileForm" action="profile.php" method="post">
                        <input type="hidden" name="updateProfile" value="1">

                        <h4 class="mb-4 pb-2 border-bottom">Personal Information</h4>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($username) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($phone) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($location) ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <?php if ($role === 'organizer'): ?>
                            <h4 class="mb-4 pb-2 border-bottom">Organizer Information</h4>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars($extra_details['company_name'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="experience" class="form-label">Experience (years)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                        <input type="number" class="form-control" name="experience" value="<?= htmlspecialchars($extra_details['experience'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                        <input type="text" class="form-control" name="website" value="<?= htmlspecialchars($extra_details['website'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="instagram" class="form-label">Instagram</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                        <input type="text" class="form-control" name="instagram" value="<?= htmlspecialchars($extra_details['instagram'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($role === 'vendor'): ?>
                            <h4 class="mb-4 pb-2 border-bottom">Vendor Information</h4>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label for="business_name" class="form-label">Business Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-store"></i></span>
                                        <input type="text" class="form-control" name="business_name" value="<?= htmlspecialchars($extra_details['business_name'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="service" class="form-label">Service</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-concierge-bell"></i></span>
                                        <select class="form-select" name="service" disabled>
                                            <option value="catering" <?= ($extra_details['service'] ?? '') === 'catering' ? 'selected' : '' ?>>Catering</option>
                                            <option value="photography" <?= ($extra_details['service'] ?? '') === 'photography' ? 'selected' : '' ?>>Photography</option>
                                            <option value="decor" <?= ($extra_details['service'] ?? '') === 'decor' ? 'selected' : '' ?>>Decor</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                        <input type="text" class="form-control" name="website" value="<?= htmlspecialchars($extra_details['website'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="instagram" class="form-label">Instagram</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                        <input type="text" class="form-control" name="instagram" value="<?= htmlspecialchars($extra_details['instagram'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="service_locations" class="form-label">Service Locations</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        <input type="text" class="form-control" name="service_locations" value="<?= htmlspecialchars($extra_details['service_locations'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="price_range" class="form-label">Price Range</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                        <input type="text" class="form-control" name="price_range" value="<?= htmlspecialchars($extra_details['price_range'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Save Changes Button -->
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="updateProfile" class="btn btn-save" id="saveChangesBtn" style="display: none;">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('updateProfileBtn').addEventListener('click', function() {
            // Debug: Log when the button is clicked
            console.log('Edit Profile Button Clicked');

            // Make form fields editable
            let inputs = document.querySelectorAll('.form-control');
            inputs.forEach(function(input) {
                input.removeAttribute('readonly'); // Remove readonly
                input.classList.add('editable'); // Add the editable class
            });

            // Toggle visibility of save button
            document.getElementById('saveChangesBtn').style.display = 'inline-block';
            document.getElementById('updateProfileBtn').style.display = 'none';

            // Debug: Log to check changes
            console.log('Fields are now editable, Save Changes button is visible');
        });

        document.getElementById('saveChangesBtn').addEventListener('click', function() {
            // Submit the form when the save button is clicked
            document.getElementById('profileForm').submit();
        });
        // Optional: Add custom validation logic on form submission
        document.getElementById('profileForm').addEventListener('submit', function(event) {
            // Optionally add custom validation or checks before submitting
            console.log('Form submitted');
        });
    </script>

</body>

</html>