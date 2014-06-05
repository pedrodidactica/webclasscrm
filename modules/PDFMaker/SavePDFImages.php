<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
require_once("modules/PDFMaker/classes/Debugger.class.php");
Debugger::GetInstance()->Init();

global $adb;
$crmid = $_REQUEST["pid"];

$sql = "DELETE FROM vtiger_pdfmaker_images WHERE crmid=?";
$adb->pquery($sql, array($crmid));

$sql = "INSERT INTO vtiger_pdfmaker_images (crmid, productid, sequence, attachmentid, width, height) VALUES";
$sql_suf = "";
foreach ($_REQUEST as $key => $value) {
    if (strpos($key, "img_") !== false) {
        list($bin, $productid, $sequence) = explode("_", $key);
        if ($value != "no_image") {
            $width = $_REQUEST["width_" . $productid . "_" . $sequence];
            $height = $_REQUEST["height_" . $productid . "_" . $sequence];
            if (!is_numeric($width) || $width > 999)
                $width = 0;
            if (!is_numeric($height) || $height > 999)
                $height = 0;
        } else {
            $value = 0;
            $width = 0;
            $height = 0;            
        }
        $sql_suf.=" (" . $crmid . "," . $productid . "," . $sequence . "," . $value . "," . $width . "," . $height . "),";
    }
}
if ($sql_suf != "") {
    $sql_suf = rtrim($sql_suf, ",");
    $sql .= $sql_suf;
    $adb->query($sql);
}

exit;
