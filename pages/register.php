<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); // Redirect to home or dashboard
    exit;
}


include("../database/databaseConnection.php"); // connects to database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
    $password = $_POST['password'];
    $location = trim($_POST['location']);
    $role = $_POST['role'];

    // organizer specific fields
    $companyName = isset($_POST['companyName']) ? trim($_POST['companyName']) : null;
    $experience = isset($_POST['experience']) ? intval($_POST['experience']) : 0;
    $website = isset($_POST['website']) ? trim($_POST['website']) : null;
    $instagram = isset($_POST['instagram']) ? trim($_POST['instagram']) : null;
    
    // Vendor specific fields
    $businessName = isset($_POST['businessName']) ? trim($_POST['businessName']) : null;
    $service = isset($_POST['service']) ? trim($_POST['service']) : null;
    $website_v = isset($_POST['website_v']) ? trim($_POST['website_v']) : null;
    $instagram_v = isset($_POST['instagram_v']) ? trim($_POST['instagram_v']) : null;
    $serviceLocations = isset($_POST['serviceLocations']) ? trim($_POST['serviceLocations']) : null;
    $priceRange = isset($_POST['priceRange']) ? trim($_POST['priceRange']) : null;

    // ✅ Role validation
    if (!in_array($role, ['user', 'organizer', 'vendor'])) {
        echo "<script>alert('Invalid role selected.'); window.history.back();</script>";
        exit;
    }

    // ✅ Required fields check
    if (empty($username) || empty($email) || empty($password) || empty($role)  || empty($phone) ) {
        echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
        exit;
    }

    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = mysqli_prepare($conn,$check_sql);
    mysqli_stmt_bind_param($check_stmt,"s",$email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if(mysqli_stmt_num_rows($check_stmt)>0){
        echo "<script>alert('An account with this email already exists. Please log in.'); window.location.href = '/EventManagementSystem/pages/login.php';</script>";
        exit;
    }
    mysqli_stmt_close($check_stmt);

    // ✅ Secure password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Profile picture handling (Default image)
    $profilePicPath = "/EventManagementSystem/uploads/profilePics/default.png";

    if(!empty($_FILES['profile_picture']['name'] && $_FILES['profile_picture']['error'] === 0 )){
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];

        if(in_array($file_type, $allowed_types) && $file_size <= 2 * 1024 * 1024) {
            $upload_dir = "../uploads/profilePics/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid("profile_", true) . "." . $file_ext;
            $profilePicPath = "/EventManagementSystem/uploads/profilePics/" . $new_file_name;

            if (!move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                echo "<script>alert('Failed to upload profile picture.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Invalid file type or file size too large.'); window.history.back();</script>";
            exit;
        }

    }
    
    // ✅ Insert user into users table
    $sql = "INSERT INTO users (username,email,phone,password,location,role,profile_picture) VALUES(?,?,?,?,?,?,?)";
    $stmt = mysqli_prepare($conn,$sql);

    if($stmt){
        mysqli_stmt_bind_param($stmt,"sssssss",$username,$email,$phone,$hashed_password,$location,$role,$profilePicPath);
        if(mysqli_stmt_execute($stmt)){
            $user_id = mysqli_insert_id($conn);

            // ✅ Insert organizer data if applicable
            if($role === 'organizer'){
                $org_sql = "INSERT INTO organizers (user_id,company_name,experience,website,instagram) VALUES(?,?,?,?,?) ";
                $org_stmt = mysqli_prepare($conn,$org_sql);
                if($org_stmt){
                    mysqli_stmt_bind_param($org_stmt,"isiss",$user_id,$companyName,$experience,$website,$instagram);
                    if(!mysqli_stmt_execute($org_stmt)){
                        die("organizer insert failed: " . mysqli_stmt_error($org_stmt));
                    }
                    mysqli_stmt_close($org_stmt);
                }
            }

            // ✅ Insert vendor data if applicable
            if($role === 'vendor'){
                $vend_sql = "INSERT INTO vendors (user_id,business_name,service,website,instagram,service_locations,price_range) VALUES(?,?,?,?,?,?,?) ";
                $vend_stmt = mysqli_prepare($conn,$vend_sql);
                if($vend_stmt){
                    mysqli_stmt_bind_param($vend_stmt,"issssss",$user_id,$businessName,$service,$website_v,$instagram_v,$serviceLocations,$priceRange);
                    if(!mysqli_stmt_execute($vend_stmt)){
                        die("organizer insert failed: " . mysqli_stmt_error($vend_stmt));
                    }
                    mysqli_stmt_close($vend_stmt);
                }
            }

            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            echo "<script>alert('User registered successfully!'); window.location.href = '../index.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error: " . mysqli_stmt_error($stmt) . "');</script>";
        }

        mysqli_stmt_close($stmt);

    } else {
        echo "<script>alert('Failed to prepare statement.');</script>";
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/styles/style.css">
    <link rel="stylesheet" href="../public/styles/header.css">
    <link rel="stylesheet" href="../public/styles/loginRegister.css">
    <title>Register - EMS</title>
</head>

<body>
    <?php include("../components/header.php") ?>
    <main class="main-box-home">
        <div class="container registerC">
            <h2>REGISTER</h2>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data" class="form">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username *" required>
                    <input type="email" name="email" placeholder="Email *" required>
                </div>
                <div class="form-group">
                    <input type="text" name="phone" placeholder="Phone number *" required>
                    <input type="password" name="password" placeholder="Password *" required>
                </div>

                <div class="form-group">
                    <input type="text" name="location" placeholder="Location" required>
                    <select id="role" name="role" required>
                        <option value="">Select Role *</option>
                        <option value="user">User</option>
                        <option value="organizer">Organizer</option>
                        <option value="vendor">Vendor</option>
                    </select>
                </div>


                <!-- Organizer specific details -->
                <div id="organizerFields" style="display: none;">
                    <div class="form-group">
                        <input type="text" name="companyName" placeholder="Organization/Company Name *">
                        <input type="number" name="experience" placeholder="Experience (Years) *" min="0">
                    </div>
                    <div class="form-group">
                        <input type="url" name="website" placeholder="Website">
                        <input type="url" name="instagram" placeholder="Instagram">
                    </div>
                </div>
                <!-- Organizer details ends -->

                <!-- Vendor specific details -->
                <div id="vendorFields" style="display: none;">
                    <div class="form-group">
                        <input type="text" name="businessName" placeholder="Business Name *">
                        <select id="service" name="service">
                            <option value="">Type of service*</option>
                            <option value="catering">Catering</option>
                            <option value="photography">Photography</option>
                            <option value="decor">Decor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <input type="url" name="website_v" placeholder="Website">
                        <input type="url" name="instagram_v" placeholder="Instagram">
                    </div>

                    <div class="form-group">
                        <input type="text" name="serviceLocations" placeholder="Service Locations">
                        <input type="text" name="priceRange" placeholder="Price Range">
                    </div>
                </div>
                <!-- Vendor details ends -->


                <div class="file-label">Profile Picture:</div>
                <input type="file" name="profile_picture">

                <button type="submit">REGISTER</button>
                <p class="footer-text">Already have an account? <a href="login.php">Login here.</a></p>
            </form>
        </div>
        <?php include("../components/footer.php") ?>
    </main>
</body>
<script src="../scripts/RegisterValidation.js"></script>

</html>