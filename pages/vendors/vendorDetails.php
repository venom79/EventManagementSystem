<?php
session_start();
include "../../database/databaseConnection.php";

// Check if vendor ID is set
if (!isset($_GET['vendorId']) || empty($_GET['vendorId'])) {
    die("Vendor ID is missing.");
}

$vendorId = intval($_GET['vendorId']);

// Fetch vendor details
$query = "SELECT v.*, u.phone FROM vendors v JOIN users u ON v.user_id = u.id WHERE v.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vendorId);
$stmt->execute();
$vendor = $stmt->get_result()->fetch_assoc();

if (!$vendor) {
    die("Vendor not found.");
}

// Fetch vendor photos
$queryPhotos = "SELECT photo_url FROM vendor_photos WHERE vendor_id = ? ORDER BY uploaded_at DESC";
$stmt = $conn->prepare($queryPhotos);
$stmt->bind_param("i", $vendorId);
$stmt->execute();
$photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch average rating and total reviews
$queryRatings = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews 
                 FROM vendor_ratings 
                 WHERE vendor_id = ?";

$stmt = $conn->prepare($queryRatings);
$stmt->bind_param("i", $vendorId);
$stmt->execute();
$ratingResult = $stmt->get_result()->fetch_assoc();
$avgRating = $ratingResult['avg_rating'] ?? 0;
$totalReviews = $ratingResult['total_reviews'] ?? 0;

// Fetch vendor reviews along with individual ratings
$queryReviews = "SELECT r.review, r.created_at, u.username, u.profile_picture, 
                        COALESCE(vr.rating, 0) AS rating
                 FROM vendor_reviews r 
                 JOIN users u ON r.user_id = u.id 
                 LEFT JOIN vendor_ratings vr ON r.user_id = vr.user_id AND r.vendor_id = vr.vendor_id
                 WHERE r.vendor_id = ? 
                 ORDER BY r.created_at DESC";

$stmt = $conn->prepare($queryReviews);
$stmt->bind_param("i", $vendorId);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);



// Ensure proper WhatsApp format
$phone = preg_replace('/[^0-9]/', '', $vendor['phone']);
if (strlen($phone) === 10) {
    $phone = "+91" . $phone;
} elseif (strlen($phone) === 12 && substr($phone, 0, 2) !== "+91") {
    $phone = "+91" . substr($phone, -10);
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);

    // Validate input
    if ($rating >= 1 && $rating <= 5) {
        // Insert rating into vendor_ratings
        $insertRating = "INSERT INTO vendor_ratings (vendor_id, user_id, rating, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertRating);
        $stmt->bind_param("iii", $vendorId, $userId, $rating);
        $stmt->execute();
    }

    if (!empty($review)) {
        // Insert review into vendor_reviews
        $insertReview = "INSERT INTO vendor_reviews (vendor_id, user_id, review, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertReview);
        $stmt->bind_param("iis", $vendorId, $userId, $review);
        $stmt->execute();
    }

    // Redirect to prevent form resubmission
    header("Location: vendorDetails.php?vendorId=$vendorId");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vendor['business_name']); ?> - Vendor Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <link rel="stylesheet" href="../../public/styles/vendorDetails.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</head>

