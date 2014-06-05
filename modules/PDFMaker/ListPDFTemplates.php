<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once('include/database/PearDatabase.php');
require_once('modules/PDFMaker/PDFMaker.php');
require_once('include/utils/UserInfoUtil.php');

global $current_user;
global $app_strings, $mod_strings;
global $theme;
$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";

Debugger::GetInstance()->Init();

$PDFMaker = new PDFMaker();
if ($PDFMaker->CheckPermissions("DETAIL") == false)
    $PDFMaker->DieDuePermission();

$smarty = new vtigerCRM_Smarty;

$orderby = "templateid";
$dir = "asc";

if (isset($_REQUEST["dir"]) && $_REQUEST["dir"] == "desc")
    $dir = "desc";

if (isset($_REQUEST["orderby"])) {
    switch ($_REQUEST["orderby"]) {
        case "name":
            $orderby = "filename";
            break;

        case "module":
            $orderby = "module";
            break;

        case "description":
            $orderby = "description";
            break;

        case "order":
            $orderby = "order";
            break;
    }
}

include("version.php");

$version_type = $PDFMaker->GetVersionType();
$license_key = $PDFMaker->GetLicenseKey();

$smarty->assign("VERSION_TYPE", $version_type);
$smarty->assign("VERSION", ucfirst($version_type) . " " . $version);
$smarty->assign("LICENSE_KEY", $license_key);

// $to_update = "false";
// $smarty->assign("TO_UPDATE",$to_update);  

if ($PDFMaker->CheckPermissions("EDIT")) {
    $smarty->assign("EXPORT", "yes");
}

if ($PDFMaker->CheckPermissions("EDIT") && $PDFMaker->GetVersionType() != "deactivate") {
    $smarty->assign("EDIT", "permitted");
    $smarty->assign("IMPORT", "yes");
}

if ($PDFMaker->CheckPermissions("DELETE") && $PDFMaker->GetVersionType() != "deactivate") {
    $smarty->assign("DELETE", "permitted");
}

$notif = $PDFMaker->GetReleasesNotif();
$smarty->assign("RELEASE_NOTIF", $notif);

//server settings pars
$php_version = phpversion();
$notif = false;
$max_in_vars = ini_get("max_input_vars");
if ($max_in_vars <= 1000 && $php_version >= "5.3.9")
    $notif = true;

$test = ini_set("memory_limit", "256M");
$memory_limit = ini_get("memory_limit");
if (substr($memory_limit, 0, -1) <= 128)
    $notif = true;

$max_exec_time = ini_get("max_execution_time");
if ($max_exec_time <= 60)
    $notif = true;

if (extension_loaded('suhosin')) {
    $request_max_vars = ini_get("suhosin.request.max_vars");
    $post_max_vars = ini_get("suhosin.post.max_vars");
    if ($request_max_vars <= 1000)
        $notif = true;
    if ($post_max_vars <= 1000)
        $notif = true;
}

if ($notif === true) {
    $notif = '<a href="index.php?module=PDFMaker&action=Debugging&parenttab=Settings" title="' . $mod_strings["LBL_GOTO_DEBUG"] . '" style="color:red;">' . $mod_strings["LBL_DBG_NOTIF"] . '</a>';
    $smarty->assign("DEBUG_NOTIF", $notif);
}

// if($PDFMaker->GetVersionType() != "deactivate")
// {
//     $notif = $mod_strings["LBL_NEW_PDFMAKER"];
//
//     $smarty->assign("RELEASE_NOTIF", $notif );
// }

$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("PARENTTAB", getParentTab());
$smarty->assign("IMAGE_PATH", $image_path);

$smarty->assign("ORDERBY", $orderby);
$smarty->assign("DIR", $dir);

$return_data = $PDFMaker->GetListviewData($orderby, $dir);
$smarty->assign("PDFTEMPLATES", $return_data);
$category = getParentTab();
$smarty->assign("CATEGORY", $category);

if (is_admin($current_user)) {
    $smarty->assign('IS_ADMIN', '1');
}

$tool_buttons = Button_Check($currentModule);
$smarty->assign('CHECK', $tool_buttons);

$smarty->display(vtlib_getModuleTemplate($currentModule, 'ListPDFTemplates.tpl'));
