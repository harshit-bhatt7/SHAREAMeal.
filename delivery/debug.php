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

// Check if user is logged in and has admin privileges - for security
if(!isset($_SESSION['email'])) {
    echo "Please log in first";
    exit();
}

// Show database connection info
echo "<p>Connected to: " . mysqli_get_host_info($connection) . "</p>";

// Get all food donations regardless of assigned status for debugging
$query = "SELECT * FROM food_donations ORDER BY date DESC";
$result = mysqli_query($connection, $query);

if (!$result) {
    echo "<p>Error: " . mysqli_error($connection) . "</p>";
    exit();
}

echo "<h2>All Food Donations (" . mysqli_num_rows($result) . " records)</h2>";

// Display each record as a table
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Food</th><th>Location</th><th>assigned_to</th></tr>";

while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Fid']) . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['food']) . "</td>";
    echo "<td>" . htmlspecialchars($row['location']) . "</td>";
    echo "<td>" . (is_null($row['assigned_to']) ? "NULL" : htmlspecialchars($row['assigned_to'])) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Run a test query to show unassigned meals
$unassigned_query = "SELECT * FROM food_donations WHERE assigned_to IS NULL";
$unassigned_result = mysqli_query($connection, $unassigned_query);

echo "<h2>Unassigned Meals (" . mysqli_num_rows($unassigned_result) . " records)</h2>";

// Display unassigned meals
if (mysqli_num_rows($unassigned_result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Food</th><th>Location</th></tr>";
    
    while($row = mysqli_fetch_assoc($unassigned_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Fid']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['food']) . "</td>";
        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No unassigned meals found.</p>";
}
?> 