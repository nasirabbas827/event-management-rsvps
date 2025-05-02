<?php
session_start();
include('config.php');

// Fetch all events initially for card display
$sql = "SELECT * FROM events";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>EventHub</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS & Custom CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <!-- Google Maps API (deferred until initMap is defined) -->
    <script>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 20, lng: 0},
                zoom: 2
            });

            <?php
            $location_query = mysqli_query($conn, "SELECT * FROM events WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
            while ($event = mysqli_fetch_assoc($location_query)) {
                $lat = $event['latitude'];
                $lng = $event['longitude'];
                $name = addslashes($event['event_name']);
                $location = addslashes($event['location']);
                echo "
                    var marker = new google.maps.Marker({
                        position: {lat: $lat, lng: $lng},
                        map: map,
                        title: '$name'
                    });
                    var infoWindow = new google.maps.InfoWindow({
                        content: '<b>$name</b><br>$location'
                    });
                    marker.addListener('click', function() {
                        infoWindow.open(map, marker);
                    });
                ";
            }
            ?>
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBP4cSBJ4IHPp15oyTcJgWo7kDt06Vh4jE&callback=initMap" async defer></script>

    <style>
        .jumbotron {
            height: 500px;
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('./images/hotel.jpg');
            background-size: cover;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .jumbotron h1 { font-size: 3rem; }
        .jumbotron p { font-size: 1.5rem; }
        .card-img-top { height: 200px; object-fit: cover; }
        #map { height: 500px; width: 100%; margin-bottom: 30px; }
        .search-bar { margin-bottom: 30px; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="jumbotron text-center">
    <h1>Welcome to EventHub</h1>
    <p>Discover Your Perfect Event and List Your Event</p>
    <a href="login.php" class="btn btn-primary btn-lg">Login to Explore</a>
</div>


<!-- Search Bar -->
<div class="container search-bar">
    <h2>Search For Events</h2>
    <input type="text" id="searchInput" class="form-control" placeholder="Search for events..." onkeyup="searchEvents()" />
</div>

<!-- Events Display -->
<div class="container">
    <h2>Listed Events</h2>
    <div class="row" id="eventsContainer">
        <?php while ($event = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img class="card-img-top" src="organizer/<?php echo htmlspecialchars($event['event_image']); ?>" alt="Event Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($event['event_name']); ?></h5>
                        <p class="card-text"><?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...</p>
                        <a href="login.php" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Google Map Section -->
<div class="container">
    <h3 class="mt-5 mb-3">Event Locations on Map</h3>
    <div id="map"></div>
</div>

<!-- Footer -->
<footer class="mt-5 py-3 bg-light">
    <div class="container text-center">
        <p>&copy; 2025 EventHub. All rights reserved.</p>
    </div>
</footer>

<!-- Scripts -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function searchEvents() {
    var input = document.getElementById("searchInput").value.toLowerCase();
    var cards = document.getElementById("eventsContainer").getElementsByClassName("col-md-4");

    for (var i = 0; i < cards.length; i++) {
        var title = cards[i].getElementsByClassName("card-title")[0].innerText.toLowerCase();
        cards[i].style.display = title.includes(input) ? "" : "none";
    }
}
</script>

</body>
</html>
