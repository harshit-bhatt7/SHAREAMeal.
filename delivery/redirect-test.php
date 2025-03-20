<?php
// Simple redirect test file
session_start();

// Check if coming from a successful order submission
if(isset($_SESSION['order_success']) && $_SESSION['order_success'] === true) {
    // Clear the session variable
    $_SESSION['order_success'] = false;
    
    // Display success message and redirect after a short delay
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Success</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                margin-top: 100px;
                background-color: #f5f5f5;
            }
            .success-message {
                background-color: #d4edda;
                color: #155724;
                padding: 20px;
                border-radius: 5px;
                max-width: 500px;
                margin: 0 auto;
            }
            .loading {
                margin-top: 20px;
            }
            .loading-dots {
                display: inline-block;
                width: 80px;
                text-align: center;
            }
            .loading-dots div {
                width: 13px;
                height: 13px;
                background: #06C167;
                border-radius: 50%;
                display: inline-block;
                animation: loading 1.4s infinite ease-in-out both;
                margin: 0 5px;
            }
            .loading-dots div:nth-child(1) {
                animation-delay: -0.32s;
            }
            .loading-dots div:nth-child(2) {
                animation-delay: -0.16s;
            }
            @keyframes loading {
                0%, 80%, 100% { transform: scale(0); }
                40% { transform: scale(1.0); }
            }
        </style>
    </head>
    <body>
        <div class="success-message">
            <h2>Order Submitted Successfully!</h2>
            <p>Your food request has been submitted. Redirecting to order details page...</p>
            <div class="loading">
                <div class="loading-dots">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
        
        <script>
            // Redirect after 2 seconds
            setTimeout(function() {
                window.location.href = "delivery-details.php";
            }, 2000);
        </script>
    </body>
    </html>
    ';
    exit();
}else{
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
        }
        button {
            background-color: #06C167;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #059452;
        }
    </style>
</head>
<body>
    <h1>Redirect Test Page</h1>
    <p>If you can see this page, redirection is working.</p>
    <p>Click the button below to try redirecting to the delivery-details.php page:</p>
    
    <button onclick="redirectToDetails()">Go to Delivery Details</button>
    
    <script>
        function redirectToDetails() {
            window.location.href = 'delivery-details.php';
        }
    </script>
</body>
</html> 
<?php
}
?>