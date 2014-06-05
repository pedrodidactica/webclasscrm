<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/ 

require_once('Smarty_setup.php');
require_once('data/Tracker.php');
require_once('include/utils/utils.php');
require_once("include/Zend/Json.php");
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');
require_once('modules/EMAILMaker/EMAILMaker.php');

require_once('modules/Dashboard/Entity_charts.php');

global $adb;
global $log;
global $mod_strings;
global $app_strings;
global $current_language;
global $theme;
global $list_max_entries_per_page;

//$list_max_entries_per_page = 2;

//$adb->setDebug("true");

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$log->info("Inside Email Template Detail View");

$module = "EMAILMaker";

$ajaxaction = $_REQUEST["ajxaction"];
if($ajaxaction == "LOADRECIPIENTSLIST")
    $is_ajax = true;
else
    $is_ajax = false;

$EMAILMaker = new EmailMaker();
if($EMAILMaker->CheckPermissions("DETAIL") == false)
  $EMAILMaker->DieDuePermission();

$smarty = new vtigerCRM_smarty;

$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("MOD", $mod_strings);

$smarty->assign("MODULE", 'Tools');
$smarty->assign("IMAGE_PATH", $image_path);

if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) {
  $smarty->assign("EDIT","permitted");
  $smarty->assign("IMPORT","yes");
  $smarty->assign("EXPORT","yes");
}

if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) {
  $smarty->assign("DELETE","permitted");
}

if (isset($_REQUEST["ajxaction"]) && $_REQUEST["ajxaction"] == "LOADRECIPIENTSLIST" && isset($_REQUEST["delete"]) && $_REQUEST["delete"] == "true")
{
    
    if (isset($_REQUEST["activityid"]) && $_REQUEST["activityid"] != "")
    {
        $del_activityid = addslashes($_REQUEST["activityid"]); 
    }
    
    if ($del_activityid != "")
    {
        $sql_u1 = "UPDATE vtiger_crmentity SET deleted = '1' WHERE setype = 'Emails' AND crmid = ?";
        $adb->pquery($sql_u1, array($del_activityid));
    }
    
    if (isset($_REQUEST["email"]) && $_REQUEST["email"] != "")
    {
        $esentid = addslashes($_REQUEST["emailid"]);
        $del_emailid = addslashes($_REQUEST["email"]); 
        $sql_u2 = "UPDATE vtiger_emakertemplates_emails SET deleted = '1' WHERE emailid = ? AND esentid = ?";
        $adb->pquery($sql_u2, array($del_emailid,$esentid));
    
        $sql_u3 = "UPDATE vtiger_emakertemplates_sent SET total_emails = total_emails - 1 WHERE esentid = ?";
        $adb->pquery($sql_u3, array($esentid));
        
        if ($del_activityid != "")
        {
            $sql_s1 = "SELECT email_flag FROM vtiger_emaildetails WHERE emailid = ? AND email_flag = 'SENT'";
            $result_s1 = $adb->pquery($sql_s1, array($del_activityid));
            $num_rows_s1 = $adb->num_rows($result_s1); 
            
            if ($num_rows_s1 > 0)
            {
                $sql_u3 = "UPDATE vtiger_emakertemplates_sent SET total_sent_emails = total_sent_emails - 1 WHERE esentid = ?";
                $adb->pquery($sql_u3, array($esentid));
            
            }
        }
    } 	
    
}


$record = $_REQUEST['emailid'];
$relationId = "ED".$record;

