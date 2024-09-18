<?php

include '../db/db.php';
include("header/header.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$connecDB = openConnection();


if (!isset($_SESSION['userid']) || $_SESSION['userid'] == 0) {
    header('location:loginsurvey.php');
    exit;
}
$username = $_SESSION["phone_number"];

// print_r($_POST);
if ($_POST) {
    set_temperature_record($connecDB, $_POST['equipment'], $_POST['location'], $_POST['time'], $_POST['temperature_value'], $username);
    // echo 'inside if post';
}



?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperature Record</title>
    <link rel="stylesheet" href="../css/style.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>

</head>

<body>
    <div class="main_container main_container_desktop">
        <h2 class="form_title">Temperature Record</h2>
        <form id="temperature-form" method="post" action="?">
            <div class="form_item">
                <label for="equipment">Select Equipment</label>
                <select name="equipment" id="equipment" required>
                    <option value="" disabled selected>Select Equipment</option>
                    <option value="Cooler">Cooler</option>
                    <option value="Freezer">Freezer</option>
                </select>
            </div>
            <div class="form_item">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" placeholder="Enter Location" required>
            </div>
            <div class="form_item">
                <label for="time">Select Time</label>

                <select name="time" id="time" required>
                    <option value="10.00">10.00</option>
                    <option value="14.00">14.00</option>
                    <option value="18.00">18.00</option>
                    <option value="22.00">22.00</option>
                    <option value="02.00">02.00</option>
                    <option value="06.00">06.00</option>
                </select>
            </div>
            <div class="form_item">
                <label for="value">Value</label>
                <input type="number" name="temperature_value" id="value" placeholder="Enter Value" required>
            </div>
            <button type="submit" class="submit_btn">Submit</button>
        </form>
    </div>


</body>

</html>