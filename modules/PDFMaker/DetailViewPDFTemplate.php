<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');
require_once('modules/PDFMaker/PDFMaker.php');

global $mod_strings, $app_strings, $theme;

$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";

Debugger::GetInstance()->Init();

$PDFMaker = new PDFMaker();
if ($PDFMaker->CheckPermissions("DETAIL") == false)
    $PDFMaker->DieDuePermission();

$smarty = new vtigerCRM_smarty;

$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("MOD", $mod_strings);

$smarty->assign("MODULE", 'Tools');
$smarty->assign("IMAGE_PATH", $image_path);

if (isset($_REQUEST['templateid']) && $_REQUEST['templateid'] != '') {
    $pdftemplateResult = $PDFMaker->GetDetailViewData($_REQUEST['templateid']);

    $smarty->assign("FILENAME", $pdftemplateResult["filename"]);
    $smarty->assign("DESCRIPTION", $pdftemplateResult["description"]);
    $smarty->assign("TEMPLATEID", $pdftemplateResult["templateid"]);
    $smarty->assign("MODULENAME", getTranslatedString($pdftemplateResult["module"]));
    $smarty->assign("BODY", decode_html($pdftemplateResult["body"]));
    $smarty->assign("HEADER", decode_html($pdftemplateResult["header"]));
    $smarty->assign("FOOTER", decode_html($pdftemplateResult["footer"]));

    $smarty->assign("IS_ACTIVE", $pdftemplateResult["is_active"]);
    $smarty->assign("IS_DEFAULT", $pdftemplateResult["is_default"]);
    $smarty->assign("ACTIVATE_BUTTON", $pdftemplateResult["activateButton"]);
    $smarty->assign("DEFAULT_BUTTON", $pdftemplateResult["defaultButton"]);
}
include("version.php");

$version_type = $PDFMaker->GetVersionType();

$smarty->assign("VERSION", $version_type . " " . $version);

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

$tool_buttons = Button_Check($currentModule);
$smarty->assign('CHECK', $tool_buttons);

$category = getParentTab();
$smarty->assign("CATEGORY", $category);
$smarty->display(vtlib_getModuleTemplate($currentModule, 'DetailViewPDFTemplate.tpl'));
