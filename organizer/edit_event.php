<?php
session_start();
include('config.php');

// Check if the user is logged in and is an Organizer
if (!isset($_SESSION["id"]) || $_SESSION["user_type"] != "organizer") {
    header("location: ../login.php");
    exit;
}

// Fetch the event details if an event ID is provided
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    
    // Fetch event data from the database
    $sql = "SELECT * FROM events WHERE id = ? AND user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $event_id, $_SESSION["id"]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $event = mysqli_fetch_assoc($result);
        } else {
            // Event not found or not owned by the organizer
            header("location: view_events.php");
            exit;
        }
    }
}

// Define variables for the event fields
$event_name = $event_date = $location = $latitude = $longitude = $description = "";
$capacity = NULL;
$event_image = "";
$event_added_msg = "";

// Handle form submission for event update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assign posted values to variables
    $event_name = trim($_POST["event_name"]);
    $event_date = trim($_POST["event_date"]);
    $location = trim($_POST["location"]);
    $description = trim($_POST["description"]);
    $latitude = !empty($_POST["latitude"]) ? trim($_POST["latitude"]) : NULL;
    $longitude = !empty($_POST["longitude"]) ? trim($_POST["longitude"]) : NULL;
    $capacity = isset($_POST['capacity']) ? $_POST['capacity'] : NULL;

    // Handle image upload
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $image_name = $_FILES['event_image']['name'];
        $image_tmp_name = $_FILES['event_image']['tmp_name'];
        $image_size = $_FILES['event_image']['size'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Define allowed file types and maximum file size (5MB)
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (in_array($image_ext, $allowed_extensions) && $image_size <= $max_size) {
            $new_image_name = uniqid() . "." . $image_ext;
            $upload_path = "uploads/" . $new_image_name;

            if (move_uploaded_file($image_tmp_name, $upload_path)) {
                $event_image = $upload_path;
            } else {
                $event_added_msg = "Error uploading the image. Please try again.";
            }
        } else {
            $event_added_msg = "Invalid image format or file too large. Please upload a valid image.";
        }
    } else {
        // If no new image is uploaded, retain the old image
        $event_image = $event['event_image'];
    }

    // Prepare the SQL update query
    $sql = "UPDATE events SET event_name = ?, event_date = ?, location = ?, latitude = ?, longitude = ?, description = ?, capacity = ?, event_image = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";

    // Prepare and execute the statement
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, "ssssssssii", $event_name, $event_date, $location, $latitude, $longitude, $description, $capacity, $event_image, $event_id, $_SESSION["id"]);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            $event_added_msg = "Event updated successfully!";
            // Update event array with the new details
            $event['event_name'] = $event_name;
            $event['event_date'] = $event_date;
            $event['location'] = $location;
            $event['latitude'] = $latitude;
            $event['longitude'] = $longitude;
            $event['description'] = $description;
            $event['capacity'] = $capacity;
            $event['event_image'] = $event_image;
        } else {
            $event_added_msg = "Error updating event. Please try again.";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    }

    // Close the connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 600px;">
    <div class="card-body">
        <h2>Edit Event</h2>

        <!-- Display success or error message -->
        <?php if (!empty($event_added_msg)) { echo '<div class="alert alert-info">' . $event_added_msg . '</div>'; } ?>

        <!-- Event editing form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?event_id=' . $event_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="event_name">Event Name</label>
                <input type="text" name="event_name" id="event_name" class="form-control" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="event_date">Event Date</label>
                <input type="datetime-local" name="event_date" id="event_date" class="form-control" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="location">Event Location</label>
                <input type="text" name="location" id="location" class="form-control" value="<?php echo htmlspecialchars($event['location']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Event Description</label>
                <textarea name="description" id="description" class="form-control" required><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="latitude">Latitude</label>
                <input type="text" name="latitude" id="latitude" class="form-control" value="<?php echo htmlspecialchars($event['latitude']); ?>">
            </div>

            <div class="form-group">
                <label for="longitude">Longitude</label>
                <input type="text" name="longitude" id="longitude" class="form-control" value="<?php echo htmlspecialchars($event['longitude']); ?>">
            </div>

            <div class="form-group">
                <label for="capacity">Event Capacity</label>
                <input type="number" name="capacity" id="capacity" class="form-control" value="<?php echo htmlspecialchars($event['capacity']); ?>" required>
            </div>

            <!-- Image Upload Field -->
            <div class="form-group">
                <label for="event_image">Event Image</label>
                <input type="file" name="event_image" id="event_image" class="form-control-file">
                <?php if (!empty($event['event_image'])): ?>
                    <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="Event Image" width="100" height="100">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <input type="submit" value="Update Event" class="btn btn-primary">
                <a class="btn btn-dark" href="view_events.php">View Events</a>
            </div>
        </form>
    </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
