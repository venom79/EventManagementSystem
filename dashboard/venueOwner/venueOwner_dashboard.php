<?php
require __DIR__ . '../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../config/');
$dotenv->load();
$apiKey = $_ENV['API_KEY'];

session_start();
require '../../database/databaseConnection.php';

// Check if the user is logged in and is a venue_owner
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'venue_owner') {
//     header("Location: ../../pages/login.php");
//     exit();
// }

// Get the actual venue ID from the venues table using user_id
$user_id = $_SESSION['user_id'];
$venueId = $_GET['venueId'];


// Fetch venue details
$stmt = $conn->prepare("SELECT * FROM venues WHERE id = ?");
$stmt->bind_param("i", $venueId);
$stmt->execute();
$result = $stmt->get_result();
$venue = $result->fetch_assoc();
$stmt->close();

// Fetch venue images
$images_stmt = $conn->prepare("SELECT * FROM venue_images WHERE venue_id = ?");
$images_stmt->bind_param("i", $venueId);
$images_stmt->execute();
$venueImages = $images_stmt->get_result();
$images_stmt->close();

// Handle form submission for updating venue details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_details'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $capacity = trim($_POST['capacity']);
    $price = trim($_POST['price']);
    $venue_used_for = trim($_POST['venue_used_for']);
    $manager_name = trim($_POST['manager_name']);
    $manager_email = trim($_POST['manager_email']);
    $manager_phone = trim($_POST['manager_phone']);
    var_dump($manager_email);

    $stmt = $conn->prepare("UPDATE venues SET name=?, description=?, location=?, capacity=?, price_per_day=?, venue_used_for=?, manager_name=?, manager_email=?, manager_phone=? WHERE id=?");
    $stmt->bind_param("sssiisssii", $name, $description, $location, $capacity, $price, $venue_used_for, $manager_name, $manager_email, $manager_phone, $venueId);

    if ($stmt->execute()) {
        header("Location: venueOwner_dashboard.php?venueId=$venueId&success=Details updated");
        exit();
    } else {
        $error = "Error updating details.";
    }
    $stmt->close();
}

// Handle image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_photo'])) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../../uploads/venue/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $venue_id . "_" . time() . "_" . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $photo_url = str_replace("../../", "", $target_file);

                // Insert into database with correct venue ID
                $stmt = $conn->prepare("INSERT INTO venue_images (venue_id, image_url) VALUES (?, ?)");
                $stmt->bind_param("is", $venueId, $photo_url);

                if ($stmt->execute()) {
                    header("Location: venueOwner_dashboard.php?venueId=$venueId&success=Photo uploaded");
                    exit();
                } else {
                    echo "Error inserting into DB: " . $stmt->error;
                }

                $stmt->close();
            } else {
                echo "Error: move_uploaded_file() failed!";
            }
        } else {
            echo "Error: Invalid file type ($imageFileType). Allowed types: JPG, JPEG, PNG, GIF.";
        }
    } else {
        echo "Error: No file selected or upload error.";
    }
}

// Handle image deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_photo'])) {
    $photo_id = $_POST['photo_id'];
    $stmt = $conn->prepare("SELECT image_url FROM venue_images WHERE id = ? AND venue_id = ?");
    $stmt->bind_param("ii", $photo_id, $venueId);
    $stmt->execute();
    $stmt->bind_result($photo_url);
    if ($stmt->fetch()) {
        unlink("../../" . $photo_url); // Delete file from server
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM venue_images WHERE id = ?");
        $stmt->bind_param("i", $photo_id);
        $stmt->execute();
        $stmt->close();

        header("Location: venueOwner_dashboard.php?venueId=$venueId&success=Photo deleted");
        exit();
    }
}


