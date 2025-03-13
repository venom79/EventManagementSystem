<?php
session_start();

include("../../database/databaseConnection.php"); // Include your database connection

// Check if user is logged in and is a venue owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_owner') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $owner_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $capacity = intval($_POST['capacity']);
    $price_per_day = floatval($_POST['price_per_day']);
    $description = trim($_POST['description']);

    // File Upload Handling
    $upload_dir = "../../uploads/venue/"; // Directory to store images
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif']; // Allowed file types
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    $thumbnail_url = ""; // Default empty

    if (!empty($_FILES["thumbnail"]["name"])) {
        $file_name = basename($_FILES["thumbnail"]["name"]);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = time() . "_" . $file_name; // Unique file name
        $target_file = $upload_dir . $new_file_name;

        if (!in_array($file_ext, $allowed_types)) {
            echo "<script>alert('Invalid file type! Only JPG, PNG, GIF allowed.');</script>";
        } elseif ($_FILES["thumbnail"]["size"] > $max_size) {
            echo "<script>alert('File size exceeds 5MB!');</script>";
        } elseif (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_file)) {
            $thumbnail_url = "/EventManagementSystem/uploads/venue/" . $new_file_name; // Save relative path
        } else {
            echo "<script>alert('Failed to upload image!');</script>";
        }
    }

    // Insert into database (status will be 'pending' for admin approval)
    $query = "INSERT INTO venues (owner_id, name, location, capacity, price_per_day, description, thumbnail, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("issidss", $owner_id, $name, $location, $capacity, $price_per_day, $description, $thumbnail_url);

    if ($stmt->execute()) {
        echo "<script>alert('Venue request submitted for approval!'); window.location.href='/EventManagementSystem/pages/venue/venues.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Venue</title>
    <link rel="stylesheet" href="../../public/styles/header.css">
    <link rel="stylesheet" href="../../public/styles/venue.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
</head>

<body>

    <main class="main-box-home">
        <?php include("../../components/header.php") ?>
        <div class="formContainer">
            <h2 class="form-title">Add Your Venue</h2>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="name">Venue Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" required>
                </div>

                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" required>
                </div>

                <div class="form-group">
                    <label for="price_per_day">Price Per Day</label>
                    <input type="number" step="0.01" id="price_per_day" name="price_per_day" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="thumbnail">Venue Thumbnail</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*" required>
                </div>

                <button type="submit" class="btn-submit">Submit Venue Request</button>
            </form>
        </div>
    </main>
    <?php include("../../components/footer.php") ?>

</body>

</html>