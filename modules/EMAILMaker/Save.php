<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

require_once('Smarty_setup.php'); 
require_once("include/Zend/Json.php");
require_once("modules/EMAILMaker/EMAILMaker.php");

global $current_user;
global $upload_badext;

global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$close_window = false;
//print_r($_REQUEST);

//exit;
//$adb->setDebug(true);

//check for mail server configuration thro ajax
if(isset($_REQUEST['server_check']) && $_REQUEST['server_check'] == 'true')
{
	$sql="select * from vtiger_systems where server_type = ?";
	$records=$adb->num_rows($adb->pquery($sql, array('email')),0,"id");
	if($records != '')
		echo 'SUCCESS';
	else
		echo 'FAILURE';	
	die;	
}


global $mod_strings,$app_strings;

if(isset($_REQUEST['description']) && $_REQUEST['description'] !='')
	$_REQUEST['description'] = fck_from_html($_REQUEST['description']);

if(isset($_REQUEST['pmodule']) && $_REQUEST['pmodule'] !='')
	$pmodule = addslashes($_REQUEST['pmodule']);
else
    $pmodule = "";

$s_type = $_REQUEST['s_type'];

//Check if the file is exist or not.
//$file_name = '';
if(isset($_REQUEST['filename_hidden'])) {
	$file_name = $_REQUEST['filename_hidden'];
} else {
	$file_name = $_FILES['filename']['name'];
}
$errorCode =  $_FILES['filename']['error'];
$errormessage = "";
if($file_name != '' && $_FILES['filename']['size'] == 0)
{
	if($errorCode == 4 || $errorCode == 0)
	{
		 if($_FILES['filename']['size'] == 0)
			 $errormessage = "<B><font color='red'>".$mod_strings['LBL_PLEASE_ATTACH']."</font></B> <br>";
	}
	else if($errorCode == 2)
	{
		  $errormessage = "<B><font color='red'>".$mod_strings['LBL_EXCEED_MAX'].$upload_maxsize.$mod_strings['LBL_BYTES']." </font></B> <br>";
	}
	else if($errorCode == 6)
	{
	     $errormessage = "<B>".$mod_strings['LBL_KINDLY_UPLOAD']."</B> <br>";
	}
	else if($errorCode == 3 )
	{
	     if($_FILES['filename']['size'] == 0)
		     $errormessage = "<b><font color='red'>".$mod_strings['LBL_PLEASE_ATTACH']."</font></b><br>";
	}
	else{}
	if($errormessage != ""){
		$ret_error = 1;
		$ret_parentid = vtlib_purify($_REQUEST['parent_id']);
		$ret_toadd = vtlib_purify($_REQUEST['parent_name']);
		$ret_subject = vtlib_purify($_REQUEST['subject']);
		$ret_ccaddress = vtlib_purify($_REQUEST['ccmail']);
		$ret_bccaddress = vtlib_purify($_REQUEST['bccmail']);
		$ret_description = vtlib_purify($_REQUEST['description']);
		echo $errormessage;
        	include("EditView.php");	
		exit();
	}
}

$Sorce_Ids = array();
if ($_REQUEST["sorce_ids"] != "")
{
   $Sorce_Ids = explode(";",addslashes($_REQUEST["sorce_ids"]));
}

$form_type = addslashes($_REQUEST['type']);
$ids_for_pdf = "";
if (isset($_REQUEST["pdf_template_ids"]) && $_REQUEST["pdf_template_ids"] != "")
{
    $pdf_template_ids = addslashes($_REQUEST["pdf_template_ids"]);
    $pdf_language = addslashes($_REQUEST["pdf_language"]);
    
    if ($form_type == "5") $ids_for_pdf = addslashes($_REQUEST["sorce_ids"]);
    
}
else
{
    $pdf_template_ids = "";
    $pdf_language = "";
}

if (isset($_REQUEST["from_email"]))
{
    list($type,$email_val) = explode("::",addslashes($_REQUEST["from_email"]),2);

    if ($email_val != "")
    {
        if($type == "a")
        {
            $from_name = $email_val;
            
            $sql_a="select * from vtiger_systems where from_email_field != ? AND server_type = ?";
            $result_a = $adb->pquery($sql_a, array('','email'));

            $from_email = $adb->query_result($result_a,0,"from_email_field");
        }
        else
        {
            $sql_u = "SELECT first_name, last_name, ".$type." AS email  FROM vtiger_users WHERE id = '".$email_val."'"; 
            $result_u = $adb->pquery($sql_u, array());
            $first_name = $adb->query_result($result_u,0,"first_name");
            $last_name = $adb->query_result($result_u,0,"last_name");
            
            $from_name = trim($first_name." ".$last_name);
            $from_email = $adb->query_result($result_u,0,"email");
        }
    }
}