if(isset($record) && $record!='')
{
  	//$total_delivered_emails = 0;
    $emailid = addslashes($record);
      
    if (substr($emailid, 0, 1) == "S")
    {
        $emailid = substr($emailid, 1);
        $is_emailmaker = false;
        
        $sql1 = "SELECT vtiger_crmentity.createdtime AS first_email_sent,
                 vtiger_crmentity.createdtime AS last_email_sent, 
                 vtiger_crmentity.smownerid, 
                  vtiger_activity.subject, 
                 '' AS body, 
                 count(vtiger_seactivityrel.crmid) AS total_emails,
                 count(vtiger_email_track.crmid) AS total_open_emails
                 FROM vtiger_activity
                 INNER JOIN vtiger_crmentity
                     ON vtiger_crmentity.crmid = vtiger_activity.activityid 
                 LEFT JOIN vtiger_seactivityrel 
                     ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
                 LEFT JOIN vtiger_email_track
                     ON vtiger_email_track.crmid = vtiger_seactivityrel.crmid AND vtiger_email_track.mailid = vtiger_activity.activityid   
                 WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.activityid=? GROUP BY vtiger_activity.activityid";
    
        $sql2a = "SELECT '' AS error, 
                        vtiger_emaildetails.email_flag, 
                        vtiger_emaildetails.to_email, 
                        vtiger_emaildetails.idlists, '' AS pid, 
                        vtiger_crmentity.createdtime AS email_date_sent, 
                        vtiger_seactivityrel.crmid AS recipientid, 
                        vtiger_activity.activityid, vtiger_activity.subject,
                        vtiger_email_track.access_count,
                        '' AS emailid
                 FROM vtiger_seactivityrel
                 INNER JOIN vtiger_activity
                     ON vtiger_activity.activityid = vtiger_seactivityrel.activityid
                 INNER JOIN vtiger_crmentity
                     ON vtiger_crmentity.crmid = vtiger_activity.activityid 
                 INNER JOIN vtiger_emaildetails    
                      ON vtiger_emaildetails.emailid = vtiger_activity.activityid 
                 LEFT JOIN vtiger_email_track 
                      ON vtiger_email_track.crmid = vtiger_seactivityrel.crmid AND vtiger_email_track.mailid = vtiger_activity.activityid  
                 WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.activityid = ?";
       $sql2b = "";
    }
    else
    {
        $is_emailmaker = true;
       
        $sql1 = "SELECT min(
                        CASE WHEN vtiger_emakertemplates_emails.email_send_date > vtiger_crmentity.createdtime THEN vtiger_emakertemplates_emails.email_send_date ELSE vtiger_crmentity.createdtime END
                        ) AS first_email_sent,
                       max(
                       CASE WHEN vtiger_emakertemplates_emails.email_send_date > vtiger_crmentity.createdtime THEN vtiger_emakertemplates_emails.email_send_date ELSE vtiger_crmentity.createdtime END
                       ) AS last_email_sent, 
                       vtiger_emakertemplates_sent.from_name, 
                       vtiger_emakertemplates_sent.from_email, 
                       vtiger_emakertemplates_sent.subject,
                       vtiger_emakertemplates_sent.body,
                       vtiger_emakertemplates_sent.total_emails,
                       count(vtiger_email_track.crmid) AS total_open_emails,  
                       vtiger_emakertemplates_sent.total_sent_emails                       
                FROM vtiger_emakertemplates_sent
                INNER JOIN vtiger_emakertemplates_emails
                    ON vtiger_emakertemplates_emails.esentid = vtiger_emakertemplates_sent.esentid
                INNER JOIN vtiger_crmentity
                    ON vtiger_crmentity.crmid = vtiger_emakertemplates_emails.parent_id
                INNER JOIN vtiger_activity
                    ON vtiger_activity.activityid = vtiger_crmentity.crmid
                LEFT JOIN vtiger_seactivityrel
                    ON vtiger_seactivityrel.activityid = vtiger_activity.activityid 
                LEFT JOIN vtiger_email_track 
                    ON vtiger_email_track.crmid = vtiger_seactivityrel.crmid AND vtiger_email_track.mailid = vtiger_activity.activityid
                WHERE vtiger_crmentity.deleted = 0 AND vtiger_crmentity.setype = 'Emails' AND vtiger_emakertemplates_emails.deleted = 0 AND vtiger_emakertemplates_sent.esentid=? 
                GROUP BY vtiger_emakertemplates_sent.esentid";
    
    
        $sql2a = "SELECT CASE WHEN vtiger_emakertemplates_emails.email_send_date > vtiger_crmentity.createdtime THEN vtiger_emakertemplates_emails.email_send_date ELSE vtiger_crmentity.createdtime END AS email_date_sent,
                        vtiger_emakertemplates_emails.error, 
                        vtiger_emaildetails.email_flag, 
                        vtiger_emaildetails.to_email, 
                        vtiger_emaildetails.idlists, 
                        vtiger_crmentity.createdtime, 
                        vtiger_emakertemplates_emails.email AS recipientid, 
                        vtiger_emakertemplates_emails.*, 
                        vtiger_activity.activityid, 
                        vtiger_activity.subject,
                        vtiger_email_track.access_count,
                        vtiger_emakertemplates_emails.emailid 
                 FROM vtiger_emakertemplates_emails
                 INNER JOIN vtiger_activity
                      ON vtiger_activity.activityid = vtiger_emakertemplates_emails.parent_id
                 INNER JOIN vtiger_crmentity
                      ON vtiger_crmentity.crmid = vtiger_activity.activityid
                 LEFT JOIN vtiger_emaildetails    
                      ON vtiger_emaildetails.emailid = vtiger_activity.activityid
                 LEFT JOIN vtiger_seactivityrel
                      ON vtiger_seactivityrel.activityid = vtiger_activity.activityid 
                 LEFT JOIN vtiger_email_track 
                      ON vtiger_email_track.crmid = vtiger_seactivityrel.crmid AND vtiger_email_track.mailid = vtiger_activity.activityid       
                 WHERE vtiger_emakertemplates_emails.esentid = ? AND vtiger_emakertemplates_emails.deleted = 0 AND vtiger_crmentity.deleted = 0 "; 

         $sql2b = " GROUP BY vtiger_activity.activityid";
    }
  	
    $sql2 = $sql2a.$sql2b;
    
    $total_sent_emails = 0;
    
    if(!$is_ajax)
    {
      	$result = $adb->pquery($sql1, array($emailid));
      	$emailResult = $adb->fetch_array($result);
        
        $total_open_emails = $emailResult["total_open_emails"];
        
        $number_of_recipients = $emailResult["total_emails"];
        
        if (!$is_emailmaker)
        {
            $emails_sent_from = getUserFullName($emailResult["smownerid"]);
        }
        else
        {
            $emails_sent_from = $emailResult["from_name"]." &lt;".$emailResult["from_email"]."&gt;";
        }
        
        if($_SESSION['rlvs'][$module][$relationId]) {
    		$order_by = $_SESSION['rlvs'][$module][$relationId]['sortby'];
            $sorder = $_SESSION['rlvs'][$module][$relationId]['sorder'];
    	} else {
    		$order_by = "vtiger_crmentity.createdtime";
    		$sorder = "ASC";
    	}
    }
    else
    {
        $order_by = $_REQUEST["order_by"];
    	$sorder = $_REQUEST["sorder"];
    }
    
    
    
    $change_sorder = array('ASC' => 'DESC', 'DESC' => 'ASC');
	$arrow_gif = array('ASC' => 'arrow_down.gif', 'DESC' => 'arrow_up.gif');
    
    $Sortby_Fields = array("subject","vtiger_crmentity.createdtime","email_flag","access_count","email_date_sent");
    
    $Header_Fields = array("recipient"=>$mod_strings["LBL_RECIPIENT"],
                            "subject"=>$mod_strings["LBL_EMAIL_SUBJECT"],
                            "related_to"=>$app_strings["LBL_RELATED_TO"],
                            "email_date_sent"=>$mod_strings["LBL_DATE_EMAIL_SENT"],
                            "email_flag"=>$mod_strings["LBL_SENT_EMAIL"],
                            "access_count"=>$mod_strings["LBL_NUMBER_OPEN_MAIL"]);

    $actionsURL = "";
    $Table_Header = array();                      
    foreach($Header_Fields AS $col => $lbl_name)                      
    {
        $moduleLabel = "Recipients";
        
        if (in_array($col, $Sortby_Fields)) {
			if ($order_by == $col) {
				$temp_sorder = $change_sorder[$sorder];
				$arrow = "&nbsp;<img src ='" . vtiger_imageurl($arrow_gif[$sorder], $theme) . "' border='0'>";
			} else {
				$temp_sorder = 'ASC';
			}


            if (isset($_REQUEST["sent_mail_selectbox"]) && $_REQUEST["sent_mail_selectbox"] != "")
            {
                $actionsURL .= "&sent_mail_selectbox=".$_REQUEST["sent_mail_selectbox"];
            }
            
            if (isset($_REQUEST["open_mail_selectbox"]) && $_REQUEST["open_mail_selectbox"] != "")
            {
                $actionsURL .= "&open_mail_selectbox=".$_REQUEST["open_mail_selectbox"];
            }
            
			$name = "<a href='javascript:void(0);' onClick='loadEMAILMakerRecipientsListBlock" .
					"(\"module=$module&action=" . $module . "Ajax&" .
					"file=DetailViewEmail&ajxaction=LOADRECIPIENTSLIST&order_by=$col&emailid=$record&sorder=$temp_sorder" .
					"$actionsURL\",\"tbl_" . $module . "_$moduleLabel\"," .
					"\"$module" . "_$moduleLabel\");' class='listFormHeaderLinks'>" . $lbl_name . "" . $arrow . "</a>";
			$arrow = '';
		}
        else
        {
            $name = $lbl_name;
        }
        $Table_Header[] = $name;
    }                      
                          
    $smarty->assign("TABLE_HEADER", $Table_Header);
    
    
    $result2 = $adb->pquery($sql2, array($emailid));
    $noofrows2 = $adb->num_rows($result2); 
    
    if ($noofrows2 > 0)
    {
        while($row1 = $adb->fetchByAssoc($result2))
        {
            if ($row1["email_flag"] == "SENT")
            {
                $total_sent_emails++;
            }
        }
    }
    
    
    $url_qry ="&order_by=".$order_by."&sorder=".$sorder;
    
    $smarty->assign("URL_QRY", $url_qry);
    
    $where3 = "";
    if (isset($_REQUEST["sent_mail_selectbox"]) && $_REQUEST["sent_mail_selectbox"] != "")
    {
        $url_qry .= "&sent_mail_selectbox=".$_REQUEST["sent_mail_selectbox"];
        $smarty->assign("SENT_MAIL_SELECTBOX", $_REQUEST["sent_mail_selectbox"]); 
        
        if ($_REQUEST["sent_mail_selectbox"] == "1") 
            $where3 .= " AND vtiger_emaildetails.email_flag = 'SENT' ";       
        elseif ($_REQUEST["sent_mail_selectbox"] == "0")    
            $where3 .= " AND vtiger_emaildetails.email_flag != 'SENT' ";  
    }
    
    if (isset($_REQUEST["open_mail_selectbox"]) && $_REQUEST["open_mail_selectbox"] != "")
    {
        $url_qry .= "&open_mail_selectbox=".$_REQUEST["open_mail_selectbox"];
        $smarty->assign("OPEN_MAIL_SELECTBOX", $_REQUEST["open_mail_selectbox"]);  
        
        if ($_REQUEST["open_mail_selectbox"] == "1") 
            $where3 .= " AND access_count > 0";       
        elseif ($_REQUEST["open_mail_selectbox"] == "0")    
            $where3 .= " AND (vtiger_email_track.access_count < 1 OR vtiger_email_track.access_count IS NULL)"; 
    }
    $sql3 = $sql2a.$where3.$sql2b;
    
    $result3 = $adb->pquery($sql3, array($emailid));
    $noofrows3 = $adb->num_rows($result3); 
    
    if ($order_by != '' AND $sorder != '') $sql3 .= ' ORDER BY '.$order_by.' '.$sorder;

    $start = RelatedListViewSession::getRequestCurrentPage($relationId, $query);
    $smarty->assign("N_START", $start);
	$navigation_array =  VT_getSimpleNavigationValues($start, $list_max_entries_per_page, $noofrows3);

	$limit_start_rec = ($start-1) * $list_max_entries_per_page;
    
    //echo $sql3." LIMIT $limit_start_rec, $list_max_entries_per_page";
    
    if( $adb->dbType == "pgsql")
		$list_result = $adb->pquery($sql3." OFFSET $limit_start_rec LIMIT $list_max_entries_per_page", array($emailid));
	else
		$list_result = $adb->pquery($sql3." LIMIT $limit_start_rec, $list_max_entries_per_page", array($emailid));

    $navigationOutput = Array();
	$navigationOutput[] =  getRecordRangeMessage($list_result, $limit_start_rec,$noofrows3);
	$navigationOutput[] = getEmailRecipientsTableHeaderNavigation($navigation_array,$url_qry,$module,$record);
    
    $smarty->assign("NAVIGATION", $navigationOutput);
   
    $json = new Zend_Json();
    
    while($row2 = $adb->fetchByAssoc($list_result))
    {
        $related_to = "";
        $pid = $row2["pid"];
        $recipientid = $row2["recipientid"];
        if(strpos($recipientid, "@")) list($recipientid) = explode("@",$recipientid); 
        
        $to_emails = html_entity_decode($row2["to_email"], ENT_QUOTES);
        $To_emails = $json->decode($to_emails);

        $Id_Lists = explode("|",$row2["idlists"]);

        if ($recipientid == "email")
        {
            $recipient_name = substr($row2["recipientid"], 6);
        }
        else
        {
            $seType = getSalesEntityType($recipientid);
        	$entityNames = getEntityName($seType, $recipientid);
            
            $to_name = $recipient_name = $entityNames[$recipientid];
            
            $n = 0;
            foreach($Id_Lists AS $idstr)
            {
                list($id,$itype) = explode("@",$idstr);
                if ($id == $recipientid)
                {
                    $to_name = vt_suppressHTMLTags($To_emails[$n]);
                    break;
                }
                $n++;
            }
 
            if (substr($to_name, 0, 2) == '["') $to_name = substr($to_name, 2);
            if (substr($to_name, -2) == '"]') $to_name = substr($to_name, 0, -2);

            if (substr($to_name, 0, 7) == '[&quot;') $to_name = substr($to_name, 7);
            if (substr($to_name, -7) == '&quot;]') $to_name = substr($to_name, 0, -7);
            

            if (isPermitted($seType,'DetailView',$recipientid) == 'yes')
            {
                $recipient_name = '<a href="index.php?module='.$seType.'&action=DetailView&return_module=Documents&return_action=DetailView&record='.$recipientid.'&parenttab='.vtlib_purify($_REQUEST['parenttab']).'" alt="'.$recipient_name.'">'.$to_name.'</a>'; 
            }
            
        }
       
        if ($pid != "" && $pid != "0" )
        {
            $seType2 = getSalesEntityType($pid);
        	$entityNames2 = getEntityName($seType2, $pid);
            
            $related_to = $entityNames2[$pid];
            if (isPermitted($seType2,'DetailView',$pid) == 'yes')
            {
                $related_to = '<a href="index.php?module='.$seType2.'&action=DetailView&return_module=Documents&return_action=DetailView&record='.$pid.'&parenttab='.vtlib_purify($_REQUEST['parenttab']).'">'.$related_to.'</a>'; 
            }
        }
       
        $email_sent = $row2["email_date_sent"];
        
        if ($row2["email_flag"] != "SENT")
        {
            $is_sent = $app_strings["no"];
        }
        else
        {
            $is_sent = $app_strings["yes"];
            
            //$total_sent_emails++;
        }
        
        $access_count = $row2["access_count"];

        $urldata = "module=EMAILMaker&action=EMAILMakerAjax&file=DetailViewEmail&emailid=".$record."&ajxaction=LOADRECIPIENTSLIST".$url_qry."&start=".$start."&delete=true&activityid=".$row2["activityid"]."&email=".$row2["emailid"]; 
        $actions = "<a href=\"javascript:;\" onClick=\"if(confirm('".$app_strings["NTC_DELETE_CONFIRMATION"]."'))loadEMAILMakerRecipientsListBlock('".$urldata."','tbl_EMAILMaker_Recipients','EMAILMaker_Recipients');\">".$app_strings["LBL_DELETE"];
        
        if ($access_count == "") $access_count = "0";

        $Recipients[] = array("subject"=> $row2["subject"],
                              "activityid"=> $row2["activityid"],
                              "recipient_name" => $recipient_name,
                              "related_to" => $related_to,
                              "is_sent"=>$is_sent,
                              "email_sent"=> $email_sent,
                              "error"=>$row2["error"],
                              "access_count"=>$access_count,
                              "actions"=>$actions);	
    } 

         
    $smarty->assign("RECIPIENTS", $Recipients);
    
    if(!$is_ajax)
    {
        $smarty->assign("EMAILS_SENT_FROM", $emails_sent_from);
        
        $smarty->assign("SUBJECT", decode_html($emailResult["subject"]));
        $smarty->assign("EMAIL_BODY", decode_html($emailResult["body"]));
        
        $smarty->assign("NUMBER_OF_RECIPIENTS", $number_of_recipients);
        $smarty->assign("TOTAL_SENT_EMAILS", $total_sent_emails);
        //$smarty->assign("TOTAL_DELIVERED_EMAILS", $total_delivered_emails);
        
        $smarty->assign("TOTAL_OPEN_EMAILS", $total_open_emails);
        
        $first_email_sent = $emailResult["first_email_sent"];
        $last_email_sent = $emailResult["last_email_sent"];
        
        $smarty->assign("FIRST_EMAIL_SENT", $first_email_sent);
        $smarty->assign("LAST_EMAIL_SENT", $last_email_sent);
    }
}

