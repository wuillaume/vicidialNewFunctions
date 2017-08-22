
<!-- SCRIPT TO TREAT DUPLICATES LEAD_ID on vicidial using phonenumber as main id -->

<!-- if Number of leads > 1
	init var leadmaster
	init var statusMaster, entrydateMaster,userMaster

	for (lead in list #list group together by phonenumber)
		if( leadMaster is null) -> then leadMaster=lead
		else

			if Status is sale then update statusMaster is sale
			if this.entrydate earlier 
			than
			 {
				entrydateMaster = this.entryDate
				userMaster = this.user
				userMaster = this.user
			}
				 



 -->


 <?php


$version = '2.12-86';
$build = '161020-1042';
$api_url_log = 0;

$startMS = microtime();

require("dbconnect_mysqli.php");
require("functions.php");

### If you have globals turned off uncomment these lines
$agent_user = "";
$date_start="";
$date_end="";
$disposition='';
$lead_id="";
$phonenumber="";
$methodChoice="";
$booleanAddReview="1";
if (isset($_GET["user"]))						{$user=$_GET["user"];}
	elseif (isset($_POST["user"]))				{$user=$_POST["user"];}
if (isset($_GET["pass"]))						{$pass=$_GET["pass"];}
	elseif (isset($_POST["pass"]))				{$pass=$_POST["pass"];}
if (isset($_GET["agent"]))						{$agent_user=$_GET["agent"];}
	elseif (isset($_POST["agent"]))				{$agent_user=$_POST["agent"];}
if (isset($_GET["date_start"]))						{$date_start=$_GET["date_start"];}
	elseif (isset($_POST["date_start"]))				{$date_start=$_POST["date_start"];}
if (isset($_GET["date_end"]))						{$date_end=$_GET["date_end"];}
	elseif (isset($_POST["date_end"]))				{$date_end=$_POST["date_end"];}
if (isset($_GET["disposition"]))						{$disposition=$_GET["disposition"];}
	elseif (isset($_POST["disposition"]))				{$disposition=$_POST["disposition"];}
if (isset($_GET["lead_id"]))						{$lead_id=$_GET["lead_id"];}
	elseif (isset($_POST["lead_id"]))				{$lead_id=$_POST["lead_id"];}
if (isset($_GET["phonenumber"]))						{$phonenumber=$_GET["phonenumber"];}
	elseif (isset($_POST["phonenumber"]))				{$phonenumber=$_POST["phonenumber"];}
if (isset($_GET["methodChoice"]))						{$methodChoice=$_GET["methodChoice"];}
	elseif (isset($_POST["methodChoice"]))				{$methodChoice=$_POST["methodChoice"];}
if (isset($_GET["booleanAddReview"]))						{$booleanAddReview=$_GET["booleanAddReview"];}
	elseif (isset($_POST["booleanAddReview"]))				{$booleanAddReview=$_POST["booleanAddReview"];}

// $date="date=2017-08-08";
$source= "TestGettingData";



echo $user;

header ("Content-type: text/html; charset=utf-8");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,custom_fields_enabled,pass_hash_enabled,agent_whisper_enabled,active_modules,auto_dial_limit FROM system_settings;";
$rslt=mysql_to_mysqli($stmt, $link);
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =				$row[0];
	$custom_fields_enabled =	$row[1];
	$SSpass_hash_enabled =		$row[2];
	$agent_whisper_enabled =	$row[3];
	$active_modules =			$row[4];
	$SSauto_dial_limit =		$row[5];
	# slightly increase limit value, because PHP somehow thinks 2.8 > 2.8
	$SSauto_dial_limit = ($SSauto_dial_limit + 0.001);
	}
##### END SETTINGS LOOKUP #####
###########################################

if ($non_latin < 1)
	{
	$DB=preg_replace('/[^0-9]/','',$DB);
	$user=preg_replace('/[^-_0-9a-zA-Z]/','',$user);
	$pass=preg_replace('/[^-_0-9a-zA-Z]/','',$pass);
	}
else
	{
	$user = preg_replace("/'|\"|\\\\|;|#/","",$user);
	$pass = preg_replace("/'|\"|\\\\|;|#/","",$pass);
	}


$USarea = 			substr($phone_number, 0, 3);
$USprefix = 		substr($phone_number, 3, 3);
if (strlen($hopper_priority)<1) {$hopper_priority=0;}
if ($hopper_priority < -99) {$hopper_priority=-99;}
if ($hopper_priority > 99) {$hopper_priority=99;}

$StarTtime = date("U");
$NOW_DATE = date("Y-m-d");
$NOW_TIME = date("Y-m-d H:i:s");
$CIDdate = date("mdHis");
$ENTRYdate = date("YmdHis");
$ip = getenv("REMOTE_ADDR");
$MT[0]='';
$api_script = 'non-agent';
$api_logging = 1;

$vicidial_list_fields = '|lead_id|vendor_lead_code|source_id|list_id|gmt_offset_now|called_since_last_reset|phone_code|phone_number|title|first_name|middle_initial|last_name|address1|address2|address3|city|state|province|postal_code|country_code|gender|date_of_birth|alt_phone|email|security_phrase|comments|called_count|last_local_call_time|rank|owner|';

