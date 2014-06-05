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

global $mod_strings, $app_strings, $currentModule, $current_user, $adb;
Debugger::GetInstance()->Init();

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);

$PDFMaker = new PDFMaker();
if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "save") {
    $sql = "DELETE FROM vtiger_pdfmaker_usersettings WHERE userid=?";
    $adb->pquery($sql, array($current_user->id));

    $save_sett = array();

    $save_sett["is_notified"] = 0;
    if (isset($_REQUEST["is_notified"]) && $_REQUEST["is_notified"] == "on")
        $save_sett["is_notified"] = 1;

    $sql = "INSERT INTO vtiger_pdfmaker_usersettings(userid, is_notified) VALUES(?,?)";
    $adb->pquery($sql, array($current_user->id, $save_sett["is_notified"]));

    echo '<meta http-equiv="refresh" content="0;url=index.php?module=Settings&action=ModuleManager&module_settings=true&formodule=PDFMaker&parenttab=Settings">';
}
else {
    $settings = $PDFMaker->GetUserSettings();
    if ($settings["is_notified"] == "1")
        $smarty->assign("IS_NOTIFIED_CHECKED", 'checked="checked"');

    $smarty->display(vtlib_getModuleTemplate($currentModule, 'MyPreferences.tpl'));
}
