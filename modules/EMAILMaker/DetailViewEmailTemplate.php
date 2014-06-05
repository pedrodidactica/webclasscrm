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
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');
require_once('modules/EMAILMaker/EMAILMaker.php');
require_once('modules/Documents/Documents.php');
global $adb;
global $log;
global $mod_strings;
global $app_strings;
global $current_language;
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$log->info("Inside Email Template Detail View");

$EMAILMaker = new EmailMaker();
if($EMAILMaker->CheckPermissions("DETAIL") == false)
  $EMAILMaker->DieDuePermission();

$smarty = new vtigerCRM_smarty;

$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("MOD", $mod_strings);

$smarty->assign("MODULE", 'Tools');
$smarty->assign("IMAGE_PATH", $image_path);

$edit_permission = false;
if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) {
    $edit_permission = true;
}

if ($edit_permission)
{
  $smarty->assign("EDIT","permitted");
  $smarty->assign("IMPORT","yes");
  $smarty->assign("EXPORT","yes");
}

$delete_permission = false;
if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) {
    $delete_permission = true; 
}

if ($delete_permission) $smarty->assign("DELETE","permitted");


if(isset($_REQUEST['templateid']) && $_REQUEST['templateid']!='')
{
  	$log->info("The templateid is set");
  	$tempid = $_REQUEST['templateid'];

    $emailtemplateResult = $EMAILMaker->GetDetailViewData($_REQUEST['templateid']);

    $smarty->assign("FILENAME", $emailtemplateResult["templatename"]);
    $smarty->assign("DESCRIPTION", $emailtemplateResult["description"]);
    $smarty->assign("TEMPLATEID", $emailtemplateResult["templateid"]);
    $smarty->assign("MODULENAME", getTranslatedString($emailtemplateResult["module"]));
    $smarty->assign("SUBJECT", decode_html($emailtemplateResult["subject"]));
    $smarty->assign("BODY", decode_html($emailtemplateResult["body"]));
    $smarty->assign("EMAIL_CATEGORY", $emailtemplateResult["category"]);

    $smarty->assign("IS_ACTIVE",  $emailtemplateResult["is_active"]);
    $smarty->assign("IS_DEFAULT", $emailtemplateResult["is_default"]);  
    
    if($delete_permission) $skipActions = false; else $skipActions = true;
    $return_document_value = GetRelatedDocumentsList($emailtemplateResult["templateid"],$skipActions);
    $smarty->assign("RELATEDOCUMENTSDATA", $return_document_value); 
}






$smarty->assign("VIEW_TYPE", "EmailTemplates"); 
$smarty->assign("VIEW_CONTENT", "DetailViewEmailTemplate");

$smarty = $EMAILMaker->actualizeSmarty($smarty);

$smarty->display("modules/EMAILMaker/EMAILMaker.tpl");



function GetRelatedDocumentsList($templateid,$skipActions=false)
{
	$log = LoggerManager::getLogger('account_list');
	$log->debug("Entering GetRelatedDocumentsList(".$templateid.") method ...");

	require_once("data/Tracker.php");
	require_once('include/database/PearDatabase.php');

	global $adb;
	global $app_strings;
	global $current_language;

    $module = "EMAILMaker";
    $relatedmodule = "Documents";

	$current_module_strings = return_module_language($current_language, $module);

	global $urlPrefix;
	global $currentModule;
	global $theme;
	global $theme_path;
	global $theme_path;
	global $mod_strings;
	// focus_list is the means of passing data to a ListView.
	global $focus_list;

    $focus = new Documents();

	// Added to have Purchase Order as form Title
	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";

    $query = "SELECT vtiger_notes.*, vtiger_crmentity.* FROM vtiger_notes 
          INNER JOIN vtiger_crmentity 
             ON vtiger_crmentity.crmid = vtiger_notes.notesid
          INNER JOIN vtiger_emakertemplates_documents 
             ON vtiger_emakertemplates_documents.documentid = vtiger_notes.notesid
          WHERE vtiger_crmentity.deleted = '0' AND vtiger_emakertemplates_documents.templateid = ?";
    
	//Retreive the list from Database
	//Appending the security parameter Security fix by Don
    /*
	global $current_user;
	$secQuery = getNonAdminAccessControlQuery($relatedmodule, $current_user);
	if(strlen($secQuery) > 1) {
		$query = appendFromClauseToQuery($query, $secQuery);
	}
	*/
	$list_result = $adb->pquery($query, array($templateid));
    $num_rows = $adb->num_rows($list_result);
    
	//Retreive the List View Table Header
    $navigation_array = array("start"=>"1");
    
    $focus->sortby_fields = array(); 
    
	$listview_header = getListViewHeader($focus,$relatedmodule,'',$sorder,$order_by,'','',$module,$skipActions);//"Accounts");
	
    $listview_entries = array();
    if ($num_rows > 0)
    {
        $listview_entries = getListViewEntries($focus,$relatedmodule,$list_result,$navigation_array,'relatedlist','','','','','','','',true);
    }

	$related_entries = array('header'=>$listview_header,'entries'=>$listview_entries);

	$log->debug("Exiting GetRelatedDocumentsList method ...");
	return $related_entries;
}

?>
