<?php
session_start();
include "../../database/databaseConnection.php";

// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../login.php");
//     exit();
// }

$query = "SELECT * FROM venues";
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
    <link rel="stylesheet" href="../../public/styles/header.css">
    <link rel="stylesheet" href="../../public/styles/venue.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <title>Venue - EMS</title>
</head>

<body>
    <main class="main-box-home">
        <?php include("../../components/header.php") ?>
        <div class="container">
            <div class="venueHeader flex-r">
                <h1>Venues</h1>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'venue_owner') { ?>
                    <a href="./addVenue.php" class="btn addVenueBtn">Request to add Venue</a>
                <?php } ?>
            </div>

            <div class="venueTray flex-r">
                <?php foreach ($venues as $venue) : ?>
                    <div class="venueCard flex-c">
                        <img src="<?= $venue['thumbnail'] ?>" alt="Venue Thumbnail">
                        <h3><?php echo $venue["name"] ?></h3>
                        <p>
                            <?php
                            $description = $venue["description"];
                            echo strlen($description) > 100 ? substr($description, 0, 100) . "..." : $description;
                            ?>
                        </p>
                        <a href="<?= 'venueDetails.php?venueId='. $venue['id']?>">View Venue</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <?php include("../../components/footer.php") ?>
</body>

</html>