$Email_IDs_Array = array();
$Emails_Array = array();

foreach($_REQUEST['ToEmails'] AS $to_email)
{
    list($email_type, $email_pid, $email_crmid, $email_fieldid) = explode("_",$to_email);

    $email_ids = $email_crmid."@".$email_fieldid;
    
    if ($email_type == "normal")
    {
        if ($s_type == "2" && count($Sorce_Ids) > 0)
        {
            foreach ($Sorce_Ids AS $s_pid)
            {
                $SendMail[$s_pid][] = $email_crmid."@".$email_fieldid;
                $all_emails_count++;
            }
        }
        else
        {
            $SendMail[$email_pid][] = $email_crmid."@".$email_fieldid;
            $all_emails_count++;
        }
    } 
    else
    {   
        if ($form_type == "1")
        {
            $for_id = "no";
             
            If(isset($Emails_Array[$email_ids]))
            {
                 $EmailCCBCC[$email_type][] = $Emails_Array[$email_ids];
            }
            else
            {
                if ($email_crmid == "email")
                    $emailadd = $email_fieldid;
                else
                    $emailadd = getEmailToAdressat($email_crmid,$email_fieldid);
                
                if ($emailadd != "")
                {
                    $EmailCCBCC[$email_type][] = $emailadd;
                    $Emails_Array[$email_ids] = $emailadd; 
                }
            }
        } 
        else
        {
             $SendCCandBCCMail[$email_pid][] = array("type" => $email_type, "email_ids" => $email_crmid."@".$email_fieldid);
             $for_id = $email_pid;
        }
        
        if ($email_crmid != "email")
        {
            if (!isset($Email_IDs_Array[$for_id][$email_type]))
                $Email_IDs_Array[$for_id][$email_type] = array();
            
            if (!in_array($email_crmid,$Email_IDs_Array[$for_id][$email_type]))
                $Email_IDs_Array[$for_id][$email_type][] = $email_crmid;
        }
    }   
}

if ($form_type == "1")
{
    if (count($EmailCCBCC["cc"]) > 0)
    {
        $cc = implode(", ",$EmailCCBCC["cc"]);
    }
    
    if (count($Email_IDs_Array["no"]["cc"]) > 0)
    {
        $cc_ids = implode(";",$Email_IDs_Array["no"]["cc"]);
    }
    
    if (count($EmailCCBCC["bcc"]) > 0)
    {
        $bcc = implode(", ",$EmailCCBCC["bcc"]);
    }
    
    if (count($Email_IDs_Array["no"]["bcc"]) > 0)
    {
        $bcc_ids = implode(";",$Email_IDs_Array["no"]["bcc"]);
    }
}


foreach($SendMail AS $pid => $PidsData)
{
    if ($form_type != "1")
    {
        $EmailCCBCC2[$pid] = array();
        
        if (count($SendCCandBCCMail[$pid]) > 0)
        {
            foreach($SendCCandBCCMail[$pid] AS $Email_Data)
            {
                $email_ids = $Email_Data["email_ids"];
                $email_type = $Email_Data["type"];
                
                If(isset($Emails_Array[$email_ids]))
                {
                     $EmailCCBCC2[$pid][$email_type][] = $Emails_Array[$email_ids];
                }
                else
                {
                    list($email_crmid,$email_fieldid) = explode("@",$email_ids,2);
                    
                    if ($email_crmid == "email")
                        $emailadd = $email_fieldid;
                    else
                        $emailadd = getEmailToAdressat($email_crmid,$email_fieldid);
                    
                    if ($emailadd != "")
                    {
                        $EmailCCBCC2[$pid][$email_type][] = $emailadd;
                        $Emails_Array[$email_ids] = $emailadd; 
                    }
                }
            }
        }
    } 
}

$Send_Emails = array();

