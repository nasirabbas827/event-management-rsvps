<?php
session_start();
include('config.php');

// Check if the user is logged in and is an Attendee
if (!isset($_SESSION["id"]) || $_SESSION["user_type"] != "attendee") {
    header("location: login.php");
    exit;
}

// Get today's date
$current_date = date('Y-m-d H:i:s');

// Fetch upcoming events (events where the event date is greater than the current date)
$sql = "SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $current_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $error_msg = "Error fetching events. Please try again.";
}

// Handle search functionality
$search_term = "";
if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
    $search_sql = "SELECT * FROM events WHERE (event_name LIKE ? OR location LIKE ?) AND event_date >= ? ORDER BY event_date ASC";
    if ($search_stmt = mysqli_prepare($conn, $search_sql)) {
        $search_param = "%" . $search_term . "%";
        mysqli_stmt_bind_param($search_stmt, "sss", $search_param, $search_param, $current_date);
        mysqli_stmt_execute($search_stmt);
        $result = mysqli_stmt_get_result($search_stmt);
    } else {
        $error_msg = "Error searching events. Please try again.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendee Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Enhanced Search Bar Styling */
        .search-form {
            display: flex;
            margin-bottom: 30px;
        }

        .search-input {
            flex-grow: 1;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 5px 0 0 5px;
            font-size: 1rem;
        }

        .search-button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 1rem;
        }

        .search-button:hover {
            background-color: #0056b3;
        }

        /* Equal Height Cards and Images */
        .card-container {
            display: flex;
            flex-direction: column;
            height: 100%; /* Make the container take full height of the grid cell */
        }

        .card-img-top {
            object-fit: cover; /* Ensure the image covers the area without distortion */
            height: 200px; /* Set a fixed height for all images */
        }

        .card-body {
            flex-grow: 1; /* Allow the body to grow and push content to the bottom */
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Distribute space between elements */
        }

        .card-text {
            flex-grow: 1; /* Allow description to take available space */
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Welcome to the Attendee Dashboard, <?php echo htmlspecialchars($_SESSION["email"]); ?>!</h2>

    <form method="get" class="search-form">
        <input type="text" name="search" class="search-input" placeholder="Search events by name or location" value="<?php echo htmlspecialchars($search_term); ?>" required>
        <button type="submit" class="search-button">Search</button>
    </form>

    <?php
    if (!empty($error_msg)) {
        echo '<div class="alert alert-danger">' . $error_msg . '</div>';
    }
    ?>

    <h3>Upcoming Events</h3>
    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-container">
                        <?php if (!empty($row['event_image'])): ?>
                            <img src="../organizer/<?php echo htmlspecialchars($row['event_image']); ?>" class="card-img-top" alt="Event Image">
                        <?php else: ?>
                            <img src="default-image.jpg" class="card-img-top" alt="Event Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['event_name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($row['event_date']); ?> - <?php echo htmlspecialchars($row['location']); ?></small></p>
                            <a href="event_details.php?event_id=<?php echo $row['id']; ?>" class="btn btn-info">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No upcoming events found.</p>
        <?php endif; ?>
    </div>
</div>
<!-- Footer -->
<footer class="mt-5 py-3 bg-light">
    <div class="container text-center">
        <p>&copy; 2025 EventHub. All rights reserved.</p>
    </div>
</footer>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>