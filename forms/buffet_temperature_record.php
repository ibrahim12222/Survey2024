<?php

include '../db/db.php';
include("header/header.php"); 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
 

$connecDB = openConnection();   
// if(!isset($_SESSION['userid']) || $_SESSION['userid'] ==0)
// {	
//     header('location:../loginsurvey.php');
// 	exit; 
// }
$username=$_SESSION["phone_number"];

//  print_r($_POST);
if($_POST)
{
    set_buffet_record($connecDB,$_POST['food_type'],$_POST['processed'],$_POST['food_name'],$_POST['food_temp'],$_POST['food_corrective'],$username,$_POST['survey_date']);
     echo 'inside if post';
}

?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buffet Temperature Record</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <div class="main_container main_container_desktop">
        <h2 class="form_title">Buffet Temperature Record</h2>
        <form class="form" action="?" method="post" >
            <div class="form_item">
                <label for="food-select">Select Food Type</label>
                <label for="food-select"></label>
                <select name="food_type" id="food_type" required>
                    <option value="" disabled selected>Select Food Type</option>
                    <option value="Breakfast" >Breakfast</option>
                    <option value="Lunch">Lunch</option>
                    <option value="Dinner">Dinner</option>
                </select>
            </div>
            <div class="form_item">
                <label>Process</label>
                <div class="radio_flex">
                    <div class="radio_item">
                        <input type="radio" id="hot-food" name="processed" required value="hot">
                        <label for="hot-food">Hot Food</label>
                    </div>
                    <div class="radio_item">
                        <input type="radio" id="cool-food" name="processed" value="cold">
                        <label for="cool-food">Cool Food</label>
                    </div>
                </div>
            </div>
            <div class="form_item">
                <label for="food-name">Select Food </label>
                <input type="text" name="food_name" id="food_name" placeholder="Enter Food" required >

            </div>
            <div class="form_item">
                <label for="food-temp">Temp&deg;C</label>
                <input type="number" name="food_temp" id="food_temp" placeholder="Enter Temp&deg;C" required >
            </div>
            <div class="form_item">
                <label for="food-corrective">Corrective Action</label>
                <textarea name="food_corrective" id="food_corrective"  placeholder="Enter Corrective Action" required ></textarea>
            </div>
            <div class="form_item">
                <label for="value">Date</label>
                <input type="date" id="survey_date" name="survey_date" required>            
            </div>
            
            <button type="submit"class="submit_btn">Submit</button>
        </form>
        <div class="Guidelines_area">
            <h3>Guidelines</h3>
            <div class="mt-4 table_record table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Food type</th>
                            <th>Standard Minimum core cooking temperature in &deg;C</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                Hot Holding
                            </td>
                            <td>65&deg;C and above</td>
                            <td>FSSAI(2011)</td>
                        </tr>
                        <tr>
                            <td>
                                Cold Holding
                            </td>
                            <td>Ambient temperature above 15&deg;C exposure time must not exceed 45 minutes.</td>
                            <td>IFSA AEA Food safety guidelines</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <img src="temp_safety.webp" alt="temp_safety" class="temp_safety">
        </div>
    </div>
</body>

</html>