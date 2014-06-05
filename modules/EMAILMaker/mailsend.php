<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

require_once("include/utils/GetGroupUsers.php");
require_once("include/utils/UserInfoUtil.php");
require_once("modules/EMAILMaker/ConvertEMAIL.php");
require_once("modules/EMAILMaker/SavePDFIntoEmail.php");
require_once("modules/EMAILMaker/EMAILMaker.php");

global $adb;
global $current_user;


$sql_s = "SELECT * FROM vtiger_emakertemplates_settings";
$result_s = $adb->query($sql_s);
$phpmailer_version  = $adb->query_result($result_s,0,"phpmailer_version");

//set the return module and return action and set the return id based on return module and record
$returnmodule = vtlib_purify($_REQUEST['return_module']);
$returnaction = vtlib_purify($_REQUEST['return_action']);
$type = addslashes($_REQUEST['type']);

if((($returnmodule != 'Emails') || ($returnmodule == 'Emails' && $_REQUEST['record'] == '')) && $_REQUEST['return_id'] != '')
{
	$returnid = vtlib_purify($_REQUEST['return_id']);
}
else
{
	$returnid = $focus->id;//$_REQUEST['record'];
}

$SendMail = array();


foreach($_REQUEST['ToEmails'] AS $to_email)
{
    list($email_type, $email_pid, $email_crmid, $email_fieldid) = explode("_",$to_email);

    if ($email_type == "normal")
    {
        $SendMail[$email_pid][] = $email_crmid."@".$email_fieldid;
    } 
    else
    {
        if($type != "1") $SendCCandBCCMail[$email_pid][] = array("type" => $email_type, "email_ids" => $email_crmid."@".$email_fieldid);
    }
}

$adb->println("\n\nMail Sending Process has been started.");
//This function call is used to send mail to the assigned to user. In this mail CC and BCC addresses will be added.
if($_REQUEST['assigntype' == 'T'] && $_REQUEST['assigned_group_id']!='')
{
	$grp_obj = new GetGroupUsers();
	$grp_obj->getAllUsersInGroup($_REQUEST['assigned_group_id']);
	$users_list = constructList($grp_obj->group_users,'INTEGER');
	if (count($users_list) > 0) {
		$sql = "select first_name, last_name, email1, email2, secondaryemail  from vtiger_users where id in (". generateQuestionMarks($users_list) .")";
		$params = array($users_list);
	} else {
		$sql = "select first_name, last_name, email1, email2, secondaryemail  from vtiger_users";
		$params = array();
	}
	$res = $adb->pquery($sql, $params);
	$user_email = '';
	while ($user_info = $adb->fetch_array($res))
	{
		$email = $user_info['email1'];
		if($email == '' || $email == 'NULL')
		{
			$email = $user_info['email2'];
			if($email == '' || $email == 'NULL')
			{
				$email = $user_info['secondaryemail '];
			}
		}	
		if($user_email=='')
		$user_email .= $user_info['first_name']." ".$user_info['last_name']."<".$email.">";
		else
		$user_email .= ",".$user_info['first_name']." ".$user_info['last_name']."<".$email.">";
		$email='';
	}
	$to_email = $user_email;
}
else
{
	$to_email = getUserEmailId('id',$focus->column_fields["assigned_user_id"]);
}
$cc = $_REQUEST['ccmail'];
$bcc = $_REQUEST['bccmail'];
if($to_email == '' && $cc == '' && $bcc == '')
{
	$adb->println("Mail Error : send_em_mail function not called because To email id of assigned to user, CC and BCC are empty");
	$mail_status_str = "'".$to_email."'=0&&&";
	$errorheader1 = 1;
}
else
{
	$query1 = "select email1 from vtiger_users where id =?";
	$res1 = $adb->pquery($query1, array($current_user->id));
	$val = $adb->query_result($res1,0,"email1");
	
	$query = 'update vtiger_emaildetails set email_flag ="SENT",from_email =? where emailid=?';
	$adb->pquery($query, array($val, $focus->id));
	//set the errorheader1 to 1 if the mail has not been sent to the assigned to user
	if($mail_status != 1)//when mail send fails
	{
		$errorheader1 = 1;
		$mail_status_str = $to_email."=".$mail_status."&&&";
	}
	elseif($mail_status == 1 && $to_email == '')//Mail send success only for CC and BCC but the 'to' email is empty 
	{
		$adb->pquery($query, array($val, $focus->id));
		$errorheader1 = 1;
		$mail_status_str = "cc_success=0&&&";
	}
	else
	{
		$mail_status_str = $to_email."=".$mail_status."&&&";
	}
}

$all_to_emailids = Array();
$from_name = $current_user->user_name;
$from_address = $current_user->column_fields['email1'];

