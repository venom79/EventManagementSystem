<?php
require __DIR__ . '../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../config/');
$dotenv->load();
$apiKey = $_ENV['API_KEY'];

session_start();
include("../../database/databaseConnection.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to book a vendor.'); window.location.href='../login.php';</script>";
    exit();
}

$userId = $_SESSION['user_id'];
$vendorId = $_GET['vendorId'] ?? null;

if (!$vendorId || !is_numeric($vendorId)) {
    echo "<script>alert('Invalid Vendor ID'); window.location.href='vendors.php';</script>";
    exit();
}

// Fetch vendor details
$query = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
$query->bind_param("i", $vendorId);
$query->execute();
$result = $query->get_result();
$vendor = $result->fetch_assoc();

if (!$vendor) {
    echo "<script>alert('Vendor not found'); window.location.href='vendors.php';</script>";
    exit();
}

// Fetch booked dates
$bookingsQuery = $conn->prepare("SELECT booking_date FROM vendor_bookings WHERE vendor_id = ?");
$bookingsQuery->bind_param("i", $vendorId);
$bookingsQuery->execute();
$bookingsResult = $bookingsQuery->get_result();
$bookedDates = [];
while ($row = $bookingsResult->fetch_assoc()) {
    $bookedDates[] = $row['booking_date'];
}

// Fetch venues
$venues = [];
$venuesQuery = $conn->query("SELECT id, name, location FROM venues ORDER BY name ASC");
if ($venuesQuery) {
    while ($venue = $venuesQuery->fetch_assoc()) {
        $venues[] = $venue;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bookingDate = $_POST['booking_date'];
    $venueId = $_POST['venue_selection'];

    if ($venueId === "other") {
        $venueName = "Other";
        $venueLocation = $_POST['location'] ?? '';
    } else {
        // Fetch venue details
        $venueQuery = $conn->prepare("SELECT name, location FROM venues WHERE id = ?");
        $venueQuery->bind_param("i", $venueId);
        $venueQuery->execute();
        $venueResult = $venueQuery->get_result();
        $venueData = $venueResult->fetch_assoc();

        $venueName = $venueData['name'] ?? '';
        $venueLocation = $venueData['location'] ?? '';
    }

    if (!$bookingDate || strtotime($bookingDate) < strtotime(date("Y-m-d"))) {
        echo "<script>alert('Invalid booking date. Please select a future date.');</script>";
    } elseif (in_array($bookingDate, $bookedDates)) {
        echo "<script>alert('This vendor is already booked on this date. Please select another date.');</script>";
    } elseif (empty($venueName)) {
        echo "<script>alert('Please select or enter a venue.');</script>";
    } else {
        $insertQuery = $conn->prepare("INSERT INTO vendor_bookings (user_id, vendor_id, booking_date, venue_name, venue_location, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW(), NOW())");
        $insertQuery->bind_param("iisss", $userId, $vendorId, $bookingDate, $venueName, $venueLocation);

        if ($insertQuery->execute()) {
            echo "<script>alert('Booking request submitted successfully!'); window.location.href='vendors.php';</script>";
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
    <title>Book <?php echo htmlspecialchars($vendor['business_name']); ?> - EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <link rel="stylesheet" href="../../public/styles/venueBooking.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">

    <style>
        #map {
            height: 300px;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .map-container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 800px;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            border-radius: 8px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .map-controls {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }
    </style>

</head>

<body>

    <?php include("../../components/header.php"); ?>

    <div class="container my-4">
        <div class="venue-header">
            <h2><?php echo htmlspecialchars($vendor['business_name']); ?></h2>
        </div>

        <div class="row g-4">
            <!-- Booking Form Section -->
            <div class="col-md-5">
                <div class="card h-100">
                    <h4 class="mb-3">Book Your Vendor</h4>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?vendorId=' . $vendorId; ?>">
                        <div class="mb-3">
                            <label for="booking_date" class="form-label">Select Booking Date</label>
                            <input type="date" class="form-control" id="booking_date" name="booking_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="venue_selection" class="form-label">Select Venue</label>
                            <select class="form-control" id="venue_selection" name="venue_selection" required onchange="updateVenueLocation()">
                                <option value="">-- Select Venue --</option>
                                <?php foreach ($venues as $venue): ?>
                                    <option value="<?php echo $venue['id']; ?>" data-location="<?php echo htmlspecialchars($venue['location']); ?>">
                                        <?php echo htmlspecialchars($venue['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="venue_location_container" style="display: none;">
                            <label for="location" class="form-label">Venue Location</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="location" name="location" readonly>
                                <button type="button" class="btn btn-primary" id="openMapBtn">Choose from Map</button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Submit Booking Request</button>
                    </form>

                    <div class="booking-tip mt-3">
                        <strong>Tip:</strong> Dates marked in red are already booked. Please select an available date. <br>
                    </div>
                    <div class="booking-tip mt-3">
                        <strong>Tip:</strong> You will be notified via notification once your booking is confirmed.
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="col-md-7">
                <div class="card">
                    <h4 class="mb-3">Vendor Availability</h4>
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

    <!-- Map Modal -->
    <div class="overlay" id="overlay"></div>
    <div class="map-container" id="mapModal">
        <h4>Select Venue Location</h4>
        <div class="input-group mb-3">
            <input type="text" class="form-control" id="searchBox" placeholder="Search for a location">
            <button class="btn btn-outline-secondary" type="button" id="searchButton">Search</button>
        </div>
        <div id="map"></div>
        <p id="selectedLocation">Selected location: None</p>
        <div class="map-controls">
            <button type="button" class="btn btn-secondary me-2" id="closeMapBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmLocationBtn">Confirm Location</button>
        </div>
    </div>

    <?php include("../../components/footer.php"); ?>

    <script>
        function updateVenueLocation() {
            let venueSelect = document.getElementById('venue_selection');
            let venueLocation = document.getElementById('location');
            let venueContainer = document.getElementById('venue_location_container');
            let selectedOption = venueSelect.options[venueSelect.selectedIndex];

            if (venueSelect.value === "other") {
                venueContainer.style.display = 'block';
                venueLocation.value = '';
                venueLocation.removeAttribute('readonly');
            } else {
                venueContainer.style.display = 'none';
                venueLocation.value = selectedOption.getAttribute('data-location');
            }
        }

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
                dayHeaderFormat: {
                    weekday: 'narrow'
                },
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
            document.getElementById('booking_date').setAttribute('min', todayStr);

            // Highlight selected date on calendar when picking a date
            document.getElementById('booking_date').addEventListener('change', function() {
                var selectedDate = this.value;
                calendar.gotoDate(selectedDate);
            });
        });
    </script>
    <!-- Load Google Maps API with Places library -->
    <script src="../../scripts/mapAPI.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>&libraries=places&callback=initMap" async defer></script>

</body>

</html>