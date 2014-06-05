<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

global $adb, $current_user;
// $adb->setDebug(true);
$templateid = $_REQUEST["templateid"];
$subject = $_REQUEST["subjectChanged"];

//query to vtiger_pdfmaker_settings to check if the template is just for listiview or not
$sql = "SELECT is_listview FROM vtiger_pdfmaker_settings WHERE templateid=?";
$result = $adb->pquery($sql, array($templateid));
if ($adb->query_result($result, 0, "is_listview") == "1")
    $set_default_val = "2";
else
    $set_default_val = "3";

$sql = "SELECT *
      FROM vtiger_pdfmaker_userstatus
      WHERE templateid=? AND userid=?";
$result = $adb->pquery($sql, array($templateid, $current_user->id));

if ($adb->num_rows($result) > 0) {
    if ($subject == "active")
        $sql = "UPDATE vtiger_pdfmaker_userstatus SET is_active=IF(is_active=0,1,0), is_default=IF(is_active=0,0,is_default) WHERE templateid=? AND userid=?";
    elseif ($subject == "default")
        $sql = "UPDATE vtiger_pdfmaker_userstatus SET is_default=IF(is_default > 0,0," . $set_default_val . ") WHERE templateid=? AND userid=?";
}
else {
    if ($subject == "active")
        $sql = "INSERT INTO vtiger_pdfmaker_userstatus(templateid,userid,is_active,is_default) VALUES(?,?,0,0)";
    elseif ($subject == "default")
        $sql = "INSERT INTO vtiger_pdfmaker_userstatus(templateid,userid,is_active,is_default) VALUES(?,?,1," . $set_default_val . ")";
}
$adb->pquery($sql, array($templateid, $current_user->id));

$sql = "SELECT is_default, module
      FROM vtiger_pdfmaker_userstatus
      INNER JOIN vtiger_pdfmaker USING(templateid)
      WHERE templateid=? AND userid=?";
$result = $adb->pquery($sql, array($templateid, $current_user->id));
$new_is_default = $adb->query_result($result, 0, "is_default");
$module = $adb->query_result($result, 0, "module");

if ($new_is_default == $set_default_val) {
    $sql5 = "UPDATE vtiger_pdfmaker_userstatus 
	       INNER JOIN vtiger_pdfmaker USING(templateid)
	       SET is_default=0
	       WHERE is_default > 0
             AND userid=?
             AND module=?
             AND templateid!=?";
    $adb->pquery($sql5, array($current_user->id, $module, $templateid));
}

echo '<meta http-equiv="refresh" content="0;url=index.php?action=DetailViewPDFTemplate&module=PDFMaker&templateid=' . $templateid . '&parenttab=Tools" />';