$secX = date("U");
$hour = date("H");
$min = date("i");
$sec = date("s");
$mon = date("m");
$mday = date("d");
$year = date("Y");
$isdst = date("I");
$Shour = date("H");
$Smin = date("i");
$Ssec = date("s");
$Smon = date("m");
$Smday = date("d");
$Syear = date("Y");
$pulldate0 = "$year-$mon-$mday $hour:$min:$sec";
$inSD = $pulldate0;
$dsec = ( ( ($hour * 3600) + ($min * 60) ) + $sec );

$format = "Y-m-d H:i:s";

$stmt = "SELECT * FROM `vicidial_list_duplicate` ORDER BY `vicidial_list_duplicate`.`phone_number` ASC;";
$rslt=mysql_to_mysqli($stmt, $link);
$qm_conf_ct = mysqli_num_rows($rslt);

$leadMasterId="";
$currentPhoneNumber="";
$newPhoneNumber=="";
$lead_id_master="";
$entry_date_master="";
$modify_date_master="";
$status_master="";
$user_master="";
$vendor_lead_code_master="";
$source_id_master="";
$list_id_master="";
$gmt_offset_now_master="";
$called_since_last_reset_master="";
$phone_code_master="";
$phone_number_master="";
$title_master="";
$first_name_master="";
$middle_initial_master="";
$last_name_master="";
$address1_master="";
$address2_master="";
$address3_master="";
$city_master="";
$state_master="";
$province_master="";
$postal_code_master="";
$country_code_master="";
$gender_master="";
$date_of_birth_master="";
$alt_phone_master="";
$email_master="";
$security_phrase_master="";
$comments_master="";
$called_count_master="";
$last_local_call_time_master="";
$rank_master="";
$owner_master="";
$entry_list_id_master="";

$stringList_first_name="";
$stringList_middle_initial="";
$stringList_last_name="";
$stringList_list_id="";

$called_count_sum=0;
## lead_id,entry_date,modify_date,status,user,vendor_lead_code,source_id,list_id,gmt_offset_now,called_since_last_reset,phone_code,phone_number,title,first_name,middle_initial,last_name,address1,address2,address3,city,state,province,postal_code,country_code,gender,date_of_birth,alt_phone,email,security_phrase,comments,called_count,last_local_call_time,rank,owner,entry_list_id

function dateEarlier($dateNew,$dateRef){

}

function mostFrequentInListString($str, $delemeter){
	$arrayString = explode($delemeter,$str);
	$frequentArray=array_count_values(array_map("strtolower", $arrayString));
	$maxKey = array_keys($frequentArray, max($frequentArray));
	return $maxKey;
	
}

function returnStringAsAList($str, $delemeter){
	$arrayString = explode($delemeter,$str);
	$newString="";
	foreach ($arrayString => $value) {
		$newString=$newString."'".$value."';";		
	}
	return $newString;
}

function putOnStringList($str,$strlist){
	if(!empty($str)){
		return $strlist."@@".$str;
	}
	else{
		return $strlist;
	}
}

