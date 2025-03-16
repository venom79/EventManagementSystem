<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'venue_owner') {
    header("Location: ../../login.php");
    exit();
}

include("../../database/databaseConnection.php");

$message = "";

if (isset($_POST['submit']) && isset($_SESSION['user_id'])) {
    $owner_id = $_SESSION['user_id'];
    
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $location = trim(mysqli_real_escape_string($conn, $_POST['location']));
    $capacity = intval($_POST['capacity']);
    $price_per_day = floatval($_POST['price_per_day']);
    $description = trim(mysqli_real_escape_string($conn, $_POST['description']));
    $manager_name = trim(mysqli_real_escape_string($conn, $_POST['manager_name']));
    $manager_email = trim(mysqli_real_escape_string($conn, $_POST['manager_email']));
    $manager_phone = trim(mysqli_real_escape_string($conn, $_POST['manager_phone']));
    $venue_used_for = isset($_POST['venue_used_for']) ? implode(", ", $_POST['venue_used_for']) : '';
    
    $target_dir = "../../uploads/venue/";
    $target_file = $target_dir . basename($_FILES["thumbnail"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    
    if (!in_array($imageFileType, $allowed_types)) {
        $message = "Invalid file type. Only JPG, JPEG, PNG & GIF allowed.";
    } else if ($_FILES["thumbnail"]["size"] > 5000000) {
        $message = "File size too large. Max 5MB allowed.";
    } else {
        if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO venues (name, location, capacity, price_per_day, description, venue_used_for, manager_name, manager_email, manager_phone, thumbnail, owner_id) 
                    VALUES ('$name', '$location', '$capacity', '$price_per_day', '$description', '$venue_used_for', '$manager_name', '$manager_email', '$manager_phone', '$target_file', '$owner_id')";
            
            if (mysqli_query($conn, $sql)) {
                $message = "Venue Request added successfully!";
                $alertType = "success";
            } else {
                $message = "Error: " . mysqli_error($conn);
                $alertType = "danger";
            }
        } else {
            $message = "Error uploading file.";
            $alertType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Venue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .footer-space { margin-bottom: 20px; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; }
    </style>
</head>
<body>
    <?php include("../../components/header.php"); ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Add Venue</h2>
        
        <?php if (!empty($message)) : ?>
            <div class="alert alert-<?php echo $alertType; ?> text-center" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Venue Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="col-md-6">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" required>
            </div>

            <div class="col-md-4">
                <label for="capacity" class="form-label">Capacity</label>
                <input type="number" class="form-control" id="capacity" name="capacity" required>
            </div>

            <div class="col-md-4">
                <label for="price_per_day" class="form-label">Price Per Day</label>
                <input type="number" step="0.01" class="form-control" id="price_per_day" name="price_per_day" required>
            </div>

            <div class="col-md-4">
                <label for="thumbnail" class="form-label">Venue Thumbnail</label>
                <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*" required>
            </div>

            <div class="col-md-12">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="col-md-12">
                <label class="form-label">Venue Used For</label>
                <div class="checkbox-group">
                    <?php
                    $options = ["Wedding", "Conference", "Party", "Concert", "Workshop", "Birthday"];
                    foreach ($options as $option) {
                        echo "<div class='form-check'>
                                <input class='form-check-input' type='checkbox' name='venue_used_for[]' value='$option' id='$option'>
                                <label class='form-check-label' for='$option'>$option</label>
                              </div>";
                    }
                    ?>
                </div>
            </div>

            <h4 class="mt-4">Manager Details</h4>
            <div class="col-md-4">
                <label for="manager_name" class="form-label">Manager Name</label>
                <input type="text" class="form-control" id="manager_name" name="manager_name" required>
            </div>
            <div class="col-md-4">
                <label for="manager_email" class="form-label">Manager Email</label>
                <input type="email" class="form-control" id="manager_email" name="manager_email" required>
            </div>
            <div class="col-md-4">
                <label for="manager_phone" class="form-label">Manager Phone</label>
                <input type="tel" class="form-control" id="manager_phone" name="manager_phone" required>
            </div>
            <div class="col-12 footer-space">
                <button type="submit" class="btn btn-primary w-100" name="submit">Submit Venue Request</button>
            </div>
        </form>
    </div>

    <?php include("../../components/footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
