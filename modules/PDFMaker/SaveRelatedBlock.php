<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once('include/database/PearDatabase.php');
require_once("include/Zend/Json.php");
require_once("modules/PDFMaker/classes/Debugger.class.php");
Debugger::GetInstance()->Init();

global $adb;

//$adb->database->debug = true;

$rel_module = $_REQUEST["pdfmodule"];

require_once('include/utils/UserInfoUtil.php');
global $current_language;

global $current_language;


$relblockid = vtlib_purify($_REQUEST["record"]);

$name = vtlib_purify($_REQUEST["blockname"]);
$module = vtlib_purify($_REQUEST["primarymodule"]);
$secmodule = vtlib_purify($_REQUEST["secondarymodule"]);
$block = vtlib_purify($_REQUEST["relatedblock"]);


$stdDateFilterField = vtlib_purify($_REQUEST["stdDateFilterField"]);
$stdDateFilter = vtlib_purify($_REQUEST["stdDateFilter"]);
$startdate = getValidDBInsertDateValue($_REQUEST["startdate"]);
$enddate = getValidDBInsertDateValue($_REQUEST["enddate"]);

$json = new Zend_Json();

$advft_criteria = $_REQUEST['advft_criteria'];
$advft_criteria = $json->decode($advft_criteria);

$advft_criteria_groups = $_REQUEST['advft_criteria_groups'];
$advft_criteria_groups = $json->decode($advft_criteria_groups);


if ($relblockid != "") {
    $sql = "UPDATE vtiger_pdfmaker_relblocks SET name=?, block=? WHERE relblockid=?";
    $adb->pquery($sql, array($name, $block, $relblockid));

    //Sorting of RelatedBlock
    if (isset($_REQUEST["sortColCount"]) && $_REQUEST["sortColCount"] > 0) {
        $sql = "UPDATE vtiger_pdfmaker_relblockcol
                SET sortorder='', sortsequence=NULL
                WHERE relblockid=?";
        $adb->pquery($sql, array($relblockid));

        $seqCounter = 1;
        for ($i = 1; $i <= $_REQUEST["sortColCount"]; $i++) {
            if (isset($_REQUEST["sortCol" . $i]) && isset($_REQUEST["sortDir" . $i])) {
                if ($_REQUEST["sortCol" . $i] != "0") {
                    $sql = "UPDATE vtiger_pdfmaker_relblockcol
                            SET sortorder=?, sortsequence=?
                            WHERE relblockid=?
                              AND columnname=?";
                    $adb->pquery($sql, array($_REQUEST["sortDir" . $i], $seqCounter, $relblockid, $_REQUEST["sortCol" . $i]));
                    $seqCounter++;
                }
            }
        }
    }
    //$sql2 = "delete from vtiger_pdfmaker_relblockcol where relblockid=?";
    //$idelreportsortcolsqlresult = $adb->pquery($sql2, array($relblockid));
} else {
    $relblockid = $adb->getUniqueID('vtiger_pdfmaker_relblocks');

    $sql = "INSERT INTO vtiger_pdfmaker_relblocks (relblockid, name, module, secmodule, block) VALUES (?,?,?,?,?)";
    $adb->pquery($sql, array($relblockid, $name, $module, $secmodule, $block));

    $selectedcolumnstring = $_REQUEST["selectedColumnsString"];
    $selectedcolumns = explode(";", $selectedcolumnstring);

    $sortCols = getSortCols($selectedcolumns);

    for ($i = 0; $i < count($selectedcolumns); $i++) {
        if (!empty($selectedcolumns[$i])) {
            $icolumnsql = "INSERT INTO vtiger_pdfmaker_relblockcol (relblockid,colid,columnname,sortorder,sortsequence)
                               VALUES (?,?,?,?,?)";
            $adb->pquery($icolumnsql, array($relblockid, $i, (decode_html($selectedcolumns[$i])), $sortCols[$i]["order"], $sortCols[$i]["sequence"]));
        }
    }
}

$idelrelcriteriasql = "delete from vtiger_pdfmaker_relblockcriteria where relblockid=?";
$adb->pquery($idelrelcriteriasql, array($relblockid));

