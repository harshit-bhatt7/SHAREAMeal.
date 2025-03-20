<?php
session_start();
include '../connection.php';  // Use relative path to include from parent directory

// Display debugging info if needed
$debug = false;
if ($debug) {
    echo "<h3>Debug Info:</h3>";
    echo "<pre>";
    echo "Session: ";
    print_r($_SESSION);
    echo "</pre>";
}

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("location: ../signin.php");
    exit();
}

// Check if food donation ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid or no food item selected!'); window.location.href='Meals.php';</script>";
    exit();
}

$food_id = intval($_GET['id']); // Convert to integer for security

// Get food donation details without checking assigned_to status first
$query = "SELECT * FROM food_donations WHERE Fid = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $food_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0) {
    echo "<script>alert('Food item not found!'); window.location.href='Meals.php';</script>";
    exit();
}

$food_item = mysqli_fetch_assoc($result);

// Process form submission
if(isset($_POST['submit'])) {
    // Validate inputs
    $errors = [];
    
    // Validate name
    if(empty($_POST['name'])) {
        $errors[] = "Name is required";
    } else {
        $name = mysqli_real_escape_string($connection, trim($_POST['name']));
    }
    
    // Validate phone
    if(empty($_POST['phone'])) {
        $errors[] = "Phone number is required";
    } elseif(!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
        $errors[] = "Phone number must be 10 digits";
    } else {
        $phone = mysqli_real_escape_string($connection, trim($_POST['phone']));
    }
    
    // Validate address
    if(empty($_POST['address'])) {
        $errors[] = "Address is required";
    } else {
        $address = mysqli_real_escape_string($connection, trim($_POST['address']));
    }
    
    // Validate pickup time
    if(empty($_POST['pickup_time'])) {
        $errors[] = "Pickup time is required";
    } else {
        $pickup_time = trim($_POST['pickup_time']);
    }
    
    // Process notes (optional)
    $notes = !empty($_POST['notes']) ? mysqli_real_escape_string($connection, trim($_POST['notes'])) : "";
    
    // If delivery address and pickup time are provided, add them to notes
    if(!empty($address) && !empty($pickup_time)) {
        // Add important details to notes including food info
        $notes = "Food: " . $food_item['food'] . 
                 "\nFood Type: " . $food_item['type'] . 
                 "\nCategory: " . $food_item['category'] . 
                 "\nQuantity: " . $food_item['quantity'] . 
                 "\nDelivery Address: " . $address . 
                 "\nPhone: " . $phone . 
                 "\nPickup Time: " . $pickup_time . 
                 "\nAdditional Notes: " . $notes;
        $notes = mysqli_real_escape_string($connection, $notes);
    }
    
    // Check if food is already assigned to someone else - do this right before update to avoid race conditions
    $check_query = "SELECT assigned_to FROM food_donations WHERE Fid = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $food_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['assigned_to'] != NULL) {
        $errors[] = "This meal has already been assigned to someone else. Please choose another meal.";
    } else {
        // Only try to get user ID if no errors so far
        if (empty($errors)) {
            // Get user ID from session or email
            $user_id = null;
            
            // First try to get directly from SESSION if it exists
            if (isset($_SESSION['id'])) {
                $user_id = $_SESSION['id'];
            } else {
                // Otherwise, look up by email
                $email = mysqli_real_escape_string($connection, $_SESSION['email']);
                $user_query = "SELECT id FROM login WHERE email = ?";
                $user_stmt = mysqli_prepare($connection, $user_query);
                mysqli_stmt_bind_param($user_stmt, "s", $email);
                mysqli_stmt_execute($user_stmt);
                $user_result = mysqli_stmt_get_result($user_stmt);
                
                if(mysqli_num_rows($user_result) > 0) {
                    $user = mysqli_fetch_assoc($user_result);
                    $user_id = $user['id'];
                    // Store for future use
                    $_SESSION['id'] = $user_id;
                } else {
                    $errors[] = "User account not found. Please log in again.";
                }
            }
            
            // If we have a user ID and no errors, proceed with database update
            if($user_id && empty($errors)) {
                // Check if request_notes column exists
                $column_check = mysqli_query($connection, "SHOW COLUMNS FROM food_donations LIKE 'request_notes'");
                $has_request_notes = mysqli_num_rows($column_check) > 0;
                
                // Prepare update query based on available columns
                if ($has_request_notes) {
                    $update_query = "UPDATE food_donations SET 
                                    assigned_to = ?,
                                    request_notes = ?,
                                    pickup_time = ?
                                    WHERE Fid = ? AND assigned_to IS NULL";
                    
                    $update_stmt = mysqli_prepare($connection, $update_query);
                    mysqli_stmt_bind_param($update_stmt, "issi", $user_id, $notes, $pickup_time, $food_id);
                } else {
                    // Fallback query without request_notes
                    $update_query = "UPDATE food_donations SET 
                                    assigned_to = ?,
                                    pickup_time = ?
                                    WHERE Fid = ? AND assigned_to IS NULL";
                    
                    $update_stmt = mysqli_prepare($connection, $update_query);
                    mysqli_stmt_bind_param($update_stmt, "isi", $user_id, $pickup_time, $food_id);
                }
                
                // Check if prepare statement was successful
                if($update_stmt === false) {
                    $errors[] = "Error preparing statement: " . mysqli_error($connection);
                } else {
                    $update_result = mysqli_stmt_execute($update_stmt);
                    
                    if($update_result && mysqli_affected_rows($connection) > 0) {
                        // Redirect to completion page with success status
                        $food_name = urlencode($food_item['food']);
                        header("Location: order_completion.php?success=1&food=$food_name");
                        exit();
                    } else {
                        // Race condition - someone else got it first
                        $errors[] = "Sorry, this meal was just assigned to someone else. Please try another meal.";
                    }
                }
            }
        }
    }
}

