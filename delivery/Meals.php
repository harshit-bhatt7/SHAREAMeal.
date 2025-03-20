<?php
// Enable all error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Add cache control headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include '../connection.php';  // Use relative path to include from parent directory

// Check for database connection errors
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Debug database connection
echo "<!-- Connected to database: " . mysqli_get_host_info($connection) . " -->";

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("location: ../signin.php");
    exit();
}

// Get available food donations - exclude expired meals
$query = "SELECT * FROM food_donations 
          WHERE assigned_to IS NULL 
          AND (expiry_time IS NULL OR expiry_time > NOW())
          ORDER BY date DESC";

$result = mysqli_query($connection, $query);

// Check for query errors
if (!$result) {
    echo "Error: " . mysqli_error($connection);
    exit();
}

// Debug line to check if we're finding records
echo "<!-- Found " . mysqli_num_rows($result) . " total meals -->";

// Get the user's recent orders
$email = $_SESSION['email'];
$user_query = "SELECT id FROM login WHERE email = ?";
$user_stmt = mysqli_prepare($connection, $user_query);
mysqli_stmt_bind_param($user_stmt, "s", $email);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);

$recent_orders = false;
if (mysqli_num_rows($user_result) > 0) {
    $user = mysqli_fetch_assoc($user_result);
    $user_id = $user['id'];
    
    // Get the user's orders
    $orders_query = "SELECT * FROM food_donations WHERE assigned_to = ? ORDER BY date DESC LIMIT 5";
    $orders_stmt = mysqli_prepare($connection, $orders_query);
    mysqli_stmt_bind_param($orders_stmt, "i", $user_id);
    mysqli_stmt_execute($orders_stmt);
    $recent_orders = mysqli_stmt_get_result($orders_stmt);
}

// Check if 'city' exists in the session before accessing it
$city = isset($_SESSION['city']) ? $_SESSION['city'] : '';

