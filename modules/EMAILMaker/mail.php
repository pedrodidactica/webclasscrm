<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
require_once("modules/EMAILMaker/PHPMailer/class.phpmailer.php");
require_once("modules/Emails/mail.php");

function send_em_mail($module,$to_email,$from_name,$from_email,$subject,$contents,$cc='',$bcc='',$attachment='',$emailid='',$logo='',$Email_Images = array(), $phpmailer_version = 'emailmaker')
{
	global $adb, $log;
	global $root_directory;
	global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;

	$uploaddir = $root_directory ."/test/upload/";

	$adb->println("To id => '".$to_email."'\nSubject ==>'".$subject."'\nContents ==> '".$contents."'");

	if($from_email == '') 
    {
			$from_email = getUserEmailId('user_name',$from_name);
	}
    
    $replyToEmail = $from_email;
/*
	//if the newly defined from email field is set, then use this email address as the from address
	//and use the username as the reply-to address
	$query = "select * from vtiger_systems where server_type=?";
	$params = array('email');
	$result = $adb->pquery($query,$params);
	$from_email_field = $adb->query_result($result,0,'from_email_field');
	if(isset($from_email_field) && $from_email_field!=''){
		//setting from _email to the defined email address in the outgoing server configuration
		$from_email = $from_email_field;
	}
*/
	if($module != "Calendar")
		$contents = addSignature($contents,$from_name);
    
    if ($phpmailer_version == "emailmaker")
        $mail = new EMPHPMailer();
    else
        $mail = new PHPMailer();

	setMailerProperties($mail,$subject,$contents,$from_email,$from_name,trim($to_email,","),$attachment,$emailid,$module,$logo);
	setCCAddress($mail,'cc',$cc);
	setCCAddress($mail,'bcc',$bcc);
   
    if (count($Email_Images) > 0)
    {
        foreach ($Email_Images AS $cid => $cdata)
        {
            $mail->AddEmbeddedImage($cdata["path"], $cid, $cdata["name"]);
        }    
    }    
    
	if(!empty($replyToEmail)) {
		$mail->AddReplyTo($replyToEmail);
	}
	
	// vtmailscanner customization: If Support Reply to is defined use it.
	global $HELPDESK_SUPPORT_EMAIL_REPLY_ID;
	if($HELPDESK_SUPPORT_EMAIL_REPLY_ID && $HELPDESK_SUPPORT_EMAIL_ID != $HELPDESK_SUPPORT_EMAIL_REPLY_ID) {
		$mail->AddReplyTo($HELPDESK_SUPPORT_EMAIL_REPLY_ID);
	}
	// END

	// Fix: Return immediately if Outgoing server not configured
    if(empty($mail->Host)) {
		return 0;
    }
    // END
    
	$mail_status = MailSend($mail);

	if($mail_status != 1)
	{
		$mail_error = getMailError($mail,$mail_status,$mailto);
	    if ($mail_error == "" && $mail_status != "") $mail_error = $mail_status;
    }
	else
	{
		$mail_error = $mail_status;
	}

	return $mail_error;
}

function isEMUserInitiated() {
	return (($_REQUEST['module'] == 'Emails' || $_REQUEST['module'] == 'Webmails') && 
			($_REQUEST['action'] == 'mailsend' || $_REQUEST['action'] == 'webmailsend' || $_REQUEST['action'] == 'Save'));
}
?>