if(isset($_REQUEST["is_drip"]) && $_REQUEST["is_drip"] == "yes")
{
    $is_drip = true;
    $close_window = true;
    $drip_group_id = $adb->getUniqueID("vtiger_emakertemplates_drip_groups");
    $drip_group_name = $_REQUEST['subject'];
    
    $sql_dg = "INSERT INTO vtiger_emakertemplates_drip_groups (drip_group_id,drip_group_name,drip_group_save_date) VALUES (?,?,now())";
    $adb->pquery($sql_dg,array($drip_group_id,$drip_group_name));

    foreach($_REQUEST["send_templateid"] AS $eid => $rtemplateid)
    {
        $sql = "SELECT subject, body FROM vtiger_emakertemplates WHERE templateid = ?";
        $result = $adb->pquery($sql,array($rtemplateid));
        $num_rows = $adb->num_rows($result);
        
        if ($num_rows > 0)
        {
            $subject = html_entity_decode($adb->query_result($result,0,"subject"),ENT_QUOTES);
            $description = html_entity_decode($adb->query_result($result,0,"body"),ENT_QUOTES);
        
            $days = $_REQUEST["delay_days_".$eid];
            $hours = $_REQUEST["delay_hours_".$eid];
            $minutes = $_REQUEST["delay_minutes_".$eid];
        
            $drip_delay = ($days * 86400) + ($hours * 3600) + ($minutes * 60);
        
            $att_documents = "";
            $sql_ad = "SELECT vtiger_seattachmentsrel.attachmentsid FROM vtiger_notes 
                      INNER JOIN vtiger_crmentity 
                         ON vtiger_crmentity.crmid = vtiger_notes.notesid
                      INNER JOIN vtiger_seattachmentsrel 
                         ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid   
                      INNER JOIN vtiger_emakertemplates_documents 
                         ON vtiger_emakertemplates_documents.documentid = vtiger_notes.notesid
                      WHERE vtiger_crmentity.deleted = '0' AND vtiger_emakertemplates_documents.templateid = ?";
            $result_ad = $adb->pquery($sql_ad, array($rtemplateid));
            $num_rows_ad = $adb->num_rows($result_ad);  
            if($num_rows_ad > 0)
            {
                $Att_Documents = array();
                while($row_ad = $adb->fetchByAssoc($result_ad))
                {
                	$Att_Documents[] = $row_ad['notesid'];
                }
                
                $att_documents = implode(",",$Att_Documents);
            }
            
            $Send_Emails[] = array("subject"=>$subject,"description"=>$description,"drip_delay"=>$drip_delay,"att_documents"=>$att_documents);
        }
    }
}
else
{
    $is_drip = false;
    $drip_group_id = "0";
    
    $att_documents = "";
    if (isset($_REQUEST["documents"]))
    {
        $Att_Documents = array();
        foreach($_REQUEST["documents"] AS $doc)
        {
            if ($doc != "")
            {
                $result_doc = $adb->pquery("SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid = ?",array($doc));  
                $att_id = $adb->query_result($result_doc,0,"attachmentsid");
                
                if (!in_array($att_id,$Att_Documents))
                {    
                    $Att_Documents[] = addslashes($att_id);
                }  
            }  
        }
        
        if (count($Att_Documents) > 0)
            $att_documents = implode(",",$Att_Documents);
    }
    
    if (isset($_REQUEST["email_delay"]) && $_REQUEST["email_delay"] == "1")
    {
        $close_window = true;
        
        $days = $_REQUEST["email_delay_days"];
        $hours = $_REQUEST["email_delay_hours"];
        $minutes = $_REQUEST["email_delay_minutes"];
    
        $drip_delay = ($days * 86400) + ($hours * 3600) + ($minutes * 60);
    }
    else
    {
        $drip_delay = "-900";
    }
    
    $Send_Emails[] = array("subject"=>$_REQUEST['subject'],"description"=>$_REQUEST['description'],"drip_delay"=>$drip_delay,"att_documents"=>$att_documents);
}

$e_focus = new Emails();

$date_start = date(getNewDisplayDate());

$actual_time = time();
$optimalized_time = ceil($actual_time/900) *900;

