<?php
session_start();

// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../login.php");
//     exit();
// }

include("../../database/databaseConnection.php");

if (!isset($_GET['eventId']) || !is_numeric($_GET['eventId'])) {
    echo "<script>alert('Invalid Venue ID'); window.location.href='venues.php';</script>";
    exit();
}

$eventId = $_GET['eventId'];

// Use a prepared statement to prevent SQL injection
$query = $conn->prepare("SELECT * FROM events WHERE id = ?");
$query->bind_param("i", $eventId);
$query->execute();
$result = $query->get_result();

$event = $result->fetch_assoc(); // Fetch a single row instead of an array of rows

if (!$event) {
    echo "<script>alert('event not found'); window.location.href='events.php';</script>";
    exit();
}

$venueQuery = $conn->prepare("SELECT * FROM venues WHERE id = ?");
$venueQuery->bind_param("i",$event['venue_id']);
$venueQuery->execute();
$resultVenue = $venueQuery->get_result();
$venue = $resultVenue->fetch_assoc();

if (!$venue) {
    echo "<script>alert('venue not found'); window.location.href='events.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/styles/header.css">
    <link rel="stylesheet" href="../../public/styles/events.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <title><?php echo htmlspecialchars($event['name']); ?> - EMS</title>
</head>

<body>
    <main class="main-box-home">
        <?php include("../../components/header.php") ?>
        <div class="container eventDetailsContainer flex-r">
            <div class="eventLeft">
                <h1><?php echo htmlspecialchars($event['name']); ?></h1>
                <p>date: <?php echo htmlspecialchars($event['date']); ?></p>
                <p>Description: <?php echo htmlspecialchars($event['description']); ?></p>
                <p>Capacity: <?php echo htmlspecialchars($event['capacity']); ?></p>
                <p>status: <?php echo htmlspecialchars($event['status']); ?></p>
            </div>
            <div class="eventRight">
                <div class="venueDisplay flex-c">
                    <h1>VENUE</h1>
                    <img src="<?php echo htmlspecialchars($venue['thumbnail']); ?>" alt="Venue Image">
                    <h2><?php echo htmlspecialchars($venue['name']); ?></h2>
                    <a href="<?= '../venue/venueDetails.php?venueId='. $venue['id']?>" class="btn">View Venue</a>
                </div>
            </div>
        </div>
    </main>

    <?php include("../../components/footer.php") ?>
</body>

</html>
