<?php
/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('Smarty_setup.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');

global $adb, $mod_strings, $app_strings, $theme;

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$smarty = new vtigerCRM_smarty;

$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("MOD", $mod_strings);

$smarty->assign("MODULE", 'Tools');
$smarty->assign("IMAGE_PATH", $image_path);

$specified_license_key = $_REQUEST["license_key"];

include_once("version.php");

$sql = "SELECT version_type, license_key FROM vtiger_emakertemplates_license";
$result = $adb->query($sql);
$version_type = $adb->query_result($result,0,"version_type");
$license_key = $adb->query_result($result,0,"license_key");

if ($license_key == "") $license_key = $specified_license_key;

$smarty->assign("VERSION_TYPE",$version_type);
$smarty->assign("VERSION",ucfirst($version_type)." ".$version);
$smarty->assign("LICENSE_KEY",$license_key);

$category = getParentTab();
$smarty->assign("CATEGORY",$category);
$smarty->display(vtlib_getModuleTemplate($currentModule,'DeactivateEMAILMaker.tpl'));
?>