<?php
//change mysqli_connect(host_name,username, password, database_name); 
$connection = mysqli_connect("localhost", "root", "", "demo");

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
