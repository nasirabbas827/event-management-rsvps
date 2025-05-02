<?php
session_start();
include('config.php');

// Check if the user is logged in and is an Organizer
if (!isset($_SESSION["id"]) || $_SESSION["user_type"] != "organizer") {
    header("location: ../login.php");
    exit;
}

// Fetch all events for the logged-in user
$sql = "SELECT * FROM events WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $error_msg = "Error fetching events. Please try again.";
}

// Handle event deletion
if (isset($_GET['delete_id'])) {
    $event_id = $_GET['delete_id'];
    
    $delete_sql = "DELETE FROM events WHERE id = ?";
    if ($delete_stmt = mysqli_prepare($conn, $delete_sql)) {
        mysqli_stmt_bind_param($delete_stmt, "i", $event_id);
        if (mysqli_stmt_execute($delete_stmt)) {
            $success_msg = "Event deleted successfully!";
        } else {
            $error_msg = "Error deleting event. Please try again.";
        }
        mysqli_stmt_close($delete_stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Events</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Your Events</h2>

    <!-- Display success or error message -->
    <?php
    if (!empty($success_msg)) {
        echo '<div class="alert alert-success">' . $success_msg . '</div>';
    }
    if (!empty($error_msg)) {
        echo '<div class="alert alert-danger">' . $error_msg . '</div>';
    }
    ?>

    <!-- Events Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Event ID</th>
                <th>Event Name</th>
                <th>Event Date</th>
                <th>Location</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td>
                        <?php if (!empty($row['event_image'])): ?>
                            <img src="<?php echo $row['event_image']; ?>" alt="Event Image" width="100" height="100">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Edit and Delete buttons -->
                        <a href="edit_event.php?event_id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
