<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["user_type"] != "attendee") {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$rsvp_query = "SELECT e.id, e.event_name, e.event_date, e.location, e.event_image, r.status
               FROM rsvps r
               JOIN events e ON r.event_id = e.id
               WHERE r.user_id = ?";

$stmt = mysqli_prepare($conn, $rsvp_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$rsvp_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your RSVP'd Events</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Your RSVP'd Events</h2>

    <?php if (mysqli_num_rows($rsvp_result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Image</th>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>RSVP Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($rsvp = mysqli_fetch_assoc($rsvp_result)): ?>
                        <tr>
                            <td style="width: 120px;">
                                <?php if (!empty($rsvp['event_image'])): ?>
                                    <img src="../organizer/<?php echo htmlspecialchars($rsvp['event_image']); ?>" class="img-fluid" style="max-height: 80px;" alt="Event Image">
                                <?php else: ?>
                                    <img src="default-image.jpg" class="img-fluid" style="max-height: 80px;" alt="Default Image">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($rsvp['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($rsvp['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($rsvp['location']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($rsvp['status'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>You have not RSVP'd to any events yet.</p>
    <?php endif; ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php mysqli_close($conn); ?>