$smarty->assign("VIEW_TYPE", "Emails"); 
$smarty->assign("VIEW_CONTENT", "DetailViewEmail"); 

global $tmp_dir;

//$total_sent_emails += 1000;

$unsent_emails = $number_of_recipients - $total_sent_emails;

$total_not_open_emails = $number_of_recipients - $unsent_emails - $total_open_emails;

$referdata = "";
/*
if ($unsent_emails > 0) $referdata = $unsent_emails."::";
$referdata .= $total_open_emails."::".$total_not_open_emails;

$refer_code = "";
if ($unsent_emails > 0) $refer_code = $unsent_emails." ".$mod_strings["LBL_UNSENT"]."::";

$refer_code .= $total_open_emails." ".$mod_strings["LBL_OPENED"]."::".$total_not_open_emails." ".$mod_strings["LBL_UNOPENED"];
*/ 
 
$width = "250px";
$height = "240px";

$left = $right = $top = $bottom = "10px";
$title = "";

$target_val = "";

$cache_file_name = $tmp_dir."email_analyse_".$record;
$html_image_name = "email_analyse_".$record;

$referdata1 = $total_sent_emails."::".$total_open_emails."::".$total_not_open_emails;
$refer_code1 = $mod_strings["LBL_SENT"]."::".$mod_strings["LBL_OPENED"]."::".$mod_strings["LBL_UNOPENED"];

