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

// Fetch username from users table
$userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result()->fetch_assoc();
$userName = $userResult['username'] ?? 'User ' . $userId;
$userStmt->close();

// Fetch payment details
$paymentStmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? AND booking_id = ? AND booking_type = ? AND status = 'paid'");
$paymentStmt->bind_param("iis", $userId, $bookingId, $type);
$paymentStmt->execute();
$payment = $paymentStmt->get_result()->fetch_assoc();
$paymentStmt->close();

if (!$payment) {
    die("Payment not found.");
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .receipt-card {
            max-width: 450px;
            margin: auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .amount {
            font-size: 2.5rem;
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

        @media print {
            body * {
                visibility: hidden;
            }

            #receipt,
            #receipt * {
                visibility: visible;
            }

            #receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <?php include("../../components/header.php") ?>
    <div class="container mb-5">
        <div class="mb-4">
            <a href="javascript:history.back()" class="btn btn-outline-secondary">&larr; Back</a>
            <button onclick="downloadReceipt()" class="btn btn-outline-primary float-end">⬇️ Download as Image</button>
        </div>

        <div id="receipt" class="receipt-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Payment Receipt</h4>
                <span class="badge bg-success">PAID</span>
            </div>

            <div class="text-center mb-4">
                <div class="amount">₹<?= number_format($payment['amount'], 2) ?></div>
            </div>

            <div class="mb-3">
                <span class="label">From:</span><br>
                <span class="value"><?= htmlspecialchars($userName) ?></span>
            </div>

            <div class="mb-3">
                <span class="label">To:</span><br>
                <span class="value"><?= htmlspecialchars($entityName) ?></span>
            </div>

            <div class="mb-3">
                <span class="label">Transaction ID:</span><br>
                <span class="value"><?= $payment['transaction_id'] ?></span>
            </div>

            <div class="mb-3">
                <span class="label">Payment Date:</span><br>
                <span class="value"><?= date("d M Y, h:i A", strtotime($payment['payment_date'])) ?></span>
            </div>

            <div class="mb-3">
                <span class="label">Booking For:</span><br>
                <span class="value"><?= ucfirst($type) ?></span>
            </div>

            <div class="mb-3">
                <span class="label">Booking Date:</span><br>
                <span class="value"><?= date("d M Y", strtotime($bookingDate)) ?></span>
            </div>
        </div>
    </div>
    <?php include("../../components/footer.php") ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function downloadReceipt() {
            const receipt = document.getElementById("receipt");
            html2canvas(receipt).then(canvas => {
                const link = document.createElement('a');
                link.download = 'payment_receipt.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }
    </script>

</body>

</html>