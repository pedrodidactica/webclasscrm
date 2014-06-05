<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

require_once('Smarty_setup.php');
require_once('include/database/PearDatabase.php');
require_once('modules/EMAILMaker/EMAILMaker.php');

global $adb;
global $list_max_entries_per_page;

require_once('include/utils/UserInfoUtil.php');
global $app_strings;
global $mod_strings;
global $theme,$default_charset;
global $currentModule;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$url_string = '';
if($_REQUEST['query'] == 'true') {
	$url_string .= "&query=true$ustring";
}

$_SESSION['open_emailmaker_site'] = "ListView";


$EMAILMaker = new EmailMaker();

if($EMAILMaker->CheckPermissions("DETAIL") == false)
  $EMAILMaker->DieDuePermission();

$smarty = new vtigerCRM_Smarty;
global $current_language;

$orderby="templateid";
$dir="asc";

$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("PARENTTAB", getParentTab());
$smarty->assign("IMAGE_PATH",$image_path);


if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) {
  $smarty->assign("EDIT","permitted");
  //$smarty->assign("IMPORT","yes");
}

if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) {
  $smarty->assign("DELETE","permitted");
}
$where = "";
$url_drip = "";
if (isset($_REQUEST["dripid"]) && $_REQUEST["dripid"] != "")
{
    $where = " AND drip_group_id = '".addslashes($_REQUEST["dripid"])."'";
    $url_string .= $url_drip = "&dripid=".addslashes($_REQUEST["dripid"]);
    
    
    $sql_d = "SELECT drip_group_name FROM vtiger_emakertemplates_drip_groups WHERE drip_group_id = '".addslashes($_REQUEST["dripid"])."' LIMIT 0,1";
    $result_d = $adb->query($sql_d);
    $drip_subject = $adb->query_result($result_d,0,"drip_group_name"); 
    
    $smarty->assign("DRIP_SUBJECT",$drip_subject);
}

$query = "SELECT vtiger_crmentity.crmid,
        CASE WHEN vtiger_emakertemplates_emails.esentid IS NULL THEN '' ELSE vtiger_emakertemplates_sent.related_to END AS related_to,
        CASE WHEN vtiger_emakertemplates_emails.esentid IS NULL THEN 'standard' ELSE 'emailmaker' END AS email_type,
        CASE WHEN vtiger_emakertemplates_emails.esentid IS NULL THEN concat('S',vtiger_crmentity.crmid) ELSE vtiger_emakertemplates_emails.esentid END AS esentids,
        CASE WHEN vtiger_emakertemplates_emails.esentid IS NULL THEN count(vtiger_seactivityrel.crmid) ELSE vtiger_emakertemplates_sent.total_emails END AS num_total_emails,
        CASE WHEN vtiger_emakertemplates_emails.esentid IS NULL THEN vtiger_activity.subject ELSE vtiger_emakertemplates_sent.subject END AS subject,
        CASE WHEN vtiger_emakertemplates_emails.esentid IS NULL THEN vtiger_crmentity.createdtime ELSE min(
                                                                                                           CASE WHEN vtiger_emakertemplates_emails.email_send_date > vtiger_crmentity.createdtime THEN vtiger_emakertemplates_emails.email_send_date ELSE vtiger_crmentity.createdtime END 
                                                                                                           ) END AS first_email,
        CASE WHEN vtiger_emakertemplates_emails.esentid IS NULL THEN vtiger_crmentity.createdtime ELSE max(
                                                                                                           CASE WHEN vtiger_emakertemplates_emails.email_send_date > vtiger_crmentity.createdtime THEN vtiger_emakertemplates_emails.email_send_date ELSE vtiger_crmentity.createdtime END 
                                                                                                           ) END AS last_email,
        MIN(vtiger_crmentity.createdtime) AS firstcreatedtime,
        count(vtiger_email_track.crmid) AS total_open_emails,
        vtiger_emakertemplates_emails.esentid,
        vtiger_emakertemplates_drip_groups.drip_group_id,
        vtiger_emakertemplates_drip_groups.drip_group_name,
        CASE WHEN vtiger_emakertemplates_emails.esentid IS NULL THEN 
            CASE WHEN  vtiger_emaildetails.email_flag = 'SENT' THEN count(vtiger_seactivityrel.crmid)  ELSE 0 END
        ELSE vtiger_emakertemplates_sent.total_sent_emails END AS total_sent_emails
        FROM vtiger_crmentity
        INNER JOIN vtiger_activity
            ON vtiger_activity.activityid = vtiger_crmentity.crmid
        INNER JOIN vtiger_emaildetails
            ON vtiger_emaildetails.emailid = vtiger_crmentity.crmid
        LEFT JOIN vtiger_emakertemplates_emails
            ON vtiger_emakertemplates_emails.parent_id = vtiger_crmentity.crmid 
        LEFT JOIN vtiger_emakertemplates_sent 
            ON vtiger_emakertemplates_sent.esentid = vtiger_emakertemplates_emails.esentid
        LEFT JOIN vtiger_seactivityrel 
            ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
        LEFT JOIN vtiger_email_track
            ON vtiger_email_track.crmid = vtiger_seactivityrel.crmid AND vtiger_email_track.mailid = vtiger_activity.activityid
        LEFT JOIN vtiger_emakertemplates_drip_groups
            ON vtiger_emakertemplates_drip_groups.drip_group_id  = vtiger_emakertemplates_sent.drip_group 
        WHERE vtiger_crmentity.deleted = 0 AND vtiger_crmentity.setype = 'Emails' ".$where." GROUP BY esentids HAVING num_total_emails > 0";   
$result = $adb->query($query);
$noofrows = $adb->num_rows($result);