<body class="d-flex flex-column min-vh-100">
    <?php include("../../components/header.php"); ?>

    <div class="container my-2">
        <a href="./vendors.php" class="btn btn-outline-primary btn-back mb-3">← Back to Vendors</a>

        <!-- Vendor Header -->
        <div class="vendor-header mb-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8 mx-auto text-center">
                        <h1 class="mb-3"><?php echo htmlspecialchars($vendor['business_name']); ?></h1>
                        <div class="mb-4">
                            <span class="badge badge-service"><?php echo ucfirst($vendor['service']); ?></span>
                        </div>

                        <div class="vendor-info mb-4">
                            <div class="row justify-content-center">
                                <div class="col-md-5 mb-3">
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <span><strong>Location:</strong> <?php echo htmlspecialchars($vendor['service_locations']); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <div class="info-item">
                                        <i class="fas fa-tag me-2"></i>
                                        <span><strong>Price Range:</strong> <?php echo htmlspecialchars($vendor['price_range']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="vendor-description mb-4">
                            <p><?php echo nl2br(htmlspecialchars($vendor['description'])); ?></p>
                        </div>

                        <div class="vendor-actions mt-4 d-flex flex-wrap justify-content-center gap-3">
                            <?php if (!empty($vendor['website'])) { ?>
                                <a href="<?php echo htmlspecialchars($vendor['website']); ?>" target="_blank" class="btn btn-glass btn-website">
                                    <i class="fas fa-globe"></i> Visit Website
                                </a>
                            <?php } ?>

                            <?php if (!empty($vendor['instagram'])) { ?>
                                <a href="<?php echo htmlspecialchars($vendor['instagram']); ?>" target="_blank" class="btn btn-glass btn-instagram">
                                    <i class="fab fa-instagram"></i> Instagram
                                </a>
                            <?php } ?>

                            <a href="https://wa.me/<?php echo htmlspecialchars($phone); ?>" target="_blank" class="btn btn-glass btn-whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Gallery -->
        <div class="row">
            <div class="col-lg-8">
                <h3 class="mt-4 text-center">Photo Gallery</h3>
                <div class="photo-grid mt-3">
                    <?php if (count($photos) > 0) {
                        foreach ($photos as $photo) {
                            $photoUrl = "../../" . $photo['photo_url'];
                    ?>
                            <a href="<?php echo $photoUrl; ?>" target="_blank">
                                <img src="<?php echo $photoUrl; ?>" class="img-fluid" alt="Vendor Image">
                            </a>
                        <?php }
                    } else { ?>
                        <p class="text-center text-muted">No photos available.</p>
                    <?php } ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="p-4 border rounded shadow">
                    <h3 class="text-center mb-3">Ratings & Reviews</h3>

                    <!-- Average Rating -->
                    <div class="text-center mb-3">
                        <p class="mb-1"><strong>Average Rating:</strong> ⭐ <?php echo number_format($avgRating, 1); ?></p>
                        <p class="text-muted">(<?php echo $totalReviews; ?> reviews)</p>
                    </div>

                    <!-- Review Form -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" class="mb-4">
                            <label class="fw-bold">Rating:</label>
                            <select name="rating" class="form-select mb-2" required>
                                <option value="1">1 Star</option>
                                <option value="2">2 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="5">5 Stars</option>
                            </select>

                            <label class="fw-bold">Review:</label>
                            <textarea name="review" class="form-control mb-3" rows="3" placeholder="Write your review..."></textarea>

                            <button type="submit" class="btn btn-primary w-100">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <p class="text-center"><a href="../../login.php" class="fw-bold">Log in</a> to leave a review.</p>
                    <?php endif; ?>

                    <!-- Reviews Section -->
                    <div class="reviews mt-3">
                        <h4 class="mb-3">User Reviews</h4>

                        <?php if (count($reviews) > 0): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-box p-3 border rounded mb-3 shadow-sm">
                                    <div class="d-flex align-items-start">
                                        <img src="<?php echo htmlspecialchars($review['profile_picture']); ?>" class="review-profile me-3 rounded-circle border" alt="Profile" width="50" height="50">
                                        <div>
                                            <!-- Name and Time in the same row -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong class="fw-bold me-5"><?php echo htmlspecialchars($review['username']); ?></strong>
                                                <small class="text-muted"><?php echo date("M d, Y", strtotime($review['created_at'])); ?></small>
                                            </div>
                                            <!-- Review below -->
                                            <p class="mt-2 text-muted"><?php echo htmlspecialchars($review['review']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No reviews yet. Be the first to review!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include("../../components/footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
</body>

</html>