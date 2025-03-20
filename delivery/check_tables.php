<?php
include '../connection.php';

// Check if tables exist
$tables = ['food_donations', 'admin', 'login'];
foreach ($tables as $table) {
    $result = mysqli_query($connection, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) == 0) {
        echo "Table '$table' does not exist<br>";
    } else {
        echo "Table '$table' exists<br>";
        // Show table structure
        $columns = mysqli_query($connection, "SHOW COLUMNS FROM $table");
        echo "Columns:<br>";
        while ($column = mysqli_fetch_assoc($columns)) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>";
        }
        echo "<br>";
    }
}

// Check for sample data
echo "<h3>Sample Data Check:</h3>";
foreach ($tables as $table) {
    $result = mysqli_query($connection, "SELECT COUNT(*) as count FROM $table");
    $row = mysqli_fetch_assoc($result);
    echo "Number of records in $table: " . $row['count'] . "<br>";
}
?> 