if (isset($_REQUEST["order_by"])) $_SESSION['rlvs'][$currentModule]["e1"]['sorder'] = $_REQUEST["order_by"]; 
if (isset($_REQUEST["sorder"])) $_SESSION['rlvs'][$currentModule]["e1"]['sortby'] = $_REQUEST["sorder"]; 

if($_SESSION['rlvs'][$currentModule]["e1"]) {
	$order_by = $_SESSION['rlvs'][$currentModule]["e1"]['sorder'];
	$sorder = $_SESSION['rlvs'][$currentModule]["e1"]['sortby'];
} else {
	$order_by = "first_email";
	$sorder = "DESC";
}               

$query .= ' ORDER BY '.$order_by.' '.$sorder;
$url_string .="&order_by=".$order_by."&sorder=".$sorder;

$queryMode = (isset($_REQUEST['query']) && $_REQUEST['query'] == 'true');
$start = ListViewSession::getRequestCurrentPage("EMAILMaker", $query, "e1", $queryMode);

$change_sorder = array('ASC' => 'DESC', 'DESC' => 'ASC');
$arrow_gif = array('ASC' => 'arrow_down.gif', 'DESC' => 'arrow_up.gif');

$Sortby_Fields = array("email_type","subject","first_email","last_email","num_total_emails","total_open_emails","total_sent_emails");

$Header_Fields = array("subject"=>$mod_strings["LBL_EMAIL_SUBJECT"],
                       //"email_type"=>$mod_strings["LBL_EMAIL_TYPE"],
                       "is_drip"=>$mod_strings["LBL_DRIP_NAME"],
                       "related"=>$app_strings["RELATED"],
                       "first_email"=>$mod_strings["LBL_FIRST_EMAIL_SENT"],
                       "last_email"=>$mod_strings["LBL_LAST_EMAIL_SENT"],
                       "num_total_emails"=>$mod_strings["LBL_NUMBER_OF_RECIPIENTS"],
                       "total_sent_emails"=>$mod_strings["LBL_TOTAL_SENT_EMAILS"],
                       "total_open_emails"=>$mod_strings["LBL_TOTAL_OPEN_EMAILS"]);

$listview_header = array();                      
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
        $name = "<a href='javascript:void(0);' onClick='getListViewEntries_js(\"$currentModule\",\"&order_by=$col&sorder=$temp_sorder&start=".$start.$url_drip."\");' class='listFormHeaderLinks'>" . $lbl_name . "" . $arrow . "</a>";

        $arrow = '';
	}
    else
    {
        $name = $lbl_name;
    }
    $listview_header[] = $name;
}                      

$smarty->assign("LISTHEADER", $listview_header); 






$navigation_array = VT_getSimpleNavigationValues($start,$list_max_entries_per_page,$noofrows);

$limit_start_rec = ($start-1) * $list_max_entries_per_page;

if( $adb->dbType == "pgsql")
	$list_result = $adb->pquery($query. " OFFSET $limit_start_rec LIMIT $list_max_entries_per_page", array());
else
	$list_result = $adb->pquery($query. " LIMIT $limit_start_rec, $list_max_entries_per_page", array());

$recordListRangeMsg = getRecordRangeMessage($list_result, $limit_start_rec,$noofrows);
$smarty->assign('recordListRange',$recordListRangeMsg);

$noofrows = $adb->num_rows($list_result);

while($row = $adb->fetchByAssoc($list_result))
{
    $LData = array();
    
    $is_drip = "";
    $email_type = $row["email_type"]; 
    
    if ($row["drip_group_id"] != "") 
    {
        $email_type = "".$mod_strings["LBL_DRIP"];
        if ($row["drip_group_name"] != "") $is_drip = "<a href='index.php?module=EMAILMaker&action=ListView&dripid=".$row["drip_group_id"]."'>".$row["drip_group_name"]."</a>";
    }
    
    $related = "";
    if ($row["related_to"])
    {
        $parent_module =  getSalesEntityType($row["related_to"]);
        $parent_name = getEntityName($parent_module, $row["related_to"]);
        
        if ($related != "") $related .= " &gt; ";
        
        if ($parent_module == "Campaigns")
            $related .= "<a href='index.php?module=Campaigns&action=DetailView&record=".$row["related_to"]."'>".$parent_name[$row["related_to"]]."</a>";
        else
            $related .= $parent_name[$row["related_to"]];
    }
    
    
    $LData["subject"] = "<a href='index.php?action=DetailViewEmail&module=EMAILMaker&emailid=".$row["esentids"]."&parenttab=Tools'>".$row["subject"]; 
    //$LData["email_type"] = $email_type;
    $LData["is_drip"] = $is_drip;
    $LData["related"] = $related;
   
    $LData["first_email"] = $row["first_email"]; 
    $LData["last_email"] = $row["last_email"]; 
    $LData["total_emails"] = $row["num_total_emails"];
   
   
    if ($row["total_sent_emails"] == "") $row["total_sent_emails"] = "0";
    $LData["total_sent_emails"] = $row["total_sent_emails"];
    
    $LData["total_open_emails"] = $row["total_open_emails"];
    
    
    $listview_entries[$row["crmid"]] = $LData;
    
}

$smarty->assign("LISTENTITY", $listview_entries);

$navigationOutput = getTableHeaderSimpleNavigation($navigation_array, $url_string,"EMAILMaker","index","ei");
$smarty->assign("NAVIGATION", $navigationOutput);

$smarty->assign("VIEW_TYPE", "Emails"); 
$smarty->assign("VIEW_CONTENT", "ListEmails"); 

$smarty = $EMAILMaker->actualizeSmarty($smarty);

if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] != '')
	$smarty->display("modules/EMAILMaker/ListEmailsEntries.tpl");
else
    $smarty->display("modules/EMAILMaker/EMAILMaker.tpl");


?>
