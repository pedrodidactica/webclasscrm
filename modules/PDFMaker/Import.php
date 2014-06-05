<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once('include/utils/CommonUtils.php');
require_once("modules/PDFMaker/PDFMaker.php");

global $mod_strings;
global $current_user;
global $import_dir;

if (!is_uploaded_file($_FILES['userfile']['tmp_name'])) {
    show_error_import($mod_strings['LBL_IMPORT_MODULE_ERROR_NO_UPLOAD']);
    exit;
} else if ($_FILES['userfile']['size'] > $upload_maxsize) {
    show_error_import($mod_strings['LBL_IMPORT_MODULE_ERROR_LARGE_FILE'] . " " . $upload_maxsize . " " . $mod_strings['LBL_IMPORT_MODULE_ERROR_LARGE_FILE_END']);
    exit;
}
if (!is_writable($import_dir)) {
    show_error_import($mod_strings['LBL_IMPORT_MODULE_NO_DIRECTORY'] . $import_dir . $mod_strings['LBL_IMPORT_MODULE_NO_DIRECTORY_END']);
    exit;
}

$tmp_file_name = $import_dir . "IMPORT_" . $current_user->id;

move_uploaded_file($_FILES['userfile']['tmp_name'], $tmp_file_name);

$fh = fopen($tmp_file_name, "r");
$xml_content = fread($fh, filesize($tmp_file_name));
fclose($fh);

$PDFMaker = new PDFMaker();

$xml = new SimpleXMLElement($xml_content);

foreach ($xml->template AS $data) {
    //print_r($data);
    $filename = cdataDecode($data->templatename);
    $nameOfFile = cdataDecode($data->filename);
    $description = cdataDecode($data->description);
    $modulename = cdataDecode($data->module);
    $pdf_format = cdataDecode($data->settings->format);
    $pdf_orientation = cdataDecode($data->settings->orientation);

    $tabid = getTabId($modulename);

    if ($PDFMaker->GetVersionType() == "professional" || in_array($tabid, $PDFMaker->GetBasicModules())) {
        if ($data->settings->margins->top > 0)
            $margin_top = $data->settings->margins->top;
        else
            $margin_top = 0;
        if ($data->settings->margins->bottom > 0)
            $margin_bottom = $data->settings->margins->bottom;
        else
            $margin_bottom = 0;
        if ($data->settings->margins->left > 0)
            $margin_left = $data->settings->margins->left;
        else
            $margin_left = 0;
        if ($data->settings->margins->right > 0)
            $margin_right = $data->settings->margins->right;
        else
            $margin_right = 0;

        $dec_point = cdataDecode($data->settings->decimals->point);
        $dec_decimals = cdataDecode($data->settings->decimals->decimals);
        $dec_thousands = cdataDecode($data->settings->decimals->thousands);

        $header = cdataDecode($data->header);
        $body = cdataDecode($data->body);
        $footer = cdataDecode($data->footer);

        $templateid = $adb->getUniqueID('vtiger_pdfmaker');
        $sql1 = "insert into vtiger_pdfmaker (filename,module,description,body,deleted,templateid) values (?,?,?,?,?,?)";
        $params1 = array($filename, $modulename, $description, $body, 0, $templateid);
        $adb->pquery($sql1, $params1);

        $sql2 = "INSERT INTO vtiger_pdfmaker_settings (templateid, margin_top, margin_bottom, margin_left, margin_right, format, orientation, decimals, decimal_point, thousands_separator, header, footer, encoding, file_name) 
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $params2 = array($templateid, $margin_top, $margin_bottom, $margin_left, $margin_right, $pdf_format, $pdf_orientation, $dec_decimals, $dec_point, $dec_thousands, $header, $footer, "auto", $nameOfFile);
        $adb->pquery($sql2, $params2);

        $PDFMaker->AddLinks($modulename);
    }
}

header("Location:index.php?module=PDFMaker&action=ListPDFTemplates&parenttab=Tools");
exit;

function cdataDecode($text) {
    $From = array("<|!|[%|CDATA|[%|", "|%]|]|>");
    $To = array("<![CDATA[", "]]>");

    $decode_text = str_replace($From, $To, $text);

    return $decode_text;
}
