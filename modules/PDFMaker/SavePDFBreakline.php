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

$sql = "DELETE FROM vtiger_pdfmaker_breakline WHERE crmid=?";
$adb->pquery($sql, array($crmid));

$breaklines = rtrim($_REQUEST["breaklines"], "|");

if ($breaklines != "") {
    $sql = "INSERT INTO vtiger_pdfmaker_breakline (crmid, productid, sequence, show_header, show_subtotal) VALUES";
    $sql_suf = "";

    $show_header = 0;
    $show_subtotal = 0;
    if ($_REQUEST["show_header"] == "true")
        $show_header = 1;
    if ($_REQUEST["show_subtotal"] == "true")
        $show_subtotal = 1;

    $products = explode("|", $breaklines);
    for ($i = 0; $i < count($products); $i++) {
        list($productid, $sequence) = explode("_", $products[$i], 2);
        $sql_suf.=" (" . $crmid . "," . $productid . "," . $sequence . "," . $show_header . "," . $show_subtotal . "),";
    }

    if ($sql_suf != "") {
        $sql_suf = rtrim($sql_suf, ",");
        $sql .= $sql_suf;
        $adb->query($sql);
    }
}
exit;
