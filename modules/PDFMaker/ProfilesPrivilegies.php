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

global $mod_strings, $app_strings, $theme, $currentModule, $adb;
Debugger::GetInstance()->Init();

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);

$PDFMaker = new PDFMaker();
$permissions = $PDFMaker->GetProfilesPermissions();
$profilesActions = $PDFMaker->GetProfilesActions();
$actionEDIT = getActionid($profilesActions["EDIT"]);
$actionDETAIL = getActionid($profilesActions["DETAIL"]);
$actionDELETE = getActionid($profilesActions["DELETE"]);
$actionEXPORT_RTF = getActionid($profilesActions["EXPORT_RTF"]);

if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "save") {
    foreach ($permissions as $profileid => $subArr) {
        foreach ($subArr as $actionid => $perm) {
            $sql = "DELETE FROM vtiger_pdfmaker_profilespermissions WHERE profileid = ? AND operation = ?";
            $adb->pquery($sql, array($profileid, $actionid));
            if (isset($_REQUEST["priv_chk_" . $profileid . "_" . $actionid]) && $_REQUEST["priv_chk_" . $profileid . "_" . $actionid] == "on") {
                $params = array($profileid, $actionid, "0");
            } else {
                $params = array($profileid, $actionid, "1");
            }
            $sql = "INSERT INTO vtiger_pdfmaker_profilespermissions (profileid, operation, permissions) VALUES(?, ?, ?)";
            $adb->pquery($sql, $params);
        }
    }

    echo '<meta http-equiv="refresh" content="0; url=index.php?module=Settings&action=ModuleManager&module_settings=true&formodule=PDFMaker&parenttab=Settings">';
} else {
    $permissionNames = array();
    foreach ($permissions as $profileid => $subArr) {
        $permissionNames[$profileid] = array();
        $profileName = getProfileName($profileid);
        foreach ($subArr as $actionid => $perm) {
            $permStr = ($perm == "0" ? 'checked="checked"' : "");
            switch ($actionid) {
                case $actionEDIT:
                    $permissionNames[$profileid][$profileName]["EDIT"]["name"] = 'priv_chk_' . $profileid . '_' . $actionEDIT;
                    $permissionNames[$profileid][$profileName]["EDIT"]["checked"] = $permStr;
                    break;

                case $actionDETAIL:
                    $permissionNames[$profileid][$profileName]["DETAIL"]["name"] = 'priv_chk_' . $profileid . '_' . $actionDETAIL;
                    $permissionNames[$profileid][$profileName]["DETAIL"]["checked"] = $permStr;
                    break;

                case $actionDELETE:
                    $permissionNames[$profileid][$profileName]["DELETE"]["name"] = 'priv_chk_' . $profileid . '_' . $actionDELETE;
                    $permissionNames[$profileid][$profileName]["DELETE"]["checked"] = $permStr;
                    break;

                case $actionEXPORT_RTF:
                    $permissionNames[$profileid][$profileName]["EXPORT_RTF"]["name"] = 'priv_chk_' . $profileid . '_' . $actionEXPORT_RTF;
                    $permissionNames[$profileid][$profileName]["EXPORT_RTF"]["checked"] = $permStr;
                    break;
            }
        }
    }

    $smarty->assign("PERMISSIONS", $permissionNames);
    $smarty->display(vtlib_getModuleTemplate($currentModule, 'ProfilesPrivilegies.tpl'));
}
