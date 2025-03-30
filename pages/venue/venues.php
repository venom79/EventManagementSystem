<?php
session_start();
include "../../database/databaseConnection.php";

$query = "SELECT * FROM venues WHERE status = 'approved'";
$result = $conn->query($query);
$venues = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $venues[] = $row;
    }
}

// Fetch unique "best_for" categories for the filter dropdown
$bestForOptions = [];
foreach ($venues as $venue) {
    $categories = array_map('trim', explode(",", $venue['venue_used_for']));
    foreach ($categories as $category) {
        $categoryLower = strtolower($category);
        if (!in_array($categoryLower, array_map('strtolower', $bestForOptions))) {
            $bestForOptions[] = $category;
        }
    }
}
sort($bestForOptions);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venues - EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <script>
        function filterVenues() {
            let selectedFilter = document.getElementById("best_for_filter").value.toLowerCase();
            let venueCards = document.querySelectorAll(".venue-card");
            
            venueCards.forEach(card => {
                let bestFor = card.getAttribute("data-best-for").toLowerCase();
                if (selectedFilter === "all" || bestFor.includes(selectedFilter)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        }
    </script>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include("../../components/header.php") ?>

    <main class="container my-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm mb-4">
            <h1 class="m-0">Venues</h1>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'venue_owner') { ?>
                <a href="./addVenue.php" class="btn btn-danger">Request to add Venue</a>
            <?php } ?>
        </div>

        <!-- Filter Dropdown -->
        <form class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="best_for_filter" class="form-label">Filter venues by category:</label>
                    <select id="best_for_filter" class="form-select" onchange="filterVenues()">
                        <option value="all">All</option>
                        <?php foreach ($bestForOptions as $option) : ?>
                            <option value="<?= htmlspecialchars(strtolower($option)) ?>">
                                <?= htmlspecialchars($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <div class="row g-4">
            <?php foreach ($venues as $venue) : ?>
                <div class="col-lg-4 col-md-6 venue-card" data-best-for="<?= strtolower($venue['venue_used_for']) ?>">
                    <div class="card shadow-sm border-0 h-100">
                        <img src="<?= $venue['thumbnail'] ?>" class="card-img-top" alt="Venue Thumbnail">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"> <?= htmlspecialchars($venue["name"]) ?> </h5>
                            <p class="card-text text-muted">
                                <?= strlen($venue["description"]) > 100 ? substr($venue["description"], 0, 100) . "..." : htmlspecialchars($venue["description"]) ?>
                            </p>
                            <a href="venueDetails.php?venueId=<?= $venue['id'] ?>" class="btn btn-dark mt-auto">View Venue</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include("../../components/footer.php") ?>
</body>

</html>
