<?php


include 'db/db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$connecDB = openConnection();
$msg = '';
$verified = false;

// print_r($_POST);
if (isset($_POST['sub_btn'])) {
    $verifiedid = getLoginDetails($connecDB, $_POST['login']);
    // echo $verifiedid;
    if ($verifiedid == 0) {
        $msg = '<span style="color:red">Incorrect mobile no.</span>';
    } else {
        $_SESSION["surveyidtoverify"]  = $verifiedid;
        $verified = true;
    }
} elseif (isset($_POST['otp_btn'])) {
    if (verifyOtp($connecDB, $_POST['login'], $_POST['otp'])) {
        $_SESSION["userid"]   = $_SESSION["surveyidtoverify"];
        $_SESSION["phone_number"]  = $_POST['login'];

        header('location:forms/temperature_record.php');
        exit;
    } else {
        $msg = '<span style="color:red">Incorrect OTP!</span>';
        $verified = true;
    }
} else {
    unset($_SESSION['userid']);
    unset($_SESSION['phone_number']);
    unset($_SESSION['surveyidtoverify']);
}

?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php

    print $msg;

    if ($verified) {
    ?> <div class="main_container main_container_desktop">
            <h2 class="form_title">Login OTP is sent to your mobile. </h2>
            <form method="post" action="?">
                <div class="form_item">
                    <input type="hidden" id="login" name="login" value="<?php echo $_POST['login']; ?>">
                    <input type="text" id="otp" class="fadeIn second admin-login " name="otp" placeholder="Enter OTP" required>
                    <input type="submit" class="fadeIn fourth submit_btn" value="Verify" id="otp_btn" name="otp_btn">
                    <p class="resend_otp"><a href="#" class="resend ">Resend OTP</a></p>
                </div>
            </form>
        </div>
    <?php
    } else {

    ?>
        <div class="main_container main_container_desktop">
            <h2 class="form_title">Login </h2>
            <form method="post" action="?">
                <div class="form_item">
                    <input type="text" id="login" class="fadeIn second admin-login " name="login" placeholder="Enter Mobile No" required>
                    <input type="submit" class="fadeIn fourth submit_btn" value="Login" id="sub_btn" name="sub_btn">
                </div>
            </form>
        </div>
    <?php
    } ?>


    <script type="text/javascript">
        $(document).ready(function() {
            $('#login').focus();
            $('#otp').focus();
        });


        $("a.resend").click(function() {

            post("?", {
                'sub_btn': '1',
                'login': $("#login").val()
            });
        });

        function post(path, parameters) {

            var form = $('<form></form>');

            form.attr("method", "post");
            form.attr("action", path);

            $.each(parameters, function(key, value) {
                var field = $('<input></input>');

                field.attr("type", "hidden");
                field.attr("name", key);
                field.attr("value", value);

                form.append(field);
            });

            $(document.body).append(form);
            form.submit();
        }
    </script>

</body>

</html>