if (count($Send_Emails) > 0)
{
    foreach ($Send_Emails AS $SED)
    {
        $email_send_date = "";
        
        if ($SED["drip_delay"] > 0 OR $is_drip)
        {
            $next_time = $optimalized_time + $SED["drip_delay"];
            $email_send_date = date("Y-m-d H:i:s",$next_time); 
        }
        
        If(isset($_REQUEST["related_to"]) && $_REQUEST["related_to"] != "") 
            $related_to = addslashes($_REQUEST["related_to"]);
        else
            $related_to = "";
        
        $sql = "INSERT INTO vtiger_emakertemplates_sent (from_name,from_email,subject,body,type,ids_for_pdf,pdf_template_ids,pdf_language,userid,att_documents,drip_group,saved_drip_delay,drip_delay,total_sent_emails,related_to,pmodule) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $result = $adb->pquery($sql,array($from_name,$from_email,$SED["subject"],$SED["description"],$s_type,$ids_for_pdf,$pdf_template_ids,$pdf_language,$current_user->id,$SED["att_documents"],$drip_group_id,$SED["drip_delay"],$SED["drip_delay"],"0",$related_to,$pmodule));
        
        $esentid = $adb->database->Insert_ID("vtiger_emakertemplates_sent");
        
        $f = 0;
        
        $attachments = "0";
        
        if (isset($_REQUEST["file_".$f."_hidden"]) && $_REQUEST["file_".$f."_hidden"] != "")
        {
            $attachments = upladEmailAttachment($_FILES,$f,$esentid,$attachments);
        }
        
        $total_emails = 0;
        foreach($SendMail AS $pid => $PidsData)
        {
            if ($form_type != "1")
            {
                if (count($EmailCCBCC2[$pid]["cc"]) > 0)
                {
                    $cc = implode(", ",$EmailCCBCC2[$pid]["cc"]);
                }
                
                if (count($EmailCCBCC2[$pid]["bcc"]) > 0)
                {
                    $bcc = implode(", ",$EmailCCBCC2[$pid]["bcc"]);
                }
                
                if (count($Email_IDs_Array[$pid]["cc"]) > 0)
                {
                    $cc_ids = implode(";",$Email_IDs_Array[$pid]["cc"]);
                }
                
                if (count($Email_IDs_Array[$pid]["bcc"]) > 0)
                {
                    $bcc_ids = implode(";",$Email_IDs_Array[$pid]["bcc"]);
                }
            } 
            
            foreach($PidsData AS $to_email)
            {
                $Inserted_Emails = array();
                
                $focus = clone $e_focus; 
                
                list($mycrmid,$temp) = explode("@",$to_email,2);
                
                if ($mycrmid == "email")
                {
                    $mycrmid = "";
                }
                else
                {
                    if ($temp == "-1")
                        $rmodule = "Users";
                    else
                        $rmodule=getSalesEntityType($mycrmid);
                }
                
                $focus->column_fields["assigned_user_id"]=$current_user->id;
                $focus->column_fields["activitytype"]="Emails";
                $focus->column_fields["date_start"]= $date_start;//This will be converted to db date format in save
                
                $focus->column_fields["subject"] = $SED["subject"];
                $focus->column_fields["description"] = $SED["description"];
                
                $focus->column_fields["ccmail"] = $cc;
                $focus->column_fields["bccmail"] = $bcc;
        
                $focus->column_fields["parent_id"] = $mycrmid;
        
                $saved_toid = "";
                if($temp == "-1")
                {
                    $emailadd = $adb->query_result($adb->pquery("select email1 from vtiger_users where id=?", array($mycrmid)),0,'email1');
                    $user_full_name = getUserFullName($mycrmid);
                    
                    $saved_toid = $user_full_name."<".$emailadd.">"; 
                }
                else
                {
                    if ($mycrmid != "")
                    {
                        $emailadd = getEmailToAdressat($mycrmid,$temp,$rmodule);
                        
                        $entityNames = getEntityName($rmodule, $mycrmid);
                	    $pname = $entityNames[$mycrmid];
                        
                        $saved_toid = $pname."<".$emailadd.">"; 
                    }
                    else
                    {
                        $saved_toid = $emailadd;
                    }
                } 
                $focus->column_fields["saved_toid"] = $saved_toid;
        
                $focus->save("Emails");
                
                if ($mycrmid != "")
                {
                    $Inserted_Emails[] = $mycrmid; 
                   
                    $rel_sql1 = 'DELETE FROM vtiger_seactivityrel WHERE crmid = ? AND activityid = ?';
                    $rel_sql2 = 'INSERT INTO vtiger_seactivityrel VALUES (?,?)';
            		$rel_params = array($mycrmid,$focus->id);
            		$adb->pquery($rel_sql1,$rel_params);
                    $adb->pquery($rel_sql2,$rel_params);
                }
                
                if (count($Email_IDs_Array[$pid]["cc"]) > 0)
                {
                    foreach ($Email_IDs_Array[$pid]["cc"] AS $email_crm_id)
                    {
                        if (!in_array($email_crm_id,$Inserted_Emails))
                        {
                            $Inserted_Emails[] = $email_crm_id; 
                            $rel_sql_2 = 'insert into vtiger_seactivityrel values(?,?)';
                    		$rel_params_2 = array($email_crm_id,$focus->id);
                    		$adb->pquery($rel_sql_2,$rel_params_2);
                        }
                    }
                }
                
                if (count($Email_IDs_Array[$pid]["bcc"]) > 0)
                {
                    foreach ($Email_IDs_Array[$pid]["bcc"] AS $email_crm_id)
                    {
                        if (!in_array($email_crm_id,$Inserted_Emails))
                        {
                            $Inserted_Emails[] = $email_crm_id;
                            $rel_sql_3 = 'insert into vtiger_seactivityrel values(?,?)';
                		    $rel_params_3 = array($email_crm_id,$focus->id);
                		    $adb->pquery($rel_sql_3,$rel_params_3);
                        }    
                    }
                }

                $sql2 = "INSERT INTO vtiger_emakertemplates_emails (esentid,pid,email,cc,bcc,cc_ids,bcc_ids,status,parent_id,email_send_date) VALUES (?,?,?,?,?,?,?,?,?,?)";
                $adb->pquery($sql2,array($esentid,$pid,$to_email,$cc,$bcc,$cc_ids,$bcc_ids,"0",$focus->id,$email_send_date));
                $total_emails++;
                
                unset($focus);
                unset($Inserted_Emails);
            } 
        }
        
        $sql3 = "UPDATE vtiger_emakertemplates_sent SET total_emails = ?, attachments = ? WHERE esentid = ?";
        $adb->pquery($sql3,array($total_emails,$attachments,$esentid));
    }

}

