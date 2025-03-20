<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("location: ../signin.php");
    exit();
}

// Check if this is a valid completion page request
if(!isset($_GET['success']) || $_GET['success'] != '1') {
    header("location: Meals.php");
    exit();
}

$food_name = isset($_GET['food']) ? htmlspecialchars($_GET['food']) : 'meal';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Completed - Share A Meal</title>
    <link rel="stylesheet" href="../loginstyle.css">
    <style>
        .success-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        .success-icon {
            color: #06C167;
            font-size: 60px;
            margin-bottom: 20px;
        }
        .options-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        .option-btn {
            background-color: #06C167;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .option-btn.secondary {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        .option-btn:hover {
            background-color: #059452;
        }
        .option-btn.secondary:hover {
            background-color: #e0e0e0;
        }
        .success-message {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .success-title {
            color: #06C167;
            font-size: 28px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body style="background-color: #06C167;">
    <div class="container">
        <div class="success-container">
            <div class="success-icon">✓</div>
            <h1 class="success-title">Order Completed!</h1>
            <p class="success-message">
                Your <?php echo $food_name; ?> has been successfully ordered.<br>
                It will be delivered to you soon.
            </p>
            <div class="options-container">
                <a href="../index.html" class="option-btn">Go to Home Page</a>
                <a href="Meals.php" class="option-btn secondary">View Available Meals</a>
            </div>
        </div>
    </div>
</body>
</html> 