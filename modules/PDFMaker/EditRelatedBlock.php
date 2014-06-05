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
require_once("modules/PDFMaker/classes/Debugger.class.php");
Debugger::GetInstance()->Init();

global $adb, $current_user;

$smarty = new vtigerCRM_Smarty;

require_once('include/utils/UserInfoUtil.php');
global $current_language;
global $app_strings;
global $mod_strings;
global $theme;
$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";

global $current_language;

if (isset($_REQUEST["mode"]) && ($_REQUEST["mode"] == "columns" || $_REQUEST["mode"] == "stdcriteria")) {
    $sec_module = $_REQUEST["secmodule"];

    $pri_module = "";
    if (isset($_REQUEST["primodule"]))
        $pri_module = $_REQUEST["primodule"];


    $module_list = getModuleList($sec_module);

    if ($_REQUEST["mode"] == "stdcriteria") {
        $options = getStdCriteriaByModule($sec_module, $module_list);
        if (count($options) > 0) {
            foreach ($options AS $value => $label) {
                echo "<option value='" . $value . "'>" . $label . "</option>";
            }
        }
    } else {
        foreach ($module_list AS $blockid => $optgroup) {
            $options = getColumnsListbyBlock($sec_module, $blockid, $pri_module);

            if (count($options) > 0) {
                echo "<optgroup label='" . $optgroup . "'>";

                foreach ($options AS $value => $label) {
                    echo "<option value='" . $value . "'>" . $label . "</option>";
                }
                echo "</optgroup>";
            }
        }
    }

    exit;
}

if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "Delete") {
    $record = addslashes($_REQUEST["record"]);

    $sql1 = "SELECT module FROM vtiger_pdfmaker_relblocks WHERE relblockid = '" . $record . "'";
    $rel_module = $adb->query_result($adb->query($sql1), 0, "module");

    $sql2 = "DELETE FROM vtiger_pdfmaker_relblocks WHERE relblockid = '" . $record . "'";
    $result = $adb->Query($sql2);

    header("Location:index.php?module=PDFMaker&action=PDFMakerAjax&file=ListRelatedBlocks&parenttab=Tools&pdfmodule=" . $rel_module);

    exit;
}

if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "add") {
    $record = addslashes($_REQUEST["record"]);

    $sql = "SELECT * FROM vtiger_pdfmaker_relblocks WHERE relblockid = '" . $record . "'";
    $result = $adb->query($sql);
    $Blockdata = $adb->fetchByAssoc($result, 0);

    $body = $Blockdata["block"];

    $body = str_replace("RELBLOCK_START", "RELBLOCK" . $record . "_START", $body);
    $body = str_replace("RELBLOCK_END", "RELBLOCK" . $record . "_END", $body);

    //echo $body; exit;

    echo "<div id='block' style='display:none;'>" . $body . "</div>";

    echo "<script> 
		var oEditor = window.opener.CKEDITOR.instances.body; 
		
		content = document.getElementById('block').innerHTML;
		
		oEditor.insertHtml(content); 
		self.close();
		</script>";

    exit;
}

$rep_mod_strings = return_module_language($current_language, "Reports");

$smarty->assign("REP", $rep_mod_strings);
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("PARENTTAB", getParentTab());
$smarty->assign("THEME_PATH", $theme_path);
$smarty->assign("IMAGE_PATH", $image_path);

$smarty->assign("DATEFORMAT", $current_user->date_format);
$smarty->assign("JS_DATEFORMAT", parse_calendardate($app_strings['NTC_DATE_FORMAT']));

$record = $_REQUEST["record"];

