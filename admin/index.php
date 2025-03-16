<?php
session_start();
include("../database/databaseConnection.php");

// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: auth/login.php");
    exit();
}

// Fetch venues by status
$query_pending = "SELECT id, name FROM venues WHERE status = 'pending'";
$query_approved = "SELECT id, name FROM venues WHERE status = 'approved'";
$query_rejected = "SELECT id, name FROM venues WHERE status = 'rejected'";

$result_pending = $conn->query($query_pending);
$result_approved = $conn->query($query_approved);
$result_rejected = $conn->query($query_rejected);

// Update venue status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['venue_id'], $_POST['action'])) {
    $venue_id = intval($_POST['venue_id']);
    
    if ($_POST['action'] == 'approve') {
        $status = 'approved';
    } elseif ($_POST['action'] == 'reject') {
        $status = 'rejected';
    } elseif ($_POST['action'] == 'pending') {
        $status = 'pending';
    }
    
    $update_query = "UPDATE venues SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if ($stmt) {
        $stmt->bind_param("si", $status, $venue_id);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Admin Dashboard</h1>
            <a href="./auth/logout.php" class="btn btn-danger">Logout</a>
        </div>
        
        <div class="row">
            <?php 
            $sections = ['Pending' => $result_pending, 'Approved' => $result_approved, 'Rejected' => $result_rejected];
            $actions = ['Pending' => ['approve' => 'success', 'reject' => 'danger'], 'Approved' => ['reject' => 'warning'], 'Rejected' => ['approve' => 'success']];
            
            foreach ($sections as $status => $result) { ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h3 class="mb-0 text-center"><?php echo $status; ?> Venues</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Venue Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($venue = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($venue['name']); ?></td>
                                            <td>
                                                <a class="btn btn-primary btn-sm" href="../pages/venue/venueDetails.php?venueId=<?php echo $venue['id']; ?>">Check Venue</a>
                                                <?php foreach ($actions[$status] as $action => $color) { ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                                                        <button class="btn btn-<?php echo $color; ?> btn-sm" type="submit" name="action" value="<?php echo $action; ?>">
                                                            <?php echo ucfirst($action); ?>
                                                        </button>
                                                    </form>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
