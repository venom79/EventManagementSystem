<?php
session_start();
include "../../database/databaseConnection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$query = "SELECT * FROM events";
$result = $conn->query($query);
if($result){
    $events = [];
    while($row = $result->fetch_assoc()){
        $events[] = $row; 
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/styles/header.css">
    <link rel="stylesheet" href="../../public/styles/events.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <title>Events - EMS</title>
</head>

<body>
    <main class="main-box-home">
        <?php include("../../components/header.php") ?>
        <div class="container">
            <div class="EventHeader flex-r">
                <h1>Events</h1>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'organizer') { ?>
                    <a href="./addEvent.php" class="btn addEventBtn">add Event</a>
                <?php } ?>
            </div>
            <div class="eventTray flex-c">
                <?php foreach($events as $event):?>
                <div class="eventCard flex-r">
                    <h3><?php echo $event["name"] ?></h3>
                    <div class="eventDet flex-r">
                        <p><?php echo $event["date"] ?></p>
                        <p><?php echo $event["time"] ?></p>
                        <a href="<?= 'eventDetails.php?eventId='. $event['id']?>" class="btn">View Event</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>
    <?php include("../../components/footer.php") ?>
</body>

</html>