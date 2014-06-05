<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once("include/utils/utils.php");

global $mod_strings, $app_strings, $theme, $currentModule;
$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);

$extensions = Array();

$extname = "CustomerPortal";
$extensions[$extname]["label"] = $mod_strings["LBL_CUSTOMERPORTAL"];
$extensions[$extname]["desc"] = $mod_strings["LBL_CUSTOMERPORTAL_DESC"];
$extensions[$extname]["exinstall"] = $mod_strings["LBL_CP_EXPRESS_INSTAL_EXT"];
$extensions[$extname]["manual"] = "index.php?module=PDFMaker&action=downloadfile&parenttab=Tools&extid=$extname&mode=manual";
$extensions[$extname]["download"] = "index.php?module=PDFMaker&action=downloadfile&parenttab=Tools&extid=$extname&mode=download";

$extname = "Workflow";
$extensions[$extname]["label"] = $mod_strings["LBL_WORKFLOW"];
$extensions[$extname]["desc"] = $mod_strings["LBL_WORKFLOW_DESC"];
$extensions[$extname]["exinstall"] = $mod_strings["LBL_EXPRESS_INSTAL_EXT"];
$extensions[$extname]["manual"] = "index.php?module=PDFMaker&action=downloadfile&parenttab=Tools&extid=$extname&mode=manual";
$extensions[$extname]["download"] = "index.php?module=PDFMaker&action=downloadfile&parenttab=Tools&extid=$extname&mode=download";

$smarty->assign("EXTENSIONS_ARR", $extensions);

if (isset($_SESSION["download_error"]) && $_SESSION["download_error"] != "") {
    unset($_SESSION["download_error"]);
    $smarty->assign("ERROR", "true");
}

$smarty->display(vtlib_getModuleTemplate($currentModule, 'Extensions.tpl'));