$columns1 = "";
if ($record) {
    $sql = "SELECT * FROM vtiger_pdfmaker_relblocks WHERE relblockid = '" . $record . "'";
    $result = $adb->query($sql);
    $Blockdata = $adb->fetchByAssoc($result, 0);

    $rel_module = $Blockdata["module"];
    $sec_module = $Blockdata["secmodule"];
    $blockname = $Blockdata["name"];
    $block = $Blockdata["block"];

    $sql2 = "SELECT * FROM vtiger_pdfmaker_relblockdatefilter WHERE datefilterid = '" . $record . "'";
    $result2 = $adb->query($sql2);
    $num_rows2 = $adb->num_rows($result2);

    if ($num_rows2 > 0) {
        $datecolumnname = $adb->query_result($result2, 0, "datecolumnname");
        $stdselectedfilter = $adb->query_result($result2, 0, "datefilter");
        $startdate = $adb->query_result($result2, 0, "startdate");
        $enddate = $adb->query_result($result2, 0, "enddate");

        if ($startdate != "" && $startdate != "0000-00-00")
            $smarty->assign("STARTDATE_STD", getValidDisplayDate($startdate));

        if ($enddate != "" && $startdate != "0000-00-00")
            $smarty->assign("ENDDATE_STD", getValidDisplayDate($enddate));
    }


    $module_list = getModuleList($sec_module);

    $options1 = getStdCriteriaByModule($sec_module, $module_list);
    if (count($options1) > 0) {
        foreach ($options1 AS $value => $label) {
            if ($value == $datecolumnname)
                $sel = "selected";
            else
                $sel = "";
            $columns1 .= "<option value='" . $value . "' " . $sel . ">" . $label . "</option>";
        }
    }


    $columns2 = "";
    foreach ($module_list AS $blockid => $optgroup) {
        $options2 = getColumnsListbyBlock($sec_module, $blockid, $rel_module);
        if (count($options2) > 0) {
            $columns2 .= "<optgroup label='" . $optgroup . "'>";
            foreach ($options2 AS $value => $label) {
                $columns2 .= "<option value='" . $value . "'>" . $label . "</option>";
            }
            $columns2 .= "</optgroup>";
        }
    }

    $selected_columns = getSelectedColumnsList($rel_module, $sec_module, $record);
    list($sortCols, $sortOrder) = getSortColumns($record, $selected_columns);
    $advft_criteria = getAdvancedFilterList($record);

//     $smarty->assign("BACK", "false"); // VlZa temporary fix of not saving criteria directly from step 2
} else {
    $rel_module = $_REQUEST["pdfmodule"];
    $block = "";
    $record = "";
    $blockname = "";
    $columns = "";
    $columns2 = "";
    $selected_columns = "";
    $sortCols = "";
    $sortOrder = "";
    $advft_criteria = array();
    $stdselectedfilter = "";
}

$smarty->assign("CRITERIA_GROUPS", $advft_criteria);

$FILTER_OPTION = getAdvCriteriaHTML();
$smarty->assign("FOPTION", $FILTER_OPTION);


$smarty->assign("STDDATEFILTERFIELDS", $columns1);
$smarty->assign("SECCOLUMNS", $columns2);
$smarty->assign("SELECTEDCOLUMNS", $selected_columns);
$smarty->assign("SORTCOLUMNS", $sortCols);
$smarty->assign("SORTORDER", $sortOrder);

$smarty->assign("RECORD", $record);
$smarty->assign("BLOCKNAME", $blockname);


$rel_module_id = getTabid($rel_module);

$restricted_modules = array('Emails', 'Events', 'Webmails');
$Related_Modules = array();

$rsql = "SELECT vtiger_tab.name FROM vtiger_tab 
				INNER JOIN vtiger_relatedlists on vtiger_tab.tabid=vtiger_relatedlists.related_tabid 
				WHERE vtiger_tab.isentitytype=1 
				AND vtiger_tab.name NOT IN(" . generateQuestionMarks($restricted_modules) . ") 
				AND vtiger_tab.presence=0 AND vtiger_relatedlists.label!='Activity History'
        AND vtiger_relatedlists.tabid = '" . $rel_module_id . "' AND vtiger_tab.tabid != '" . $rel_module_id . "'";
$relatedmodules = $adb->pquery($rsql, array($restricted_modules));

if ($adb->num_rows($relatedmodules)) {
    while ($resultrow = $adb->fetch_array($relatedmodules)) {
        $Related_Modules[] = $resultrow['name'];
    }
}


if ($record == "")
    $sec_module = $Related_Modules[0];

$smarty->assign("SEC_MODULE", $sec_module);

$smarty->assign("RELATED_MODULES", $Related_Modules);

include("version.php");
$smarty->assign("VERSION", $version);


$smarty->assign("REL_MODULE", $rel_module);


//Standard criteria S
$BLOCKJS = getRBlockCriteriaJS();
$smarty->assign("BLOCKJS_STD", $BLOCKJS);

if (isset($_REQUEST["record"]) == false || $_REQUEST["record"] == '')
    $BLOCKCRITERIA = getRBlockSelectedStdFilterCriteria();
else
    $BLOCKCRITERIA = getRBlockSelectedStdFilterCriteria($stdselectedfilter);

$smarty->assign("BLOCKCRITERIA_STD", $BLOCKCRITERIA);
//Standard criteria E


$smarty->assign("RELATEDBLOCK", $block);


if (isset($_REQUEST["record"]) == false || $_REQUEST["record"] == '')
    $smarty->display(vtlib_getModuleTemplate($currentModule, 'CreateRelatedBlock.tpl'));
