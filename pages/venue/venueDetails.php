<?php
session_start();
include("../../database/databaseConnection.php");

if (!isset($_GET['venueId']) || !is_numeric($_GET['venueId'])) {
    echo "<script>alert('Invalid Venue ID'); window.location.href='venues.php';</script>";
    exit();
}

$venueId = $_GET['venueId'];

// Fetch venue details directly from the venues table
$query = $conn->prepare("SELECT * FROM venues WHERE id = ?");
$query->bind_param("i", $venueId);
$query->execute();
$result = $query->get_result();
$venue = $result->fetch_assoc();

if (!$venue) {
    echo "<script>alert('Venue not found'); window.location.href='venues.php';</script>";
    exit();
}

// Fetch booked dates, excluding past dates
$bookingsQuery = $conn->prepare("SELECT DATE(event_date) AS event_date FROM venue_bookings WHERE venue_id = ? AND event_date >= CURDATE()");
$bookingsQuery->bind_param("i", $venueId);
$bookingsQuery->execute();
$bookingsResult = $bookingsQuery->get_result();
$bookedDates = [];
while ($row = $bookingsResult->fetch_assoc()) {
    $bookedDates[] = $row['event_date'];
}

// Fetch additional venue images
$imagesQuery = $conn->prepare("SELECT image_url FROM venue_images WHERE venue_id = ?");
$imagesQuery->bind_param("i", $venueId);
$imagesQuery->execute();
$imagesResult = $imagesQuery->get_result();
$venueImages = [];
while ($row = $imagesResult->fetch_assoc()) {
    $venueImages[] = $row['image_url'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($venue['name']); ?> - EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid #ddd;
        }

        .venue-images img {
            width: 100px;
            height: 100px;
            cursor: pointer;
            border-radius: 5px;
            object-fit: cover;
            margin: 5px;
            transition: transform 0.2s ease-in-out;
            border: 2px solid #ddd;
        }

        .venue-images img:hover {
            transform: scale(1.1);
            border-color: #007bff;
        }

        .calendar-container {
            width: 100%;
            padding: 10px;
        }

        #calendar {
            width: 100%;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <?php include("../../components/header.php"); ?>

    <div class="container mt-5">
        <div class="row g-4">
            <div class="col-lg-6">
                <img id="mainImage" src="<?php echo htmlspecialchars($venue['thumbnail']); ?>" class="main-image" alt="Venue Image">
            </div>
            <div class="col-lg-6">
                <div class="card p-3">
                    <h4 class="text-info">More Images</h4>
                    <div class="venue-images d-flex flex-wrap">
                        <?php foreach ($venueImages as $image) { ?>
                            <img src="../../<?php echo htmlspecialchars($image); ?>" onclick="changeMainImage(this.src)" alt="Venue Image">
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-6">
                <h2 class="text-dark"> <?php echo htmlspecialchars($venue['name']); ?> </h2>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($venue['location']); ?></p>
                <p><strong>Best for :</strong> <?php echo htmlspecialchars($venue['venue_used_for']); ?></p>
                <p><strong>Capacity:</strong> <?php echo htmlspecialchars($venue['capacity']); ?> people</p>
                <div class="d-md-flex justify-content-between align-items-md-center mb-2">
                    <p><strong>Price Per Day:</strong> ₹<?php echo htmlspecialchars($venue['price_per_day']); ?></p>
                    <a href="./venueBooking.php?venueId=<?php echo htmlspecialchars($venueId); ?>" class="btn btn-danger">Book now</a>
                </div>
                <p><?php echo htmlspecialchars($venue['description']); ?></p>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <h4 class="text-primary">Manager Details</h4>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($venue['manager_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($venue['manager_email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($venue['manager_phone']); ?></p>
                </div>
                <div class="card mt-3">
                    <h4 class="text-success">Venue Availability</h4>
                    <div class="calendar-container">
                        <div id='calendar'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("../../components/footer.php"); ?>

    <script>
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: "auto",
                contentHeight: "auto",
                aspectRatio: 1.8,
                events: [
                    <?php
                    foreach ($bookedDates as $date) {
                        echo "{ title: 'Booked', start: '$date', backgroundColor: 'red', borderColor: 'red', textColor: 'white' },";
                    }
                    ?>
                ],
                dayCellDidMount: function(info) {
                    let today = new Date();
                    let todayStr = today.toLocaleDateString('en-CA'); 
                    let cellDate = new Date(info.date);
                    let cellDateStr = cellDate.toLocaleDateString('en-CA'); 
                    if (cellDateStr < todayStr) {
                        info.el.style.backgroundColor = '#d3d3d3';
                    } else if (cellDateStr === todayStr) {
                        info.el.style.backgroundColor = '#ffecb3';
                    }
                }

            });
            calendar.render();
        });
    </script>
</body>

</html>


