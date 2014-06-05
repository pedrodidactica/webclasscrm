<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('modules/PDFMaker/PDFMaker.php');
Debugger::GetInstance()->Init();

$PDFMaker = new PDFMaker();
if ($PDFMaker->CheckPermissions("DELETE") == false)
    $PDFMaker->DieDuePermission();

$id_array = array();

if (isset($_REQUEST['templateid']) && $_REQUEST['templateid'] != "") {
    $templateid = $_REQUEST['templateid'];

    $checkSql = "select module from vtiger_pdfmaker where templateid=?";
    $checkRes = $adb->pquery($checkSql, array($templateid));
    $checkRow = $adb->fetchByAssoc($checkRes);
    //if we are trying to delete template that is not allowed for current user then die because user should not be able to see the template
    $PDFMaker->CheckTemplatePermissions($checkRow["module"], $templateid);

    $sql = "delete from vtiger_pdfmaker where templateid=?";
    $adb->pquery($sql, array($templateid));

    $sql = "delete from vtiger_pdfmaker_settings where templateid=?";
    $adb->pquery($sql, array($templateid));
} else {
    $idlist = $_REQUEST['idlist'];
    $id_array = explode(';', $idlist);

    $params = rtrim(implode(",", $id_array), ",");

    $checkSql = "select templateid, module from vtiger_pdfmaker where templateid IN (" . $params . ")";
    $checkRes = $adb->query($checkSql);
    $checkArr = array();
    while ($checkRow = $adb->fetchByAssoc($checkRes)) {
        $checkArr[$checkRow["templateid"]] = $checkRow["module"];
    }

    for ($i = 0; $i < count($id_array) - 1; $i++) {
        //if we are trying to delete template that is not allowed for current user then die because user should not be able to see the template
        $PDFMaker->CheckTemplatePermissions($checkArr[$id_array[$i]], $id_array[$i]);
        $sql = "delete from vtiger_pdfmaker where templateid=?";
        $adb->pquery($sql, array($id_array[$i]));

        $sql = "delete from vtiger_pdfmaker_settings where templateid=?";
        $adb->pquery($sql, array($id_array[$i]));
    }
}
header("Location:index.php?module=PDFMaker&action=ListPDFTemplates&parenttab=Tools");
exit;
