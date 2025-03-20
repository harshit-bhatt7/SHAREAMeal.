<?php
session_start();
include '../connection.php';

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("location: ../signin.php");
    exit();
}

// Get user details
$email = mysqli_real_escape_string($connection, $_SESSION['email']);
$user_query = "SELECT id FROM login WHERE email = '$email'";
$user_result = mysqli_query($connection, $user_query);
$user = mysqli_fetch_assoc($user_result);
$user_id = $user['id'];

// Get the most recent order
$recent_order_query = "SELECT fd.*, l.name as requester_name, l.email as requester_email 
                      FROM food_donations fd 
                      JOIN login l ON fd.assigned_to = l.id 
                      WHERE fd.assigned_to = '$user_id' 
                      ORDER BY fd.date DESC LIMIT 1";
$recent_order_result = mysqli_query($connection, $recent_order_query);
$recent_order = mysqli_fetch_assoc($recent_order_result);

// Get order history
$history_query = "SELECT fd.*, 
                 DATE_FORMAT(fd.date, '%M %d, %Y') as formatted_date
                 FROM food_donations fd 
                 WHERE fd.assigned_to = '$user_id' 
                 ORDER BY fd.date DESC";
$history_result = mysqli_query($connection, $history_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Details - Share A Meal</title>
    <link rel="stylesheet" href="../loginstyle.css">
    <link rel="stylesheet" href="../home.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
    integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI="
    crossorigin=""/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js"></script>
    <style>
        .container-details {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #06C167;
            border-bottom: 2px solid #06C167;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .order-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .detail-group {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        
        .detail-value {
            color: #333;
        }
        
        .map-container {
            width: 100%;
            height: 400px;
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .history-table th, .history-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .history-table th {
            background-color: #f2f2f2;
            color: #333;
        }
        
        .history-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
        }
        
        .status-completed {
            background-color: #D4EDDA;
            color: #155724;
        }
        
        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #06C167;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        @media (max-width: 768px) {
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .map-container {
                height: 300px;
            }
            
            .history-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body style="background-color: #f5f5f5;">
    <header>
        <div class="logo">Share <b style="color: #06C167;">A Meal</b></div>
        <div class="hamburger">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>
        <nav class="nav-bar">
            <ul>
                <li><a href="Meals.php">Available Meals</a></li>
                <li><a href="delivery-details.php" class="active">My Orders</a></li>
                <li><a href="../profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <script>
        hamburger = document.querySelector(".hamburger");
        hamburger.onclick = function() {
            navBar = document.querySelector(".nav-bar");
            navBar.classList.toggle("active");
        }
    </script>
    
    <div class="container-details">
        <h1 class="section-title">Delivery Details</h1>
        
        <?php if($recent_order): ?>
        <div class="order-card">
            <h2>Latest Order</h2>
            <div class="order-details">
                <div>
                    <div class="detail-group">
                        <div class="detail-label">Food Item:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($recent_order['food']); ?></div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Type:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($recent_order['type']); ?></div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Category:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($recent_order['category']); ?></div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Quantity:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($recent_order['quantity']); ?></div>
                    </div>
                </div>
                <div>
                    <div class="detail-group">
                        <div class="detail-label">Pickup Location:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($recent_order['location']); ?></div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Date:</div>
                        <div class="detail-value">
                            <?php echo date('M d, Y h:i A', strtotime($recent_order['date'])); ?>
                        </div>
                    </div>
                    <?php if(isset($recent_order['prep_time']) && $recent_order['prep_time']): ?>
                    <div class="detail-group">
                        <div class="detail-label">Prepared:</div>
                        <div class="detail-value"><?php echo date('M d, Y h:i A', strtotime($recent_order['prep_time'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(isset($recent_order['expiry_time']) && $recent_order['expiry_time']): ?>
                    <div class="detail-group">
                        <div class="detail-label">Best Before:</div>
                        <div class="detail-value"><?php echo date('M d, Y h:i A', strtotime($recent_order['expiry_time'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="detail-group">
                        <div class="detail-label">Notes:</div>
                        <div class="detail-value">
                            No additional notes
                        </div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value">
                            <span class="status-badge status-pending">Pending Pickup</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="#" class="btn btn-primary" onclick="markAsCompleted(<?php echo $recent_order['Fid']; ?>)">Mark as Completed</a>
                <a href="Meals.php" class="btn btn-secondary">Find More Meals</a>
            </div>
        </div>
        
        <h2 class="section-title">Delivery Location</h2>
        <div id="map-container" class="map-container"></div>
        <div id="location-info" style="text-align: center; margin-top: 10px;"></div>
        
        <?php else: ?>
        <div class="no-orders">
            <h2>No Orders Found</h2>
            <p>You haven't requested any meals yet.</p>
            <a href="Meals.php" class="btn btn-primary">Browse Available Meals</a>
        </div>
        <?php endif; ?>
        
        <h2 class="section-title">Order History</h2>
        <?php if(mysqli_num_rows($history_result) > 0): ?>
        <div class="table-responsive">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Food Item</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Requested On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($history_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['food']); ?></td>
                        <td><?php echo htmlspecialchars($order['category']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td><?php echo $order['formatted_date']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p>No order history available.</p>
        <?php endif; ?>
    </div>
    
    <script>
        // Initialize the map
        function initMap() {
            <?php if($recent_order): ?>
            // Create a map centered on the user's current location
            navigator.geolocation.getCurrentPosition(function(position) {
                var userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                var map = L.map('map-container').setView(userLocation, 13);
                
                // Add the OpenStreetMap tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
                    maxZoom: 18,
                    tileSize: 512,
                    zoomOffset: -1
                }).addTo(map);
                
                // Add a marker for the user's current location
                var userMarker = L.marker(userLocation).addTo(map);
                userMarker.bindPopup("<b>Your Location</b>").openPopup();
                
                // Get the pickup location coordinates using geocoding
                var pickupAddress = "<?php echo addslashes($recent_order['location']); ?>";
                var geocodeUrl = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(pickupAddress);
                
                fetch(geocodeUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            var pickupLocation = {
                                lat: parseFloat(data[0].lat),
                                lng: parseFloat(data[0].lon)
                            };
                            
                            // Add a marker for the pickup location
                            var pickupMarker = L.marker([pickupLocation.lat, pickupLocation.lng]).addTo(map);
                            pickupMarker.bindPopup("<b>Pickup Location</b><br>" + pickupAddress).openPopup();
                            
                            // Fit the map to show both markers
                            var bounds = L.latLngBounds([
                                [userLocation.lat, userLocation.lng],
                                [pickupLocation.lat, pickupLocation.lng]
                            ]);
                            map.fitBounds(bounds, { padding: [50, 50] });
                            
                            // Draw a line between the two points
                            var polyline = L.polyline([
                                [userLocation.lat, userLocation.lng],
                                [pickupLocation.lat, pickupLocation.lng]
                            ], {color: '#06C167', weight: 3}).addTo(map);
                            
                            // Calculate distance
                            var distance = calculateDistance(
                                userLocation.lat, userLocation.lng,
                                pickupLocation.lat, pickupLocation.lng
                            );
                            
                            document.getElementById('location-info').innerHTML = 
                                "Distance to pickup location: approximately " + distance.toFixed(2) + " km";
                        } else {
                            document.getElementById('location-info').innerHTML = 
                                "Could not geocode the pickup address. Please check the address.";
                        }
                    })
                    .catch(error => {
                        console.error("Error geocoding address:", error);
                        document.getElementById('location-info').innerHTML = 
                            "Error finding pickup location. Please try again later.";
                    });
            }, function() {
                // Handle errors when retrieving the user's location
                document.getElementById('map-container').innerHTML = 
                    "<div style='padding: 20px; text-align: center;'>Error: Could not access your location. Please enable location services.</div>";
            });
            <?php endif; ?>
        }
        
        // Calculate distance between two points using the Haversine formula
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the Earth in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distance = R * c; // Distance in km
            return distance;
        }
        
        // Function to mark an order as completed
        function markAsCompleted(orderId) {
            if (confirm("Are you sure you want to mark this order as completed?")) {
                // Send AJAX request to update order status
                fetch('update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Order marked as completed!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred. Please try again.");
                });
            }
        }
        
        // Initialize the map when the page loads
        window.onload = initMap;
    </script>
</body>
</html> 