$idelrelcriteriagroupsql = "delete from vtiger_pdfmaker_relblockcriteria_g where relblockid=?";
$adb->pquery($idelrelcriteriagroupsql, array($relblockid));

if (count($advft_criteria) > 0) {
    foreach ($advft_criteria as $column_index => $column_condition) {
        if (empty($column_condition))
            continue;

        $adv_filter_column = $column_condition["columnname"];
        $adv_filter_comparator = $column_condition["comparator"];
        $adv_filter_value = $column_condition["value"];
        $adv_filter_column_condition = $column_condition["columncondition"];
        $adv_filter_groupid = $column_condition["groupid"];

        $column_info = explode(":", $adv_filter_column);
        $temp_val = explode(",", $adv_filter_value);
        if (($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' )) {
            $val = Array();
            for ($x = 0; $x < count($temp_val); $x++) {
                list($temp_date, $temp_time) = explode(" ", $temp_val[$x]);
                $temp_date = getValidDBInsertDateValue(trim($temp_date));
                $val[$x] = $temp_date;
                if ($temp_time != '')
                    $val[$x] = $val[$x] . ' ' . $temp_time;
            }
            $adv_filter_value = implode(",", $val);
        }

        $irelcriteriasql = "insert into vtiger_pdfmaker_relblockcriteria(relblockid,colid,columnname,comparator,value,groupid,column_condition) values (?,?,?,?,?,?,?)";
        $$adb->pquery($irelcriteriasql, array($relblockid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition));

        // Update the condition expression for the group to which the condition column belongs
        $groupConditionExpression = '';
        if (!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
            $groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
        }
        $groupConditionExpression = $groupConditionExpression . ' ' . $column_index . ' ' . $adv_filter_column_condition;
        $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
    }
}

if (count($advft_criteria_groups) > 0) {
    foreach ($advft_criteria_groups as $group_index => $group_condition_info) {
        if (empty($group_condition_info))
            continue;

        $irelcriteriagroupsql = "insert into vtiger_pdfmaker_relblockcriteria_g (groupid,relblockid,group_condition,condition_expression) values (?,?,?,?)";
        $adb->pquery($irelcriteriagroupsql, array($group_index, $relblockid, $group_condition_info["groupcondition"], $group_condition_info["conditionexpression"]));
    }
}


$idelreportdatefiltersql = "delete from vtiger_pdfmaker_relblockdatefilter where datefilterid=?";
$adb->pquery($idelreportdatefiltersql, array($relblockid));

$ireportmodulesql = "insert into vtiger_pdfmaker_relblockdatefilter (datefilterid,datecolumnname,datefilter,startdate,enddate) values (?,?,?,?,?)";
$adb->pquery($ireportmodulesql, array($relblockid, $stdDateFilterField, $stdDateFilter, $startdate, $enddate));

//echo '<script>window.opener.location.href=window.opener.location.href;self.close();</script>';

echo "<script>window.opener.refresh_related_blocks_array('" . $relblockid . "');
              self.close();
      </script>";

//header("Location:index.php?module=PDFMaker&action=PDFMakerAjax&file=ListRelatedBlocks&parenttab=Tools&pdfmodule=".$rel_module);

function getSortCols($selectedcolumns) {
    $sortCols = array();
    for ($i = 0; $i < count($selectedcolumns); $i++) {
        $sortCols[$i]["order"] = "";
        $sortCols[$i]["sequence"] = "";
    }

    if (isset($_REQUEST["sortColCount"]) && $_REQUEST["sortColCount"] > 0) {
        $seqCounter = 1;
        for ($i = 1; $i <= $_REQUEST["sortColCount"]; $i++) {
            if (isset($_REQUEST["sortCol" . $i]) && isset($_REQUEST["sortDir" . $i])) {
                $colIdx = array_search($_REQUEST["sortCol" . $i], $selectedcolumns);
                if ($colIdx !== false) {
                    $sortCols[$colIdx]["order"] = $_REQUEST["sortDir" . $i];
                    $sortCols[$colIdx]["sequence"] = $seqCounter++;
                }
            }
        }
    }

    return $sortCols;
}
