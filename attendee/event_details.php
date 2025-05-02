<?php
session_start();
include('config.php');

// Check if the user is logged in and is an Attendee
if (!isset($_SESSION["id"]) || $_SESSION["user_type"] != "attendee") {
    header("location: login.php");
    exit;
}

// Check if event_id is provided in the URL
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Event not found.</div></div>";
    exit;
}

$event_id = $_GET['event_id'];

// Fetch event details
$sql = "SELECT * FROM events WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    $event_result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($event_result);
} else {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Error fetching event details.</div></div>";
    exit;
}

// Fetch RSVP status of the current attendee for this event
$rsvp_status = null;
$sql_rsvp = "SELECT status FROM rsvps WHERE event_id = ? AND user_id = ?";
if ($stmt_rsvp = mysqli_prepare($conn, $sql_rsvp)) {
    mysqli_stmt_bind_param($stmt_rsvp, "ii", $event_id, $_SESSION["id"]);
    mysqli_stmt_execute($stmt_rsvp);
    $rsvp_result = mysqli_stmt_get_result($stmt_rsvp);
    $rsvp_status = mysqli_fetch_assoc($rsvp_result)['status'] ?? null;
}

// Get the event date and compare it with the current date
$current_date = date('Y-m-d');
$event_date = $event['event_date']; // Assuming 'event_date' is stored as 'YYYY-MM-DD'

$event_date_passed = (strtotime($event_date) < strtotime($current_date));

// Handle RSVP submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rsvp_status']) && !$event_date_passed) {
    $status = $_POST['rsvp_status'];

    // Check if the user has already RSVP'd for this event
    if ($rsvp_status !== null) {
        // Update existing RSVP
        $update_sql = "UPDATE rsvps SET status = ? WHERE event_id = ? AND user_id = ?";
        if ($stmt_update = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($stmt_update, "sii", $status, $event_id, $_SESSION["id"]);
            mysqli_stmt_execute($stmt_update);
        }
    } else {
        // Insert new RSVP
        $insert_sql = "INSERT INTO rsvps (event_id, user_id, status, created_at) VALUES (?, ?, ?, NOW())";
        if ($stmt_insert = mysqli_prepare($conn, $insert_sql)) {
            mysqli_stmt_bind_param($stmt_insert, "iis", $event_id, $_SESSION["id"], $status);
            mysqli_stmt_execute($stmt_insert);
        }
    }

    // Refresh the RSVP status after submission
    header("Location: event_details.php?event_id=" . $event_id);
    exit;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .event-details-container {
            background-color: #f8f9fa;
            padding: 30px;
            margin-top: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .event-title {
            color: #343a40;
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 15px;
        }

        .event-info p {
            margin-bottom: 10px;
            color: #555;
        }

        .event-info strong {
            font-weight: bold;
            color: #343a40;
        }

        .event-image-container {
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .event-image {
            width: 100%;
            display: block;
        }

        .rsvp-section {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .rsvp-title {
            color: #343a40;
            margin-bottom: 15px;
        }

        .rsvp-status-display {
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .rsvp-status-display strong {
            font-weight: bold;
        }

        .rsvp-form label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }

        .rsvp-form select {
            margin-bottom: 15px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        #map {
            height: 400px;
            margin-top: 20px;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBP4cSBJ4IHPp15oyTcJgWo7kDt06Vh4jE&callback=initMap" async defer></script>
    <script>
        function initMap() {
            var eventLocation = { lat: <?php echo $event['latitude']; ?>, lng: <?php echo $event['longitude']; ?> };
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: eventLocation
            });
            var marker = new google.maps.Marker({
                position: eventLocation,
                map: map,
                title: "<?php echo htmlspecialchars($event['event_name']); ?>"
            });
        }
    </script>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <?php if ($event): ?>
        <div class="event-details-container">
            <h2 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h2>
            <div class="row">
                <div class="col-md-8 event-info">
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    <div class="event-image-container">
                        <?php if (!empty($event['event_image'])): ?>
                            <img src="../organizer/<?php echo htmlspecialchars($event['event_image']); ?>" class="event-image img-fluid" alt="Event Image">
                        <?php else: ?>
                            <img src="default-image.jpg" class="event-image img-fluid" alt="Event Image">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4 rsvp-section">
                    <h4 class="rsvp-title">Your RSVP</h4>
                    <div class="rsvp-status-display">
                        <?php if ($rsvp_status): ?>
                            <strong>Status:</strong> <span class="badge badge-info"><?php echo ucfirst(htmlspecialchars($rsvp_status)); ?></span>
                        <?php else: ?>
                            <strong>Status:</strong> <span class="badge badge-secondary">Not Yet Responded</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($event_date_passed): ?>
                        <div class="alert alert-danger">Sorry, RSVP is closed for this event.</div>
                    <?php else: ?>
                        <h4 class="rsvp-title">RSVP for this event</h4>
                        <form method="POST" class="rsvp-form">
                            <div class="form-group">
                                <label for="rsvp_status">Choose your status:</label>
                                <select name="rsvp_status" id="rsvp_status" class="form-control" required>
                                    <option value="attend" <?php echo ($rsvp_status === 'attend') ? 'selected' : ''; ?>>Attend</option>
                                    <option value="maybe" <?php echo ($rsvp_status === 'maybe') ? 'selected' : ''; ?>>Maybe</option>
                                    <option value="decline" <?php echo ($rsvp_status === 'decline') ? 'selected' : ''; ?>>Decline</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Submit RSVP</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Google Map Container -->
        <div id="map"></div>
    <?php else: ?>
        <div class="container mt-5">
            <div class="alert alert-danger">Event not found.</div>
        </div>
    <?php endif; ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