foreach ($SendMail AS $pid => $Pemails)
{ 
  foreach ($Pemails AS $myid)
  {
    $cc = "";
    $bcc = "";
    $EmailCCBCC = array();

    if (count($SendCCandBCCMail[$pid]) > 0)
    {
        foreach($SendCCandBCCMail[$pid] AS $Email_Data)
        {
            $email_ids = $Email_Data["email_ids"];
            $email_type = $Email_Data["type"];
            
            If(isset($Emails_Array[$email_ids]))
            {
                 $EmailCCBCC[$email_type][] = $Emails_Array[$email_ids];
            }
            else
            {
                list($email_crmid,$email_fieldid) = explode("@",$email_ids);
                
                $emailadd = getEmailToAdressat($email_crmid,$email_fieldid);
                
                if ($emailadd != "")
                {
                    $EmailCCBCC[$email_type][] = $emailadd;
                    $Emails_Array[$email_ids] = $emailadd; 
                }
            }
        }
    }

    if (count($EmailCCBCC["cc"]) > 0)
    {
        $cc = implode("; ",$EmailCCBCC["cc"]);
    }

    if (count($EmailCCBCC["bcc"]) > 0)
    {
        $bcc = implode("; ",$EmailCCBCC["bcc"]);
    }

    list($mycrmid,$temp) = explode("@",$myid);

    $pmodule=getSalesEntityType($mycrmid);
    
    $track_URL = "$site_URL/modules/Emails/TrackAccess.php?record=$mycrmid&mailid=$emailid&app_key=$application_unique_key";
	$description = "<img src='$track_URL' alt='' width='1' height='1'>$description";

	$pos = strpos($description, '$logo$');
	if ($pos !== false)
	{

		$description =str_replace('$logo$','<img src="cid:logo" />',$description);
		$logo=1;
	} 
            
    $Email_Content = new EMAILContent();
    $Email_Content->setContent($_REQUEST['subject']."|@{[&]}@|".$_REQUEST['description'], $mycrmid, $pmodule, $pid);
    $convert_content = $Email_Content->getContent(); 
    $Email_Images = $Email_Content->getEmailImages();

    list($subject,$description) = explode("|@{[&]}@|",$convert_content);
    
    $focus2 = clone $focus;
    
    $focus2->parent_id = $myid;
    $focus2->parent_type = $pmodule;
    $focus2->column_fields["subject"] = $subject;
    $focus2->column_fields["description"] = $description;
    $focus2->save("Emails");
    
    if (isset($_REQUEST["pdf_template_ids"]) && $_REQUEST["pdf_template_ids"] != "")
    {
        $pdf_template_ids = addslashes($_REQUEST["pdf_template_ids"]);
        $pdf_language = addslashes($_REQUEST["pdf_language"]);
        
        savePDFIntoEmail($focus2,$pid,$pdf_template_ids,$pdf_language);
    }
    
	if($temp == -1)
    {
        //handle the mail send to vtiger_users
        $emailadd = $adb->query_result($adb->pquery("select email1 from vtiger_users where id=?", array($mycrmid)),0,'email1');
        $pmodule = 'Users';
        
        $mail_status = send_em_mail('Emails',$emailadd,$from_name,$from_address,$subject,$description,$cc,$bcc,'all',$focus2->id,$logo,$Email_Images,$phpmailer_version);
        $all_to_emailids []= $emailadd;
        $mail_status_str .= $emailadd."=".$mail_status."&&&";
    }
    else
    {
		//Send mail to vtiger_account or lead or contact based on their ids
        $emailadd = getEmailToAdressat($mycrmid,$temp,$pmodule);

		if($emailadd != '')
		{
			global $site_URL, $application_unique_key;
			$emailid = $focus->id;
			 
			if(isPermitted($pmodule,'DetailView',$mycrmid) == 'yes')
			{
				$mail_status = send_em_mail('Emails',$emailadd,$from_name,$from_address,$subject,$description,$cc,$bcc,'all',$focus2->id,$logo,$Email_Images,$phpmailer_version);
			}	

			$all_to_emailids []= $emailadd;
			$mail_status_str .= $emailadd."=".$mail_status."&&&";
			//added to get remain the EditView page if an error occurs in mail sending
			if($mail_status != 1)
			{
				$errorheader2 = 1;
			}
		}
	}
    
    $sql_msg = "INSERT INTO vtiger_emakertemplates_msgs (msgtype,email,status,userid,email_id,parent_id) VALUES ('1','".$emailadd."','".$mail_status."','".$current_user->id."','".$focus2->id."','".$myid."')";
    $adb->query($sql_msg);
    	
    unset($focus2);
  }
}  
//Added to redirect the page to Emails/EditView if there is an error in mail sending
if($errorheader1 == 1 || $errorheader2 == 1)
{
	$returnset = 'return_module='.$returnmodule.'&return_action='.$returnaction.'&return_id='.vtlib_purify($_REQUEST['return_id']);
	$returnmodule = 'Emails';
	$returnaction = 'EditView';
	//This condition is added to set the record(email) id when we click on send mail button after returning mail error
	if($_REQUEST['mode'] == 'edit')
	{
		$returnid = $_REQUEST['record'];
	}
	else
	{
		$returnid = $_REQUEST['currentid'];
	}
}
else
{
	global $adb;
	$date_var = date('Ymd');
	$query = 'update vtiger_activity set date_start =? where activityid = ?';
	$adb->pquery($query, array($date_var, $returnid));
}
//The following function call is used to parse and form a encoded error message and then pass to result page
$mail_error_str = getMailErrorString($mail_status_str);
$adb->println("Mail Sending Process has been finished.\n\n");  

?>