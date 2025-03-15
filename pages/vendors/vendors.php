<?php
session_start();
include "../../database/databaseConnection.php";

// Fetch all unique services for filtering
$query = "SELECT DISTINCT service FROM vendors";
$result = $conn->query($query);
$services = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors - EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <style>
        /* Custom Styles */
        .vendor-card {
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .vendor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .vendor-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .card-body {
            padding: 20px;
        }

        .filter-container {
            max-width: 300px;
            margin: auto;
        }

        @media (max-width: 768px) {
            .vendor-card img {
                height: 180px;
            }
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include("../../components/header.php") ?>

    <div class="container my-5">
        <h1 class="text-center mb-4">Find the Best Vendors</h1>

        <!-- Filter Section -->
        <div class="filter-container mb-4">
            <label for="serviceFilter" class="form-label fw-bold">Filter by Service</label>
            <select id="serviceFilter" class="form-select">
                <option value="" selected>All Services</option>
                <?php foreach ($services as $service) { ?>
                    <option value="<?php echo $service['service']; ?>"><?php echo ucfirst($service['service']); ?></option>
                <?php } ?>
            </select>
        </div>

        <!-- Vendors Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="vendorCards">
            <?php
            // Fetch all vendors along with their most recent photo
            $query = "SELECT v.*, vp.photo_url 
                      FROM vendors v
                      LEFT JOIN vendor_photos vp 
                      ON vp.id = (SELECT id FROM vendor_photos WHERE vendor_id = v.id ORDER BY uploaded_at DESC LIMIT 1)";

            $result = $conn->query($query);

            while ($vendor = $result->fetch_assoc()) {
                $photoUrl = !empty($vendor['photo_url']) ? "../../" . $vendor['photo_url'] : "https://via.placeholder.com/300x200?text=No+Image+Available";
            ?>
                <div class="col vendorCard" data-service="<?php echo $vendor['service']; ?>">
                    <div class="card vendor-card">
                        <img src="<?php echo $photoUrl; ?>" class="card-img-top" alt="Vendor Image">
                        <div class="card-body">
                            <h5 class="card-title text-primary fw-bold"><?php echo $vendor['business_name']; ?></h5>
                            <p class="card-text"><strong>Service:</strong> <?php echo ucfirst($vendor['service']); ?></p>
                            <p class="card-text"><strong>Location:</strong> <?php echo $vendor['service_locations']; ?></p>
                            <p class="card-text"><strong>Price Range:</strong> <?php echo $vendor['price_range']; ?></p>
                            <a href="./vendorDetails.php?vendorId=<?php echo $vendor['id'] ?>" class="btn btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php include("../../components/footer.php") ?>
    <script>
        // Filter vendors by service
        document.getElementById("serviceFilter").addEventListener("change", function() {
            let selectedService = this.value.toLowerCase();
            let vendorCards = document.querySelectorAll(".vendorCard");

            vendorCards.forEach(function(card) {
                let service = card.getAttribute("data-service").toLowerCase();
                card.style.display = (selectedService === "" || service.includes(selectedService)) ? "" : "none";
            });
        });
    </script>
</body>

</html>
