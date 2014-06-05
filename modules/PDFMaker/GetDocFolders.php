<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

$sql = "select foldername,folderid from vtiger_attachmentsfolder order by foldername";
$res = $adb->pquery($sql, array());
for ($i = 0; $i < $adb->num_rows($res); $i++) {
    $fid = $adb->query_result($res, $i, "folderid");
    $fname = $adb->query_result($res, $i, "foldername");
    $fieldvalue[] = $fid . "@" . $fname;
}


echo implode("###", $fieldvalue);
