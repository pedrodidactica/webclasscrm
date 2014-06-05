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
global $default_language;
global $current_language;
global $site_URL;
global $current_user;
    
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$EMAILMaker = new EmailMaker(); 
if($EMAILMaker->CheckPermissions("DETAIL") == false)
  $EMAILMaker->DieDuePermission();

$smarty = new vtigerCRM_Smarty;

$category = getParentTab();
$smarty->assign("CATEGORY",$category);


  
if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) {
    $smarty->assign("EDIT","permitted");
    //$smarty->assign("IMPORT","yes");
} else {
    header("Location:index.php?module=EMAILMaker&action=ListDripEmails&parenttab=Tools");
    exit;
}

if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) {
  $smarty->assign("DELETE","permitted");
}

if(isset($_REQUEST['dripid']) && $_REQUEST['dripid']!='')
{
  	$dripid = $_REQUEST['dripid'];
  	
  	$sql1 = "SELECT * FROM vtiger_emakertemplates_drips WHERE dripid=?";
  	$result1 = $adb->pquery($sql1, array($dripid));
  	$dripemailResult = $adb->fetch_array($result1);

    $dripname = $dripemailResult["dripname"];
    $selected_module = $dripemailResult["module"];
}
else
{
    $dripid = "";
    $dripname = "";
    $dripemailResult = array();
    $selected_module = "";
}

$Delay = array("days"=> "0", "hours"=>"0", "minutes"=>"0");
 
if(isset($_REQUEST['driptplid']) && $_REQUEST['driptplid']!='')
{
    $driptplid = $_REQUEST['driptplid'];
  	
  	$sql2 = "SELECT vtiger_emakertemplates.templateid, vtiger_emakertemplates.templatename, vtiger_emakertemplates_drip_tpls.*
             FROM  vtiger_emakertemplates_drip_tpls 
             LEFT JOIN  vtiger_emakertemplates
                ON vtiger_emakertemplates.templateid = vtiger_emakertemplates_drip_tpls.templateid
              WHERE driptplid =?";
  	$result2 = $adb->pquery($sql2, array($driptplid));
  	$driptemplateResult = $adb->fetch_array($result2);
    
    $templateid  = $driptemplateResult["templateid"];
    $templatename  = $driptemplateResult["templatename"];
    
    $dtime = $driptemplateResult["delay"];

    $Delay["days"] = floor($dtime / 86400);
    $Delay["hours"] = floor(($dtime - ($Delay["days"] * 86400))/ 3600);
    $Delay["minutes"] = ($dtime - (($Delay["days"] * 86400) + ($Delay["hours"] * 3600)))/ 60;
    
    
} 
else
{
    $driptplid = "";
    $templateid = "";  
    $templatename = "";   

}     
 
$smarty->assign("DRIPTPLID",$driptplid);
$smarty->assign("DRIPNAME",$dripname);
$smarty->assign("TEMPLATEID",$templateid);
$smarty->assign("TEMPLATENAME",$templatename);

$smarty->assign("DELAY",$Delay);

$Delay_Minutes = array("0"=>"0","15"=>"15","30"=>"30","45"=>"45");
$smarty->assign("DELAY_MINUTES",$Delay_Minutes);

$smarty->assign("DESCRIPTION", $dripemailResult["description"]);

if ($templateid!="")
  $smarty->assign("EMODE", "edit");  

$smarty->assign("DRIPID", $dripid);
$smarty->assign("MODULENAME", getTranslatedString($select_module));
$smarty->assign("SELECTMODULE", $select_module);

$smarty->assign("MOD",$mod_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("APP", $app_strings);
$smarty->assign("PARENTTAB", getParentTab());

if(file_exists("modules/Calendar/language/$default_language.lang.php"))
  $Calendar_Mod_Strings = return_specified_module_language($current_language, "Calendar");    
else 
  $Calendar_Mod_Strings = return_specified_module_language("en_us", "Calendar");

$smarty->assign("CMOD", $Calendar_Mod_Strings); 

$smarty->assign("VIEW_TYPE", "DripEmails"); 

$smarty->assign("VIEW_TYPE", "DripEmails"); 
$smarty->assign("VIEW_CONTENT", "EditDripEmailTemplates");

$Email_Templates_To_Drip = $EMAILMaker->getEmailTemplatesToDrip($selected_module);
$smarty->assign("EMAIL_TEMPLATES_TO_DRIP", $Email_Templates_To_Drip); 


$smarty->display('modules/EMAILMaker/EMAILMaker.tpl');

?>
