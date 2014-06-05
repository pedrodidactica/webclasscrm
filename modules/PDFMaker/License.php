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
require_once('modules/PDFMaker/PDFMaker.php');

global $currentModule;

Debugger::GetInstance()->Init();

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("MODULE", $currentModule);

$PDFMaker = CRMEntity::getInstance($currentModule);
$smarty->assign("LICENSE", $PDFMaker->GetLicenseKey());
$smarty->assign("VERSION_TYPE", $PDFMaker->GetVersionType());

$smarty->display(vtlib_getModuleTemplate($currentModule, 'License.tpl'));

$error_text = "";
if (isset($_REQUEST["deactivate"]) && $_REQUEST["deactivate"] != "") {
    switch ($_REQUEST["deactivate"]) {
        case "invalid_key": $error_text = $mod_strings["LBL_INVALID_KEY"];
            break;
        case "failed": $error_text = $mod_strings["LBL_DEACTIVATE_ERROR"];
            break;
        case "ok": $error_text = $mod_strings["LBL_DEACTIVATE_SUCCESS"];
            break;
    }
} elseif (isset($_REQUEST["reactivate"]) && $_REQUEST["reactivate"] != "") {
    switch ($_REQUEST["reactivate"]) {
        case "invalid": $error_text = $mod_strings["LBL_INVALID_KEY"];
            break;
        case "error": $error_text = $mod_strings["REACTIVATE_ERROR"];
            break;
        case "ok": $error_text = $mod_strings["REACTIVATE_SUCCESS"];
            break;
    }
}
if ($error_text != "")
    echo "<script>alert('" . $error_text . "');</script>";