$referdata2 = $total_open_emails."::".$total_not_open_emails;
$refer_code2 = $mod_strings["LBL_OPENED"]."::".$mod_strings["LBL_UNOPENED"];

// vertical_graph, horizontal_graph, pie_chart

$graph1 = vertical_graph($referdata1,$refer_code1,$width,$height,$left,$right,$top,$bottom,$title,$target_val,$cache_file_name."_1",$html_image_name."_1");
$smarty->assign("GRAPH1", $graph1);

$graph2 = pie_chart($referdata2,$refer_code2,$width,$height,$left,$right,$top,$bottom,$title,$target_val,$cache_file_name."_2",$html_image_name."_2");
$smarty->assign("GRAPH2", $graph2); 

$smarty->assign("RECORDID", $record); 


$smarty = $EMAILMaker->actualizeSmarty($smarty);

if($is_ajax)
    $smarty->display("modules/EMAILMaker/DetailViewEmailContent.tpl");
else
    $smarty->display("modules/EMAILMaker/EMAILMaker.tpl");

function getEmailRecipientsTableHeaderNavigation($navigation_array, $url_qry, $module, $recordid) {
	global $log, $app_strings, $adb;
	$log->debug("Entering getEmailRecipientsTableHeaderNavigation(" . $navigation_array . "," . $url_qry . "," . $module . "," . $recordid . ") method ...");
	global $theme;

	$header = "Recipients";
	$actions = $relatedListRow['actions'];
	$functionName = $relatedListRow['name'];

	$urldata = "module=$module&action={$module}Ajax&file=DetailViewEmail&emailid={$recordid}&ajxaction=LOADRECIPIENTSLIST{$url_qry}";

	$formattedHeader = str_replace(' ', '', $header);
	$target = 'tbl_' . $module . '_' . $formattedHeader;
	$imagesuffix = $module . '_' . $formattedHeader;

	$output = '<td align="right" style="padding="5px;">';
	if (($navigation_array['prev']) != 0) {
		$output .= '<a href="javascript:;" onClick="loadEMAILMakerRecipientsListBlock(\'' . $urldata . '&start=1\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . vtiger_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		$output .= '<a href="javascript:;" onClick="loadEMAILMakerRecipientsListBlock(\'' . $urldata . '&start=' . $navigation_array['prev'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><img src="' . vtiger_imageurl('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
	} else {
		$output .= '<img src="' . vtiger_imageurl('start_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . vtiger_imageurl('previous_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}

	$jsHandler = "return VT_disableFormSubmit(event);";
	$output .= "<input class='small' name='pagenum' type='text' value='{$navigation_array['current']}'
		style='width: 3em;margin-right: 0.7em;' onchange=\"loadEMAILMakerRecipientsListBlock('{$urldata}&start='+this.value+'','{$target}','{$imagesuffix}');\"
		onkeypress=\"$jsHandler\">";
	$output .= "<span name='listViewCountContainerName' class='small' style='white-space: nowrap;'>";
	$computeCount = $_REQUEST['withCount'];
	if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true
			|| ((boolean) $computeCount) == true) {
		$output .= $app_strings['LBL_LIST_OF'] . ' ' . $navigation_array['verylast'];
	} else {
		$output .= "<img src='" . vtiger_imageurl('windowRefresh.gif', $theme) . "' alt='" . $app_strings['LBL_HOME_COUNT'] . "'
			onclick=\"loadEMAILMakerRecipientsListBlock('{$urldata}&withCount=true&start={$navigation_array['current']}','{$target}','{$imagesuffix}');\"
			align='absmiddle' name='" . $module . "_listViewCountRefreshIcon'/>
			<img name='" . $module . "_listViewCountContainerBusy' src='" . vtiger_imageurl('vtbusy.gif', $theme) . "' style='display: none;'
			align='absmiddle' alt='" . $app_strings['LBL_LOADING'] . "'>";
	}
	$output .= '</span>';

	if (($navigation_array['next']) != 0) {
		$output .= '<a href="javascript:;" onClick="loadEMAILMakerRecipientsListBlock(\'' . $urldata . '&start=' . $navigation_array['next'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><img src="' . vtiger_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		$output .= '<a href="javascript:;" onClick="loadEMAILMakerRecipientsListBlock(\'' . $urldata . '&start=' . $navigation_array['verylast'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><img src="' . vtiger_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
	} else {
		$output .= '<img src="' . vtiger_imageurl('next_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . vtiger_imageurl('end_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}
	$output .= '</td>';
	$log->debug("Exiting getTableHeaderNavigation method ...");
	if ($navigation_array['first'] == '')
		return;
	else
		return $output;
}
?>
