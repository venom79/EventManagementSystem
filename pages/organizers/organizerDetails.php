<?php
include("../../database/databaseConnection.php");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get organizer ID from URL
$organizer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch organizer details
$query = "SELECT o.*, u.username, u.location,u.phone
          FROM organizers o
          JOIN users u ON o.user_id = u.id
          WHERE o.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$organizer = $result->fetch_assoc();

// Fetch organizer photos
$photo_query = "SELECT photo_url FROM vendor_photos WHERE vendor_id = ?";
$photo_stmt = $conn->prepare($photo_query);
$photo_stmt->bind_param("i", $organizer_id);
$photo_stmt->execute();
$photo_result = $photo_stmt->get_result();
$photos = $photo_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($organizer['company_name'] ?? 'Organizer Details') ?> - EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 1000px;
        }
        .profile-card {
            border-radius: 12px;
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.1);
            background: #fff;
            padding: 20px;
        }
        .profile-card h2 {
            font-weight: 600;
            color: #333;
        }
        .social-buttons a {
            margin: 5px;
            font-size: 16px;
            width: 150px;
        }
        .photo-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .photo-gallery img {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
        }
        @media (max-width: 768px) {
            .profile-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include("../../components/header.php"); ?>

    <div class="container mt-5">
        <div class="profile-card text-center">
            <h2><?= htmlspecialchars($organizer['company_name'] ?? 'Not Available') ?></h2>
            <p><strong>Speciality:</strong> <?= htmlspecialchars($organizer['speciality'] ?? 'Not Specified') ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($organizer['location'] ?? 'Not Provided') ?></p>
            <p><strong>Experience:</strong> <?= htmlspecialchars($organizer['experience'] ?? '0') ?> years</p>

            <div class="social-buttons d-flex justify-content-center">
                <?php if (!empty($organizer['website'])) { ?>
                    <a href="<?= htmlspecialchars($organizer['website']) ?>" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fa fa-globe"></i> Website
                    </a>
                <?php } ?>
                <?php if (!empty($organizer['instagram'])) { ?>
                    <a href="<?= htmlspecialchars($organizer['instagram']) ?>" target="_blank" class="btn btn-danger btn-sm">
                        <i class="fab fa-instagram"></i> Instagram
                    </a>
                <?php } ?>
                <?php if (!empty($organizer['phone'])) { ?>
                    <a href="https://wa.me/<?= htmlspecialchars($organizer['phone']) ?>" target="_blank" class="btn btn-success btn-sm">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                <?php } ?>
            </div>
        </div>

        <h3 class="text-center text-primary fw-bold mt-4">Organizer's Photos</h3>
        <div class="photo-gallery text-center mt-3">
            <?php if (count($photos) > 0) { 
                foreach ($photos as $photo) { ?>
                    <img src="<?= htmlspecialchars($photo['photo_url']) ?>" alt="Organizer Photo">
            <?php } } else { ?>
                <p>No photos uploaded yet.</p>
            <?php } ?>
        </div>
    </div>

    <?php include("../../components/footer.php"); ?>
</body>
</html>
