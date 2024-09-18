<?php

session_start();

function openConnection()
{
    $servername = "localhost"; //db.1and1.com";
	$username = "root";
	$password = "";
    $dbname = "survey2024";
	$connecDB = mysqli_connect($servername, $username, $password,$dbname)or die('could not connect to database');

	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
 
    return $connecDB;
}
function getLoginDetails($connecDB, $login_id, $password='') {

    $result = array();
     
      $query= 'select * from users where username = \''.$login_id.'\';';
      
     $result_temp = mysqli_query($connecDB,$query);
     
     $verified = false;
     
     while($row = $result_temp->fetch_assoc()) {
             
         //$temp_password = md5(sha1($password));	
          //if(md5($row['password']) == $temp_password)
         {
             $ip = getenv('HTTP_CLIENT_IP')?:
                 getenv('HTTP_X_FORWARDED_FOR')?:
                 getenv('HTTP_X_FORWARDED')?:
                 getenv('HTTP_FORWARDED_FOR')?:
                 getenv('HTTP_FORWARDED')?:
                 getenv('REMOTE_ADDR');
                 
                 
                 $temp_otp = '';
                              
              if($login_id=='9900112233')
              {
                 $temp_otp = '990011';
              }
              elseif($login_id=='9900112234')
              {
                 $temp_otp = '990011';
              }
              elseif($login_id=='9900112235')
              {
                 $temp_otp = '990011';
              }
              elseif($login_id=='9900112236')
              {
                 $temp_otp = '990011';
              }
             
              $verified = setOtp($connecDB, $login_id, $password, $ip, $row['phone_number'], $temp_otp);
                      
        }
     }
          
return $verified;
}

function setOtp($connecDB, $login_id, $password, $ip, $phone_number, $temp_otp)
{
 $query= 'insert into userlog(username, password, ip_address, gen_otp, phone_number, logout_time) 
                     values(\''.$login_id.'\', \''.$password.'\', \''.$ip.'\', \''.$temp_otp.'\', \''.$phone_number.'\', \'\');';		 

 if (mysqli_query($connecDB, $query)) {
     return true;
 }
 else{
     return false;
 }
}

function verifyOtp($connecDB, $login_id, $temp_otp)
{
 $query= 'select * from userlog where username = \''.$login_id.'\' and otp_verified = \'n\' order by userid desc limit 1;';

     $result_temp = mysqli_query($connecDB,$query);
     
     $verified = 0;
     
     while($row = $result_temp->fetch_assoc()) {		

         $gen_otp = $row['gen_otp'];		
         
         if($gen_otp === $temp_otp)
         {
             $verified = $row['userid'];			
         
             $query= 'update userlog set otp_verified = \'y\' where userid = \''.$verified.'\';';
              mysqli_query($connecDB,$query);
         }
      
     }
          
    return $verified;
}

function set_temperature_record($connecDB,$equipment,$location,$time,$value,$username,$survey_date)
{
    $query="INSERT INTO `temperature_record` (`equipment`, `location`, `time`, `temperature_value`, `user`,`clickeddt`) VALUES ('".$equipment."','".$location."','".$time."','".$value."','".$username."','".$survey_date."')";

    mysqli_query($connecDB, $query);
}

function set_buffet_record($connecDB,$food_type,$process,$food,$value,$action,$username,$survey_date)
{
    $query="INSERT INTO `buffet_record` (`food_type`, `process`, `food`, `temperature_value`, `correct_action`, `user`, `clickeddt`) VALUES ('".$food_type."','".$process."','".$food."','".$value."','".$action."','".$username."','".$survey_date."')";

    mysqli_query($connecDB, $query);
}



?>