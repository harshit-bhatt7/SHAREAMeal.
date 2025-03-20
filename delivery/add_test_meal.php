<?php
// Enable all error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../connection.php';

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    echo "Please log in first";
    exit();
}

// Simple form to add a test meal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food = mysqli_real_escape_string($connection, $_POST['food']);
    $type = mysqli_real_escape_string($connection, $_POST['type']);
    $category = mysqli_real_escape_string($connection, $_POST['category']);
    $quantity = mysqli_real_escape_string($connection, $_POST['quantity']);
    $location = mysqli_real_escape_string($connection, $_POST['location']);
    $name = mysqli_real_escape_string($connection, $_SESSION['name'] ?? 'Test User');
    $email = mysqli_real_escape_string($connection, $_SESSION['email']);
    $address = mysqli_real_escape_string($connection, 'Test Address');
    $phone = mysqli_real_escape_string($connection, '1234567890');
    
    // Insert query
    $query = "INSERT INTO food_donations (name, email, food, type, category, quantity, address, location, phoneno, assigned_to) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "sssssssss", $name, $email, $food, $type, $category, $quantity, $address, $location, $phone);
    $result = mysqli_stmt_execute($stmt);
    
    if($result) {
        echo "<p style='color:green'>Test meal added successfully! <a href='Meals.php'>View available meals</a></p>";
    } else {
        echo "<p style='color:red'>Error adding test meal: " . mysqli_error($connection) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Test Meal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 500px; margin: 0 auto; }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #06C167; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        .links { margin-top: 20px; text-align: center; }
        .links a { margin: 0 10px; color: #06C167; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Add Test Meal</h1>
    <p>Use this form to add a test meal that will appear in the Available Meals page</p>
    
    <form method="post">
        <div>
            <label for="food">Food Name:</label>
            <input type="text" id="food" name="food" value="Test Meal" required>
        </div>
        
        <div>
            <label for="type">Type:</label>
            <select id="type" name="type" required>
                <option value="Veg">Veg</option>
                <option value="Non-veg">Non-veg</option>
            </select>
        </div>
        
        <div>
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="cooked-food">Cooked Food</option>
                <option value="raw-food">Raw Food</option>
                <option value="packaged-food">Packaged Food</option>
            </select>
        </div>
        
        <div>
            <label for="quantity">Quantity:</label>
            <input type="text" id="quantity" name="quantity" value="1kg" required>
        </div>
        
        <div>
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="Mumbai" required>
        </div>
        
        <div>
            <button type="submit">Add Test Meal</button>
        </div>
    </form>
    
    <div class="links">
        <a href="Meals.php">View Available Meals</a> | 
        <a href="debug.php">View Debug Information</a> | 
        <a href="../profile.php">Back to Profile</a>
    </div>
</body>
</html> 