<?php
session_start();
include("../../database/databaseConnection.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to book a venue.'); window.location.href='../login.php';</script>";
    exit();
}

$userId = $_SESSION['user_id'];
$venueId = $_GET['venueId'] ?? null;

if (!$venueId || !is_numeric($venueId)) {
    echo "<script>alert('Invalid Venue ID'); window.location.href='venues.php';</script>";
    exit();
}

$query = $conn->prepare("SELECT * FROM venues WHERE id = ?");
$query->bind_param("i", $venueId);
$query->execute();
$result = $query->get_result();
$venue = $result->fetch_assoc();

if (!$venue) {
    echo "<script>alert('Venue not found'); window.location.href='venues.php';</script>";
    exit();
}

$bookingsQuery = $conn->prepare("SELECT DATE(event_date) AS event_date FROM venue_bookings WHERE venue_id = ?");
$bookingsQuery->bind_param("i", $venueId);
$bookingsQuery->execute();
$bookingsResult = $bookingsQuery->get_result();
$bookedDates = [];
while ($row = $bookingsResult->fetch_assoc()) {
    $bookedDates[] = $row['event_date'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eventDate = $_POST['event_date'];
    $eventPurpose = $_POST['event_purpose'];
    if (!$eventDate || strtotime($eventDate) < strtotime(date("Y-m-d"))) {
        echo "<script>alert('Invalid event date. Please select a future date.');</script>";
    } elseif (in_array($eventDate, $bookedDates)) {
        echo "<script>alert('This date is already booked. Please select another.');</script>";
    } else {
        $insertQuery = $conn->prepare("INSERT INTO venue_bookings (user_id, venue_id, event_date,	event_purpose, status, created_at, updated_at) VALUES (?, ?, ?, ?,'pending', NOW(), NOW())");
        $insertQuery->bind_param("iiss", $userId, $venueId, $eventDate,$eventPurpose);
        if ($insertQuery->execute()) {
            echo "<script>alert('Booking request submitted successfully!'); window.location.href='venues.php';</script>";
        } else {
            echo "<script>alert('Booking failed. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo htmlspecialchars($venue['name']); ?> - EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <link rel="stylesheet" href="../../public/styles/venueBooking.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
</head>
<body>

<?php include("../../components/header.php"); ?>

<div class="container my-4">
    <div class="venue-header">
        <h2><?php echo htmlspecialchars($venue['name']); ?></h2>
        <p class="mb-0"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($venue['location']); ?></p>
    </div>

    <div class="row g-4">
        <!-- Booking Form Section -->
        <div class="col-md-5">
            <div class="card h-100">
                <h4 class="mb-3">Book Your Event</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label for="event_date" class="form-label">Select Event Date</label>
                        <input type="date" class="form-control" id="event_date" name="event_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_purpose" class="form-label">What will the venue be used for?</label>
                        <input type="text" class="form-control" name="event_purpose" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit Booking Request</button>
                </form>
                
                <div class="booking-tip mt-3">
                    <strong>Tip:</strong> Dates marked in red are already booked. Please select an available date. <br>
                </div>
                <div class="booking-tip mt-3">
                    <strong>Tip:</strong>  You will be notified via notification once your booking is confirmed.
                </div>
            </div>
        </div>

        <!-- Calendar Section -->
        <div class="col-md-7">
            <div class="card">
                <h4 class="mb-3">Venue Availability</h4>
                <div id='calendar'></div>
                
                <div class="date-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #e74c3c;"></div>
                        <span>Booked</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../../components/footer.php"); ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var bookedDates = <?php echo json_encode($bookedDates); ?>;
        var calendarEl = document.getElementById('calendar');
        
        // Get current date
        var today = new Date();
        var todayStr = today.toISOString().split('T')[0];
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            // Simplify the header
            headerToolbar: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            // Hide week numbers to save space
            weekNumbers: false,
            // Make day names more compact
            dayHeaderFormat: { weekday: 'narrow' },
            // Disable row heights that would cause scrolling
            expandRows: false,
            events: bookedDates.map(date => ({
                start: date,
                display: 'background',
                backgroundColor: '#e74c3c',
                classNames: ['booked-date']
            })),
            // Hide time display
            displayEventTime: false,
            // Disable click and hover interactions to simplify
            selectable: false,
            dateClick: null,
            eventClick: null,
            // Apply custom CSS to day cells
            dayCellDidMount: function(info) {
                // Extra styling for day cells
                info.el.style.padding = '0';
                info.el.style.cursor = 'default';
                
                // Make date number smaller
                const dateNum = info.el.querySelector('.fc-daygrid-day-top a');
                if (dateNum) {
                    dateNum.style.fontSize = '0.8em';
                    dateNum.style.padding = '1px';
                    dateNum.style.margin = '0';
                }
            }
        });

        calendar.render();

        // Force render at smaller size
        setTimeout(function() {
            calendar.updateSize();
        }, 100);

        // Disable past dates in date picker
        document.getElementById('event_date').setAttribute('min', todayStr);
        
        // Highlight selected date on calendar when picking a date
        document.getElementById('event_date').addEventListener('change', function() {
            var selectedDate = this.value;
            calendar.gotoDate(selectedDate);
        });
    });
</script>

</body>
</html>