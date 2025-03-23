<?php
session_start();
// Database connection (make sure this is set up correctly)
include("../../database/databaseConnection.php");

// Validate vendor ID
if (!isset($_SESSION['vendorId']) || $_SESSION['vendorId'] <= 0) {
    die("Invalid vendor ID.");
}
$vendorId = $_SESSION['vendorId'];

if ($vendorId <= 0) {
    die("Invalid vendor ID.");
}

// Fetch vendor details from the database
$query = "SELECT * FROM vendors WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vendorId);
$stmt->execute();
$vendor = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if vendor exists
if (!$vendor) {
    die("Vendor not found.");
}

// Fetch bookings for the vendor
$bookings = [];
$sql = "SELECT 
        vb.id as booking_id,
        vb.user_id,
        vb.booking_date, 
        vb.venue_name, 
        vb.venue_location, 
        vb.status, 
        v.business_name,
        u.username, 
        u.email, 
        u.phone 
    FROM 
        vendor_bookings AS vb 
    JOIN 
        vendors AS v ON vb.vendor_id = v.id
    JOIN 
        users AS u ON vb.user_id = u.id
    WHERE 
        vb.vendor_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vendorId);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['statusHandler'])) {
    $status = isset($_POST['status']) ? $_POST['status'] : 'pending';
    $booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : null;
    $business_name = $_POST['business_name'];
    $user_id = $_POST['user_id'];
    $userName = $_POST['userName'];
    $date = $_POST['date'];

    $update_stmt = $conn->prepare("UPDATE vendor_bookings SET status = ? WHERE id = ?");
    if (!$update_stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $update_stmt->bind_param("si", $status, $booking_id);
    $update_stmt->execute();
    $update_stmt->close();

    //notify the customer abuot the result of his/her booking
    if ($status === "confirmed" || $status === "cancelled") {
        $message = "";
        if ($status === 'confirmed') {
            $message = "Dear $userName,\n"
                . "We are delighted to inform you that your booking at $business_name on $date has been successfully confirmed! 🎉\n"
                . "Our team is excited to host you and ensure that you have a wonderful experience. If you have any specific requests or need further assistance, feel free to reach out.\n"
                . "Thank you for choosing $business_name. We look forward to making your event special!\n"
                . "Best regards,\n"
                . "$business_name Team";
        } elseif ($status === 'cancelled') {
            $message = "Dear $userName,\n"
                . "We regret to inform you that your booking at $business_name on $date has been cancelled. 😔\n"
                . "We understand this might be disappointing, and we sincerely apologize for any inconvenience caused. If you would like to reschedule or need further assistance, please do not hesitate to contact us.\n"
                . "We truly appreciate your interest in $business_name and hope to serve you in the future.\n"
                . "Best regards,\n"
                . "$business_name Team";
        }
        $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id,booking_id,booking_type,message)      VALUES(?,?,'vendor',?)");
        if (!$notify_stmt) {
            die("Query preparation failed: " . $conn->error);
        }
        $notify_stmt->bind_param("iis", $user_id, $booking_id, $message);
        $notify_stmt->execute();
        $notify_stmt->close();

        if ($status === "cancelled") {
            // Delete the booking from the database
            $delete_stmt = $conn->prepare("DELETE FROM vendor_bookings WHERE id = ?");
            if (!$delete_stmt) {
                die("Query preparation failed: " . $conn->error);
            }
            $delete_stmt->bind_param("i", $booking_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
    }

    header("Location: bookingRequest.php?");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Requests - <?= htmlspecialchars($vendor['business_name']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/styles/style.css">
</head>

<body>
    <?php include("../../components/header.php"); ?>
    <div class="container mt-5">
        <a class="btn btn-secondary mb-3" href="./vendor_dashboard.php">← Back</a>
        <h2 class="text-center"><?= htmlspecialchars($vendor['business_name']) ?> - Booking Requests</h2>

        <?php if (count($bookings) > 0): ?>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Booking Date</th>
                        <th>Venue</th>
                        <th>Venue Location</th>
                        <th>Customer Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                            <td><?= htmlspecialchars($booking['venue_name']) ?></td>
                            <td><?= htmlspecialchars($booking['venue_location']) ?></td>
                            <td><?= htmlspecialchars($booking['username']) ?></td>
                            <td><?= htmlspecialchars($booking['phone']) ?></td>
                            <td><?= htmlspecialchars($booking['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                    <input type="hidden" name="user_id" value="<?= $booking['user_id'] ?>">
                                    <input type="hidden" name="business_name" value="<?= htmlspecialchars($booking['business_name']) ?>">
                                    <input type="hidden" name="userName" value="<?= $booking['username'] ?>">
                                    <input type="hidden" name="date" value="<?= $booking['booking_date'] ?>">
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" required>
                                        <option value="pending" <?= ($booking['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= ($booking['status'] === 'confirmed') ? 'selected' : '' ?>>Confirm</option>
                                        <option value="cancelled" <?= ($booking['status'] === 'cancelled') ? 'selected' : '' ?>>Cancel</option>
                                    </select>

                                    <input type="submit" value="Update" name="statusHandler" class="btn btn-primary btn-sm">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No bookings found for this vendor.</p>
        <?php endif; ?>
    </div>

    <?php include("../../components/footer.php"); ?>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
</body>

</html>