else
    $smarty->display(vtlib_getModuleTemplate($currentModule, 'EditRelatedBlock.tpl'));

function getColumnsListbyBlock($module, $block, $pri_module) {
    global $adb, $current_user;

    if (is_string($block))
        $block = explode(",", $block);

    $tabid = getTabid($module);
    if ($module == 'Calendar') {
        $tabid = array('9', '16');
    }
    $params = array($tabid, $block);

    require('user_privileges/user_privileges_' . $current_user->id . '.php');
    //Security Check 
    if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
        $sql = "select * from vtiger_field where vtiger_field.tabid in (" . generateQuestionMarks($tabid) . ") and vtiger_field.block in (" . generateQuestionMarks($block) . ") and vtiger_field.displaytype in (1,2,3) and vtiger_field.presence in (0,2) ";

        //fix for Ticket #4016
        if ($module == "Calendar")
            $sql.=" group by vtiger_field.fieldlabel order by sequence";
        else
            $sql.=" order by sequence";
    }
    else {

        $profileList = getCurrentUserProfileList();
        $sql = "select * from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid in (" . generateQuestionMarks($tabid) . ")  and vtiger_field.block in (" . generateQuestionMarks($block) . ") and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
        if (count($profileList) > 0) {
            $sql .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
            array_push($params, $profileList);
        }

        //fix for Ticket #4016
        if ($module == "Calendar")
            $sql.=" group by vtiger_field.fieldid,vtiger_field.fieldlabel order by sequence";
        else
            $sql.=" group by vtiger_field.fieldid order by sequence";
    }

    $result = $adb->pquery($sql, $params);
    $noofrows = $adb->num_rows($result);
    for ($i = 0; $i < $noofrows; $i++) {
        $fieldtablename = $adb->query_result($result, $i, "tablename");
        $fieldcolname = $adb->query_result($result, $i, "columnname");
        $fieldname = $adb->query_result($result, $i, "fieldname");
        $fieldtype = $adb->query_result($result, $i, "typeofdata");
        $uitype = $adb->query_result($result, $i, "uitype");
        $fieldtype = explode("~", $fieldtype);
        $fieldtypeofdata = $fieldtype[0];

        //Here we Changing the displaytype of the field. So that its criteria will be displayed correctly in Reports Advance Filter.
        $fieldtypeofdata = ChangeTypeOfData_Filter($fieldtablename, $fieldcolname, $fieldtypeofdata);

        if ($uitype == 68 || $uitype == 59) {
            $fieldtypeofdata = 'V';
        }
        if ($fieldtablename == "vtiger_crmentity") {
            $fieldtablename = $fieldtablename . $module;
        }
        if ($fieldname == "assigned_user_id") {
            $fieldtablename = "vtiger_users" . $module;
            $fieldcolname = "user_name";
        }
        if ($fieldname == "account_id") {
            $fieldtablename = "vtiger_account" . $module;
            $fieldcolname = "accountname";
        }
        if ($fieldname == "contact_id") {
            $fieldtablename = "vtiger_contactdetails" . $module;
            $fieldcolname = "lastname";
        }
        if ($fieldname == "parent_id") {
            $fieldtablename = "vtiger_crmentityRel" . $module;
            $fieldcolname = "setype";
        }
        if ($fieldname == "vendor_id") {
            $fieldtablename = "vtiger_vendorRel" . $module;
            $fieldcolname = "vendorname";
        }
        if ($fieldname == "potential_id") {
            $fieldtablename = "vtiger_potentialRel" . $module;
            $fieldcolname = "potentialname";
        }
        if ($fieldname == "assigned_user_id1") {
            $fieldtablename = "vtiger_usersRel1";
            $fieldcolname = "user_name";
        }
        if ($fieldname == 'quote_id') {
            $fieldtablename = "vtiger_quotes" . $module;
            $fieldcolname = "subject";
        }

        $product_id_tables = array(
            "vtiger_troubletickets" => "vtiger_productsRel",
            "vtiger_campaign" => "vtiger_productsCampaigns",
            "vtiger_faq" => "vtiger_productsFaq",
        );
        if ($fieldname == 'product_id' && isset($product_id_tables[$fieldtablename])) {
            $fieldtablename = $product_id_tables[$fieldtablename];
            $fieldcolname = "productname";
        }
        if ($fieldname == 'campaignid' && $module == 'Potentials') {
            $fieldtablename = "vtiger_campaign" . $module;
            $fieldcolname = "campaignname";
        }
        if ($fieldname == 'currency_id' && $fieldtablename == 'vtiger_pricebook') {
            $fieldtablename = "vtiger_currency_info" . $module;
            $fieldcolname = "currency_name";
        }

        $fieldlabel = $adb->query_result($result, $i, "fieldlabel");
        $fieldlabel1 = str_replace(" ", "_", $fieldlabel);
        $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $fieldlabel1 . ":" . $fieldname . ":" . $fieldtypeofdata;
        //$this->adv_rel_fields[$fieldtypeofdata][] = '$'.$module.'#'.$fieldname.'$'."::".getTranslatedString($module,$module)." ".$fieldlabel;
        //added to escape attachments fields in Reports as we have multiple attachments
        if ($module != 'HelpDesk' || $fieldname != 'filename')
            $module_columnlist[$optionvalue] = getTranslatedString($fieldlabel, $module);
    }
    $blockname = getBlockName($block);
    if ($blockname == 'LBL_RELATED_PRODUCTS' && ($module == 'PurchaseOrder' || $module == 'SalesOrder' || $module == 'Quotes' || $module == 'Invoice')) {
        $fieldtablename = 'vtiger_inventoryproductrel';
        $fields = array('productid' => getTranslatedString('Product Name', $module),
            'serviceid' => getTranslatedString('Service Name', $module),
            'listprice' => getTranslatedString('List Price', $module),
            'discount' => getTranslatedString('Discount', $module),
            'quantity' => getTranslatedString('Quantity', $module),
            'comment' => getTranslatedString('Comments', $module),
        );
        $fields_datatype = array('productid' => 'V',
            'serviceid' => 'V',
            'listprice' => 'I',
            'discount' => 'I',
            'quantity' => 'I',
            'comment' => 'V',
        );
        foreach ($fields as $fieldcolname => $label) {
            $fieldtypeofdata = $fields_datatype[$fieldcolname];
            $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $label . ":" . $fieldcolname . ":" . $fieldtypeofdata;
            $module_columnlist[$optionvalue] = $label;
        }
    } elseif ($pri_module == "PriceBooks" && $blockname == "LBL_PRICING_INFORMATION" && ($module == "Products" || $module == "Services")) {
        $fieldtablename = "vtiger_pricebookproductreltmp" . $module;
        $fieldcolname = "listprice";
        $label = getTranslatedString("LBL_PB_LIST_PRICE", $module);
        $customTmpLabel = "LBL@~@PB@~@LIST@~@PRICE";    // "@~@" stands for "_" that needs special handling because of translation of RB header
        $fieldtypeofdata = "I";
        $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $customTmpLabel . ":" . $fieldcolname . ":" . $fieldtypeofdata;
        $module_columnlist[$optionvalue] = $label;
    }

    return $module_columnlist;
}