if ($close_window)
{
    //$inputs="<script>window.opener.location.href=window.opener.location.href;window.self.close();</script>";
    $inputs="<script>window.self.close();</script>";
	echo $inputs;
}
else
{
    $smarty = new vtigerCRM_Smarty();
    
    $smarty->assign("THEME", $theme);
    $smarty->assign("IMAGE_PATH", $image_path);
    $smarty->assign("APP", $app_strings);
    $smarty->assign("MOD", $mod_strings);
    
    $smarty->assign('ESENTID',$esentid);
    
    $smarty->assign('STRING1',$mod_strings['LBL_PLEASE_DONT_CLOSE_WINDOW']);
    $smarty->assign('STRING2',$mod_strings['LBL_POPUP_WILL_BE_CLOSED_AUT']);
    
    $smarty->assign('PROCESS_CONTENT','0 '.$mod_strings["LBL_EMAILS_SENT_FROM"].' '.$total_emails);
    
    $smarty->display("modules/EMAILMaker/EMAILPopUp.tpl");
    
    echo "<script>";
    echo "window.opener.startEmailProcessed('".$esentid."'); ";
    echo "</script>";
}

function upladEmailAttachment($_FILES,$f,$esentid,$attachments)
{    
    global $adb,$root_directory,$upload_badext;
    
    if (isset($_FILES["file_".$f]) && $_FILES["file_".$f]["tmp_name"] != "")
    {
        $File = $_FILES["file_".$f];
        
        $file_name = $File["name"];
        $binFile = sanitizeUploadFileName($file_name, $upload_badext);
        
        //$filename = ltrim(basename(" ".$binFile));
    	$filetype= $File['type'];
    	$filesize = $File['size'];
        
        $file_desc = "modules/EMAILMaker/tmp/".$esentid."_".$binFile;
        
        move_uploaded_file($File["tmp_name"],$file_desc);
        //echo "move <br />";
        $sql3 = "INSERT INTO vtiger_emakertemplates_attch (esentid,file_desc,filename,type) VALUES (?,?,?,?)";
        $result = $adb->pquery($sql3,array($esentid,$file_desc,$binFile,$filetype));
        //echo "INSERT INTO vtiger_emakertemplates_attch (esentid,desc,filename) VALUES (".$esentid.",'".$file_desc."','".$binFile."')<br />";
    
        $attachments = "1";
    }
    
    $f++;
    
    if (isset($_REQUEST["file_".$f."_hidden"]) && $_REQUEST["file_".$f."_hidden"] != "")
    {
    	//echo "upload ".$_FILES["file_".$f]["tmp_name"]."<br />";
        $attachments = upladEmailAttachment($_FILES,$f,$esentid,$attachments);
    }
    
    return $attachments;
}

exit;
?>