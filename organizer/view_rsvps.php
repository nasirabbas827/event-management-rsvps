<?php
session_start();
include('config.php');

// Check if the user is logged in and is an Organizer
if (!isset($_SESSION["id"]) || $_SESSION["user_type"] != "organizer") {
    header("location: ../login.php");
    exit;
}

// Check if event_id is provided
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Fetch event details
    $sql_event = "SELECT * FROM events WHERE id = ? AND user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql_event)) {
        mysqli_stmt_bind_param($stmt, "ii", $event_id, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);
        $event_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($event_result) == 0) {
            // Event not found or event does not belong to this organizer
            echo "<p>Event not found or you do not have access to view RSVPs for this event.</p>";
            exit;
        }

        $event = mysqli_fetch_assoc($event_result);
    } else {
        echo "<p>Error fetching event details. Please try again.</p>";
        exit;
    }

    // Fetch RSVPs for the event
    $sql_rsvps = "SELECT rsvp_id, status, u.username, u.email, u.phone 
                  FROM rsvps r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.event_id = ?";
    if ($stmt_rsvps = mysqli_prepare($conn, $sql_rsvps)) {
        mysqli_stmt_bind_param($stmt_rsvps, "i", $event_id);
        mysqli_stmt_execute($stmt_rsvps);
        $rsvp_result = mysqli_stmt_get_result($stmt_rsvps);
    } else {
        echo "<p>Error fetching RSVPs. Please try again.</p>";
        exit;
    }

} else {
    echo "<p>No event ID provided.</p>";
    exit;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View RSVPs</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container mt-5 mb-5">
    <h2>RSVPs for Event: <?php echo htmlspecialchars($event['event_name']); ?></h2>
    <p><strong>Event Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>

    <!-- RSVP List -->
    <div class="mt-4">
        <h4>RSVP Details</h4>
        <?php if (mysqli_num_rows($rsvp_result) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($rsvp = mysqli_fetch_assoc($rsvp_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rsvp['username']); ?></td>
                            <td><?php echo htmlspecialchars($rsvp['email']); ?></td>
                            <td><?php echo htmlspecialchars($rsvp['phone']); ?></td>
                            <td><?php echo ucfirst($rsvp['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No RSVPs for this event.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
