<?php
session_start();
include("../../database/databaseConnection.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'venue_owner') {
    die("Unauthorized Access");
}

$venueOwnerId = $_SESSION['user_id'];

// Fetch all venues owned by this venue owner
$sqlVenues = "SELECT id, name FROM venues WHERE owner_id = ?";
$stmtVenues = $conn->prepare($sqlVenues);
if (!$stmtVenues) {
    die("Query preparation failed: " . $conn->error);
}
$stmtVenues->bind_param("i", $venueOwnerId);
$stmtVenues->execute();
$venues = $stmtVenues->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtVenues->close();

$venueId = $_GET['venueId'] ?? $_SESSION['venueId'];
$_SESSION['venueId'] = $venueId;

// Fetch the venue name
$venueName = "";
if ($venueId) {
    $sqlVenueName = "SELECT name FROM venues WHERE id = ?";
    $stmtVenueName = $conn->prepare($sqlVenueName);
    if ($stmtVenueName) {
        $stmtVenueName->bind_param("i", $venueId);
        $stmtVenueName->execute();
        $result = $stmtVenueName->get_result()->fetch_assoc();
        $venueName = $result ? $result['name'] : "Unknown Venue";
        $stmtVenueName->close();
    }
}

// Fetch bookings along with payment status
$bookings = [];
if ($venueId) {
    $sql = "SELECT 
                vb.id as booking_id,
                vb.event_date,
                vb.event_purpose,
                vb.status,
                u.username,
                u.email,
                u.phone,
                u.id as user_id,
                p.status as payment_status
            FROM 
                venue_bookings vb
            JOIN 
                users u ON vb.user_id = u.id
            LEFT JOIN 
                payments p ON p.booking_id = vb.id AND p.booking_type = 'venue'
            WHERE 
                vb.venue_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $venueId);
    $stmt->execute();
    $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Change the status of the booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["statusHandler"])) {
    $status = $_POST['status'] ?? 'pending';
    $booking_id = $_POST['booking_id'] ?? null;
    $venueId = $_POST['venue_id'] ?? $_GET['venueId'];
    $user_id = $_POST['user_id'];
    $userName = $_POST['userName'];
    $date = $_POST['date'];

    if (!$venueId) {
        die("Venue ID is missing!");
    }

    $update_stmt = $conn->prepare("UPDATE venue_bookings SET status = ? WHERE id = ?");
    if (!$update_stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $update_stmt->bind_param("si", $status, $booking_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Notify user
    if ($status === "confirmed" || $status === "cancelled") {
        $message = ($status === "confirmed")
            ? "Dear $userName,\nWe're excited to inform you that your booking for $venueName on $date has been confirmed! 🎊\nWe look forward to hosting your event. If you have any special requirements or need further assistance, feel free to reach out.\nThank you for choosing us!\nBest Regards,\n$venueName"
            : "Dear $userName,\nWe regret to inform you that your booking for $venueName on $date has been cancelled.\nIf you need assistance with rescheduling or have any questions, please don't hesitate to contact us. We apologize for any inconvenience.\nBest Regards,\n$venueName";

        $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, booking_id, booking_type, message) VALUES (?, ?, 'venue', ?)");
        if (!$notify_stmt) {
            die("Query preparation failed: " . $conn->error);
        }
        $notify_stmt->bind_param("iis", $user_id, $booking_id, $message);
        $notify_stmt->execute();
        $notify_stmt->close();

        if ($status === "cancelled") {
            $delete_stmt = $conn->prepare("DELETE FROM venue_bookings WHERE id = ?");
            if (!$delete_stmt) {
                die("Query preparation failed: " . $conn->error);
            }
            $delete_stmt->bind_param("i", $booking_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
    }

    header("Location: bookingRequest.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venue Bookings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include("../../components/header.php") ?>

    <div class="container my-5">
        <a class="btn btn-secondary mb-3" href="./venues.php">← Back</a>
        <h2 class="mb-4">Bookings for: <span class="text-primary"><?= htmlspecialchars($venueName) ?></span></h2>

        <!-- Venue Selection -->
        <form method="GET" class="mb-3">
            <label for="venueSelect" class="form-label">Select Venue:</label>
            <select name="venueId" id="venueSelect" class="form-select" onchange="this.form.submit()">
                <?php foreach ($venues as $venue): ?>
                    <option value="<?= $venue['id'] ?>" <?= ($venue['id'] == $venueId) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($venue['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Booking Table -->
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Event Date</th>
                    <th>Purpose</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No bookings found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['event_date']) ?></td>
                            <td><?= htmlspecialchars($booking['event_purpose']) ?></td>
                            <td><?= htmlspecialchars($booking['username']) ?></td>
                            <td><?= htmlspecialchars($booking['email']) ?></td>
                            <td><?= htmlspecialchars($booking['phone']) ?></td>
                            <td>
                                <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $booking['payment_status'] === 'paid' ? 'success' : ($booking['payment_status'] === 'pending' ? 'danger' : 'secondary') ?>">
                                    <?= $booking['payment_status'] ? ucfirst($booking['payment_status']) : 'N/A' ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                    <input type="hidden" name="venue_id" value="<?= $venueId ?>">
                                    <input type="hidden" name="user_id" value="<?= $booking['user_id'] ?>">
                                    <input type="hidden" name="userName" value="<?= $booking['username'] ?>">
                                    <input type="hidden" name="date" value="<?= $booking['event_date'] ?>">
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto">
                                        <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <input type="submit" value="Update" name="statusHandler" class="btn btn-primary btn-sm mt-1">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include("../../components/footer.php") ?>

</body>

</html>
