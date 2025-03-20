<?php
include("login.php"); 
if($_SESSION['name']==''){
	header("location: signin.php");
}
// include("login.php"); 
$emailid= $_SESSION['email'];
$connection=mysqli_connect("localhost","root","");
$db=mysqli_select_db($connection,'demo');
if(isset($_POST['submit']))
{
    $foodname=mysqli_real_escape_string($connection, $_POST['foodname']);
    $meal=mysqli_real_escape_string($connection, $_POST['meal']);
    $category=$_POST['image-choice'];
    $quantity=mysqli_real_escape_string($connection, $_POST['quantity']);
    $prep_time=mysqli_real_escape_string($connection, $_POST['prep_time']);
    $expiry_time=mysqli_real_escape_string($connection, $_POST['expiry_time']);
    // $email=$_POST['email'];
    $phoneno=mysqli_real_escape_string($connection, $_POST['phoneno']);
    $district=mysqli_real_escape_string($connection, $_POST['district']);
    $address=mysqli_real_escape_string($connection, $_POST['address']);
    $name=mysqli_real_escape_string($connection, $_POST['name']);
  

 



    $query="insert into food_donations(email,food,type,category,phoneno,location,address,name,quantity,prep_time,expiry_time) values('$emailid','$foodname','$meal','$category','$phoneno','$district','$address','$name','$quantity','$prep_time','$expiry_time')";
    $query_run= mysqli_query($connection, $query);
    if($query_run)
    {
        echo '<script type="text/javascript">alert("data saved")</script>';
        header("location:delivery.html");
    }
    else{
        echo '<script type="text/javascript">alert("Data not saved. Error: ' . mysqli_error($connection) . '")</script>';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Donate</title>
    <link rel="stylesheet" href="loginstyle.css">
</head>
<body style="    background-color: #06C167;">
    <div class="container">
        <div class="regformf" >
    <form action="" method="post">
        <p class="logo">Share <b style="color: #06C167; ">A Meal</b></p>
        
       <div class="input">
        <label for="foodname"  > Food Name:</label>
        <input type="text" id="foodname" name="foodname" required/>
        </div>
      
      
        <div class="radio">
        <label for="meal" >Meal type :</label> 
        <br><br>

        <input type="radio" name="meal" id="veg" value="veg" required/>
        <label for="veg" style="padding-right: 40px;">Veg</label>
        <input type="radio" name="meal" id="Non-veg" value="Non-veg" >
        <label for="Non-veg">Non-veg</label>
    
        </div>
        <br>
        <div class="radio">
        <label for="food">Select the Category:</label>
        <br><br>
        <input type="radio" name="image-choice" id="perishable" value="perishable" checked required/>
        <label for="perishable" style="padding-right: 40px;">Perishable</label>
        <input type="radio" name="image-choice" id="non-perishable" value="non-perishable">
        <label for="non-perishable">Non-Perishable</label>
        </div>
        <br>
        <!-- <input type="text" id="food" name="food"> -->
        <div class="input">
        <label for="quantity">Quantity:(number of person /kg)</label>
        <input type="text" id="quantity" name="quantity" required/>
        </div>
        
        <div class="input">
        <label for="prep_time">When was the food prepared?</label>
        <input type="datetime-local" id="prep_time" name="prep_time" required/>
        </div>
        
        <div class="input">
        <label for="expiry_time">Until what time will the food last?</label>
        <input type="datetime-local" id="expiry_time" name="expiry_time" required/>
        </div>
        
       <b><p style="text-align: center;">Contact Details</p></b>
        <div class="input">
          <!-- <div>
      <label for="email">Email:</label>
      <input type="email" id="email" name="email">
          </div> -->
      <div>
      <label for="name">Name:</label>
      <input type="text" id="name" name="name"value="<?php echo"". $_SESSION['name'] ;?>" required/>
      </div>
      <div>
        <label for="phoneno" >PhoneNo:</label>
      <input type="text" id="phoneno" name="phoneno" maxlength="10" pattern="[0-9]{10}" required />
        
      </div>
      </div>
        <div class="input">
        <label for="district">District:</label>
        <select id="district" name="district" style="width: 100%; padding: 12px; margin-bottom: 20px; box-sizing: border-box; border: 2px solid #333; border-radius: 4px; font-size: 16px;">                          <option value="Andheri">Andheri</option>
        <option value="Andheri">Andheri</option>
                        <option value="Bandra">Bandra</option>
                        <option value="Bhandup">Bhandup</option>
                        <option value="Borivali">Borivali</option>
                        <option value="Byculla">Byculla</option>
                        <option value="Chembur">Chembur</option>
                        <option value="Colaba">Colaba</option>
                        <option value="Dadar" selected>Dadar</option>
                        <option value="Dahisar">Dahisar</option>
                        <option value="Ghatkopar">Ghatkopar</option>
                        <option value="Goregaon">Goregaon</option>
                        <option value="Juhu">Juhu</option>
                        <option value="Kandivali">Kandivali</option>
                        <option value="Kurla">Kurla</option>
                        <option value="Lower Parel">Lower Parel</option>
                        <option value="Malad">Malad</option>
                        <option value="Marine Lines">Marine Lines</option>
                        <option value="Mazgaon">Mazgaon</option>
                        <option value="Mulund">Mulund</option>
                        <option value="Powai">Powai</option>
                        <option value="Sewri">Sewri</option>
                        <option value="Sion">Sion</option>
                        <option value="South Mumbai">South Mumbai</option>
                        <option value="Trombay">Trombay</option>
                        <option value="Vasai">Vasai</option>
                        <option value="Vikhroli">Vikhroli</option>
                        <option value="Virar">Virar</option>
                        <option value="Worli">Worli</option>
        </select> 
        </div>
        
        <div class="input">
        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required/>
        </div>
        <div class="btn">
            <button type="submit" name="submit"> Submit</button>
     
        </div>
     </form>
     </div>
   </div>
     
    
</body>
</html>