<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once('data/Tracker.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');
require_once('modules/PDFMaker/PDFMaker.php');

global $adb;
global $mod_strings;
global $app_strings;
global $current_language;
global $theme;
$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";

Debugger::GetInstance()->Init();

$PDFMaker = new PDFMaker();
if ($PDFMaker->CheckPermissions("DETAIL") == false) {
    $output = '<table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
              <tr>
                <td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;text-align:center;">
                  <strong>' . $app_strings["LBL_PERMISSION"] . '</strong>
                </td>
              </tr>              		
              </table>';
    die($output);
}

$record = $_REQUEST["record"];

$sql = "SELECT setype FROM vtiger_crmentity WHERE crmid = '" . $record . "'";
$relmodule = $adb->query_result($adb->query($sql), 0, "setype");

$smarty = new vtigerCRM_smarty;

$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("MOD", $mod_strings);

$smarty->assign("MODULE", $relmodule);
$smarty->assign("IMAGE_PATH", $image_path);

$smarty->assign("ID", $_REQUEST["record"]);

if (is_dir("modules/PDFMaker/mpdf")) {
    if ($PDFMaker->CheckPermissions("DETAIL"))
        $smarty->assign("ENABLE_PDFMAKER", 'true');
}

if (is_dir("modules/EMAILMaker") && vtlib_isModuleActive('EMAILMaker')) {
    $res = $adb->query("SELECT * FROM vtiger_links WHERE tabid = '" . getTabId($relmodule) . "' AND linktype = 'DETAILVIEWWIDGET' AND linkurl LIKE 'module=EMAILMaker&action=EMAILMakerAjax&file=getEMAILActions&record=%'");
    if ($adb->num_rows($res) > 0)
        $smarty->assign("ENABLE_EMAILMAKER", 'true');
}

$smarty->assign('PDFMAKER_MOD', return_module_language($current_language, "PDFMaker"));

if (!isset($_SESSION["template_languages"]) || $_SESSION["template_languages"] == "") {
    $temp_res = $adb->query("SELECT label, prefix FROM vtiger_language WHERE active=1");
    while ($temp_row = $adb->fetchByAssoc($temp_res)) {
        $template_languages[$temp_row["prefix"]] = $temp_row["label"];
    }
    $_SESSION["template_languages"] = $template_languages;
}

$smarty->assign('TEMPLATE_LANGUAGES', $_SESSION["template_languages"]);
$smarty->assign('CURRENT_LANGUAGE', $current_language);

$userid = 0;
if (isset($_SESSION["authenticated_user_id"]))
    $userid = $_SESSION["authenticated_user_id"];

$templates = $PDFMaker->GetAvailableTemplates($relmodule);

if (count($templates) > 0)
    $no_templates_exist = 0;
else
    $no_templates_exist = 1;

$smarty->assign('CRM_TEMPLATES', $templates);
$smarty->assign('CRM_TEMPLATES_EXIST', $no_templates_exist);

//Action permission handling
//edit and export
$editAndExportAction = "1";
if (isPermitted($relmodule, "EditView", $record) == "no")
    $editAndExportAction = "0";
//save as doc
$saveDocAction = "1";
if (isPermitted("Documents", "EditView") == "no")
    $saveDocAction = "0";
//send email with pdf
$sendEmailPDF = "1";
if (isPermitted("Emails", "") == "no" && isPermitted("MailManager", "") == "no") {
    $sendEmailPDF = "0";
}
if (is_dir("modules/EMAILMaker") && vtlib_isModuleActive('EMAILMaker')) {
    if (isPermitted("EMAILMaker", "") == "no" && $sendEmailPDF = "0")
        $sendEmailPDF = "0";
    else
        $sendEmailPDF = "1";
}
//export to rtf
$exportToRTF = "1";
if($PDFMaker->CheckPermissions("EXPORT_RTF") === false)
    $exportToRTF = "0";

$smarty->assign("EDIT_AND_EXPORT_ACTION", $editAndExportAction);
$smarty->assign("SAVE_AS_DOC_ACTION", $saveDocAction);
$smarty->assign("SEND_EMAIL_PDF_ACTION", $sendEmailPDF);
$smarty->assign("EXPORT_TO_RTF_ACTION", $exportToRTF);

$category = getParentTab();
$smarty->assign("CATEGORY", $category);
$smarty->display(vtlib_getModuleTemplate($currentModule, 'PDFMakerActions.tpl'));
