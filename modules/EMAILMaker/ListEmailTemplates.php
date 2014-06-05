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

require_once('include/utils/UserInfoUtil.php');
global $app_strings;
global $mod_strings;
global $theme,$default_charset;
global $currentModule;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$_SESSION['open_emailmaker_site'] = "ListEmailTemplates";

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

$version_type = $EMAILMaker->GetVersionType();
$smarty->assign("VERSION_TYPE",$version_type);

if($EMAILMaker->CheckPermissions("EDIT") && $version_type != "deactivate" ) {
  $smarty->assign("EDIT","permitted");
  $smarty->assign("IMPORT","yes");
}

if($EMAILMaker->CheckPermissions("DELETE") && $version_type != "deactivate" ) {
  $smarty->assign("DELETE","permitted");
}

if (isset($_REQUEST["filter_module"]) && $_REQUEST["filter_module"] != "")
    $show_module = addslashes($_REQUEST["filter_module"]);
else
    $show_module = "all";

$return_data = $EMAILMaker->GetListviewData($show_module,$orderby, $dir);
$smarty->assign("EMAILTEMPLATES",$return_data);

if($EMAILMaker->CheckPermissions("EDIT")) {
	$smarty->assign("EXPORT","yes");
}

$show_modules["all"] = $app_strings["LBL_ALL"];
$show_modules["none"] = $app_strings["LBL_NONE"];

$show_modules = $EMAILMaker->GetListviewModules($show_modules);

$smarty->assign('SHOWMODULES', $show_modules);

$smarty->assign('SHOWMODULE', $show_module);

$smarty->assign("VIEW_TYPE", "EmailTemplates"); 
$smarty->assign("VIEW_CONTENT", "ListEmailTemplates"); 

$smarty = $EMAILMaker->actualizeSmarty($smarty);

$smarty->display("modules/EMAILMaker/EMAILMaker.tpl");   

?>         