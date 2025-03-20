<?php
include '../connection.php';

// Create food_donations table with proper structure
$sql = "CREATE TABLE IF NOT EXISTS food_donations (
    Fid INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    food VARCHAR(255) NOT NULL,
    type VARCHAR(50),
    category VARCHAR(50),
    quantity VARCHAR(50),
    location VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    prep_time DATETIME,
    expiry_time DATETIME,
    assigned_to INT,
    delivery_by INT,
    request_notes TEXT,
    pickup_time DATETIME,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('available', 'assigned', 'delivered', 'cancelled') DEFAULT 'available',
    FOREIGN KEY (assigned_to) REFERENCES login(id),
    FOREIGN KEY (delivery_by) REFERENCES admin(Aid)
)";

if (mysqli_query($connection, $sql)) {
    echo "✅ food_donations table created/verified successfully<br>";
} else {
    echo "❌ Error creating table: " . mysqli_error($connection) . "<br>";
}

// Add sample data if table is empty
$check_query = "SELECT COUNT(*) as count FROM food_donations";
$result = mysqli_query($connection, $check_query);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    // Add sample food donations
    $sample_data = [
        [
            "name" => "Test Donor 1",
            "food" => "Vegetable Curry",
            "type" => "Veg",
            "category" => "Main Course",
            "quantity" => "2 plates",
            "location" => "Mumbai",
            "prep_time" => date('Y-m-d H:i:s'),
            "expiry_time" => date('Y-m-d H:i:s', strtotime('+24 hours')),
            "status" => "available"
        ],
        [
            "name" => "Test Donor 2",
            "food" => "Rice and Dal",
            "type" => "Veg",
            "category" => "Main Course",
            "quantity" => "3 plates",
            "location" => "Delhi",
            "prep_time" => date('Y-m-d H:i:s'),
            "expiry_time" => date('Y-m-d H:i:s', strtotime('+12 hours')),
            "status" => "available"
        ]
    ];

    foreach ($sample_data as $data) {
        $insert_query = "INSERT INTO food_donations (name, food, type, category, quantity, location, prep_time, expiry_time, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $insert_query);
        mysqli_stmt_bind_param($stmt, "sssssssss", 
            $data['name'], $data['food'], $data['type'], $data['category'], 
            $data['quantity'], $data['location'], $data['prep_time'], 
            $data['expiry_time'], $data['status']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✅ Added sample food donation: " . $data['food'] . "<br>";
        } else {
            echo "❌ Error adding sample data: " . mysqli_error($connection) . "<br>";
        }
    }
} else {
    echo "✅ Table already contains data<br>";
}

// Show current table structure
echo "<h3>Current Table Structure:</h3>";
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

// Show current data
echo "<h3>Current Data:</h3>";
$data_query = "SELECT * FROM food_donations ORDER BY date DESC";
$data_result = mysqli_query($connection, $data_query);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Food</th><th>Type</th><th>Location</th><th>Status</th><th>Assigned To</th><th>Delivery By</th></tr>";
while ($row = mysqli_fetch_assoc($data_result)) {
    echo "<tr>";
    echo "<td>" . $row['Fid'] . "</td>";
    echo "<td>" . $row['food'] . "</td>";
    echo "<td>" . $row['type'] . "</td>";
    echo "<td>" . $row['location'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>" . ($row['assigned_to'] ? $row['assigned_to'] : 'Not assigned') . "</td>";
    echo "<td>" . ($row['delivery_by'] ? $row['delivery_by'] : 'Not assigned') . "</td>";
    echo "</tr>";
}
echo "</table>";
?> 