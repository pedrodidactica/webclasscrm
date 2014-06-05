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
global $app_strings;
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

global $mod_strings,$app_strings;

$smarty = new vtigerCRM_Smarty();

$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("APP", $app_strings);
$smarty->assign("MOD", $mod_strings);
//echo "aaaaaa";

$esentid = $_REQUEST["esentid"];

$sql = "SELECT total_emails FROM vtiger_emakertemplates_sent WHERE esentid = '".$esentid."'";
$result = $adb->query($sql);
$total_emails = $adb->query_result($result,0,"total_emails");

$sql2 = "SELECT count(emailid) as total FROM vtiger_emakertemplates_emails WHERE status = '1' AND esentid = '".$esentid."'";
$result2 = $adb->query($sql2);
$sent_emails = $adb->query_result($result2,0,"total"); 

$smarty->assign('ESENTID',$esentid);

$smarty->assign('STRING1',$mod_strings['LBL_PLEASE_DONT_CLOSE_WINDOW']);
$smarty->assign('STRING2',$mod_strings['LBL_POPUP_WILL_BE_CLOSED_AUT']);

$smarty->assign('PROCESS_CONTENT',$sent_emails.' '.$mod_strings["LBL_EMAILS_SENT_FROM"].' '.$total_emails);

$smarty->display("modules/EMAILMaker/EMAILPopUp.tpl");
?>