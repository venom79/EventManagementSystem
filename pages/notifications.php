<?php
session_start();
include("../database/databaseConnection.php");

$userId = $_SESSION['user_id'];

// Handle marking as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $notifId = $_POST['notif_id'];
    $updateQuery = "UPDATE notifications SET status = 'read' WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($updateQuery);
    $update_stmt->bind_param("ii", $notifId, $userId);
    $update_stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF'] . "?user_id=" . $userId);
    exit();
}

// Fetch notifications for both venue and vendor bookings
$notifQuery = "
    SELECT 
        n.id, 
        n.message, 
        n.status, 
        n.created_at, 
        vb.venue_id, 
        v.name AS venue_name, 
        vdb.vendor_id, 
        vd.business_name AS vendor_name
    FROM notifications AS n
    LEFT JOIN venue_bookings AS vb ON vb.id = n.booking_id
    LEFT JOIN venues AS v ON v.id = vb.venue_id
    LEFT JOIN vendor_bookings AS vdb ON vdb.id = n.booking_id
    LEFT JOIN vendors AS vd ON vd.id = vdb.vendor_id
    WHERE n.user_id = ? 
    ORDER BY n.created_at DESC;
";

$notif_stmt = $conn->prepare($notifQuery);
if (!$notif_stmt) {
    die("Query preparation failed: " . $conn->error);
}
$notif_stmt->bind_param("i", $userId);
$notif_stmt->execute();
$result = $notif_stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/styles/style.css">
    <title>Notifications</title>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .notification-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 15px;
            transition: 0.3s;
        }

        .notification-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            font-size: 0.85rem;
            font-weight: bold;
        }

        .date-time {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #343a40;
        }
    </style>
</head>

<body>
    <?php include("../components/header.php") ?>

    <div class="container my-5">
        <h2 class="mb-4 text-primary">Your Notifications</h2>

        <h3 class="section-title">📌 New</h3>
        <?php
        $hasUnread = false;
        foreach ($notifications as $notification) {
            if ($notification['status'] === 'unread') {
                $hasUnread = true;
                $source = $notification['venue_name'] ? $notification['venue_name'] : $notification['vendor_name'];
        ?>
                <div class="notification-card border-start border-3 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-1"><?= htmlspecialchars($source) ?></h5>
                        <span class="badge bg-warning status-badge">Unread</span>
                    </div>
                    <p class="mb-1"><?= nl2br(htmlspecialchars($notification['message'])) ?></p>
                    <div class="text-end date-time">
                        <?= date("F j, Y, g:i A", strtotime($notification['created_at'])) ?>
                    </div>
                    <form method="POST" class="text-end mt-2">
                        <input type="hidden" name="notif_id" value="<?= $notification['id'] ?>">
                        <button type="submit" name="mark_as_read" class="btn btn-sm btn-primary">Mark as Read</button>
                    </form>
                </div>
        <?php
            }
        }
        if (!$hasUnread) {
            echo '<div class="alert alert-info text-center" role="alert">No new notifications.</div>';
        }
        ?>

        <h3 class="section-title">📁 Old</h3>
        <?php
        $hasRead = false;
        foreach ($notifications as $notification) {
            if ($notification['status'] === 'read') {
                $hasRead = true;
                $source = $notification['venue_name'] ? $notification['venue_name'] : $notification['vendor_name'];
        ?>
                <div class="notification-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-1"><?= htmlspecialchars($source) ?></h5>
                        <span class="badge bg-success status-badge">Read</span>
                    </div>
                    <p class="mb-1"><?= nl2br(htmlspecialchars($notification['message'])) ?></p>
                    <div class="text-end date-time">
                        <?= date("F j, Y, g:i A", strtotime($notification['created_at'])) ?>
                    </div>
                </div>
        <?php
            }
        }
        if (!$hasRead) {
            echo '<div class="alert alert-secondary text-center" role="alert">No old notifications.</div>';
        }
        ?>
    </div>

    <?php include("../components/footer.php") ?>
</body>

</html>
