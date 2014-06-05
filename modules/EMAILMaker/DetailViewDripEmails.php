<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('Smarty_setup.php');
require_once('include/utils/utils.php');
require_once('modules/EMAILMaker/EMAILMaker.php');

global $app_strings;
global $mod_strings;
global $app_list_strings;
global $adb;
global $upload_maxsize;
global $theme,$default_charset;
global $current_language;
global $site_URL;
global $current_user;
    
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$EMAILMaker = new EmailMaker(); 
if($EMAILMaker->CheckPermissions("DETAIL") == false)
  $EMAILMaker->DieDuePermission();

$smarty = new vtigerCRM_Smarty;

if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) {
    $smarty->assign("EDIT","permitted");
    //$smarty->assign("IMPORT","yes");
}

if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) {
  $smarty->assign("DELETE","permitted");
}

if(isset($_REQUEST['dripid']) && $_REQUEST['dripid']!='')
{
  	$dripid = $_REQUEST['dripid'];
  	
  	$sql = "SELECT * FROM vtiger_emakertemplates_drips WHERE dripid=?";
  	$result = $adb->pquery($sql, array($dripid));
  	$dripemailResult = $adb->fetch_array($result);

    $dripname = $dripemailResult["dripname"];
  	$select_module = $dripemailResult["module"];
    
    $owner = $dripemailResult["owner"];
    $sharingtype = $dripemailResult["sharingtype"];
    $sharingMemberArray = $EMAILMaker->GetSharingMemberArray($dripid,"drip");
}


 
$smarty->assign("DRIPNAME",$dripname);

$smarty->assign("DESCRIPTION", $dripemailResult["description"]);

$smarty->assign("DRIPID", $dripid);
$smarty->assign("SELECTMODULE", getTranslatedString($select_module));

$smarty->assign("DRIP_OWNER",getUserFullName($owner));

$smarty->assign("SHARINGTYPE", $app_strings[strtoupper($sharingtype)."_FILTER"]);

$smarty->assign("MOD",$mod_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("APP", $app_strings);
$smarty->assign("PARENTTAB", getParentTab());


$cmod = return_specified_module_language($current_language, "Settings");
$smarty->assign("CMOD", $cmod);

if(file_exists("modules/Calendar/language/$default_language.lang.php"))
    $Calendar_Mod_Strings = return_specified_module_language($current_language, "Calendar");    
else 
    $Calendar_Mod_Strings = return_specified_module_language("en_us", "Calendar");

$Email_Templates = $EMAILMaker->getDripEmailTemplates($dripid,true);

$smarty->assign("EMAILTEMPLATES", $Email_Templates); 

$smarty->assign("VIEW_TYPE", "DripEmails"); 
$smarty->assign("VIEW_CONTENT", "DetailViewDripEmails");

$smarty = $EMAILMaker->actualizeSmarty($smarty);
$smarty->display('modules/EMAILMaker/EMAILMaker.tpl');

?>
