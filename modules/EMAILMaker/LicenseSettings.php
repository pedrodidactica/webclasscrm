<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
require_once('Smarty_setup.php');
require_once('include/CustomFieldUtil.php');
require_once('include/utils/UserInfoUtil.php');
require_once('modules/EMAILMaker/EMAILMaker.php');
require_once('include/utils/utils.php');
require_once 'modules/PickList/PickListUtils.php';

global $mod_strings,$app_strings,$log,$theme,$current_language;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$smarty=new vtigerCRM_Smarty;
$subMode = $_REQUEST['sub_mode'];
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$settings_strings = return_specified_module_language($current_language, 'Settings');
$smarty->assign("SMOD",$settings_strings);
$smarty->assign("JS_DATEFORMAT",parse_calendardate($app_strings['NTC_DATE_FORMAT']));
$module = vtlib_purify($_REQUEST['module']);

$EMAILMaker = new EmailMaker();
$smarty->assign("LICENSE",$EMAILMaker->GetLicenseKey());
$smarty->assign("VERSION_TYPE",$EMAILMaker->GetVersionType());
$tabid = $_REQUEST['tabid'];
$currentsequence = $_REQUEST['sequence'];
$smarty->assign("THEME", $theme);
$cfimagecombo = Array(
	$image_path."text.gif",
	$image_path."number.gif",
	$image_path."percent.gif",
	$image_path."currency.gif",
	$image_path."date.gif",
	$image_path."email.gif",
	$image_path."phone.gif",
	$image_path."picklist.gif",
	$image_path."url.gif",
	$image_path."checkbox.gif",
	$image_path."text.gif",
	$image_path."picklist.gif"
	);

$cftextcombo = Array(
	$mod_strings['Text'],
	$mod_strings['Number'],
	$mod_strings['Percent'],
	$mod_strings['Currency'],
	$mod_strings['Date'],
	$mod_strings['Email'],
	$mod_strings['Phone'],
	$mod_strings['PickList'],
	$mod_strings['LBL_URL'],
	$mod_strings['LBL_CHECK_BOX'],
	$mod_strings['LBL_TEXT_AREA'],
	$mod_strings['LBL_MULTISELECT_COMBO']
	);
$smarty->assign("CFTEXTCOMBO",$cftextcombo);
$smarty->assign("CFIMAGECOMBO",$cfimagecombo);
$smarty->assign("MODULE",$module);
$smarty->assign("MODE", $mode);
$smarty->display(vtlib_getModuleTemplate($module,'LicenseSettings.tpl'));

$error_text = "";
if (isset($_REQUEST["deactivate"]) && $_REQUEST["deactivate"] != "")
{
    switch($_REQUEST["deactivate"])
    {
        case "invalid_key": $error_text = $mod_strings["LBL_INVALID_KEY"]; break;
        case "failed": $error_text = $mod_strings["LBL_DEACTIVATE_ERROR"]; break;
    }
}

if (isset($_REQUEST["reactivate"]) && $_REQUEST["reactivate"] != "")
{
    switch($_REQUEST["reactivate"])
    {
        case "error": $error_text = $mod_strings["LBL_INVALID_KEY"]; break;
        case "ok": $error_text = $mod_strings["REACTIVATE_SUCCESS"]; break;
    }
}
if ($error_text != "") echo "<script>alert('".$error_text."');</script>";
?>