if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$newPhoneNumber=$row['phone_number'];
	if($newPhoneNumber!=$currentPhoneNumber){

		#######################
		### ALL THE THINGS THAT NEED TO BE BEFORE WE ARE MOVING OVER A NEW PHONENUMBER
		#######################

		### FOR the status check what is in a list and set the priority one for instance sale is prior to DNC

		### Get the list of name,last, middle and compare, clean of merged ...

		### phone_code : The most frequent one
		$phone_code_master = mostFrequentInListString($stringList_phone_code,"@@");

		### title : The most frequent one not null
		$title_master = mostFrequentInListString($stringList_title,"@@");

		### Address : The most frequent one not null
		## BIG ISSUE SEE pn 7793058753
		//$title_master = mostFrequentInListString($stringList_title,"@@");

		### Email : All the list as well
		

		## $last_local_call_time should be the most recent one

		## $called_count should be the sum of all called_count



		####################  *********** ###############



		$currentPhoneNumber=$newPhoneNumber;
		$leadMasterId=$row['lead_id'];
		$lead_id_master=$row['lead_id'];
		$entry_date_master=$row['entry_date'];
		$modify_date_master=$row['modify_date'];
		
		$stringList_status=$row['status'];
		$user_master=$row['user'];
		$vendor_lead_code_master=$row['vendor_lead_code'];
		$source_id_master=$row['source_id'];
		$list_id_master=$row['list_id'];
		$gmt_offset_now_master=$row['gmt_offset_now'];
		$called_since_last_reset_master=$row['called_since_last_reset'];
		$phone_code_master=$row['phone_code'];
		$phone_number_master=$row['phone_number'];
		$title_master=$row['title'];
		$first_name_master=$row['first_name'];
		$middle_initial_master=$row['middle_initial'];
		$last_name_master=$row['last_name'];
		$address1_master=$row['address1'];
		$address2_master=$row['address2'];
		$address3_master=$row['address3'];
		$city_master=$row['city'];
		$state_master=$row['state'];
		$province_master=$row['province'];
		$postal_code_master=$row['postal_code'];
		$country_code_master=$row['country_code'];
		$gender_master=$row['gender'];
		$date_of_birth_master=$row['date_of_birth'];
		$alt_phone_master=$row['alt_phone'];
		$email_master=$row['email'];
		$security_phrase_master=$row['security_phrase'];
		$comments_master=$row['comments'];
		$called_count_master=$row['called_count'];
		$last_local_call_time_master=$row['last_local_call_time'];
		$rank_master=$row['rank'];
		$owner_master=$row['owner'];
		$entry_list_id_master=$row['entry_list_id'];

		$entry_date_format_master  = \DateTime::createFromFormat($format, $entry_date_master);

		$stringList_status=$row['status'];
		$stringList_list_id=$row['list_id'];
		$stringList_first_name=$row['first_name'];
		$stringList_middle_initial=$row['middle_initial'];
		$stringList_last_name=$row['last_name'];
		$stringList_phone_code=$row['phone_code'];
		if($row['title']!=""){
			$stringList_title=$row['title'];
		}

		$called_count_sum=$row['called_count'];;
		



	}
	else{
			## if Status is sale then update statusMaster is sale
		ìf($row['status'] == "SALE"){
			$status_master="SALE";
			echo "Update Status master as Sale";
		}

			## if The new entry date is older then update info
		$entry_date=$row['entry_date'];
		$entry_date_format  = \DateTime::createFromFormat($format, $newDate);

		echo var_dump($entry_date_format >$entry_date_format_master);

		if($entry_date_format<$entry_date_format_master){
			echo "entry_date_format /".var_dump($entry_date_format);
			echo "entry_date_format_master /".var_dump($entry_date_format_master);
			$entry_date_master = $row['entry_date'];
			$entry_date_format_master=$entry_date_format;
			$user_master=$row['user'];
		}

		### status on a list
		$stringList_status=putOnStringList($row['status'],$stringList_status);

		##Put on list vendor_lead_code_master
		$stringList_vendor_lead_code = putOnStringList($row['vendor_lead_code'],$stringList_vendor_lead_code);

		$stringList_source_id = putOnStringList($row['source_id'],$stringList_vendor_lead_code);

		$stringList_list_id = putOnStringList($row['list_id'],$stringList_list_id);

		$stringList_title=putOnStringList($row['title'],$stringList_title);
		$stringList_first_name=putOnStringList($row['first_name'],$stringList_first_name);
		$stringList_middle_initial=putOnStringList($row['middle_initial'],$stringList_middle_initial);
		$stringList_last_name=putOnStringList($row['last_name'],$stringList_last_name);
		$stringList_gender=putOnStringList($row['gender'],$stringList_gender);
		$stringList_date_of_birth=putOnStringList($row['date_of_birth'],$stringList_date_of_birth);

		$stringList_phone_code=putOnStringList($row['phone_code'],$stringList_phone_code);

		### FOR THE ADRESS

		$stringList_address1=putOnStringList($row['address1'],$stringList_address1);
		$stringList_address2=putOnStringList($row['address2'],$stringList_address2);
		$stringList_address3=putOnStringList($row['address3'],$stringList_address3);
		$stringList_city=putOnStringList($row['city'],$stringList_city);
		$stringList_state=putOnStringList($row['state'],$stringList_state);
		$stringList_province=putOnStringList($row['province'],$stringList_province);
		$stringList_postal_code=putOnStringList($row['postal_code'],$stringList_postal_code);
		$stringList_country_code=putOnStringList($row['country_code'],$stringList_country_code);
		
		$stringList_alt_phone=putOnStringList($row['alt_phone'],$stringList_alt_phone);
		$stringList_email=putOnStringList($row['email'],$stringList_email);

		$stringList_security_phrase=putOnStringList($row['security_phrase'],$stringList_security_phrase);
		$stringList_comments=putOnStringList($row['comments'],$stringList_comments);

		$called_count_sum=$called_count_sum+$row['called_count'];

		######## last_local_call_time

		$last_local_call_time=$row['last_local_call_time'];
		$last_local_call_time_format  = \DateTime::createFromFormat($format, $last_local_call_time);
		if($last_local_call_time_format>$last_local_call_time_format_master){
			$last_local_call_time_format_master=$last_local_call_time_format;
			$last_local_call_time_master = $last_local_call_time;
		}
		###############
		
		$stringList_rank=putOnStringList($row['rank'],$stringList_rank);
		$stringList_owner=putOnStringList($row['owner'],$stringList_owner);
		$stringList_entry_list_id=putOnStringList($row['entry_list_id'],$stringList_entry_list_id);

	
		


	}
	

// 		if( leadMaster is null) -> then leadMaster=lead
// 		else

// 			if Status is sale then update statusMaster is sale
// 			if this.entrydate earlier 
// 			than
// 			 {
// 				entrydateMaster = this.entryDate
// 				userMaster = this.user
// 				userMaster = this.user
// 			}
?>
