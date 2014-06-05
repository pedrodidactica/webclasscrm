<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

require_once('Smarty_setup.php');
require_once('include/database/PearDatabase.php');
require_once("include/Zend/Json.php");
require_once('modules/EMAILMaker/EMAILMaker.php');

global $adb, $current_user;

$smarty = new vtigerCRM_Smarty;

$rel_module = $_REQUEST["emailmodule"];
$record = $_REQUEST["record"];

$rel_module_id = getTabid($rel_module);

require_once('include/utils/UserInfoUtil.php');
global $current_language;
global $app_strings;
global $mod_strings;
global $theme,$default_charset;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

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


if ($relblockid != "")
{
    $sql = "UPDATE vtiger_emakertemplates_relblocks SET name = '".$name."', block = '".$block."' WHERE relblockid = '".$relblockid."'";
    $adb->query($sql); 
}
else
{
    $relblockid = $adb->getUniqueID('vtiger_emakertemplates_relblocks');

    $sql = "INSERT INTO vtiger_emakertemplates_relblocks (relblockid, name, module, secmodule, block) VALUES ('".$relblockid."', '".$name."', '".$module."', '".$secmodule."', '".$block."')";
    $adb->query($sql); 

    $selectedcolumnstring = $_REQUEST["selectedColumnsString"];
    $selectedcolumns = explode(";",$selectedcolumnstring);

    for($i=0 ;$i<count($selectedcolumns);$i++)
    {
    	 if(!empty($selectedcolumns[$i]))
       {
    			$icolumnsql = "insert into vtiger_emakertemplates_relblockcol (relblockid,colid,columnname) values (?,?,?)";
    			$icolumnsqlresult = $adb->pquery($icolumnsql, array($relblockid,$i,(decode_html($selectedcolumns[$i]))));
    	 }
    }
}

$advft_criteria = $_REQUEST['advft_criteria'];
if(!empty($advft_criteria)) $advft_criteria = $json->decode($advft_criteria);

$advft_criteria_groups = $_REQUEST['advft_criteria_groups'];
if(!empty($advft_criteria_groups)) $advft_criteria_groups = $json->decode($advft_criteria_groups);

updateEmailRelAdvancedCriteria($relblockid,$advft_criteria,$advft_criteria_groups);
/*
$idelrelcriteriasql = "delete from vtiger_emakertemplates_relblockcriteria where relblockid=?";
$idelrelcriteriasqlresult = $adb->pquery($idelrelcriteriasql, array($relblockid));

$idelrelcriteriagroupsql = "delete from vtiger_emakertemplates_relblockcriteria_g where relblockid=?";
$idelrelcriteriagroupsqlresult = $adb->pquery($idelrelcriteriagroupsql, array($relblockid));

if (count($advft_criteria) > 0)
{
    foreach($advft_criteria as $column_index => $column_condition) 
    {
    		if(empty($column_condition)) continue;
    				
    		$adv_filter_column = $column_condition["columnname"];
    		$adv_filter_comparator = $column_condition["comparator"];
    		$adv_filter_value = $column_condition["value"];
    		$adv_filter_column_condition = $column_condition["columncondition"];
    		$adv_filter_groupid = $column_condition["groupid"];
    	
    		$column_info = explode(":",$adv_filter_column);
    		$temp_val = explode(",",$adv_filter_value);
    		if(($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' ))
    		{
    			$val = Array();
    			for($x=0;$x<count($temp_val);$x++) {
    				list($temp_date,$temp_time) = explode(" ",$temp_val[$x]);
    				$temp_date = getValidDBInsertDateValue(trim($temp_date));
    				$val[$x] = $temp_date;
    				if($temp_time != '') $val[$x] = $val[$x].' '.$temp_time;
    			}
    			$adv_filter_value = implode(",",$val);
    		}
    
    		$irelcriteriasql = "insert into vtiger_emakertemplates_relblockcriteria(relblockid,colid,columnname,comparator,value,groupid,column_condition) values (?,?,?,?,?,?,?)";
    		$irelcriteriaresult = $adb->pquery($irelcriteriasql, array($relblockid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition));
    	
    		// Update the condition expression for the group to which the condition column belongs
    		$groupConditionExpression = '';
    		if(!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
    			$groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
    		}
    		$groupConditionExpression = $groupConditionExpression .' '. $column_index .' '. $adv_filter_column_condition;
    		$advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
    }
}

if (count($advft_criteria_groups) > 0)
{
    foreach($advft_criteria_groups as $group_index => $group_condition_info) 
    {							
      	if(empty($group_condition_info)) continue;
      						
      	$irelcriteriagroupsql = "insert into vtiger_emakertemplates_relblockcriteria_g (groupid,relblockid,group_condition,condition_expression) values (?,?,?,?)";
      	$irelcriteriagroupresult = $adb->pquery($irelcriteriagroupsql, array($group_index, $relblockid, $group_condition_info["groupcondition"], $group_condition_info["conditionexpression"]));
    }
}
*/

