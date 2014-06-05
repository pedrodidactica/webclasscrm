<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
require_once('modules/PDFMaker/PDFMaker.php');
$PDFMaker = new PDFMaker();

$fieldvalue = array();

if ($PDFMaker->CheckPermissions("DETAIL") !== false) {
    $workflowid = addslashes($_REQUEST["workflowid"]);
    $sql1 = "SELECT module_name FROM com_vtiger_workflows WHERE workflow_id = '" . $workflowid . "'";
    $module_name = $adb->query_result($adb->query($sql1), 0, 'module_name');

    $templates = $PDFMaker->GetAvailableTemplates($module_name);
    $def_template = array();
    $idx = 1;
    foreach ($templates as $templateid => $valArr) {
        if ($valArr["is_default"] == "1" || $valArr["is_default"] == "3")
            $def_template[$idx] = $templateid . "@" . $valArr["templatename"];
        else
            $fieldvalue[$idx] = $templateid . "@" . $valArr["templatename"];
        $idx++;
    }
    $fieldvalue = (array) $def_template + (array) $fieldvalue;
}

if (count($fieldvalue) == 0)
    $fieldvalue[] = "0@none";
    
//load the PDF languages
$langvalue = array();
$temp_res = $adb->query("SELECT label, prefix FROM vtiger_language WHERE active=1");
$currlang = array();
$idx = 1;
while ($temp_row = $adb->fetchByAssoc($temp_res)) {
    $template_languages[$temp_row["prefix"]] = $temp_row["label"];

	if($temp_row["prefix"] == $current_language)
		$currlang[$idx] = $temp_row["prefix"] . "@" .$temp_row["label"];
	else
		$langvalue[$idx] = $temp_row["prefix"] . "@" .$temp_row["label"];
	$idx++;
}
$langvalue = (array) $currlang + (array) $langvalue;

$response = implode("###", $fieldvalue)."%%%".implode("###", $langvalue);

echo $response;
?>
