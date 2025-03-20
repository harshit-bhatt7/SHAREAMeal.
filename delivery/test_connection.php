<?php
session_start();
include '../connection.php';

echo "<h2>Testing Connection Flow</h2>";

// 1. Test Database Connection
echo "<h3>1. Database Connection Test</h3>";
if ($connection) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br>";
}

// 2. Test Food Donations Table
echo "<h3>2. Food Donations Test</h3>";
$query = "SELECT * FROM food_donations WHERE assigned_to IS NULL LIMIT 1";
$result = mysqli_query($connection, $query);
if ($result) {
    echo "✅ Food donations query successful<br>";
    if (mysqli_num_rows($result) > 0) {
        $food = mysqli_fetch_assoc($result);
        echo "Found unassigned food: " . htmlspecialchars($food['food']) . "<br>";
    } else {
        echo "No unassigned food found<br>";
    }
} else {
    echo "❌ Food donations query failed: " . mysqli_error($connection) . "<br>";
}

// 3. Test Admin Table (Delivery Personnel)
echo "<h3>3. Delivery Personnel Test</h3>";
$query = "SELECT * FROM admin LIMIT 1";
$result = mysqli_query($connection, $query);
if ($result) {
    echo "✅ Admin query successful<br>";
    if (mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        echo "Found delivery personnel: " . htmlspecialchars($admin['name']) . "<br>";
    } else {
        echo "No delivery personnel found<br>";
    }
} else {
    echo "❌ Admin query failed: " . mysqli_error($connection) . "<br>";
}

// 4. Test Login Table (Users)
echo "<h3>4. Users Test</h3>";
$query = "SELECT * FROM login LIMIT 1";
$result = mysqli_query($connection, $query);
if ($result) {
    echo "✅ Users query successful<br>";
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        echo "Found user: " . htmlspecialchars($user['email']) . "<br>";
    } else {
        echo "No users found<br>";
    }
} else {
    echo "❌ Users query failed: " . mysqli_error($connection) . "<br>";
}

// 5. Test Order Assignment
echo "<h3>5. Order Assignment Test</h3>";
$query = "SELECT fd.*, a.name as delivery_person 
          FROM food_donations fd 
          LEFT JOIN admin a ON fd.delivery_by = a.Aid 
          WHERE fd.assigned_to IS NOT NULL 
          LIMIT 1";
$result = mysqli_query($connection, $query);
if ($result) {
    echo "✅ Order assignment query successful<br>";
    if (mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
        echo "Found assigned order:<br>";
        echo "- Food: " . htmlspecialchars($order['food']) . "<br>";
        echo "- Assigned to: " . htmlspecialchars($order['assigned_to']) . "<br>";
        echo "- Delivery by: " . ($order['delivery_person'] ? htmlspecialchars($order['delivery_person']) : "Not assigned") . "<br>";
    } else {
        echo "No assigned orders found<br>";
    }
} else {
    echo "❌ Order assignment query failed: " . mysqli_error($connection) . "<br>";
}

// 6. Test File Access
echo "<h3>6. File Access Test</h3>";
$files = ['Meals.php', 'order.php', 'delivery.php', 'update_order_status.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file not found<br>";
    }
}
?> 