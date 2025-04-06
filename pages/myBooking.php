<?php
session_start();
include("../database/databaseConnection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$userID = $_SESSION['user_id'];

// Fetch vendor bookings
$vendorBookingsQuery = "SELECT vb.id, v.id AS vendor_id, v.business_name, vb.booking_date, vb.venue_location, vb.venue_name, vb.status, vb.payment_status 
                        FROM vendor_bookings vb
                        JOIN vendors v ON vb.vendor_id = v.id
                        WHERE vb.user_id = ?";
$stmt1 = $conn->prepare($vendorBookingsQuery);
$stmt1->bind_param("i", $userID);
$stmt1->execute();
$vendorBookings = $stmt1->get_result();
$stmt1->close();

// Fetch venue bookings
$venueBookingsQuery = "SELECT vb.id, ven.id AS venue_id, ven.name, ven.location, vb.event_date, vb.event_purpose, vb.status, vb.payment_status 
                        FROM venue_bookings vb
                        JOIN venues ven ON vb.venue_id = ven.id
                        WHERE vb.user_id = ?";
$stmt2 = $conn->prepare($venueBookingsQuery);
$stmt2->bind_param("i", $userID);
$stmt2->execute();
$venueBookings = $stmt2->get_result();
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/styles/style.css">
</head>

<body>
    <?php include("../components/header.php"); ?>

    <div class="container mt-2 mb-5">
        <h2 class="mb-4 text-center fw-bold">My Bookings</h2>

        <div class="mb-5">
            <h3 class="text-primary">Vendor Bookings</h3>
            <?php if ($vendorBookings->num_rows > 0): ?>
                <div class="row g-4">
                    <?php while ($booking = $vendorBookings->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                                <div class="card-body">
                                    <h5 class="card-title text-dark fw-bold">Vendor: <?php echo htmlspecialchars($booking['business_name']); ?></h5>
                                    <p class="text-muted mb-2"><strong>Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
                                    <p class="mb-2"><strong>Location:</strong> <?php echo htmlspecialchars($booking['venue_location']); ?></p>
                                    <p class="mb-2"><strong>Venue Name:</strong> <?php echo htmlspecialchars($booking['venue_name']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                                        <span class="badge bg-<?php echo ($booking['status'] == 'confirmed') ? 'success' : (($booking['status'] == 'cancelled') ? 'danger' : 'warning'); ?> px-3 py-2 rounded-pill">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                        <a href="./vendors/vendorDetails.php?vendorId=<?php echo $booking['vendor_id']; ?>" class="btn btn-outline-secondary">View Vendor</a>
                                        <?php if ($booking['status'] == 'confirmed'): ?>
                                            <?php if ($booking['payment_status'] == 'pending'): ?>
                                                <a href="./payment/payment.php?type=vendor&booking_id=<?= $booking['id'] ?>&vendor_id=<?= $booking['vendor_id'] ?>" class="btn btn-success">Pay Now</a>
                                            <?php else: ?>
                                                <a href="./payment/paymentDetails.php?type=vendor&booking_id=<?= $booking['id'] ?>" class="btn btn-outline-success">Payment Details</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No vendor bookings found.</p>
            <?php endif; ?>
        </div>

        <div>
            <h3 class="text-primary">Venue Bookings</h3>
            <?php if ($venueBookings->num_rows > 0): ?>
                <div class="row g-4">
                    <?php while ($booking = $venueBookings->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                                <div class="card-body">
                                    <h5 class="card-title text-dark fw-bold">Venue: <?php echo htmlspecialchars($booking['name']); ?></h5>
                                    <p class="text-muted mb-2"><strong>Event Date:</strong> <?php echo htmlspecialchars($booking['event_date']); ?></p>
                                    <p class="mb-2"><strong>Location:</strong> <?php echo htmlspecialchars($booking['location']); ?></p>
                                    <p class="mb-2"><strong>Purpose:</strong> <?php echo htmlspecialchars($booking['event_purpose']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                                        <span class="badge bg-<?php echo ($booking['status'] == 'confirmed') ? 'success' : (($booking['status'] == 'cancelled') ? 'danger' : 'warning'); ?> px-3 py-2 rounded-pill">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                        <a href="./venue/venueDetails.php?venueId=<?php echo $booking['venue_id']; ?>" class="btn btn-outline-secondary">View Venue</a>
                                        <?php if ($booking['status'] == 'confirmed'): ?>
                                            <?php if ($booking['payment_status'] == 'pending'): ?>
                                                <a href="./payment/payment.php?type=venue&booking_id=<?= $booking['id'] ?>&venue_id=<?= $booking['venue_id'] ?>" class="btn btn-success">Pay Now</a>
                                            <?php else: ?>
                                                <a href="./payment/paymentDetails.php?type=venue&booking_id=<?= $booking['id'] ?>" class="btn btn-outline-success">Payment Details</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No venue bookings found.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include("../components/footer.php"); ?>
</body>

</html>
