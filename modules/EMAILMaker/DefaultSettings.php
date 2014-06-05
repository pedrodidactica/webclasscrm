<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('Smarty_setup.php');
require_once("include/utils/utils.php");

global $mod_strings,$app_strings,$theme,$currentModule,$app_list_strings;
$smarty=new vtigerCRM_Smarty;
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$smarty->assign("THEME", $theme);

if (isset($_REQUEST["mode"]))
    $mode = addslashes($_REQUEST["mode"]);
else
    $mode = "view";

if ($mode == "save")
{
    $phpmailer_version = $_REQUEST["phpmailer_version"];
    
    $sql_u = "UPDATE vtiger_emakertemplates_settings SET phpmailer_version = ?";
    $adb->pquery($sql_u, array($phpmailer_version));
    
    $mode = "view";
}

$smarty->assign("MODE", $mode);

$sql = "SELECT * FROM vtiger_emakertemplates_settings";
$result = $adb->pquery($sql, array());
	
$phpmailer_version = $adb->query_result($result,0,'phpmailer_version');
      
$Settings_Data = array();

$o1_selected = $o2_selected = "";

if ($phpmailer_version == "emailmaker")
{   
    $o2_selected = "selected";
    
    $view = "5.2.1";
}
else
{
    $o1_selected = "selected"; 
    
    $view = $mod_strings["LBL_STANDARD_VTIGER_VERSION"]; 
}
$edit .= '<select name="phpmailer_version">';
$edit .= '<option value="standard" '.$o1_selected.'>'.$mod_strings["LBL_STANDARD_VTIGER_VERSION"].'</option>';
$edit .= '<option value="emailmaker" '.$o2_selected.'>5.2.1</option>';
$edit .= '</select>';




$Settings_Data[] = array("label" => $mod_strings["PHPMailer_Setting"], "view"=> $view, "edit"=>$edit);

$smarty->assign("SETTINGS_DATA", $Settings_Data);



$smarty->display(vtlib_getModuleTemplate($currentModule,'DefaultSettings.tpl'));


?>