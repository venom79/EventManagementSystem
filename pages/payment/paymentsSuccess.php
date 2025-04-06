<?php
require_once '../../database/databaseConnection.php'; 

$paymentId = $_GET['payment_id'] ?? null;

if (!$paymentId) {
    die("Invalid payment ID.");
}

// Step 1: Get payment details
$stmt = $conn->prepare("SELECT transaction_id, amount, user_id, booking_type, booking_id FROM payments WHERE id = ?");
$stmt->bind_param("i", $paymentId);
$stmt->execute();
$stmt->bind_result($transactionId, $amount, $userId, $bookingType, $bookingId);
$stmt->fetch();
$stmt->close();

// Step 2: Get username
$name = "User";
$userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userStmt->bind_result($username);
if ($userStmt->fetch()) {
    $name = $username;
}
$userStmt->close();

// Step 3: Get entity name (vendor or venue)
$entityName = "N/A";
if ($bookingType === 'vendor') {
    $query = $conn->prepare("SELECT v.business_name 
                             FROM vendor_bookings vb 
                             JOIN vendors v ON vb.vendor_id = v.id 
                             WHERE vb.id = ?");
} else {
    $query = $conn->prepare("SELECT v.name 
                             FROM venue_bookings vb 
                             JOIN venues v ON vb.venue_id = v.id 
                             WHERE vb.id = ?");
}
$query->bind_param("i", $bookingId);
$query->execute();
$query->bind_result($entity);
if ($query->fetch()) {
    $entityName = $entity;
}
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .success-box {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .checkmark {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: inline-block;
            border: 4px solid #28a745;
            position: relative;
            animation: pop 0.5s ease;
        }
        .checkmark::after {
            content: '';
            position: absolute;
            left: 28px;
            top: 14px;
            width: 25px;
            height: 50px;
            border: solid #28a745;
            border-width: 0 6px 6px 0;
            transform: rotate(45deg);
        }
        @keyframes pop {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="success-box">
    <div class="checkmark mb-4"></div>
    <h2 class="text-success">Payment Successful!</h2>

    <p class="mb-1"><strong>User Name:</strong> <?= htmlspecialchars($name) ?></p>
    <p class="mb-1"><strong>Transaction ID:</strong> <?= htmlspecialchars($transactionId) ?></p>
    <p class="mb-1"><strong>Amount:</strong> ₹<?= number_format($amount, 2) ?></p>
    <p class="mb-1"><strong>Booking Type:</strong> <?= ucfirst($bookingType) ?></p>
    <p class="mb-3"><strong>Paid For:</strong> <?= htmlspecialchars($entityName) ?></p>

    <a href="../myBooking.php" class="btn btn-primary mt-3">Go to MyBooking</a>
</div>

</body>
</html>
