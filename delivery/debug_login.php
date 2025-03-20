<?php
session_start();
include '../connection.php';

// Show current session data
echo "<h2>Current Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check login table structure
echo "<h2>Login Table Structure</h2>";
$query = "DESCRIBE login";
$result = mysqli_query($connection, $query);

if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error checking login table structure: " . mysqli_error($connection);
}

// Check user data in login table
echo "<h2>Login Table Data</h2>";
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
echo "Session email: " . htmlspecialchars($email) . "<br>";

if (!empty($email)) {
    $query = "SELECT * FROM login WHERE email = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    } else {
        echo "No user found with email: " . htmlspecialchars($email);
    }
} else {
    echo "No email in session";
}

// Show all food donations with assignment status
echo "<h2>Food Donations Assignment Status</h2>";
$query = "SELECT Fid, food, assigned_to FROM food_donations";
$result = mysqli_query($connection, $query);

if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Food</th><th>Assigned To</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Fid'] . "</td>";
        echo "<td>" . $row['food'] . "</td>";
        echo "<td>" . ($row['assigned_to'] ? $row['assigned_to'] : 'Not Assigned') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error checking food donations: " . mysqli_error($connection);
}
?> 