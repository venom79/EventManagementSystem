<?php
session_start();
include("../../database/databaseConnection.php");

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

$userId = $_SESSION['user_id'];
$bookingId = $_GET['booking_id'] ?? null;
$type = $_GET['type'] ?? null;

if (!$bookingId || !$type || !in_array($type, ['vendor', 'venue'])) {
    die("Invalid request.");
}

// Fetch the pending payment record
$paymentStmt = $conn->prepare("SELECT id, amount, status FROM payments WHERE user_id = ? AND booking_id = ? AND booking_type = ?");
$paymentStmt->bind_param("iis", $userId, $bookingId, $type);
$paymentStmt->execute();
$paymentResult = $paymentStmt->get_result()->fetch_assoc();
$paymentStmt->close();

if (!$paymentResult || $paymentResult['status'] === 'paid') {
    die("No pending payment found or already paid.");
}

$amount = $paymentResult['amount'];
$paymentId = $paymentResult['id'];

// Get booking details
$entityName = "";
$bookingDate = "";

if ($type === 'vendor') {
    $stmt = $conn->prepare("
        SELECT v.business_name AS name, vb.booking_date 
        FROM vendor_bookings vb 
        JOIN vendors v ON vb.vendor_id = v.id 
        WHERE vb.id = ?
    ");
} else {
    $stmt = $conn->prepare("
        SELECT v.name, vb.event_date AS booking_date 
        FROM venue_bookings vb 
        JOIN venues v ON vb.venue_id = v.id 
        WHERE vb.id = ?
    ");
}
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$entityName = $res['name'] ?? 'Unknown';
$bookingDate = $res['booking_date'] ?? '';
$stmt->close();

// Handle fake payment POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $transactionId = strtoupper("T" . $type . "-" . date("Ymd") . "-{$bookingId}");

    // Update payment record
    $update = $conn->prepare("
        UPDATE payments 
        SET status = 'paid', transaction_id = ?, payment_date = NOW() 
        WHERE user_id = ? AND booking_id = ? AND booking_type = ?
    ");
    $update->bind_param("siis", $transactionId, $userId, $bookingId, $type);
    $update->execute();
    $update->close();

    // Update booking payment status
    $bookingTable = $type === 'vendor' ? "vendor_bookings" : "venue_bookings";
    $updateBooking = $conn->prepare("UPDATE $bookingTable SET payment_status = 'paid' WHERE id = ?");
    $updateBooking->bind_param("i", $bookingId);
    $updateBooking->execute();
    $updateBooking->close();

    header("Location: paymentsSuccess.php?payment_id=" . $paymentId);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GPay Style Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .gpay-card {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .amount {
            font-size: 3rem;
            font-weight: bold;
            color: #2e7d32;
        }
        .label {
            font-weight: 500;
            color: #666;
        }
        .value {
            font-weight: 600;
        }
        .pay-btn {
            padding: 12px;
            font-size: 1.2rem;
            border-radius: 10px;
        }
        #paymentOverlay {
            position: fixed;
            inset: 0;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        #paymentOverlay.show {
            display: flex;
            opacity: 1;
            pointer-events: auto;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">

    <div class="gpay-card text-center">
        <div class="amount mb-3">₹<?= number_format($amount, 2) ?></div>
        <div class="text-muted mb-4">to <?= htmlspecialchars($entityName) ?></div>

        <div class="text-start mb-3">
            <div class="mb-2">
                <span class="label">Booking For:</span><br>
                <span class="value"><?= ucfirst($type) ?></span>
            </div>
            <div class="mb-2">
                <span class="label"><?= $type === 'vendor' ? 'Vendor' : 'Venue' ?> Name:</span><br>
                <span class="value"><?= htmlspecialchars($entityName) ?></span>
            </div>
            <div class="mb-2">
                <span class="label">Booking Date:</span><br>
                <span class="value"><?= date("d M Y", strtotime($bookingDate)) ?></span>
            </div>
        </div>

        <form method="POST" id="paymentForm">
            <input type="hidden" name="pay_now" value="1">
            <button type="submit" class="btn btn-success w-100 pay-btn">Pay Now</button>
        </form>
    </div>

    <!-- Loading Overlay -->
    <div id="paymentOverlay" class="d-flex justify-content-center align-items-center">
        <div class="text-center">
            <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
            <div class="fw-bold fs-5 text-dark">Processing Payment...</div>
        </div>
    </div>

    <script>
        const form = document.getElementById('paymentForm');
        const overlay = document.getElementById('paymentOverlay');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Show overlay with fade-in
            overlay.classList.add('show');

            // Submit after 3 seconds
            setTimeout(() => {
                form.submit();
            }, 3000);
        });
    </script>
</body>
</html>
