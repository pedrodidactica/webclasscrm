<?php

$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Module.php');
$module = Vtiger_Module::getInstance('PDFMaker');
if ($module) {
    require("modules/PDFMaker/DeactivateLicense.php");
    $module->delete();
    @shell_exec('rm -R modules/PDFMaker');
    @shell_exec('rm -R Smarty/templates/modules/PDFMaker');
}
include_once('include/utils/utils.php');
$adb->setDebug(true);
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_breakline");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_ignorepicklistvalues");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_images");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_labels"); // obsolete
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_label_keys");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_label_vals");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_license");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_productbloc_tpl");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_profilespermissions");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_relblockcol");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_relblockcriteria");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_relblockcriteria_g");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_relblockdatefilter");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_relblocks");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_relblocks_seq");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_releases");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_seq");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_settings");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_sharing");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_usersettings");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_userstatus");
$adb->query("DROP TABLE IF EXISTS vtiger_pdfmaker_version");
