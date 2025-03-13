<?php
session_start();

// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../login.php");
//     exit();
// }

include("../../database/databaseConnection.php");

if (!isset($_GET['venueId']) || !is_numeric($_GET['venueId'])) {
    echo "<script>alert('Invalid Venue ID'); window.location.href='venues.php';</script>";
    exit();
}

$venueId = $_GET['venueId'];

// Use a prepared statement to prevent SQL injection
$query = $conn->prepare("SELECT * FROM venues WHERE id = ?");
$query->bind_param("i", $venueId);
$query->execute();
$result = $query->get_result();

$venue = $result->fetch_assoc(); // Fetch a single row instead of an array of rows

if (!$venue) {
    echo "<script>alert('Venue not found'); window.location.href='venues.php';</script>";
    exit();
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
    <title><?php echo htmlspecialchars($venue['name']); ?> - EMS</title>
</head>

<body>
    <main class="main-box-home">
        <?php include("../../components/header.php") ?>
        <div class="container venueDetailsContainer flex-r">
            <div class="venueLeft">
                <img src="<?php echo htmlspecialchars($venue['thumbnail']); ?>" alt="Venue Image">
                <h1><?php echo htmlspecialchars($venue['name']); ?></h1>
                <p>Location: <?php echo htmlspecialchars($venue['location']); ?></p>
                <p>Description: <?php echo htmlspecialchars($venue['description']); ?></p>
                <p>Capacity: <?php echo htmlspecialchars($venue['capacity']); ?></p>
                <p>Price: <?php echo htmlspecialchars($venue['price_per_day']); ?></p>
            </div>
            <div class="venueRight">
                
            </div>
        </div>
    </main>

    <?php include("../../components/footer.php") ?>
</body>

</html>