function getStdCriteriaByModule($sec_module, $module_list) {
    global $adb, $current_user;
    require('user_privileges/user_privileges_' . $current_user->id . '.php');

    $tabid = getTabid($sec_module);
    foreach ($module_list as $blockid => $key) {
        $blockids[] = $blockid;
    }
    //$blockids = implode("','",$blockids);	

    $params = array($tabid, $blockids);
    if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
        //uitype 6 and 23 added for start_date,EndDate,Expected Close Date
        $sql = "select * from vtiger_field where vtiger_field.tabid=? and (vtiger_field.uitype =5 or vtiger_field.uitype = 6 or vtiger_field.uitype = 23 or vtiger_field.displaytype=2) and vtiger_field.block in (" . generateQuestionMarks($blockids) . ") and vtiger_field.presence in (0,2) order by vtiger_field.sequence";
    } else {
        $profileList = getCurrentUserProfileList();
        $sql = "select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid  where vtiger_field.tabid=? and (vtiger_field.uitype =5 or vtiger_field.displaytype=2) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.block in (" . generateQuestionMarks($blockids) . ") and vtiger_field.presence in (0,2)";
        if (count($profileList) > 0) {
            $sql .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
            array_push($params, $profileList);
        }
        $sql .= " order by vtiger_field.sequence";
    }

    $result = $adb->pquery($sql, $params);

    while ($criteriatyperow = $adb->fetch_array($result)) {
        $fieldtablename = $criteriatyperow["tablename"];
        $fieldcolname = $criteriatyperow["columnname"];
        $fieldlabel = $criteriatyperow["fieldlabel"];

        if ($fieldtablename == "vtiger_crmentity") {
            $fieldtablename = $fieldtablename . $module;
        }
        $fieldlabel1 = str_replace(" ", "_", $fieldlabel);
        $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $fieldlabel1;
        $stdcriteria_list[$optionvalue] = getTranslatedString($fieldlabel, $module);
    }

    return $stdcriteria_list;
}