?>


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>venue Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .image-container {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
        }

        .image-container img {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .image-container img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .overlayImg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #map {
            height: 300px;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .map-container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 800px;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            border-radius: 8px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .map-controls {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .image-container:hover .overlayImg {
            opacity: 1;
        }

        .btn {
            border-radius: 5px;
            padding: 10px 20px;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <?php include("../../components/header.php") ?>
    <div class="container mt-4 mb-5">
        <a class="btn btn-secondary mb-3" href="./venues.php">← Back</a>
        <div class="d-flex justify-content-between align-items-center">
            <h2>Welcome, <?php echo htmlspecialchars($venue['manager_name']); ?></h2>
        </div>

        <!-- Display Success/Error Messages -->
        <?php if (isset($_GET['success'])) echo "<p class='alert alert-success'>" . htmlspecialchars($_GET['success']) . "</p>"; ?>
        <?php if (isset($error)) echo "<p class='alert alert-danger'>$error</p>"; ?>

        <div class="row mt-4">
            <!-- Left Column: Business Details -->
            <div class="col-md-6">
                <h3>venue Details</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">venue Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($venue['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($venue['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($venue['location']); ?>">
                            <button type="button" class="btn btn-primary" id="openMapBtn">Choose from Map</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">capacity</label>
                        <input type="number" name="capacity" class="form-control" value="<?php echo htmlspecialchars($venue['capacity']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" min="0" name="price" class="form-control" value="<?php echo htmlspecialchars($venue['price_per_day']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Best for</label>
                        <input type="text" name="venue_used_for" class="form-control" value="<?php echo htmlspecialchars($venue['venue_used_for']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Manager name</label>
                        <input type="text" name="manager_name" class="form-control" value="<?php echo htmlspecialchars($venue['manager_name']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Manager email</label>
                        <input type="text" name="manager_email" class="form-control" value="<?php echo htmlspecialchars($venue['manager_email']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Manager phone</label>
                        <input type="text" name="manager_phone" class="form-control" value="<?php echo htmlspecialchars($venue['manager_phone']); ?>">
                    </div>
                    <button type="submit" name="update_details" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            <!-- Right Column: Service Photos -->
            <div class="col-md-6">
                <h3>Service Photos</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="photo" class="form-control mb-3">
                    <button type="submit" name="upload_photo" class="btn btn-success">Upload Photo</button>
                </form>

                <div class="mt-4">
                    <h4>Uploaded Photos</h4>
                    <div class="row">
                        <?php
                        $images = [];
                        while ($image = $venueImages->fetch_assoc()) {
                            $images[] = $image; // Store images in an array
                        }

                        if (empty($images)): ?>
                            <p>No photos available</p>
                        <?php else: ?>
                            <?php foreach ($images as $image): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="image-container">
                                        <img src="../../<?php echo $image['image_url']; ?>" class="img-fluid rounded shadow-sm" alt="Service Image">
                                        <div class="overlayImg d-flex justify-content-center align-items-center">
                                            <form method="POST">
                                                <input type="hidden" name="photo_id" value="<?php echo $image['id']; ?>">
                                                <button type="submit" name="delete_photo" class="btn btn-danger btn-lg">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>


    </div>
    <!-- Map Modal -->
    <div class="overlay" id="overlay"></div>
    <div class="map-container" id="mapModal">
        <h4>Select Venue Location</h4>
        <div class="input-group mb-3">
            <input type="text" class="form-control" id="searchBox" placeholder="Search for a location">
            <button class="btn btn-outline-secondary" type="button" id="searchButton">Search</button>
        </div>
        <div id="map"></div>
        <p id="selectedLocation">Selected location: None</p>
        <div class="map-controls">
            <button type="button" class="btn btn-secondary me-2" id="closeMapBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmLocationBtn">Confirm Location</button>
        </div>
    </div>
    <?php include("../../components/footer.php") ?>
</body>
<script>
    // Automatically hide success/error messages after 5 seconds
    setTimeout(function() {
        let alertBox = document.querySelector(".alert");
        if (alertBox) {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500); // Remove element after fade-out
        }
    }, 5000);
</script>
<!-- Load Google Maps API with Places library -->
<script src="../../scripts/mapAPI.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>&libraries=places&callback=initMap" async defer></script>

</html>

</html>