// Check if 'Did' exists in the session before accessing it
$id = isset($_SESSION['Did']) ? $_SESSION['Did'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Meals - Share A Meal</title>
    <link rel="stylesheet" href="../loginstyle.css">
    <style>
        .meals-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .meal-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px;
            transition: transform 0.3s ease;
        }
        .meal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .meal-title {
            color: #06C167;
            margin-top: 0;
            font-size: 1.2rem;
        }
        .meal-info {
            margin: 5px 0;
        }
        .order-btn {
            background-color: #06C167;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .order-btn:hover {
            background-color: #059452;
        }
        .no-meals {
            text-align: center;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-top: 20px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #06C167;
            text-decoration: none;
        }
        .recent-orders {
            margin-top: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .expiry-badge {
            background-color: #ffebee;
            color: #f44336;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            display: inline-block;
            margin-left: 5px;
        }
        .debug-info {
            background: #fff; 
            margin-top: 20px; 
            padding: 15px; 
            display: none;
        }
        .section-title {
            text-align: center;
            margin-top: 30px;
            color: #fff;
            font-size: 1.5rem;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 5px;
        }
        .status-pending {
            background-color: #fff3e0;
            color: #e65100;
        }
        .status-assigned {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
    </style>
</head>
<body style="background-color: #06C167;">
    <div class="container">
        <div class="regformf" style="max-width: 1000px;">
            <p class="logo">Share <b style="color: #06C167;">A Meal</b></p>
            <h2 style="text-align: center;">Available Meals</h2>
            
            <?php
            // Reset result pointer to beginning
            mysqli_data_seek($result, 0);
            $has_meals = false;
            ?>
            
            <div id="available-meals" class="meals-container">
                <?php while($food = mysqli_fetch_assoc($result)): ?>
                    <?php $has_meals = true; ?>
                    <div class="meal-card">
                        <h3 class="meal-title">
                            <?php echo htmlspecialchars($food['food'] ?? 'Unnamed Food'); ?>
                        </h3>
                        <p class="meal-info"><strong>Type:</strong> <?php echo htmlspecialchars($food['type'] ?? 'N/A'); ?></p>
                        <p class="meal-info"><strong>Category:</strong> <?php echo htmlspecialchars($food['category'] ?? 'N/A'); ?></p>
                        <p class="meal-info"><strong>Quantity:</strong> <?php echo htmlspecialchars($food['quantity'] ?? 'N/A'); ?></p>
                        <p class="meal-info"><strong>Location:</strong> <?php echo htmlspecialchars($food['location'] ?? 'N/A'); ?></p>
                        
                        <?php if(isset($food['prep_time']) && $food['prep_time']): ?>
                            <p class="meal-info"><strong>Prepared:</strong> <?php echo date('M d, Y h:i A', strtotime($food['prep_time'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if(isset($food['expiry_time']) && $food['expiry_time']): ?>
                            <p class="meal-info">
                                <strong>Best Before:</strong> 
                                <?php echo date('M d, Y h:i A', strtotime($food['expiry_time'])); ?>
                                <?php if(strtotime($food['expiry_time']) < strtotime('+24 hours')): ?>
                                    <span class="expiry-badge">Expiring Soon</span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        
                        <a href="order.php?id=<?php echo $food['Fid']; ?>" class="order-btn">Order This Meal</a>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <?php if(!$has_meals): ?>
                <div class="no-meals">
                    <h3>No meals available at the moment</h3>
                    <p>Please check back later for available food donations.</p>
                </div>
            <?php endif; ?>
            
            <!-- Recent Orders Section -->
            <?php if($recent_orders && mysqli_num_rows($recent_orders) > 0): ?>
                <h2 class="section-title">Your Recent Orders</h2>
                <div class="recent-orders">
                    <div class="meals-container">
                        <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                            <div class="meal-card">
                                <h3 class="meal-title">
                                    <?php echo htmlspecialchars($order['food'] ?? 'Unnamed Food'); ?>
                                </h3>
                                <p class="meal-info"><strong>Ordered On:</strong> <?php echo date('M d, Y h:i A', strtotime($order['date'])); ?></p>
                                <p class="meal-info"><strong>Location:</strong> <?php echo htmlspecialchars($order['location'] ?? 'N/A'); ?></p>
                                
                                <?php if(isset($order['pickup_time']) && $order['pickup_time']): ?>
                                    <p class="meal-info"><strong>Pickup Time:</strong> <?php echo date('M d, Y h:i A', strtotime($order['pickup_time'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <a href="../profile.php" class="back-link">Back to Profile</a>
        </div>
    </div>

    <!-- Debug information -->
    <div class="debug-info">
        <h3>Debug Info</h3>
        <p>Session Email: <?php echo $_SESSION['email'] ?? 'Not set'; ?></p>
        <p>Query: <?php echo $query; ?></p>
        <p>Records found: <?php echo mysqli_num_rows($result); ?></p>
        
        <h4>Database Tables Check</h4>
        <?php
        $tables = ['food_donations', 'login', 'admin'];
        foreach ($tables as $table) {
            $check_query = "SHOW TABLES LIKE '$table'";
            $check_result = mysqli_query($connection, $check_query);
            echo "$table table exists: " . (mysqli_num_rows($check_result) > 0 ? 'Yes' : 'No') . "<br>";
            
            if (mysqli_num_rows($check_result) > 0) {
                $count_query = "SELECT COUNT(*) as count FROM $table";
                $count_result = mysqli_query($connection, $count_query);
                $count_row = mysqli_fetch_assoc($count_result);
                echo "Records in $table: " . $count_row['count'] . "<br>";
            }
        }
        ?>
        
        <h4>Sample Data</h4>
        <?php
        mysqli_data_seek($result, 0); // Reset pointer to beginning
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Food</th><th>Location</th></tr>";
        
        $count = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            if ($count < 5) { // Show only first 5 rows
                echo "<tr>";
                echo "<td>" . ($row['Fid'] ?? 'N/A') . "</td>";
                echo "<td>" . ($row['name'] ?? 'N/A') . "</td>";
                echo "<td>" . ($row['food'] ?? 'N/A') . "</td>";
                echo "<td>" . ($row['location'] ?? 'N/A') . "</td>";
                echo "</tr>";
                $count++;
            } else {
                break;
            }
        }
        echo "</table>";
        
        if ($recent_orders) {
            echo "<h4>Recent Orders</h4>";
            echo "<p>Recent orders found: " . mysqli_num_rows($recent_orders) . "</p>";
        }
        ?>
    </div>
</body>
</html> 