function getRBlockCriteriaJS() {
    $today = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
    $tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
    $yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

    $currentmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
    $currentmonth1 = date("Y-m-t");
    $lastmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, "01", date("Y")));
    $lastmonth1 = date("Y-m-t", strtotime("-1 Month"));
    $nextmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, "01", date("Y")));
    $nextmonth1 = date("Y-m-t", strtotime("+1 Month"));

    $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
    $lastweek1 = date("Y-m-d", strtotime("-1 week Saturday"));

    $thisweek0 = date("Y-m-d", strtotime("-1 week Sunday"));
    $thisweek1 = date("Y-m-d", strtotime("this Saturday"));

    $nextweek0 = date("Y-m-d", strtotime("this Sunday"));
    $nextweek1 = date("Y-m-d", strtotime("+1 week Saturday"));

    $next7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 6, date("Y")));
    $next30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 29, date("Y")));
    $next60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 59, date("Y")));
    $next90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 89, date("Y")));
    $next120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 119, date("Y")));

    $last7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 6, date("Y")));
    $last30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 29, date("Y")));
    $last60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 59, date("Y")));
    $last90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 89, date("Y")));
    $last120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 119, date("Y")));

    $currentFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
    $currentFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y")));
    $lastFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") - 1));
    $lastFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") - 1));
    $nextFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
    $nextFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") + 1));

    if (date("m") <= 3) {
        $cFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
        $cFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
        $nFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
        $nFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
        $pFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y") - 1));
        $pFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
    } else if (date("m") > 3 and date("m") <= 6) {
        $pFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
        $pFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
        $cFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
        $cFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
        $nFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
        $nFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
    } else if (date("m") > 6 and date("m") <= 9) {
        $nFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
        $nFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
        $pFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
        $pFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
        $cFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
        $cFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
    } else if (date("m") > 9 and date("m") <= 12) {
        $nFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
        $nFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y") + 1));
        $pFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
        $pFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
        $cFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
        $cFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
    }

    $sjsStr = '<script language="JavaScript" type="text/javaScript">
			function showDateRange( type )
			{
				if (type!="custom")
				{
					document.NewBlock.startdate.readOnly=true
					document.NewBlock.enddate.readOnly=true
					getObj("jscal_trigger_date_start").style.visibility="hidden"
					getObj("jscal_trigger_date_end").style.visibility="hidden"
				}
				else
				{
					document.NewBlock.startdate.readOnly=false
					document.NewBlock.enddate.readOnly=false
					getObj("jscal_trigger_date_start").style.visibility="visible"
					getObj("jscal_trigger_date_end").style.visibility="visible"
				}
				if( type == "today" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}
				else if( type == "yesterday" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($yesterday) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($yesterday) . '";
				}
				else if( type == "tomorrow" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($tomorrow) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($tomorrow) . '";
				}        
				else if( type == "thisweek" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($thisweek0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($thisweek1) . '";
				}                
				else if( type == "lastweek" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($lastweek0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($lastweek1) . '";
				}                
				else if( type == "nextweek" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($nextweek0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($nextweek1) . '";
				}                

				else if( type == "thismonth" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($currentmonth0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($currentmonth1) . '";
				}                

				else if( type == "lastmonth" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($lastmonth0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($lastmonth1) . '";
				}             
				else if( type == "nextmonth" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($nextmonth0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($nextmonth1) . '";
				}           
				else if( type == "next7days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next7days) . '";
				}                
				else if( type == "next30days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next30days) . '";
				}                
				else if( type == "next60days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next60days) . '";
				}                
				else if( type == "next90days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next90days) . '";
				}        
				else if( type == "next120days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next120days) . '";
				}        
				else if( type == "last7days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last7days) . '";
					document.NewBlock.enddate.value =  "' . getValidDisplayDate($today) . '";
				}                        
				else if( type == "last30days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last30days) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}                
				else if( type == "last60days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last60days) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}        
				else if( type == "last90days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last90days) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}        
				else if( type == "last120days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last120days) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}        
				else if( type == "thisfy" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($currentFY0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($currentFY1) . '";
				}                
				else if( type == "prevfy" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($lastFY0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($lastFY1) . '";
				}                
				else if( type == "nextfy" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($nextFY0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($nextFY1) . '";
				}                
				else if( type == "nextfq" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($nFq) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($nFq1) . '";
				}                        
				else if( type == "prevfq" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($pFq) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($pFq1) . '";
				}                
				else if( type == "thisfq" )
				{
					document.NewBlock.startdate.value = "' . getValidDisplayDate($cFq) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($cFq1) . '";
				}                
				else
				{
					document.NewBlock.startdate.value = "";
					document.NewBlock.enddate.value = "";
				}        
			}        
		</script>';

    return $sjsStr;
}

function getRBlockSelectedStdFilterCriteria($selecteddatefilter = "") {
    global $rep_mod_strings;

    $datefiltervalue = Array("custom", "prevfy", "thisfy", "nextfy", "prevfq", "thisfq", "nextfq",
        "yesterday", "today", "tomorrow", "lastweek", "thisweek", "nextweek", "lastmonth", "thismonth",
        "nextmonth", "last7days", "last30days", "last60days", "last90days", "last120days",
        "next30days", "next60days", "next90days", "next120days"
    );

    $datefilterdisplay = Array("Custom", "Previous FY", "Current FY", "Next FY", "Previous FQ", "Current FQ", "Next FQ", "Yesterday",
        "Today", "Tomorrow", "Last Week", "Current Week", "Next Week", "Last Month", "Current Month",
        "Next Month", "Last 7 Days", "Last 30 Days", "Last 60 Days", "Last 90 Days", "Last 120 Days",
        "Next 7 Days", "Next 30 Days", "Next 60 Days", "Next 90 Days", "Next 120 Days"
    );

    for ($i = 0; $i < count($datefiltervalue); $i++) {
        if ($selecteddatefilter == $datefiltervalue[$i]) {
            $sshtml .= "<option selected value='" . $datefiltervalue[$i] . "'>" . $rep_mod_strings[$datefilterdisplay[$i]] . "</option>";
        } else {
            $sshtml .= "<option value='" . $datefiltervalue[$i] . "'>" . $rep_mod_strings[$datefilterdisplay[$i]] . "</option>";
        }
    }

    return $sshtml;
}

function getModuleList($sec_module) {
    global $adb;

    $sec_module_id = getTabid($sec_module);
    $reportblocks = $adb->pquery("SELECT blockid, blocklabel, tabid FROM vtiger_blocks WHERE tabid IN (?)", array($sec_module_id));
    $prev_block_label = '';
    if ($adb->num_rows($reportblocks)) {
        while ($resultrow = $adb->fetch_array($reportblocks)) {
            $blockid = $resultrow['blockid'];
            $blocklabel = $resultrow['blocklabel'];
            if (!empty($blocklabel)) {
                if ($sec_module == 'Calendar' && $blocklabel == 'LBL_CUSTOM_INFORMATION')
                    $module_list[$blockid] = getTranslatedString($blocklabel, $sec_module);
                else
                    $module_list[$blockid] = getTranslatedString($blocklabel, $sec_module);
                $prev_block_label = $blocklabel;
            } else {
                $module_list[$blockid] = getTranslatedString($prev_block_label, $sec_module);
            }
        }
    }

    return $module_list;
}

function getSelectedColumnsList($primodule, $secmodule, $relblockid) {
    global $adb;
    global $modules;
    global $current_user;

    $ssql = "select vtiger_pdfmaker_relblockcol.* from vtiger_pdfmaker_relblocks";
    $ssql .= " left join vtiger_pdfmaker_relblockcol on vtiger_pdfmaker_relblockcol.relblockid = vtiger_pdfmaker_relblocks.relblockid";
    $ssql .= " where vtiger_pdfmaker_relblocks.relblockid = ?";
    $ssql .= " order by vtiger_pdfmaker_relblockcol.colid";
    $result = $adb->pquery($ssql, array($relblockid));
    $permitted_fields = Array();

    $selected_mod = split(":", $secmodule);
    array_push($selected_mod, $primodule);

    while ($columnslistrow = $adb->fetch_array($result)) {
        $fieldname = "";
        $fieldcolname = $columnslistrow["columnname"];

        $selmod_field_disabled = true;
        foreach ($selected_mod as $smod) {
            if ((stripos($fieldcolname, ":" . $smod . "_") > -1) && vtlib_isModuleActive($smod)) {
                $selmod_field_disabled = false;
                break;
            }
        }
        if ($selmod_field_disabled == false) {
            list($tablename, $colname, $module_field, $fieldname, $single) = split(":", $fieldcolname);
            require('user_privileges/user_privileges_' . $current_user->id . '.php');
            list($module, $field) = split("_", $module_field);
            if (sizeof($permitted_fields) == 0 && $is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
                $permitted_fields = getaccesfield($module, $primodule, $secmodule);
            }
            $querycolumns = getEscapedColumns($selectedfields, $primodule, $secmodule);
            $fieldlabel = trim(str_replace($module, " ", $module_field));
            $mod_arr = explode('_', $fieldlabel);
            $mod = ($mod_arr[0] == '') ? $module : $mod_arr[0];
            $fieldlabel = trim(str_replace("_", " ", $fieldlabel));
            //modified code to support i18n issue 
            //$mod_lbl = getTranslatedString($mod,$module); //module
            //$fld_lbl = getTranslatedString($fieldlabel,$module); //fieldlabel
            //$fieldlabel = $mod_lbl." ".$fld_lbl;
            $fieldlabel = getTranslatedString($fieldlabel, $module); //fieldlabel

            if (CheckFieldPermission($fieldname, $mod) != 'true' && $colname != "crmid") {
                $shtml .= "<option permission='no' value=\"" . $fieldcolname . "\" disabled = 'true'>" . $fieldlabel . "</option>";
            } else {
                $shtml .= "<option permission='yes' value=\"" . $fieldcolname . "\">" . $fieldlabel . "</option>";
            }
        }
        //end
    }
    return $shtml;
}

function getSortColumns($relblockid, $selected_columns) {
    global $adb, $app_strings, $mod_strings;

    $sql = "SELECT columnname, sortorder, sortsequence
            FROM vtiger_pdfmaker_relblockcol
            WHERE relblockid=? AND sortorder != ''
            ORDER BY sortsequence";
    $result = $adb->pquery($sql, array($relblockid));
    $outputsArr = array();
    $sortOrder = array();

    $selected_columns = '<option value="0">' . $app_strings["LBL_NONE"] . '</option>' . $selected_columns;
    $idx = 1;
    while ($row = $adb->fetchByAssoc($result)) {
        $search = 'value="' . $row["columnname"] . '"';
        $replace = 'value="' . $row["columnname"] . '" selected="selected"';
        $outputsArr[$idx] = str_replace($search, $replace, $selected_columns);

        if ($row["sortorder"] == "Descending") {
            $sortOrder[$idx] = '<option value="Ascending">' . $mod_strings["LBL_ASC"] . '</option>
                                <option value="Descending" selected="selected">' . $mod_strings["LBL_DESC"] . '</option>';
        } else {
            $sortOrder[$idx] = '<option value="Ascending" selected="selected">' . $mod_strings["LBL_ASC"] . '</option>
                                <option value="Descending">' . $mod_strings["LBL_DESC"] . '</option>';
        }
        $idx++;

        $tmpArr = explode("</option>", $selected_columns);
        $selected_columns = "";
        foreach ($tmpArr as $option) {
            if (strpos($option, $search) === false)
                $selected_columns .= $option . '</option>';
        }
    }
    $outputsArr[$idx] = $selected_columns;
    $sortOrder[$idx] = '<option value="Ascending">' . $mod_strings["LBL_ASC"] . '</option>
                        <option value="Descending">' . $mod_strings["LBL_DESC"] . '</option>';

    return array($outputsArr, $sortOrder);
    ;
}

function getaccesfield($module, $primodule, $secmodule) {
    global $adb;
    $access_fields = Array();

    $profileList = getCurrentUserProfileList();
    $query = "select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where";
    $params = array();
    if ($module == "Calendar") {
        $query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
        if (count($profileList) > 0) {
            $query .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
            array_push($params, $profileList);
        }
        $query .= " group by vtiger_field.fieldid order by block,sequence";
    } else {
        array_push($params, $primodule, $secmodule);
        $query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
        if (count($profileList) > 0) {
            $query .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
            array_push($params, $profileList);
        }
        $query .= " group by vtiger_field.fieldid order by block,sequence";
    }
    $result = $adb->pquery($query, $params);


    while ($collistrow = $adb->fetch_array($result)) {
        $access_fields[] = $collistrow["fieldname"];
    }
    return $access_fields;
}

function getEscapedColumns($selectedfields, $primarymodule, $secondarymodule) {
    $fieldname = $selectedfields[3];
    if ($fieldname == "parent_id") {
        if ($primarymodule == "HelpDesk" && $selectedfields[0] == "vtiger_crmentityRelHelpDesk") {
            $querycolumn = "case vtiger_crmentityRelHelpDesk.setype when 'Accounts' then vtiger_accountRelHelpDesk.accountname when 'Contacts' then vtiger_contactdetailsRelHelpDesk.lastname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelHelpDesk.setype 'Entity_type'";
            return $querycolumn;
        }
        if ($primarymodule == "Products" || $secondarymodule == "Products") {
            $querycolumn = "case vtiger_crmentityRelProducts.setype when 'Accounts' then vtiger_accountRelProducts.accountname when 'Leads' then vtiger_leaddetailsRelProducts.lastname when 'Potentials' then vtiger_potentialRelProducts.potentialname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelProducts.setype 'Entity_type'";
        }
        if ($primarymodule == "Calendar" || $secondarymodule == "Calendar") {
            $querycolumn = "case vtiger_crmentityRelCalendar.setype when 'Accounts' then vtiger_accountRelCalendar.accountname when 'Leads' then vtiger_leaddetailsRelCalendar.lastname when 'Potentials' then vtiger_potentialRelCalendar.potentialname when 'Quotes' then vtiger_quotesRelCalendar.subject when 'PurchaseOrder' then vtiger_purchaseorderRelCalendar.subject when 'Invoice' then vtiger_invoiceRelCalendar.subject End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelCalendar.setype 'Entity_type'";
        }
    }
    return $querycolumn;
}

function getAdvancedFilterList($relblockid) {
    global $adb;
    global $modules;
    global $log;
// 	$adb->setDebug(true);
    $advft_criteria = array();

    $sql = 'SELECT * FROM vtiger_pdfmaker_relblockcriteria_g WHERE relblockid = ? ORDER BY relblockid';
    $groupsresult = $adb->pquery($sql, array($relblockid));

    $i = 1;
    $j = 0;
    while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
        $groupId = $relcriteriagroup["groupid"];
        $groupCondition = $relcriteriagroup["group_condition"];

// 		$ssql = 'select vtiger_pdfmaker_relblockcriteria.* from vtiger_pdfmaker_relblocks
// 					inner join vtiger_pdfmaker_relblockcriteria on vtiger_pdfmaker_relblockcriteria.relblockid = vtiger_pdfmaker_relblocks.relblockid
// 					left join vtiger_pdfmaker_relblockcriteria_g on vtiger_pdfmaker_relblockcriteria.relblockid = vtiger_pdfmaker_relblockcriteria_g.relblockid
// 							and vtiger_pdfmaker_relblockcriteria.relblockid = vtiger_pdfmaker_relblockcriteria_g.relblockid';
// 		$ssql .= " where vtiger_pdfmaker_relblocks.relblockid = ? AND vtiger_pdfmaker_relblockcriteria.groupid = ? order by vtiger_pdfmaker_relblockcriteria.colid";

        $ssql = "SELECT rbc.* FROM vtiger_pdfmaker_relblockcriteria AS rbc
                 LEFT JOIN vtiger_pdfmaker_relblockcriteria_g AS rbcg
                    USING(groupid)
                 WHERE rbc.relblockid = ?
                    AND rbc.groupid = ?
                    AND rbcg.relblockid = ?
                 ORDER BY rbc.colid";

        $result = $adb->pquery($ssql, array($relblockid, $groupId, $relblockid));

        //echo $ssql." = ".$relblockid." - ".$groupId;

        $noOfColumns = $adb->num_rows($result);
        if ($noOfColumns <= 0)
            continue;

        while ($relcriteriarow = $adb->fetch_array($result)) {
            $columnIndex = $relcriteriarow["colid"];
            $criteria = array();
            $criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"]);
            $criteria['comparator'] = $relcriteriarow["comparator"];
            $advfilterval = $relcriteriarow["value"];
            $col = explode(":", $relcriteriarow["columnname"]);
            $temp_val = explode(",", $relcriteriarow["value"]);
            if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
                $val = Array();
                for ($x = 0; $x < count($temp_val); $x++) {
                    list($temp_date, $temp_time) = explode(" ", $temp_val[$x]);
                    $temp_date = getValidDisplayDate(trim($temp_date));
                    if (trim($temp_time) != '')
                        $temp_date .= ' ' . $temp_time;
                    $val[$x] = $temp_date;
                }
                $advfilterval = implode(",", $val);
            }
            $criteria['value'] = decode_html($advfilterval);
            $criteria['column_condition'] = $relcriteriarow["column_condition"];

            $advft_criteria[$i]['columns'][$j] = $criteria;
            $advft_criteria[$i]['condition'] = $groupCondition;
            $j++;
        }
        $i++;
    }

    $log->info("Reports :: Successfully returned getAdvancedFilterList");
// 	show($advft_criteria);
    return $advft_criteria;
}

function getAdvCriteriaHTML($selected = "") {
    global $adv_filter_options;

    foreach ($adv_filter_options as $key => $value) {
        if ($selected == $key) {
            $shtml .= "<option selected value=\"" . $key . "\">" . $value . "</option>";
        } else {
            $shtml .= "<option value=\"" . $key . "\">" . $value . "</option>";
        }
    }

    return $shtml;
}