// Check AFTER form processing - if meal is already assigned and we're not the one who just assigned it
if(isset($food_item['assigned_to']) && $food_item['assigned_to'] != NULL && !isset($errors)) {
    echo "<script>alert('This meal has already been assigned. Please choose another meal.'); window.location.href='Meals.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Food - Share A Meal</title>
    <link rel="stylesheet" href="../loginstyle.css">
    <style>
        .food-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .food-details h3 {
            color: #06C167;
            margin-top: 0;
        }
        .food-details p {
            margin: 5px 0;
        }
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        textarea {
            resize: vertical;
        }
    </style>
</head>
<body style="background-color: #06C167;">
     <div class="container">
        <div class="regformf">
            <form action="" method="post">
                <p class="logo">Share <b style="color: #06C167;">A Meal</b></p>
                <h2 style="text-align: center;">Order Food</h2>
                
                <?php if(isset($errors) && !empty($errors)): ?>
                <div class="error-message">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="food-details">
                    <h3>Food Item Details</h3>
                    <p><strong>Food:</strong> <?php echo htmlspecialchars($food_item['food']); ?></p>
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($food_item['type']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($food_item['category']); ?></p>
                    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($food_item['quantity']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($food_item['location']); ?></p>
                    <?php if(isset($food_item['prep_time']) && $food_item['prep_time']): ?>
                    <p><strong>Prepared:</strong> <?php echo date('M d, Y h:i A', strtotime($food_item['prep_time'])); ?></p>
                    <?php endif; ?>
                    <?php if(isset($food_item['expiry_time']) && $food_item['expiry_time']): ?>
                    <p><strong>Best Before:</strong> <?php echo date('M d, Y h:i A', strtotime($food_item['expiry_time'])); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="input">
                    <label for="name">Your Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>" required/>
                </div>
                
                <div class="input">
                    <label for="phone">Phone Number:</label>
                    <input type="text" id="phone" name="phone" maxlength="10" pattern="[0-9]{10}" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required/>
                </div>
                
                <div class="input">
                    <label for="address">Delivery Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required/>
                </div>
                
                <div class="input">
                    <label for="pickup_time">Preferred Pickup Time:</label>
                    <input type="datetime-local" id="pickup_time" name="pickup_time" value="<?php echo isset($_POST['pickup_time']) ? htmlspecialchars($_POST['pickup_time']) : ''; ?>" required/>
                </div>
                
                <div class="input">
                    <label for="notes">Additional Notes:</label>
                    <textarea id="notes" name="notes" rows="3" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                </div>
                
                <div class="btn">
                    <button type="submit" name="submit">Submit Order</button>
                </div>
                
                <a href="Meals.php" style="display: block; text-align: center; margin-top: 15px; color: #06C167; text-decoration: none;">Back to Available Meals</a>
            </form>
        </div>
    </div>
</body>
</html> 