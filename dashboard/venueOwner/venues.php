<?php
session_start();
include "../../database/databaseConnection.php";

$venue_owner_id = $_SESSION['user_id'];

$query = "SELECT * FROM venues WHERE owner_id = $venue_owner_id";
$result = $conn->query($query);

$venues = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row["status"] === "approved") {
            $venues[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venues - EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include("../../components/header.php") ?>

    <main class="container my-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm mb-4">
            <h1 class="m-0">My Venues</h1>
        </div>

        <div class="row g-4">
            <?php foreach ($venues as $venue) : ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <img src="<?= $venue['thumbnail'] ?>" class="card-img-top" alt="Venue Thumbnail">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($venue["name"]) ?></h5>
                            <p class="card-text text-muted">
                                <?= strlen($venue["description"]) > 100 ? substr($venue["description"], 0, 100) . "..." : htmlspecialchars($venue["description"]) ?>
                            </p>
                            <a href="venueOwner_dashboard.php?venueId=<?= $venue['id'] ?>" class="btn btn-dark mt-auto">View Venue</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include("../../components/footer.php") ?>
</body>

</html>
