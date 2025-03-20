<?php
include '../connection.php';

echo "<h2>Adding Missing Columns to food_donations Table</h2>";

// Check if the request_notes column exists
$check_query = "SHOW COLUMNS FROM food_donations LIKE 'request_notes'";
$check_result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    // Add the request_notes column
    $alter_query = "ALTER TABLE food_donations ADD COLUMN request_notes TEXT AFTER pickup_time";
    if (mysqli_query($connection, $alter_query)) {
        echo "✅ Successfully added 'request_notes' column<br>";
    } else {
        echo "❌ Error adding 'request_notes' column: " . mysqli_error($connection) . "<br>";
    }
} else {
    echo "ℹ️ The 'request_notes' column already exists<br>";
}

// Check if the pickup_time column exists
$check_query = "SHOW COLUMNS FROM food_donations LIKE 'pickup_time'";
$check_result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    // Add the pickup_time column
    $alter_query = "ALTER TABLE food_donations ADD COLUMN pickup_time DATETIME AFTER delivery_by";
    if (mysqli_query($connection, $alter_query)) {
        echo "✅ Successfully added 'pickup_time' column<br>";
    } else {
        echo "❌ Error adding 'pickup_time' column: " . mysqli_error($connection) . "<br>";
    }
} else {
    echo "ℹ️ The 'pickup_time' column already exists<br>";
}

// Check if the delivery_by column exists
$check_query = "SHOW COLUMNS FROM food_donations LIKE 'delivery_by'";
$check_result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    // Add the delivery_by column
    $alter_query = "ALTER TABLE food_donations ADD COLUMN delivery_by INT AFTER assigned_to";
    if (mysqli_query($connection, $alter_query)) {
        echo "✅ Successfully added 'delivery_by' column<br>";
    } else {
        echo "❌ Error adding 'delivery_by' column: " . mysqli_error($connection) . "<br>";
    }
} else {
    echo "ℹ️ The 'delivery_by' column already exists<br>";
}

// Show current table structure
echo "<h3>Current Structure of food_donations Table:</h3>";
$structure_query = "DESCRIBE food_donations";
$structure_result = mysqli_query($connection, $structure_query);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($structure_result)) {
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
?> 