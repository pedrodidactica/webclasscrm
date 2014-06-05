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
require_once("modules/com_vtiger_workflow/VTWorkflowUtils.php");
require_once('modules/PDFMaker/PDFMaker.php');

global $mod_strings, $app_strings, $theme;

Debugger::GetInstance()->Init();

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", "$theme");
$smarty->assign("IMAGE_PATH", "themes/$theme/images/");

// Operation to be restricted for non-admin users.
global $current_user;
if (!is_admin($current_user)) {
    $smarty->display(vtlib_getModuleTemplate('Vtiger', 'OperationNotPermitted.tpl'));
} else {
    $module = vtlib_purify($_REQUEST['formodule']);
    $PDFMaker = new PDFMaker();
    $menu_array = $PDFMaker->GetAvailableSettings();

    //add blanks for 3-column layout
    $count = count($menu_array) % 3;
    if ($count > 0) {
        for ($i = 0; $i < 3 - $count; $i++) {
            $menu_array[] = array();
        }
    }

    $smarty->assign('MODULE', $module);
    $smarty->assign('MODULE_LBL', getTranslatedString($module));
    $smarty->assign('MENU_ARRAY', $menu_array);

    $smarty->display(vtlib_getModuleTemplate('Vtiger', 'Settings.tpl'));
}
