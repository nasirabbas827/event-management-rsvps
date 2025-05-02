<?php
session_start();
include('config.php');

// Check if the user is logged in and is an Organizer
if (!isset($_SESSION["id"]) || $_SESSION["user_type"] != "organizer") {
    header("location: ../login.php");
    exit;
}

// Define variables for the event fields
$event_name = $event_date = $location = $latitude = $longitude = $description = "";
$capacity = NULL;
$event_added_msg = "";

// Handle form submission
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
    $event_image = "";
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
    }

    // Prepare the SQL insert query
    $sql = "INSERT INTO events (user_id, event_name, event_date, location, latitude, longitude, description, capacity, event_image, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    // Prepare and execute the statement
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, "sssssssss", $_SESSION["id"], $event_name, $event_date, $location, $latitude, $longitude, $description, $capacity, $event_image);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            $event_added_msg = "Event added successfully!";
        } else {
            $event_added_msg = "Error adding event. Please try again.";
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
    <title>Organizer Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 600px;">
    <div class="card-body">
        <h2>Welcome to the Organizer Dashboard, <?php echo htmlspecialchars($_SESSION["email"]); ?>!</h2>

        <!-- Display success or error message -->
        <?php if (!empty($event_added_msg)) { echo '<div class="alert alert-info">' . $event_added_msg . '</div>'; } ?>

        <!-- Event creation form -->
        <h3>Add New Event</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">

            <div class="form-group">
                <label for="event_name">Event Name</label>
                <input type="text" name="event_name" id="event_name" class="form-control" value="<?php echo $event_name; ?>" required>
            </div>

            <div class="form-group">
                <label for="event_date">Event Date</label>
                <input type="datetime-local" name="event_date" id="event_date" class="form-control" value="<?php echo $event_date; ?>" min="<?= date('Y-m-d\TH:i'); ?>" required>
            </div>

            <div class="form-group">
                <label for="location">Event Location</label>
                <input type="text" name="location" id="location" class="form-control" value="<?php echo $location; ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Event Description</label>
                <textarea name="description" id="description" class="form-control" required><?php echo $description; ?></textarea>
            </div>

            <div class="form-group">
                <label for="latitude">Latitude</label>
                <input type="text" name="latitude" id="latitude" class="form-control" value="<?php echo $latitude; ?>">
            </div>

            <div class="form-group">
                <label for="longitude">Longitude</label>
                <input type="text" name="longitude" id="longitude" class="form-control" value="<?php echo $longitude; ?>">
            </div>

            <div class="form-group">
                <label for="capacity">Event Capacity</label>
                <input type="number" name="capacity" id="capacity" class="form-control" value="<?php echo isset($capacity) ? $capacity : ''; ?>" required>
            </div>

            <!-- Image Upload Field -->
            <div class="form-group">
                <label for="event_image">Event Image</label>
                <input type="file" name="event_image" id="event_image" class="form-control-file">
            </div>

            <div class="form-group">
                <input type="submit" value="Add Event" class="btn btn-primary">
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
