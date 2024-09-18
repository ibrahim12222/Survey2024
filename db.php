<?php
/**
 * The db.php file which initiates a connection to the database
 * and gives a global $db variable for access
 * @author Swashata <swashata@intechgrity.com>
 * @uses ezSQL MySQL
 */
/** edit your configuration */
// connect to local database 'itadmp' on localhost

// Database connect
//mysql_connect($dbhost,$dbuser,$dbpassword);
//mysql_select_db('gsk_reports');

/** Stop editing from here, else you know what you are doing ;) */

/** defined the root for the db */
/*if(!defined('ADMIN_DB_DIR'))
    define('ADMIN_DB_DIR', dirname(__FILE__));

require_once ADMIN_DB_DIR . '/ez_sql_core.php';
require_once ADMIN_DB_DIR . '/ez_sql_mysql.php';
global $db;
$db = new ezSQL_mysql($dbuser, $dbpassword, $dbname, $dbhost);*/

session_start();
function openConnection()
 {
		$servername = "localhost"; //db.1and1.com";
		$username = "root";
		$password = "virtual";
		$dbname = "sanofi2024";
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
				
				// ibrahim commented on 18-07-2024 for otp going to fixed otp user login 
				//  if($login_id != '9819674662')
				//  { $temp_otp = random_int(100000, 999999);	
				//   sendSMS($login_id,	$temp_otp);
				//  }		
				 
				 	
				 if($login_id != '9819674662' && $login_id!='9892725353' && $login_id=='9833104159' && $login_id=='9702858333' && $login_id!='9619734179' && $login_id!='9619512496') 
				 {
					 $temp_otp = random_int(100000, 999999);	
				  	sendSMS($login_id,	$temp_otp);
				 }

				 if($login_id=='9819674662')
				 {
					$temp_otp = '674662';
				 }
				 elseif($login_id=='9892725353')
				 {
					$temp_otp = '725353';
				 }
				 elseif ($login_id=='9833104159') 
				 {
					$temp_otp = '104159';
				 }
				 elseif ($login_id=='9702858333') 
				 {
					$temp_otp = '970285';
				 }
				 elseif ($login_id=='9619734179') 
				 {
					$temp_otp = '734179';
				 }
				 elseif ($login_id=='9619512496') 
				 {
					$temp_otp = '512496';
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

function getTotalCalls($connecDB, $vol) {

       $result = array();
		
		 //$query= "SELECT COUNT(distinct(phone_number)) AS TOTAL,  COUNT(IF(status='OPTIN',1,null)) as OPTIN  FROM stagedispo;";
						
						
		/*$query= "SELECT COUNT(distinct(s.phone_number)) AS TOTAL, 
						COUNT(distinct(IF(s.status='OPTIN',s.phone_number,null))) as OPTIN ,
						COUNT(IF(s.status not in ('OPTIN','NOT','CALLBK','UNDEC','XFER'),1,null)) as UNREACH 
						FROM stagedispo s inner join doctors d on d.phone_number = s.phone_number;";*/
		
		//zaid added on 08Jun2023
		$str_optin = 'OPTIN';		
		if($vol == 'VOL3')
		{
			$str_optin = 'VERFD';
		}	

		if($vol == 'VOL1')
		{
			$query= "select

				COUNT(distinct(s.phone_number)) AS TOTAL, (select count(*) from stagedispo s inner join doctors d on d.phone_number=s.phone_number) as dial,    (SELECT COUNT(*) FROM (SELECT mobile, template_id FROM sms_log_details 
         INNER JOIN doctors ON doctors.phone_number = sms_log_details.mobile GROUP BY mobile, template_id) AS total_SMs) AS total_SMS,
		 (select SUM(total_sent) AS total_email from ( SELECT (CASE WHEN sent != '0' THEN 1 ELSE 0 END) AS total_sent FROM email_send_log el inner join doctors d on d.email = el.email group by el.email,el.subject ) as r
) AS total_email,
			 COUNT(distinct(IF(s.status='".$str_optin."' or s.status='XFER' ,s.phone_number,null))) as OPTIN ,					
				 COUNT(IF(s.status not in ('".$str_optin."','NOT','DECLND','CALLBK','UNDEC','XFER'),1,null)) as UNREACH
				from (
				SELECT phone_number,max(event_time) as event_time FROM stagedispo where campaign_id = '".$vol."' group by phone_number ) as l
				inner join 
				stagedispo s on s.phone_number = l.phone_number and s.event_time = l.event_time and s.campaign_id = '".$vol."' 
				inner join doctors d on d.phone_number = s.phone_number";
		}		
							
						
		
			// echo $query; 
				//die();
		$result = mysqli_query($connecDB,$query);      
             
 return $result;
}


function getTotalbyDates($connecDB, $date1, $date2, $filterSpeciality, $filterState, $vol,$filterEng_all,$filterEng_4,$filterEng_3,$filterEng_n,$filterEng_l) 
{

       $result = array();
		
		/* $query= "SELECT COUNT(*) AS TOTAL, 
						COUNT(IF(status='OPTIN',1,null)) as OPTIN,						
						COUNT(IF(status='NOT',1,null)) as OPTOUT,
						COUNT(IF(status='CALLBK',1,null)) as CALLBK,
						COUNT(IF(status='UNDEC',1,null)) as UNDEC,
						COUNT(IF(status='SWCHOF',1,null)) as SWCHOF
					FROM stagedispo";*/
					
				$str_optin = 'OPTIN';		
				if($vol == 'VOL2')
				{		
					$str_optin = 'CCMD';
				}

		//zaid added on 12-08-2024
		if($vol == 'VOL1')
		{
			
			
			 $query ="select

				COUNT(s.phone_number) AS TOTAL, 
				 COUNT(IF(s.status='".$str_optin."' or s.status='XFER',1,null)) as OPTIN,						
				 COUNT(IF(s.status in ('NOT','DECLND'),1,null)) as OPTOUT,
				 COUNT(IF(s.status='CALLBK',1,null)) as CALLBK,
				 COUNT(IF(s.status='UNDEC',1,null)) as UNDEC,
				 COUNT(IF(s.status not in ('OPTIN','NOT','DECLND','CALLBK','UNDEC','XFER','COVERD','DECSD','INVD','NOPRAC','NOTDR','WRNG'),1,null)) as SWCHOF,
				 COUNT(IF(s.status in ('COVERD','DECSD','INVD','NOPRAC','NOTDR','WRNG'),1,null)) as UNUS 
				from (
				SELECT phone_number,max(event_time) as event_time FROM stagedispo where campaign_id = '".$vol."' group by phone_number ) as l
				inner join 
				stagedispo s on s.phone_number = l.phone_number and s.event_time = l.event_time and s.campaign_id = '".$vol."' 
				inner join doctors d on d.phone_number = s.phone_number";
		}
		elseif($vol=='VOL2')
		{
			$query ="select 
        COUNT(s.phone_number) AS TOTAL,  
        COUNT(IF(s.status='".$str_optin."',1,null)) as call_comp,
        COUNT(IF(s.status='SHORT',1,null)) as call_short,
		COUNT(IF(s.status='CALLBK',1,null)) as CALLBK,
        COUNT(IF(s.status='NOT',1,null)) as opt_out,
        COUNT(IF(s.status  not in ('CCMD','SHORT','NOT','TEST','UNDEC'),1,null)) as UNUS
		from (
			SELECT phone_number,max(event_time) as event_time FROM stagedispo where campaign_id = '".$vol."' group by phone_number ) as l
			inner join 
			stagedispo s on s.phone_number = l.phone_number and s.event_time = l.event_time and s.campaign_id = '".$vol."' 
			inner join doctors d on d.phone_number = s.phone_number";
				
		}

			if($date1 != '' && $date2 != '')
			{
				 $query .= ' where s.event_time >= \''.$date1.'\' and s.event_time <= \''.$date2.'\''; 
			}
			elseif($date1 != '')
			{
				 $query .= ' where s.event_time <= \''.$date1.'\''; 
			}
			
			if($filterSpeciality != '')
			{
				$query .= ' and d.Prefcallday = \''.$filterSpeciality.'\' ';
			}				
			if($filterState != '')
			{
				$query .= ' and d.state = \''.$filterState.'\' ';
			}
			
			if($filterEng_all=='')
			{
				$eng_op=' and ';
				if($filterEng_4!='')
				{
					$query.=$eng_op." d.last_name ='".$filterEng_4."'";
					$eng_op=' or ';
				}
				if($filterEng_3!='')
				{
					$query.=$eng_op." d.last_name ='".$filterEng_3."'";
					$eng_op=' or ';
				}

				if($filterEng_n!='')
				{
					$query.=$eng_op." d.last_name ='".$filterEng_n."'";
					$eng_op=' or ';
				}

				if($filterEng_l!='')
				{
					$query.=$eng_op." d.last_name ='".$filterEng_l."'";
					$eng_op=' or ';
				}
			}

			$query .= ';';
			
			//echo ' Total '.$query;
			// die();
			
		$result = mysqli_query($connecDB,$query);
             
 return $result;
}

function getGraphbyDates($connecDB, $date1, $date2, $filterSpeciality, $filterState, $vol) {

       $result = array();
	   
	   //zaid added on 08Jun2023
		$str_optin = 'OPTIN';		
		if($vol == 'VOL2')
		{
			$str_optin = 'CCMD';
		}
	   	   
	   $query = 'select COUNT(IF(s.status=\''.$str_optin.'\' or s.status=\'XFER\',1,null)) as count, DATE(s.event_time) as date from (SELECT phone_number,max(event_time) as event_time  FROM stagedispo where campaign_id = \''.$vol.'\' group by phone_number) as l inner join stagedispo s on   s.phone_number = l.phone_number and s.event_time = l.event_time and s.campaign_id = \''.$vol.'\' inner join doctors d on d.phone_number = s.phone_number ';
			
			if($date1 != '' && $date2 != '')
			{
				 $query .= 'where s.event_time >= \''.$date1.'\' and s.event_time <= \''.$date2.'\'';			
				
			}
			elseif($date1 != '')
			{
				 $query .= 'where s.event_time <= \''.$date1.'\''; 
			}
			if($filterSpeciality != '')
			{
				$query .= ' and d.Prefcallday = \''.$filterSpeciality.'\' ';
			}				
			if($filterState != '')
			{
				$query .= ' and d.state = \''.$filterState.'\' ';
			}
			
			$query .= ' group by DATE(s.event_time);';
			
			//echo ' Graph '.$query;
			
			
			$result_temp = mysqli_query($connecDB,$query);
				
				while($row = $result_temp->fetch_assoc()) {
					$result[] = $row;
				}

 return $result;
}


// ibrahim commented
// function getListCountbyDates($connecDB, $date1, $date2, $FilterBy, $filterSpeciality, $filterState, $vol,$filter_consent,$filterby_agent) 
// {

// 		$result = array();
	   	   
// 	   //zaid added on 08Jun2023
// 		$str_optin = 'OPTIN';		
// 		if($vol == 'VOL3')
// 		{
// 			$str_optin = 'VERFD';
// 		}

// 		$consent_query='';	
// 		$consent_where='';
// 		// echo $filter_consent;
// 	   if($filter_consent != '') 
// 		{
// 			 $consent_query .= "LEFT JOIN consent con ON a.reg_no = con.uniqueid ";

// 			 if($filter_consent=='OAD')
// 					$consent_where .= ' and con.therapy =\'OAD\' ';

// 				elseif($filter_consent=='INSULIN')
// 					$consent_where .= ' and con.therapy =\'INSULIN\' ';

// 				elseif($filter_consent=='BOTH')
// 					$consent_where .= ' and con.therapy =\'BOTH\' ';

// 				elseif($filter_consent=='ecert')
// 					$consent_where .= ' and (con.cert_email =1 or  con.cert_sms =1) ';

// 				elseif($filter_consent=='catlog')
// 					$consent_where .= ' and (con.catlog_email=1 or  con.catlog_sms =1) ';

// 				elseif($filter_consent=='campus')
// 					$consent_where .= ' and (con.campus_email =1 or con.campus_sms=1 ) ';
				
// 				elseif($filter_consent=='whatsapp')
// 				$consent_where .= ' and con.whatsapp=\'YES\' ';		
// 		}
	   

// 	    $query = "select  count(distinct(a.did)) as totalrec  from doctors a inner join stagedispo b on a.phone_number = b.phone_number ".$consent_query." where a.first_name != '' ".$consent_where."and ";
			
// 		if(	$FilterBy!='')
// 		{
// 			 $query = "select  count(distinct(a.did)) as totalrec  from doctors a inner join  stagedispo b on a.phone_number = b.phone_number 
// 			  inner join (
// 			    SELECT phone_number,max(event_time)   as event_time FROM stagedispo where campaign_id = '".$vol."' group by phone_number ) as l on  b.phone_number = l.phone_number and b.event_time = l.event_time ".$consent_query." where a.first_name != '' ".$consent_where."and ";
			
// 		}
		
// 		$query .=  " b.campaign_id = '".$vol."' and ";
			
// 			if($date1 != '' && $date2 != '')
// 			{
// 				 $query .= 'b.event_time >= \''.$date1.'\' and b.event_time <= \''.$date2.'\'';			
				
// 			}
// 			elseif($date1 != '')
// 			{
// 				 $query .= 'b.event_time <= \''.$date1.'\''; 
// 			}
// 			//added on 21Dec2022
// 			if($FilterBy!='')
// 			{
// 				if($FilterBy == 'OPTIN')
// 				{
// 					$query .= ' and b.status in(\''.$str_optin.'\',\'XFER\')';
// 				}
// 				else if ($FilterBy == 'SWCHOF')
// 				{ 
// 					$query .= ' and b.status not in(\''.$str_optin.'\',\'NOT\',\'CALLBK\',\'UNDEC\',\'XFER\')';
// 				}				
// 				else
// 				{
// 					$query .= ' and b.status = \''.$FilterBy.'\'';
// 					//$query .= ' and b.status = \''.$FilterBy.'\'';
// 				}
// 			}
// 			//Added on 05Jan2023
// 			if($filterSpeciality != '')
// 			{
// 				$query .= ' and a.Prefcallday = \''.$filterSpeciality.'\' ';
// 			}				
// 			if($filterState != '')
// 			{
// 				$query .= ' and a.state = \''.$filterState.'\' ';
// 			}
			
// 			// $filterby_agent='agent1';
// 			if($filterby_agent != '')
// 			{
// 				$query .= ' and b.user = \''.$filterby_agent.'\' ';
// 			}


// 			$query .= " ;";
// 			// echo $query; 
// 			$result_temp = mysqli_query($connecDB,$query);
				
// 				while($row = $result_temp->fetch_assoc()) {
// 					$result[] = $row;
// 				}

//  return $result;
// }


function getListCountbyDates($connecDB, $date1, $date2, $FilterBy, $filterSpeciality, $filterState, $vol,$filter_consent,$filterby_agent,$filterEng_all,$filterEng_4,$filterEng_3,$filterEng_n,$filterEng_l,$filterby_dslt,$filterby_dslt_check) 
{		   
	$result = array();

	if($filterby_dslt!='')
	{
		$query =" select count(*)  as totalrec  from ( select sl.mobile as phone, sl.sent_at as date_time, d.email as email,'' as status from sms_log_details sl inner join doctors d on d.phone_number = sl.mobile where sl.id_sms in (select max(id_sms) from sms_log_details group by mobile) UNION all select st.phone_number as phone, st.event_time as date_time, d.email as email,st.status as status from stagedispo st inner join doctors d on st.phone_number=d.phone_number where st.status in ('OPTIN','CCMD','XEFR','SHORT') and id in (select max(id) from stagedispo where status in('OPTIN','CCMD','XEFR','SHORT') group by phone_number) UNION all select d.phone_number as phone, el.sent_dt as date_time, el.email as email,'' as status from email_send_log el inner join doctors d on d.email = el.email where el.id_email in (select max(id_email) from email_send_log group by email))AS ls where ls.status in('OPTIN','CCMD','XEFR','SHORT') ";
		
		if($filterby_dslt_check=='1')
		{
			$query.= " and ls.date_time <= '".$filterby_dslt."' and ls.date_time >=DATE_ADD('".$filterby_dslt."', INTERVAL -20 DAY) ";
		}
		else
		{
			$query.= " and ls.date_time <= '".$filterby_dslt."' ";
		}
				

	}
	elseif($filter_consent != '') 
	{
		if($filter_consent=='OAD')
		$consent_where .= ' where con.therapy =\'OAD\' ';

		elseif($filter_consent=='INSULIN')
			$consent_where .= ' where con.therapy =\'INSULIN\' ';

		elseif($filter_consent=='BOTH')
			$consent_where .= ' where con.therapy =\'BOTH\' ';

		elseif($filter_consent=='ecert')
			$consent_where .= ' where (con.cert_email =1 or  con.cert_sms =1) ';

		elseif($filter_consent=='catlog')
			$consent_where .= ' where (con.catlog_email=1 or  con.catlog_sms =1) ';

		elseif($filter_consent=='campus')
			$consent_where .= ' where (con.campus_email =1 or con.campus_sms=1 ) ';
		
		elseif($filter_consent=='whatsapp')
		$consent_where .= ' where whatsapp=\'YES\' ';
	
		$query ="select count(distinct(a.did)) as totalrec from doctors a  inner JOIN consent con ON a.reg_no = con.uniqueid ".$consent_where." and a.first_name != '' ";
	}
	else
	{
		$result = array();
	   	   
	   //zaid added on 08Jun2023
		$str_optin = 'OPTIN';		
		if($vol == 'VOL3')
		{
			$str_optin = 'VERFD';
		}


	    $query = "select  count(distinct(a.did)) as totalrec  from doctors a inner join stagedispo b on a.phone_number = b.phone_number where a.first_name != '' and ";
			
		if(	$FilterBy!='')
		{
			 $query = "select  count(distinct(a.did)) as totalrec  from doctors a inner join  stagedispo b on a.phone_number = b.phone_number 
			  inner join (
			    SELECT phone_number,max(event_time)   as event_time FROM stagedispo where campaign_id = '".$vol."' group by phone_number ) as l on  b.phone_number = l.phone_number and b.event_time = l.event_time  where a.first_name != '' and ";
			
		}
		
		$query .=  " b.campaign_id = '".$vol."' and ";
			
			if($date1 != '' && $date2 != '')
			{
				 $query .= 'b.event_time >= \''.$date1.'\' and b.event_time <= \''.$date2.'\'';			
				
			}
			elseif($date1 != '')
			{
				 $query .= 'b.event_time <= \''.$date1.'\''; 
			}
			//added on 21Dec2022
			if($FilterBy!='')
			{
				if($FilterBy == 'OPTIN')
				{
					$query .= ' and b.status in(\''.$str_optin.'\',\'XFER\')';
				}
				else if ($FilterBy == 'SWCHOF')
				{ 
					//ibrahim commented and added new dispo to the status 29-07-2024
					//$query .= ' and b.status not in(\''.$str_optin.'\',\'NOT\',\'CALLBK\',\'UNDEC\',\'XFER\')';
					$query .= ' and b.status not in(\''.$str_optin.'\',\'NOT\',\'CALLBK\',\'UNDEC\',\'XFER\',\'COVERD\',\'DECSD\',\'INVD\',\'NOPRAC\',\'NOTDR\',\'WRNG\')';
				}				
				else if ($FilterBy == 'UNUSB')
				{ 
					//ibrahim added new dispo to the status 29-07-2024
					$query .= ' and b.status  in(\'COVERD\',\'DECSD\',\'INVD\',\'NOPRAC\',\'NOTDR\',\'WRNG\')';
				}
				else if ($FilterBy == 'UNRC')
				{ 
					//ibrahim added new dispo to the status 13-08-2024
					$query .= ' and b.status not in(\'CCMD\',\'SHORT\',\'INVD\',\'NOT\',\'UNDEC\') ';

				}
				else
				{
					$query .= ' and b.status = \''.$FilterBy.'\'';
				}
			}
			//Added on 05Jan2023
			if($filterSpeciality != '')
			{
				$query .= ' and a.Prefcallday = \''.$filterSpeciality.'\' ';
			}				
			if($filterState != '')
			{
				$query .= ' and a.state = \''.$filterState.'\' ';
			}
			
			// $filterby_agent='agent1';
			if($filterby_agent != '')
			{
				$query .= ' and b.user = \''.$filterby_agent.'\' ';
			}

			
			if($filterEng_all=='')
			{
				$eng_op=' and ';
				if($filterEng_4!='')
				{
					$query.=$eng_op." a.last_name ='".$filterEng_4."'";
					$eng_op=' or ';
				}
				if($filterEng_3!='')
				{
					$query.=$eng_op." a.last_name ='".$filterEng_3."'";
					$eng_op=' or ';
				}

				if($filterEng_n!='')
				{
					$query.=$eng_op." a.last_name ='".$filterEng_n."'";
					$eng_op=' or ';
				}

				if($filterEng_l!='')
				{
					$query.=$eng_op." a.last_name ='".$filterEng_l."'";
					$eng_op=' or ';
				}
			}


	}

			$query .= " ;";
			//   echo $query; 
			$result_temp = mysqli_query($connecDB,$query);
				
				while($row = $result_temp->fetch_assoc()) {
					$result[] = $row;
				}

 return $result;
}



// ibrahim commented

// function getListbyDates($connecDB, $date1, $date2,$pg=0, $FilterBy, $filterSpeciality, $filterState, $vol,$searchby,$filterby_agent,$filter_consent,$export_flag) 
// {

// 			$que_temp = ' where ';
// 			if($FilterBy != '')
// 			{
// 				$que_temp = ', (SELECT phone_number,max(event_time)  as event_time FROM stagedispo where campaign_id = \''.$vol.'\' group by phone_number ) as l where ';
// 			}				

		
//        $result = array();
	   
// 	   //zaid added on 08Jun2023
// 		$str_optin = 'OPTIN';		
// 		if($vol == 'VOL3')
// 		{
// 			$str_optin = 'VERFD';
// 		}
	   
// 		//ibrahim added on 20-06-2024
// 		$consent_query='';	
// 		$consent_where='';	
// 	   if($filter_consent != '') 
// 		{
// 			 $consent_query .= "inner JOIN consent con ON a.reg_no = con.uniqueid ";

// 			 if($filter_consent=='OAD')
// 					$consent_where .= ' and con.therapy =\'OAD\' ';

// 				elseif($filter_consent=='INSULIN')
// 					$consent_where .= ' and con.therapy =\'INSULIN\' ';

// 				elseif($filter_consent=='BOTH')
// 					$consent_where .= ' and con.therapy =\'BOTH\' ';

// 				elseif($filter_consent=='ecert')
// 					$consent_where .= ' and (con.cert_email =1 or  con.cert_sms =1) ';

// 				elseif($filter_consent=='catlog')
// 					$consent_where .= ' and (con.catlog_email=1 or  con.catlog_sms =1) ';

// 				elseif($filter_consent=='campus')
// 					$consent_where .= ' and (con.campus_email =1 or con.campus_sms=1 ) ';
				
// 				elseif($filter_consent=='whatsapp')
// 				$consent_where .= ' and whatsapp=\'YES\' ';		
// 		}

// 		$export_query='';
//		$consent_query='';
// 		if($export_flag=='export')
// 		{
// 			$export_query .='con.*, ';
// 			$consent_query .= "LEFT JOIN consent con ON a.reg_no = con.uniqueid ";
// 			

// 		}

// 	   $query = "select a.*,".$export_query."(select count(phone_number) from stagedispo c where c.phone_number = b.phone_number group by phone_number) as total_calls ,(select status from stagedispo c where c.phone_number = b.phone_number order by c.event_time desc limit 1)  as last_status ,r.relation , r.rel_id, (select comments from stagedispo c where c.phone_number = b.phone_number order by c.event_time desc limit 1)  as last_comment from doctors a inner join stagedispo b on a.phone_number = b.phone_number left join relation_ship r on a.phone_number = r.mobile ".$consent_query.$que_temp."  b.campaign_id = '".$vol."' ".$consent_where." and ";

	   
			
// 			if($vol!= 'VOL3' && $FilterBy != 'NOT' )
// 			{
// 				$query .= " a.first_name != '' and ";
// 			}
			
			
// 			if($date1 != '' && $date2 != '')
// 			{
// 				 $query .= 'b.event_time >= \''.$date1.'\' and b.event_time <= \''.$date2.'\'';			
				
// 			}
// 			elseif($date1 != '')
// 			{
// 				 $query .= 'b.event_time <= \''.$date1.'\''; 
// 			}
			
			
// 			if($FilterBy != '')
// 			{	
// 				if($FilterBy == 'OPTIN')
// 					$query .= ' and b.status in(\''.$str_optin.'\',\'XFER\') and b.phone_number = l.phone_number and b.event_time = l.event_time ';
// 				else if ($FilterBy == 'SWCHOF') 
// 					$query .= ' and b.status not in(\''.$str_optin.'\',\'NOT\',\'CALLBK\',\'UNDEC\',\'XFER\') and b.phone_number = l.phone_number and b.event_time = l.event_time ';
// 				else
// 					$query .= ' and b.status = \''.$FilterBy.'\' and b.phone_number = l.phone_number and b.event_time = l.event_time ';
// 			}

// 			//zaid added on 05Jan2023
// 			if($filterSpeciality != '')
// 			{
// 				$query .= ' and a.Prefcallday = \''.$filterSpeciality.'\' ';
// 			}				
// 			if($filterState != '')
// 			{
// 				$query .= ' and a.state = \''.$filterState.'\' ';
// 			}

			
// 			// $filterby_agent='agent1';
// 			if($filterby_agent != '')
// 			{
// 				$query .= ' and b.user = \''.$filterby_agent.'\' ';
// 			}

					
// 			if($searchby != '')
// 			{
// 				$query .= ' and (a.first_name like \'%'.$searchby.'%\' || a.phone_number = \''.$searchby.'\') ';
// 			}	
			
// 			$query .= " group by a.did  order by b.event_time desc"; //a.first_name asc
// 			if($pg != -1)
// 			{
// 				$pg *=100;
// 				$query .= " limit $pg,100;";
// 			}
// 			 //echo $query;
// 			//exit;
			
// 			$result_temp = mysqli_query($connecDB,$query);
				
// 				while($row = $result_temp->fetch_assoc()) {
// 					$result[] = $row;
// 				}
				 
//  return $result;
// }


function getListbyDates($connecDB, $date1, $date2,$pg=0, $FilterBy, $filterSpeciality, $filterState, $vol,$searchby,$filterby_agent,$filter_consent,$export_flag,$filterEng_all,$filterEng_4,$filterEng_3,$filterEng_n,$filterEng_l,$filterby_dslt,$filterby_dslt_check) 
{

	$result = array();

	   
		if($filterby_dslt!='')
		{
			$query="select d.*,b.status as last_status,b.comments as last_comment from ( 
				select sl.mobile as phone, sl.sent_at as date_time, d.email as email,'' as status,'SMS' from sms_log_details sl inner join doctors d on d.phone_number = sl.mobile where sl.id_sms in (select max(id_sms) from sms_log_details group by mobile)
			UNION all
				select st.phone_number as phone, st.event_time as date_time, d.email as email,st.status as status,'phone' from stagedispo st inner join doctors d on st.phone_number=d.phone_number where st.status in ('OPTIN','CCMD','XEFR','SHORT') and id in (select max(id) from stagedispo where status in('OPTIN','CCMD','XEFR','SHORT') group by phone_number)
			UNION all 
				select d.phone_number as phone, el.sent_dt as date_time, el.email as email,'' as status,'EMAIL' from email_send_log el inner join doctors d on d.email = el.email where el.id_email in (select max(id_email) from email_send_log group by email))
			AS ls inner join doctors d on ls.phone=d.phone_number left join stagedispo b on d.phone_number = b.phone_number   where ls.status in('OPTIN','CCMD','XEFR','SHORT')";
			
			if($filterby_dslt_check=='1')
			{
				$query.= " and ls.date_time <= '".$filterby_dslt."' and ls.date_time >=DATE_ADD('".$filterby_dslt."', INTERVAL -20 DAY) ";
			}
			else
			{
				$query.= " and ls.date_time <= '".$filterby_dslt."' ";
			}

			$query.= " group by d.did ";

		}
		elseif($filter_consent != '') 
		{	
			$export_query ='';
			if($export_flag=='export')
				{
					$export_query .=', con.*, (select count(phone_number) from stagedispo where phone_number = a.phone_number) as total_calls ';
				}
			
			$consent_where='';
						 if($filter_consent=='OAD')
								$consent_where .= ' where con.therapy =\'OAD\' ';
			
							elseif($filter_consent=='INSULIN')
								$consent_where .= ' where con.therapy =\'INSULIN\' ';
			
							elseif($filter_consent=='BOTH')
								$consent_where .= ' where con.therapy =\'BOTH\' ';
			
							elseif($filter_consent=='ecert')
								$consent_where .= ' where (con.cert_email =1 or  con.cert_sms =1) ';
			
							elseif($filter_consent=='catlog')
								$consent_where .= ' where (con.catlog_email=1 or  con.catlog_sms =1) ';
			
							elseif($filter_consent=='campus')
								$consent_where .= ' where (con.campus_email =1 or con.campus_sms=1 ) ';
							
							elseif($filter_consent=='whatsapp')
							$consent_where .= ' where whatsapp=\'YES\' ';

			// $query ="select a.*, (select comments from stagedispo c where c.phone_number = a.phone_number order by c.event_time desc limit 1) as last_comment from doctors a  inner JOIN consent con ON a.reg_no = con.uniqueid ".$consent_where." and a.first_name != ''  group by a.did ";

			//(select count(phone_number) from stagedispo where phone_number = a.phone_number) as total_calls
			//(select comments from stagedispo c where c.phone_number = b.phone_number order by c.event_time desc limit 1) as last_comment

			$query ="select a.*".$export_query." from doctors a  inner JOIN consent con ON a.reg_no = con.uniqueid ".$consent_where." and a.first_name != ''  group by a.did ";
			

		}
		else
		{
			$que_temp = 'where';
			if($FilterBy != '')
			{
				$que_temp = 'INNER JOIN  (SELECT phone_number, MAX(id) AS max_id, max(event_time)  as event_time FROM stagedispo  WHERE campaign_id = \''.$vol.'\'  GROUP BY phone_number) as l ON b.phone_number = l.phone_number AND b.id = l.max_id WHERE ';
			}				
		
		  
		   //zaid added on 08Jun2023
			$str_optin = 'OPTIN';		
			if($vol == 'VOL3')
			{
				$str_optin = 'VERFD';
			}
			
				$export_query='';
				$expoert_join='';
				if($export_flag=='export')
				{
					$export_query .='con.*, ';
					$expoert_join .= "LEFT JOIN consent con ON a.reg_no = con.uniqueid ";	
				}
			
			
			$query = "select a.*,a.email as email_id,".$export_query."(select count(phone_number) from stagedispo  where phone_number = b.phone_number) as total_calls,b.status as last_status,b.comments as last_comment  from doctors a inner join stagedispo b on a.phone_number = b.phone_number ".$expoert_join.$que_temp." b.campaign_id =  '".$vol."' and ";
	
	
			if($vol!= 'VOL3' && $FilterBy != 'NOT' )
				{
					$query .= " a.first_name != '' and ";
				}
				
				
				if($date1 != '' && $date2 != '')
				{
					 $query .= 'b.event_time >= \''.$date1.'\' and b.event_time <= \''.$date2.'\'';			
					
				}
				elseif($date1 != '')
				{
					 $query .= 'b.event_time <= \''.$date1.'\''; 
				}
				
				
				if($FilterBy != '')
				{	
					if($FilterBy == 'OPTIN')
					{
						$query .= ' and b.status in(\''.$str_optin.'\',\'XFER\') and b.phone_number = l.phone_number and b.event_time = l.event_time ';
					}	
					else if ($FilterBy == 'SWCHOF') 
					{
						$query .= ' and b.status not in(\''.$str_optin.'\',\'NOT\',\'CALLBK\',\'UNDEC\',\'XFER\',\'COVERD\',\'DECSD\',\'INVD\',\'NOPRAC\',\'NOTDR\',\'WRNG\') and b.phone_number = l.phone_number and b.event_time = l.event_time ';
					}
					else if ($FilterBy == 'UNUSB') 
					{
						$query .= ' and b.status  in(\'COVERD\',\'DECSD\',\'INVD\',\'NOPRAC\',\'NOTDR\',\'WRNG\') and b.phone_number = l.phone_number and b.event_time = l.event_time ';
					}
					else if ($FilterBy == 'UNRC') 
					{
						$query .= ' and b.status not in(\'CCMD\',\'SHORT\',\'INVD\',\'NOT\',\'UNDEC\') and b.phone_number = l.phone_number and b.event_time = l.event_time ';
					}
					else
					{
						$query .= ' and b.status = \''.$FilterBy.'\' and b.phone_number = l.phone_number and b.event_time = l.event_time ';
					}
				}
	
				//zaid added on 05Jan2023
				if($filterSpeciality != '')
				{
					$query .= ' and a.Prefcallday = \''.$filterSpeciality.'\' ';
				}				
				if($filterState != '')
				{
					$query .= ' and a.state = \''.$filterState.'\' ';
				}
	
	
				// $filterby_agent='agent1';
				if($filterby_agent != '')
				{
					$query .= ' and b.user = \''.$filterby_agent.'\' ';
				}
	
						
				if($searchby != '')
				{
					$query .= ' and (a.first_name like \'%'.$searchby.'%\' || a.phone_number = \''.$searchby.'\') ';
				}	


				if($filterEng_all=='')
			{
				$eng_op=' and ';
				if($filterEng_4!='')
				{
					$query.=$eng_op."a.last_name ='".$filterEng_4."'";
					$eng_op=' or ';
				}
				if($filterEng_3!='')
				{
					$query.=$eng_op."a.last_name ='".$filterEng_3."'";
					$eng_op=' or ';
				}

				if($filterEng_n!='')
				{
					$query.=$eng_op."a.last_name ='".$filterEng_n."'";
					$eng_op=' or ';
				}

				if($filterEng_l!='')
				{
					$query.=$eng_op."a.last_name ='".$filterEng_l."'";
					$eng_op=' or ';
				}
			}
				
				$query .= " group by a.did  order by b.event_time desc"; 
		}
	
	
			if($pg != -1)
			{
				$pg *=100;
				$query .= " limit $pg,100;";
			}
			//   echo $query;
			//exit;
			
			$result_temp = mysqli_query($connecDB,$query);
				
				while($row = $result_temp->fetch_assoc()) {
					$result[] = $row;
				}
				 
return $result;

}




function getCallsbyDatesExport($connecDB,  $phone, $vol) {

       $result = array();
						
			$query = 'select event_time,comments,status from stagedispo where ';
			
			/*if($date1 != '' && $date2 != '')
			{
				 $query .= 'event_time >= \''.$date1.'\' and event_time <= \''.$date2.'\'';			
				
			}
			elseif($date1 != '')
			{
				 $query .= 'event_time <= \''.$date1.'\''; 
			}*/
			
			$query .= ' phone_number = \''.$phone.'\' and campaign_id = \''.$vol.'\' order by event_time desc limit 1;';
			//echo $query; exit();
			
			$result_temp = mysqli_query($connecDB,$query);
				
				while($row = $result_temp->fetch_assoc()) {
					$result[] = $row;
				}
			

 return $result;
}



function getCallsbyDates($connecDB, $date1, $date2, $phone, $vol) {

       $result = array();
						
			$query = 'select * from stagedispo where ';
			
			if($date1 != '' && $date2 != '')
			{
				 $query .= 'event_time >= \''.$date1.'\' and event_time <= \''.$date2.'\'';			
				
			}
			elseif($date1 != '')
			{
				 $query .= 'event_time <= \''.$date1.'\''; 
			}
			
			$query .= ' and phone_number = \''.$phone.'\' and campaign_id = \''.$vol.'\' order by event_time desc;';
			//echo $query; exit();
			
			$result_temp = mysqli_query($connecDB,$query);
				
				while($row = $result_temp->fetch_assoc()) {
					$result[] = $row;
				}
			

 return $result;
}






function processEmail( $login_id, $temp_otp){
					$message = "Your One Time Password(OTP) for login to Medical Assistant is".$temp_otp;
					$subject = "OTP Verification";
					$header = "From: medicalassistant@gmail.com";
					
					$mailto = mail($login_id, $subject, $message, $header);
					if($mailto){
						echo "OTP successfully send to ".$login_id;
					}
					return $mailto;

}


function sendSMS($mobile,$otp){ 

	$sms = $otp . " is the One Time Password (OTP) to verify your login to the dashboard - Andesoft";
	$id_templet='1707166962162050200';
	$id_pe='1701159108843787083';
	
	
	try
	{
		
		$url = "https://mdssend.in/api.php?username=".urlencode('Andesoft')."&apikey=kcDga4VZjQ6U&senderid=".urlencode('ANDESO')."&route=TRANS&mobile=".urlencode($mobile)."&text=".urlencode($sms)."&TID=".urlencode($id_templet)."&PEID=".$id_pe;
		
		//$url = "http://makemysms.in//api//sendsms.php?username=" . urlencode('Andesoft') . "&password=" . urlencode('Andesoft1') . "&sender=" . urlencode('ANDESO') . "&mobile=" . urlencode($mobile) . "&type=" . urlencode('1') . "&product=" . urlencode('1') ."&template=". urlencode($id_templet). "&message=" . urlencode($sms);
	  

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$curl_scraped_page = curl_exec($ch);
		curl_close($ch);
		
	}
	catch(Exception $e)
	{
		
	}
	
}

function pageList($connecDB){
	$pn= 1;
	$limit = 100;
	$intial = ($pn-1)*$limit;

	$qry = 'SELECT * FROM `doctors`';
	$result = mysqli_query($connecDB, $qry);

	$rows = mysqli_num_rows($result);
	$pages = ceil($rows/$limit);
	$getQry= 'SELECT * FROM `doctors` LIMIT '.$intial.','.$limit;
	$final = mysqli_query($connecDB, $getQry);

	while($row = mysqli_fetch_assoc($final)){
		

	}



}

function getLastUpdatedDate($connecDB,$type)
{
	$result = array();
						
			$query = 'select * from import_logs where importtype ='.$type.' order by time_stamp desc limit 1;';			 
			
			$result_temp = mysqli_query($connecDB,$query);
				
				while($row = $result_temp->fetch_assoc()) {
					$result[] = $row;
				}
			

 return $result;
}

//Added on 04Jan2023 by sarath

function getMasterByField($connecDB, $field)
{
	$result = array();
						
			$query = 'SELECT distinct '.$field.' FROM `doctors` order by '.$field.';';			 
			
			$result_temp = mysqli_query($connecDB,$query);
				
				while($row = $result_temp->fetch_assoc()) {
					$result[] = $row;
				}
			

 return $result;
}

//zaid added on 25Jan2023
function updateDisposition($connecDB, $id, $dispo)
{
	$result_temp = array();
	
	if($id != '' && $dispo != '')
	{
		$query= 'update stagedispo set status = \''.$dispo.'\' where id = '.$id.';';
		$result_temp = mysqli_query($connecDB,$query);
	} 
	
	return $result_temp; 
}

//RM   on 220230526 

function updateRelation($connecDB, $rel_id, $relation,$mobile)
{
	$result_temp = array();
	
	
		$query= 'update doctors set qualification = \''.$relation.'\' where phone_number = '.$mobile.';';

		$result_temp = mysqli_query($connecDB,$query);
	
	
	return $result_temp;
}



function getConsentList($connecDB,$uquid = '' )
{


	$result = array();


		$query= 'select * from consent ';
		if( $uquid!='' ) 
		$query .=  " where uniqueid='".$uquid."'ORDER BY id_consent DESC LIMIT 1";
		 // return $query;select * from consent where uniqueid = "AA71484" ORDER BY id_consent DESC LIMIT 1;
		$result_temp = mysqli_query($connecDB,$query);

		while($row = $result_temp->fetch_assoc()) {
			$result[] = $row;
		}
	 
	   return $result;
}


function getsmsCount($connecDB,$mobile='')
{
	$result_temp = array();
	 $get_sms_details =" select l.*, (select group_concat(sms_link) from sms_template_master where template_id = l.template_id order by sms_link desc) as sms_link, (select group_concat(linkcnt) from ( select concat(link,'-',count(*)) as linkcnt, reg_no  from smslink_log log  group by reg_no,link order by link desc  ) as t1 where t1.reg_no = d.reg_no ) as linkclicks  from sms_log_details l inner join doctors d on d.phone_number = l.mobile where l.mobile='".$mobile."' ;";
	
	$result = mysqli_query($connecDB,$get_sms_details);

	return  mysqli_fetch_all($result, MYSQLI_ASSOC);
}



function getSmsLog($connecDB,$phone = '')
{


	$result_temp = array();
		$query= 'select * from sms_log_details ';
		if( $phone!='' ) 
			$query .=  " where mobile='".$phone."'";
		// return $query;
		$result_temp = mysqli_query($connecDB,$query);
	 
	   return  mysqli_fetch_all($result_temp, MYSQLI_ASSOC); //$result_temp->fetch_all();
}

function insertSmsLog($connecDB,$phone,$template_id,$sender,$message,$status,$sent_at,$deliver_at,$count,$temp_name)
{
			 
		$insert_query ="INSERT INTO sms_log_details(mobile,template_id,sender,message,status,sent_at,dlr_time,click_count,template_name) 
			VALUES('".$phone."','".$template_id."','".$sender."','".$message."','".$status."','".$sent_at."','".$deliver_at."','".$count."','".$temp_name."')";
               

			// return $insert_query;
			 
			mysqli_query($connecDB, $insert_query);
}

//function updateDoctorsDetails($connecDB,$first_name,$phone,$alt_phone,$email,$address,$city,$state,$postal_code,$comments,$cohort,$Dnd_status,$dipso_id)


function updateDoctorsDetails($connecDB,$first_name,$phone,$alt_phone,$email,$address,$city,$state,$postal_code,$comments,$cohort,$Dnd_status)
{
	$result_doc = array();

	$update_doc = "update doctors SET first_name='".$first_name."',alt_phone='".$alt_phone."',email='".$email."',address1='".$address."',city='".$city."',state='".$state."',postal_code='".$postal_code."',comments='".$comments."',last_name='".$cohort."',network_status='".$Dnd_status."' where phone_number='".$phone."';";
	
	 
	$result_doc = mysqli_query($connecDB,$update_doc);
	
}

function updateStageDispoComment($connecDB,$comment,$id)
{
	$result_dispo = array();
	$update_dipso= "update stagedispo SET comments='".$comment."' where id='".$id."';";
	$result_dispo = mysqli_query($connecDB,$update_dipso);
	return $result_dispo;
}

function getConsentcount($connecDB)
{
	$result_temp = array();				
    
	$query="SELECT SUM(CASE WHEN cert_email = 1 OR cert_sms = 1 THEN 1 ELSE 0 END) AS ecert,SUM(CASE WHEN catlog_email = 1 OR catlog_sms = 1 THEN 1 ELSE 0 END) AS catlog, SUM(CASE WHEN campus_email = 1 OR campus_sms = 1 THEN 1 ELSE 0 END) AS campus, SUM(CASE WHEN whatsapp = 'Yes' THEN 1 ELSE 0 END) AS whatsapp_consent, SUM(CASE WHEN therapy = 'OAD' THEN 1 ELSE 0 END) AS therapy_oad, SUM(CASE WHEN therapy = 'INSULIN' THEN 1 ELSE 0 END) AS therapy_insulin, SUM(CASE WHEN therapy = 'BOTH' THEN 1 ELSE 0 END) AS therapy_both FROM ( SELECT DISTINCT c.uniqueid, c.cert_email, c.cert_sms, c.catlog_email, c.catlog_sms, c.campus_email, c.campus_sms, c.whatsapp, c.therapy FROM consent c INNER JOIN doctors d ON c.uniqueid = d.reg_no ) AS distinct_consents;";

	    // Execute the query
    $result = mysqli_query($connecDB, $query);
    $result_temp = mysqli_fetch_assoc($result); 
    return $result_temp;
}

function getAvgCallCount($connecDB)
{
	$result_temp = array();				
    
	$query='select AVG(call_duration) from stagedispo where status="OPTIN";';

    $result = mysqli_query($connecDB, $query);
	return  $result;
}

function getChemsitCount($connecDB)
{
	$result_temp = array();				
    
	$query = 'select count(*)as  Chemist_count from consent inner join doctors where doctors.phone_number=consent.phone and chemist!="";';

    $result = mysqli_query($connecDB, $query);
	return  $result;
}

 function updateDoctorsProfile($connecDB,$mobile,$profile,$link,$name)
 {
	$result_temp = array();
	
	if($mobile != '')
	{
		$query= 'update doctors set doc_profile = \''.$profile.'\',doc_link=\''.$link.'\',first_name=\''.$name.'\'  where phone_number = '.$mobile.';';
		 $result_temp = mysqli_query($connecDB,$query);
		

	} 
	
	return $result_temp; 
 }

//ibrahim added on 27-06-2024
function getprofile($connecDB,$phone,$reg_no) 
{

	$result = array();
					 
	 	 $query = 'select * from doctors where phone_number="'.$phone.'" and reg_no="'.$reg_no.'";';
		 //echo $query;  
		 $result_temp = mysqli_query($connecDB,$query);		 
		return  mysqli_fetch_all($result_temp, MYSQLI_ASSOC);

}

//ibrahim added on 10-07-2024
function insertEmailLog($connecDB,$email,$name,$subject,$sent_dt,$sent,$delivered,$open,$hard_bounced,$soft_bounced,$unsubscribed,$link1,$link2,$link3,$link4,$link5,$link6,$link7,$link8,$link9,$link10,$e_name)
{
			$insert_query ="INSERT INTO email_send_log(email,name,subject,sent_dt,sent,delivered,open,hard_bounced,soft_bounced,unsubscribed,link1,link2,link3,link4,link5,link6,link7,link8,link9,link10,email_name) 
			VALUES('".$email."','".$name."','".$subject."','".$sent_dt."','".$sent."','".$delivered."','".$open."','".$hard_bounced."','".$soft_bounced."','".$unsubscribed."','".$link1."','".$link2."','".$link3."','".$link4."','".$link5."','".$link6."','".$link7."','".$link8."','".$link9."','".$link10."','".$e_name."')";
               
			   //echo "<pre>",print_r($insert_query),"</pre>";   

			// return $insert_query;
			 
			   mysqli_query($connecDB, $insert_query);

}

//ibrahim added on 27-06-2024
 function getSmsbyDates($connecDB, $date1, $date2, $phone) {

	$result = array();
					 
		 $query = 'select * from sms_log_details where ';
		 
		 if($date1 != '' && $date2 != '')
		 {
			  $query .= 'DATE(sent_at) >= \''.$date1.'\' and DATE(sent_at) <= \''.$date2.'\'';			
			 
		 }
		 elseif($date1 != '')
		 {
			  $query .= 'DATE(sent_at) <= \''.$date1.'\''; 
		 }
		 
		 $query .= ' and mobile = \''.$phone.'\' order by sent_at desc;';
		 
		 //echo $query; exit();
		 
		 $result_temp = mysqli_query($connecDB,$query);
			 
			 while($row = $result_temp->fetch_assoc()) {
				 $result[] = $row;
			 }
		 

return $result;
}
//ibrahim added on 18-07-2024
function getEmailbyDates($connecDB,$date1, $date2,$email,$reg_no)
{
	$result_temp = array();
	 $get_sms_details ="select e.* from email_send_log e inner join doctors d on  d.email=e.email where e.email='".$email."' and d.reg_no='".$reg_no."'";

	 if($date1 != '' && $date2 != '')
	 {
		  $query .= ' and DATE(sent_dt) >= \''.$date1.'\' and DATE(sent_dt) <= \''.$date2.'\'';			
		 
	 }
	 elseif($date1 != '')
	 {
		  $query .= ' and DATE(sent_dt) <= \''.$date1.'\''; 
	 }
	 


	  $get_sms_details .=" ;";

	
	$result = mysqli_query($connecDB,$get_sms_details);

	return  mysqli_fetch_all($result, MYSQLI_ASSOC);
}


//ibrahim added on 10-07-2024
function getEmailcount($connecDB,$email,$reg_no)
{
	$result_temp = array();
	 $get_sms_details ="select e.* from email_send_log e inner join doctors d on  d.email=e.email where e.email='".$email."' and d.reg_no='".$reg_no."' ;";

	$result = mysqli_query($connecDB,$get_sms_details);

	return  mysqli_fetch_all($result, MYSQLI_ASSOC);
}


function getTotalEmail($connecDB,$date1,$date2,$filterEng_all,$filterEng_4,$filterEng_3,$filterEng_n,$filterEng_l,$filterEmail)
{
	$result_temp = array();



	 $query ="select SUM(total_sent) AS total_sent, SUM(total_delivered) AS total_delivered ,SUM(total_open) AS total_open, SUM(hard_bounced+soft_bounced) AS total_bounced,SUM(unsubscribed) AS unsubscribed,SUM(link1 + link2+ link3 + link4 + link5 + link6 + link7 + link8 + link9 + link10) AS total_clicks from ( SELECT (CASE WHEN sent != '0' THEN 1 ELSE 0 END) AS total_sent,
    (CASE WHEN delivered != '0' THEN 1 ELSE 0 END)  AS total_delivered,
    (CASE WHEN open != '0' THEN 1 ELSE 0 END)   AS total_open,
    (CASE WHEN hard_bounced != '0' THEN 1 ELSE 0 END)   AS hard_bounced,
    (CASE WHEN soft_bounced != '0' THEN 1 ELSE 0 END)   AS soft_bounced,
    (CASE WHEN unsubscribed != '0' THEN 1 ELSE 0 END)   AS unsubscribed,
    (CASE WHEN link1 = '1' THEN 1 ELSE 0 END)   AS link1,
	(CASE WHEN link2 ='1' THEN 1 ELSE 0 END)   AS link2,
    (CASE WHEN link3 ='1' THEN 1 ELSE 0 END)   AS link3,
	(CASE WHEN link4 ='1' THEN 1 ELSE 0 END)   AS link4,
	(CASE WHEN link5 ='1' THEN 1 ELSE 0 END)   AS link5,
	(CASE WHEN link6 ='1' THEN 1 ELSE 0 END)   AS link6,
	(CASE WHEN link7 ='1' THEN 1 ELSE 0 END)   AS link7,
	(CASE WHEN link8 ='1' THEN 1 ELSE 0 END)   AS link8,
	(CASE WHEN link9 ='1' THEN 1 ELSE 0 END)   AS link9,
	(CASE WHEN link10 ='1' THEN 1 ELSE 0 END)   AS link10
     FROM email_send_log el inner join doctors d on d.email = el.email";

	 if($date1 != '' && $date2 != '')
	 {
	 	$query .= '  where el.sent_dt    >= \''.$date1.'\' and el.sent_dt <= \''.$date2.'\'';			
	 }
	 elseif($date1 != '')
	 {
	 	$query .= '  where el.sent_dt   <= \''.$date1.'\''; 
	 }
	 
	 if($filterEng_all=='')
	 {
		
			$eng_op=' and ';
		if($filterEng_4!='')
		{
			$query.=$eng_op." d.last_name ='".$filterEng_4."'";
			$eng_op=' or ';
		}
		if($filterEng_3!='')
		{
			$query.=$eng_op." d.last_name ='".$filterEng_3."'";
			$eng_op=' or ';
		}

		if($filterEng_n!='')
		{
			$query.=$eng_op." d.last_name ='".$filterEng_n."'";
			$eng_op=' or ';
		}

		if($filterEng_l!='')
		{
			$query.=$eng_op." d.last_name ='".$filterEng_l."'";
			$eng_op=' or ';
		}
	 }

	 
	 if($filterEmail != '')
	 {
	 	$query .= " and el.email_name ='".$filterEmail."'";			
	 }

	 $query.=" group by el.email,el.subject,el.sent_dt) as r; ";

	//echo $query."<br>";
	
	$result = mysqli_query($connecDB,$query);

	return  mysqli_fetch_all($result, MYSQLI_ASSOC);
}


function getTotalSms($connecDB,$date1,$date2,$filterEng_all,$filterEng_4,$filterEng_3,$filterEng_n,$filterEng_l,$sCamp)
{
	$result_temp = array();

	 $query ="select sum(total_record)   total_record,    sum(sms_delivered)   sms_delivered,    sum(sms_failed)   sms_failed  , sum(sms_click) total_sms_clicked from ( select 1 as  total_record, case when status='Delivered' then 1 else 0 END as sms_delivered , case when status='Failed' then 1 else 0 END as sms_failed, case when smslink_log.reg_no is not null then 1 else 0 end as sms_click  from sms_log_details inner join doctors on doctors.phone_number=sms_log_details.mobile left join smslink_log on smslink_log.reg_no = doctors.reg_no ";

	 if($date1 != '' && $date2 != '')
	 {
	 	$query .= '  where sms_log_details.sent_at   >= \''.$date1.'\' and sms_log_details.sent_at <= \''.$date2.'\'';			
	 }
	 elseif($date1 != '')
	 {
	 	$query .= '  where sms_log_details.sent_at  <= \''.$date1.'\''; 
	 }

	//ibrahim added on 23-07-2024
	 

	 
	 if($filterEng_all=='')
	 {
		
			$eng_op=' and ';
		if($filterEng_4!='')
		{
			$query.=$eng_op." doctors.last_name ='".$filterEng_4."'";
			$eng_op=' or ';
		}
		if($filterEng_3!='')
		{
			$query.=$eng_op." doctors.last_name ='".$filterEng_3."'";
			$eng_op=' or ';
		}

		if($filterEng_n!='')
		{
			$query.=$eng_op." doctors.last_name ='".$filterEng_n."'";
			$eng_op=' or ';
		}

		if($filterEng_l!='')
		{
			$query.=$eng_op." doctors.last_name ='".$filterEng_l."'";
			$eng_op=' or ';
		}
	 }

	if($sCamp != '')
	{
		$query .= " and sms_log_details.template_name ='".$sCamp."'";			
	}

	 
	$query.="  group by sms_log_details.mobile,sms_log_details.template_id ) as at ;";

	//echo $query."<br>";
	
	$result = mysqli_query($connecDB,$query);

	return  mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// ibrahim added on 19-07-2024
function updateSmsLog($connecDB,$phone,$template_id,$sender,$message,$status,$sent_at,$deliver_at,$count,$temp_name)
{
			 
		$insert_query ="UPDATE sms_log_details set sender='".$sender."',message='".$message."',status='".$status."',sent_at='".$sent_at."',dlr_time='".$deliver_at."',click_count='".$count."',template_name='".$temp_name."' where  mobile='".$phone."' and template_id='".$template_id."' ;";
		
		
		//return $insert_query;
			 
			mysqli_query($connecDB, $insert_query);
}

function updateEmailLog($connecDB,$email,$name,$subject,$sent_dt,$sent,$delivered,$open,$hard_bounced,$soft_bounced,$unsubscribed,$link1,$link2,$link3,$link4,$link5,$link6,$link7,$link8,$link9,$link10,$e_name)
{
			 
	$update_query = "UPDATE email_send_log set name='".$name."', sent_dt='".$sent_dt."',sent='".$sent."',delivered='".$delivered."',open='".$open."',hard_bounced='".$hard_bounced."',soft_bounced='".$soft_bounced."',unsubscribed='".$unsubscribed."',link1='".$link1."',link2='".$link2."',link3='".$link3."',link4='".$link4."',link5='".$link5."',link6='".$link6."',link7='".$link7."',link8='".$link8."',link9='".$link9."',link10='".$link10."',email_name='".$e_name."'  where email='".$email."'  and subject='".$subject."';";
		
		//return $update_query;
			 
			mysqli_query($connecDB, $update_query);
}


//ibrahim added on 22-07-2024
function getSmsCampaign($connecDB)
{
	$result_temp = array();

	$query=" select distinct(template_name) from sms_log_details sl inner join doctors d on d.phone_number=sl.mobile ;";

	$result_temp = mysqli_query($connecDB,$query);		 
	return  mysqli_fetch_all($result_temp, MYSQLI_ASSOC);
}

function getEmailCampaign($connecDB)
{
	$result_temp = array();

	$query="select distinct(email_name) from email_send_log inner join doctors d on d.email=email_send_log.email ";

	$result_temp = mysqli_query($connecDB,$query);		 
	return  mysqli_fetch_all($result_temp, MYSQLI_ASSOC);
}

function getDsltcount($connecDB,$date,$dslt)
{
	$result_temp = array();

	
	
	 //$query="select count(*) from ( select phone, date_time, email from (select sl.mobile as phone, sl.sent_at as date_time, d.email as email from sms_log_details sl inner join doctors d on d.phone_number = sl.mobile where sl.sent_at <= '".$date."' and sl.sent_at>= DATE_ADD('".$date."', INTERVAL -".$dslt." DAY) group by sl.mobile,sl.template_id  UNION all select st.phone_number as phone, st.event_time as date_time, d.email as email from stagedispo st inner join doctors d on d.phone_number = st.phone_number where st.event_time <= '".$date."' and st.event_time>= DATE_ADD('".$date."', INTERVAL -".$dslt." DAY) group by st.phone_number UNION all select d.phone_number as phone, el.sent_dt as date_time, el.email as email from email_send_log el inner join doctors d on d.email = el.email where el.sent_dt <= '".$date."' and el.sent_dt>= DATE_ADD('".$date."', INTERVAL -".$dslt." DAY)  group by el.email, el.subject  )as dsl group by phone,email )  AS ls inner join stagedispo b on ls.phone = b.phone_number WHERE b.campaign_id = 'VOL1' and b.status in('OPTIN','XFER')  and b.event_time <= '".$date."'  and b.event_time>=DATE_ADD('".$date."', INTERVAL -".$dslt." DAY) order by b.event_time desc;";

	 $query=" select count(*) from (
		select sl.mobile as phone, sl.sent_at as date_time, d.email as email,'' as status from sms_log_details sl inner join doctors d on d.phone_number = sl.mobile where sl.id_sms in (select max(id_sms) from sms_log_details group by mobile)
		UNION all
		select st.phone_number as phone, st.event_time as date_time, d.email as email,st.status as status  from stagedispo st inner join doctors d on  st.phone_number=d.phone_number  where st.status in ('OPTIN','CCMD','XEFR','SHORT') and id  in (select max(id) from stagedispo  where status in('OPTIN','CCMD','XEFR','SHORT') group by phone_number)  
		UNION all
		select d.phone_number as phone, el.sent_dt as date_time, el.email as email,'' as status from email_send_log el inner join doctors d on d.email = el.email where  el.id_email in (select max(id_email) from email_send_log group by email))AS ls where ls.status in('OPTIN','CCMD','XEFR','SHORT') ";
		
		if($date!='' && $dslt!='')
		{
			$query.=" and  ls.date_time <= '".$date."' and ls.date_time >=DATE_ADD('".$date."', INTERVAL -".$dslt." DAY) ";
		}

		if($date!='' && $dslt=='')
		{
			$query.="  and ls.date_time <= '".$date."' ";
		}
	
			$query.=" ; ";

	
	// echo $query."<br>";
	
	$result_temp = mysqli_query($connecDB,$query);		 
	return  mysqli_fetch_all($result_temp, MYSQLI_ASSOC);
}

function getstatecount($connecDB)
{
	$result_temp = array();

	$query="select d.state, count(*) as count from doctors d inner join stagedispo st on d.phone_number= st.phone_number where d.state!='' and st.status in('OPTIN','XFER') group by state order by count desc; ";

	$result_temp = mysqli_query($connecDB,$query);		 
	return  mysqli_fetch_all($result_temp, MYSQLI_ASSOC);
}

//ibrahim added on	12-08-2024
function getcampagin($connecDB)
{
	$result_temp = array();

	$query=" select distinct(campaign_id) from stagedispo where campaign_id!='';";

	$result_temp = mysqli_query($connecDB,$query);		 
	return  mysqli_fetch_all($result_temp, MYSQLI_ASSOC);
}

?>