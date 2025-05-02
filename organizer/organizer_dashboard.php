<?php 
session_start();
include('config.php');

// Check if the user is logged in and is an Organizer
if (!isset($_SESSION["id"]) || $_SESSION["user_type"] != "organizer") {
    header("location: ../login.php");
    exit;
}

// Fetch all events created by the logged-in user (organizer)
$sql = "SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $error_msg = "Error fetching events. Please try again.";
}

// Fetch RSVPs for each event
$rsvp_data = [];
while ($event = mysqli_fetch_assoc($result)) {
    $event_id = $event['id'];

    // Get counts for each RSVP status
    $sql_rsvp_count = "SELECT status, COUNT(*) as count FROM rsvps WHERE event_id = ? GROUP BY status";
    if ($stmt_rsvp_count = mysqli_prepare($conn, $sql_rsvp_count)) {
        mysqli_stmt_bind_param($stmt_rsvp_count, "i", $event_id);
        mysqli_stmt_execute($stmt_rsvp_count);
        $rsvp_result = mysqli_stmt_get_result($stmt_rsvp_count);

        $rsvp_data[$event_id] = [
            'attend' => 0,
            'maybe' => 0,
            'decline' => 0,
            'total' => 0
        ];

        while ($rsvp = mysqli_fetch_assoc($rsvp_result)) {
            $rsvp_data[$event_id][$rsvp['status']] = $rsvp['count'];
            $rsvp_data[$event_id]['total'] += $rsvp['count'];
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub - Organizer Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --text-color: #5a5c69;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
            color: var(--text-color);
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .event-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .rsvp-stats {
            background: var(--secondary-color);
            padding: 1rem;
            border-radius: 8px;
        }
        
        .stat-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 500;
        }
        
        .badge-attend {
            background-color: rgba(28, 200, 138, 0.2);
            color: var(--success-color);
        }
        
        .badge-maybe {
            background-color: rgba(246, 194, 62, 0.2);
            color: var(--warning-color);
        }
        
        .badge-decline {
            background-color: rgba(231, 74, 59, 0.2);
            color: var(--danger-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .welcome-text {
            font-weight: 300;
            margin-bottom: 0.5rem;
        }
        
        .user-email {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        
        .progress-bar-attend {
            background-color: var(--success-color);
        }
        
        .progress-bar-maybe {
            background-color: var(--warning-color);
        }
        
        .progress-bar-decline {
            background-color: var(--danger-color);
        }
        
        .event-date {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-text">Welcome back, Organizer!</h1>
                <p class="lead user-email"><?php echo htmlspecialchars($_SESSION["email"]); ?></p>
            </div>
            <div class="col-md-4 text-right">
                <a href="add_event.php" class="btn btn-light btn-lg">
                    <i class="fas fa-plus"></i> Create New Event
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="font-weight-bold">Your Events</h3>
                <span class="badge badge-primary badge-pill">
                    <?php echo isset($result) ? mysqli_num_rows($result) : 0; ?> events
                </span>
            </div>
            
            <?php if (isset($result) && mysqli_num_rows($result) > 0): ?>
                <div class="row">
                    <?php 
                    // Reset the result pointer to fetch the events again
                    mysqli_data_seek($result, 0);
                    while ($event = mysqli_fetch_assoc($result)): 
                        $total_rsvps = $rsvp_data[$event['id']]['total'];
                        $attend_percentage = $total_rsvps > 0 ? ($rsvp_data[$event['id']]['attend'] / $total_rsvps) * 100 : 0;
                        $maybe_percentage = $total_rsvps > 0 ? ($rsvp_data[$event['id']]['maybe'] / $total_rsvps) * 100 : 0;
                        $decline_percentage = $total_rsvps > 0 ? ($rsvp_data[$event['id']]['decline'] / $total_rsvps) * 100 : 0;
                    ?>
                    <div class="col-lg-6">
                        <div class="card event-card h-100">
                            <div class="card-body position-relative">
                                <span class="event-date">
                                    <?php echo date('M j', strtotime($event['event_date'])); ?>
                                </span>
                                <h4 class="card-title font-weight-bold"><?php echo htmlspecialchars($event['event_name']); ?></h4>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                
                                <div class="rsvp-stats mt-4">
                                    <h6 class="font-weight-bold mb-3">RSVP Analytics</h6>
                                    
                                    <div class="progress mb-3">
                                        <div class="progress-bar progress-bar-attend" role="progressbar" style="width: <?php echo $attend_percentage; ?>%" 
                                            aria-valuenow="<?php echo $attend_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        <div class="progress-bar progress-bar-maybe" role="progressbar" style="width: <?php echo $maybe_percentage; ?>%" 
                                            aria-valuenow="<?php echo $maybe_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        <div class="progress-bar progress-bar-decline" role="progressbar" style="width: <?php echo $decline_percentage; ?>%" 
                                            aria-valuenow="<?php echo $decline_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <span class="stat-badge badge-attend">
                                            <i class="fas fa-check-circle"></i> <?php echo $rsvp_data[$event['id']]['attend']; ?> Attending
                                        </span>
                                        <span class="stat-badge badge-maybe">
                                            <i class="fas fa-question-circle"></i> <?php echo $rsvp_data[$event['id']]['maybe']; ?> Maybe
                                        </span>
                                        <span class="stat-badge badge-decline">
                                            <i class="fas fa-times-circle"></i> <?php echo $rsvp_data[$event['id']]['decline']; ?> Declined
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mt-4 d-flex justify-content-between">
                                    <a href="view_rsvps.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-users"></i> View RSVPs
                                    </a>
                                    <a href="edit_event.php?event_id=<?php echo $event['id']; ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="empty-state">
                        <i class="fas fa-calendar-plus"></i>
                        <h4>No Events Found</h4>
                        <p class="mb-4">You haven't created any events yet. Get started by creating your first event!</p>
                        <a href="create_event.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Create Your First Event
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>