<?php
session_start();
include("../../database/databaseConnection.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to create an event.'); window.location.href = '../../login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$venues = [];
$dateFilter = $_POST["date"] ?? '';

// Fetch all venues initially without filtering
$stmt = $conn->prepare("SELECT v.id, v.name, v.location, v.capacity, v.thumbnail, 
           (SELECT COUNT(*) FROM venue_bookings vb WHERE vb.venue_id = v.id AND vb.event_date = ?) AS booked
    FROM venues v 
    WHERE v.status = 'approved'");
$stmt->bind_param("s", $dateFilter);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $venues[] = $row;
}

// Handle event creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_event"])) {
    $name = $_POST["name"];
    $description = $_POST["description"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $venue_id = $_POST["venue_id"];
    $capacity = $_POST["capacity"];

    // Check if venue is already booked
    $checkVenue = $conn->prepare("SELECT * FROM venue_bookings WHERE venue_id = ? AND event_date = ?");
    $checkVenue->bind_param("is", $venue_id, $date);
    $checkVenue->execute();
    $checkVenueResult = $checkVenue->get_result();

    if ($checkVenueResult->num_rows > 0) {
        echo "<script>alert('This venue is already booked on the selected date. Please choose another venue.');</script>";
    } else {
        // Insert event
        $sql = $conn->prepare("INSERT INTO events (user_id, name, description, date, time, venue_id, capacity) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $sql->bind_param("issssii", $user_id, $name, $description, $date, $time, $venue_id, $capacity);

        if ($sql->execute()) {
            // Book venue
            $bookVenue = $conn->prepare("INSERT INTO venue_bookings (venue_id, user_id, event_date, status) 
                                         VALUES (?, ?, ?, 'booked')");
            $bookVenue->bind_param("iis", $venue_id, $user_id, $date);
            $bookVenue->execute();

            echo "<script>alert('Event created successfully!'); window.location.href = 'events.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <link rel="stylesheet" href="../../public/styles/events.css">
    <link rel="stylesheet" href="../../public/styles/header.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
</head>
<body>

<main class="main-box-home">
    <?php include("../../components/header.php") ?>

    <div class="containerEventADD">
        <!-- Event Form -->
        <div class="form-container">
            <h2>Create an Event</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Event Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label>Event Date</label>
                    <input type="date" name="date" required id="eventDate" value="<?php echo htmlspecialchars($dateFilter); ?>">
                </div>
                <div class="form-group">
                    <label>Event Time</label>
                    <input type="time" name="time" required>
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" min="1" required>
                </div>
                <input type="hidden" name="venue_id" id="venue_id" required>
                <button type="submit" name="create_event" id="createEventBtn">Create Event</button>
            </form>
        </div>

        <!-- Venue Selection -->
        <div class="venue-container">
            <h3>Select Venue</h3>
            <input type="text" id="searchInput" placeholder="Search venue...">
            
            <div class="venue-list" id="venueList">
                <?php foreach ($venues as $venue): ?>
                    <?php $isAvailable = ($venue["booked"] == 0); ?>
                    <div class="venue-card <?php echo !$isAvailable ? 'unavailable' : ''; ?>" 
                         id="venue-<?php echo $venue['id']; ?>" 
                         data-name="<?php echo strtolower($venue['name']); ?>"
                         data-available="<?php echo $isAvailable ? 'true' : 'false'; ?>"
                         onclick="toggleVenueSelection(<?php echo $venue['id']; ?>)">
                        <img src="<?php echo $venue['thumbnail']; ?>" alt="Venue Image">
                        <p><?php echo $venue['name']; ?></p>
                        <small><?php echo $venue['location']; ?> | Capacity: <?php echo $venue['capacity']; ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<?php include("../../components/footer.php") ?>
</body>

<script>
    function toggleVenueSelection(id) {
        let venueCard = document.getElementById("venue-" + id);
        if (venueCard.classList.contains("unavailable")) return;

        let selectedVenue = document.getElementById("venue_id");
        
        if (venueCard.classList.contains("selected")) {
            venueCard.classList.remove("selected");
            selectedVenue.value = "";
        } else {
            document.querySelectorAll(".venue-card").forEach(venue => venue.classList.remove("selected"));
            venueCard.classList.add("selected");
            selectedVenue.value = id;
        }
    }
</script>

</html>
