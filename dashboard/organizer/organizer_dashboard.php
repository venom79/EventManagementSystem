<?php
session_start();
require '../../database/databaseConnection.php';

// Check if the user is logged in and is a organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../../pages/login.php");
    exit();
}

// Get the actual organizer ID from the organizers table using user_id
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id FROM organizers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($organizer_id);
$stmt->fetch();
$stmt->close();

if (!$organizer_id) {
    die("Error: organizer ID not found for this user.");
}


// Fetch organizer details
$stmt = $conn->prepare("SELECT * FROM organizers WHERE id = ?");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$organizer = $result->fetch_assoc();
$stmt->close();

// Fetch organizer images
$images_stmt = $conn->prepare("SELECT * FROM organizer_photos WHERE organizer_id = ?");
$images_stmt->bind_param("i", $organizer_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();
$images_stmt->close();

// Handle form submission for updating organizer details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_details'])) {
    $company_name = trim($_POST['company_name']);
    $description = trim($_POST['description']);
    $experience = trim($_POST['experience']);
    $website = trim($_POST['website']);
    $instagram = trim($_POST['instagram']);
    $speciality = trim($_POST['speciality']);

    $stmt = $conn->prepare("UPDATE organizers SET company_name=?, description=?, experience=?, website=?, instagram=?, speciality=? WHERE id=?");
    $stmt->bind_param("ssisssi", $company_name, $description, $experience, $website, $instagram, $speciality, $organizer_id);

    if ($stmt->execute()) {
        header("Location: organizer_dashboard.php?success=Details updated");
        exit();
    } else {
        $error = "Error updating details.";
    }
    $stmt->close();
}

// Handle image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_photo'])) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../../uploads/organizer/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $organizer_id . "_" . time() . "_" . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $photo_url = str_replace("../../", "", $target_file);

                // Insert into database with correct organizer ID
                $stmt = $conn->prepare("INSERT INTO organizer_photos (organizer_id, photo_url) VALUES (?, ?)");
                $stmt->bind_param("is", $organizer_id, $photo_url);

                if ($stmt->execute()) {
                    header("Location: organizer_dashboard.php?success=Photo uploaded");
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
    $stmt = $conn->prepare("SELECT photo_url FROM organizer_photos WHERE id = ? AND organizer_id = ?");
    $stmt->bind_param("ii", $photo_id, $organizer_id);
    $stmt->execute();
    $stmt->bind_result($photo_url);
    if ($stmt->fetch()) {
        unlink("../../" . $photo_url); // Delete file from server
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM organizer_photos WHERE id = ?");
        $stmt->bind_param("i", $photo_id);
        $stmt->execute();
        $stmt->close();

        header("Location: organizer_dashboard.php?success=Photo deleted");
        exit();
    }
}

// // Fetch reviews
// $reviews_stmt = $conn->prepare("SELECT rr.*, u.username FROM organizer_reviews rr JOIN users u ON rr.user_id = u.id WHERE rr.organizer_id = ? ");
// $reviews_stmt->bind_param("i", $organizer_id);
// $reviews_stmt->execute();
// $reviews_result = $reviews_stmt->get_result();
// $reviews_stmt->close();

// // Handle review deletion
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_review'])) {
//     $review_id = $_POST['review_id'];
//     $stmt = $conn->prepare("DELETE FROM organizer_reviews WHERE id = ? AND organizer_id = ?");
//     $stmt->bind_param("ii", $review_id, $organizer_id);
//     if ($stmt->execute()) {
//         header("Location: organizer_dashboard?success=Review deleted");
//         exit();
//     }
//     $stmt->close();
// }
// 
?>


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard</title>
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

        .overlay {
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

        .image-container:hover .overlay {
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
        <div class="d-flex justify-content-between align-items-center">
            <h2>Welcome, <?php echo htmlspecialchars($organizer['company_name']); ?></h2>
        </div>

        <!-- Display Success/Error Messages -->
        <?php
        if (isset($_GET['success'])) echo "<p class='alert alert-success'>" . htmlspecialchars($_GET['success']) . "</p>";
        ?>
        <?php if (isset($error)) echo "<p class='alert alert-danger'>$error</p>"; ?>

        <div class="row mt-4">
            <!-- Left Column: Business Details -->
            <div class="col-md-6">
                <h3>Business Details</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($organizer['company_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($organizer['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Experience</label>
                        <input type="number" name="experience" class="form-control" value="<?php echo htmlspecialchars($organizer['experience']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($organizer['website']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instagram</label>
                        <input type="text" name="instagram" class="form-control" value="<?php echo htmlspecialchars($organizer['instagram']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Speciality</label>
                        <input type="text" name="speciality" class="form-control" value="<?php echo htmlspecialchars($organizer['speciality']); ?>">
                    </div>
                    <button type="submit" name="update_details" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            <!-- Right Column: Service Photos -->
            <div class="col-md-6">
                <h3>Photos</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="photo" class="form-control mb-3">
                    <button type="submit" name="upload_photo" class="btn btn-success">Upload Photo</button>
                </form>

                <div class="mt-4">
                    <h4>Uploaded Photos</h4>
                    <div class="row">
                        <?php while ($image = $images_result->fetch_assoc()) { ?>
                            <div class="col-md-6 mb-4">
                                <div class="image-container">
                                    <img src="../../<?php echo $image['photo_url']; ?>" class="img-fluid rounded shadow-sm" alt="Service Image">
                                    <div class="overlay d-flex justify-content-center align-items-center">
                                        <form method="POST">
                                            <input type="hidden" name="photo_id" value="<?php echo $image['id']; ?>">
                                            <button type="submit" name="delete_photo" class="btn btn-danger btn-lg">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>



        <!-- <div class="row mt-4">
            <div class="col-md-12">
                <h3>User Reviews</h3>
                <?php if ($reviews_result->num_rows > 0) { ?>
                    <ul class="list-group">
                        <?php while ($review = $reviews_result->fetch_assoc()) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($review['username']); ?>:</strong>
                                    <p><?php echo htmlspecialchars($review['review']); ?></p>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <button type="submit" name="delete_review" class="btn btn-danger">Delete</button>
                                </form>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p class="text-muted">No reviews available.</p>
                <?php } ?>
            </div>
        </div> -->
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

</html>

</html>