$idelreportdatefiltersql = "delete from vtiger_emakertemplates_relblockdatefilter where datefilterid=?";
$idelreportdatefiltersqlresult = $adb->pquery($idelreportdatefiltersql, array($relblockid));

$ireportmodulesql = "insert into vtiger_emakertemplates_relblockdatefilter (datefilterid,datecolumnname,datefilter,startdate,enddate) values (?,?,?,?,?)";
$ireportmoduleresult = $adb->pquery($ireportmodulesql, array($relblockid, $stdDateFilterField, $stdDateFilter, $startdate, $enddate));

header("Location:index.php?module=EMAILMaker&action=EMAILMakerAjax&file=ListRelatedBlocks&parenttab=Tools&emailmodule=".$rel_module);


function updateEmailRelAdvancedCriteria($reportid, $advft_criteria, $advft_criteria_groups) {	

	global $adb, $log;
	
	$idelrelcriteriasql = "delete from vtiger_emakertemplates_relblockcriteria where relblockid=?";
	$idelrelcriteriasqlresult = $adb->pquery($idelrelcriteriasql, array($reportid));

	$idelrelcriteriagroupsql = "delete from vtiger_emakertemplates_relblockcriteria_g where relblockid=?";
	$idelrelcriteriagroupsqlresult = $adb->pquery($idelrelcriteriagroupsql, array($reportid));
	
	if(empty($advft_criteria)) return;

	foreach($advft_criteria as $column_index => $column_condition) {
				
		if(empty($column_condition)) continue;
				
		$adv_filter_column = $column_condition["columnname"];
		$adv_filter_comparator = $column_condition["comparator"];
		$adv_filter_value = $column_condition["value"];
		$adv_filter_column_condition = $column_condition["columncondition"];
		$adv_filter_groupid = $column_condition["groupid"];
	
		$column_info = explode(":",$adv_filter_column);
		$moduleFieldLabel = $column_info[2];
		$fieldName = $column_info[3];

		list($module, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
		$fieldInfo = getEmailRelFieldByReportLabel($module, $fieldLabel);
		$fieldType = null;
		if(!empty($fieldInfo)) {
			$field = WebserviceField::fromArray($adb, $fieldInfo);
			$fieldType = $field->getFieldDataType();
		}
		if($fieldType == 'currency') {
			// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
			if($field->getUIType() == '72') {
				$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value, null, true);
			} else {
				$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value);
			}
		}
		
		$temp_val = explode(",",$adv_filter_value);
		if(($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' ))
		{
			$val = Array();
			for($x=0;$x<count($temp_val);$x++) {
				if(trim($temp_val[$x]) != '') {
					$date = new DateTimeField(trim($temp_val[$x]));
					if($column_info[4] == 'D') {
						$val[$x] = DateTimeField::convertToUserFormat(
								trim($temp_val[$x]));
					} elseif($column_info[4] == 'DT') {
						$val[$x] = $date->getDBInsertDateTimeValue();
					} else {
						$val[$x] = $date->getDBInsertTimeValue();
					}
				}
			}
			$adv_filter_value = implode(",",$val);
		}
        
        $irelcriteriasql = "insert into vtiger_emakertemplates_relblockcriteria(relblockid,colid,columnname,comparator,value,groupid,column_condition) values (?,?,?,?,?,?,?)";
		$irelcriteriaresult = $adb->pquery($irelcriteriasql, array($reportid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition));
	
		// Update the condition expression for the group to which the condition column belongs
		$groupConditionExpression = '';
		if(!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
			$groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
		}
		$groupConditionExpression = $groupConditionExpression .' '. $column_index .' '. $adv_filter_column_condition;
		$advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
	}
	
	foreach($advft_criteria_groups as $group_index => $group_condition_info) {				
				
		if(empty($group_condition_info)) continue;
		if(empty($group_condition_info["conditionexpression"])) continue; // Case when the group doesn't have any column criteria
		
        $irelcriteriagroupsql = "insert into vtiger_emakertemplates_relblockcriteria_g (groupid,relblockid,group_condition,condition_expression) values (?,?,?,?)";
		$irelcriteriagroupresult = $adb->pquery($irelcriteriagroupsql, array($group_index, $reportid, $group_condition_info["groupcondition"], $group_condition_info["conditionexpression"]));
	}
}
?>
