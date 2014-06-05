<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once('include/utils/utils.php');
require_once('modules/PDFMaker/PDFMaker.php');

global $app_strings;
global $mod_strings;
global $adb;
global $theme;
global $current_language, $default_language, $current_user;

$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";

Debugger::GetInstance()->Init();

$PDFMaker = new PDFMaker();
if ($PDFMaker->CheckPermissions("EDIT") == false)
    $PDFMaker->DieDuePermission();

$smarty = new vtigerCRM_Smarty;

if (isset($_REQUEST['templateid']) && $_REQUEST['templateid'] != '') {
    $templateid = $_REQUEST['templateid'];
    $pdftemplateResult = $PDFMaker->GetEditViewData($templateid);

    $select_module = $pdftemplateResult["module"];
    $select_format = $pdftemplateResult["format"];
    $select_orientation = $pdftemplateResult["orientation"];
    $nameOfFile = $pdftemplateResult["file_name"];
    $is_portal = $pdftemplateResult["is_portal"];
    $is_listview = $pdftemplateResult["is_listview"];
    $is_active = $pdftemplateResult["is_active"];
    $is_default = $pdftemplateResult["is_default"];
    $order = $pdftemplateResult["order"];
    $owner = $pdftemplateResult["owner"];
    $sharingtype = $pdftemplateResult["sharingtype"];
    $sharingMemberArray = $PDFMaker->GetSharingMemberArray($templateid);
    $disp_header = $pdftemplateResult["disp_header"];
    $disp_footer = $pdftemplateResult["disp_footer"];
} else {
    $templateid = "";

    if (isset($_REQUEST["return_module"]) && $_REQUEST["return_module"] != "")
        $select_module = $_REQUEST["return_module"];
    else
        $select_module = "";
    
    $select_format = "A4";
    $select_orientation = "portrait";
    //$select_encoding = "utf-8";
    $nameOfFile = "";
    $is_portal = "0";
    $is_listview = "0";
    $is_active = "1";
    $is_default = "0";
    $order = "1";
    $owner = $current_user->id;
    $sharingtype = "public";
    $sharingMemberArray = array();
    $disp_header = "3";
    $disp_footer = "7";
}

$PDFMaker->CheckTemplatePermissions($select_module, $templateid);

if ($PDFMaker->GetVersionType() == "professional")
    $type = "professional";
else
    $type = "basic";

$smarty->assign("TYPE", $type);

if (isset($_REQUEST["isDuplicate"]) && $_REQUEST["isDuplicate"] == "true") {
    $smarty->assign("FILENAME", "");
    $smarty->assign("DUPLICATE_FILENAME", $pdftemplateResult["filename"]);
}
else
    $smarty->assign("FILENAME", $pdftemplateResult["filename"]);

$smarty->assign("DESCRIPTION", $pdftemplateResult["description"]);

if (!isset($_REQUEST["isDuplicate"]) OR (isset($_REQUEST["isDuplicate"]) && $_REQUEST["isDuplicate"] != "true"))
    $smarty->assign("SAVETEMPLATEID", $templateid);
if ($templateid != "")
    $smarty->assign("EMODE", "edit");

$smarty->assign("TEMPLATEID", $templateid);
$smarty->assign("MODULENAME", getTranslatedString($select_module));
$smarty->assign("SELECTMODULE", $select_module);

$smarty->assign("BODY", $pdftemplateResult["body"]);

$smarty->assign("MOD", $mod_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("APP", $app_strings);
$smarty->assign("PARENTTAB", getParentTab());

$modArr = $PDFMaker->GetAllModules();
$Modulenames = $modArr[0];
$ModuleIDS = $modArr[1];

$smarty->assign("MODULENAMES", $Modulenames);

// ******************************************   Company and User information: **********************************


$CUI_BLOCKS["Account"] = $mod_strings["LBL_COMPANY_INFO"];
$CUI_BLOCKS["Assigned"] = $mod_strings["LBL_USER_INFO"];
$CUI_BLOCKS["Logged"] = $mod_strings["LBL_LOGGED_USER_INFO"];
$smarty->assign("CUI_BLOCKS", $CUI_BLOCKS);


$sql = "SELECT * FROM vtiger_organizationdetails";
$result = $adb->pquery($sql, array());

$organization_logoname = decode_html($adb->query_result($result, 0, 'logoname'));
$organization_header = decode_html($adb->query_result($result, 0, 'headername'));
$organization_stamp_signature = $adb->query_result($result, 0, 'stamp_signature');

global $site_URL;
$path = $site_URL . "/test/logo/";

if (isset($organization_logoname)) {
    $organization_logo_img = "<img src=\"" . $path . $organization_logoname . "\">";
    $smarty->assign("COMPANYLOGO", $organization_logo_img);
}
if (isset($organization_stamp_signature)) {
    $organization_stamp_signature_img = "<img src=\"" . $path . $organization_stamp_signature . "\">";
    $smarty->assign("COMPANY_STAMP_SIGNATURE", $organization_stamp_signature_img);
}
if (isset($organization_header)) {
    $organization_header_img = "<img src=\"" . $path . $organization_header . "\">";
    $smarty->assign("COMPANY_HEADER_SIGNATURE", $organization_header_img);
}

$Acc_Info = array('' => $mod_strings["LBL_PLS_SELECT"],
    "COMPANY_NAME" => $mod_strings["LBL_COMPANY_NAME"],
    "COMPANY_LOGO" => $mod_strings["LBL_COMPANY_LOGO"],
    "COMPANY_ADDRESS" => $mod_strings["LBL_COMPANY_ADDRESS"],
    "COMPANY_CITY" => $mod_strings["LBL_COMPANY_CITY"],
    "COMPANY_STATE" => $mod_strings["LBL_COMPANY_STATE"],
    "COMPANY_ZIP" => $mod_strings["LBL_COMPANY_ZIP"],
    "COMPANY_COUNTRY" => $mod_strings["LBL_COMPANY_COUNTRY"],
    "COMPANY_PHONE" => $mod_strings["LBL_COMPANY_PHONE"],
    "COMPANY_FAX" => $mod_strings["LBL_COMPANY_FAX"],
    "COMPANY_WEBSITE" => $mod_strings["LBL_COMPANY_WEBSITE"]
);

$smarty->assign("ACCOUNTINFORMATIONS", $Acc_Info);

if (getTabId('MultiCompany4you') && vtlib_isModuleActive('MultiCompany4you')) {
    $MultiAcc_info = Array('' => $mod_strings["LBL_PLS_SELECT"],
        "MULTICOMPANY_COMPANYNAME" => getTranslatedString("LBL_COMPANY_NAME", 'MultiCompany4you'),
        "MULTICOMPANY_STREET" => getTranslatedString("Street", 'MultiCompany4you'),
        "MULTICOMPANY_CITY" => getTranslatedString("City", 'MultiCompany4you'),
        "MULTICOMPANY_CODE" => getTranslatedString("Code", 'MultiCompany4you'),
        "MULTICOMPANY_STATE" => getTranslatedString("State", 'MultiCompany4you'),
        "MULTICOMPANY_COUNTRY" => getTranslatedString("Country", 'MultiCompany4you'),
        "MULTICOMPANY_PHONE" => getTranslatedString("LBL_PHONE", 'MultiCompany4you'),
        "MULTICOMPANY_FAX" => getTranslatedString("Fax", 'MultiCompany4you'),
        "MULTICOMPANY_EMAIL" => getTranslatedString("LBL_EMAIL", 'MultiCompany4you'),
        "MULTICOMPANY_WEBSITE" => getTranslatedString("Website", 'MultiCompany4you'),
        "MULTICOMPANY_LOGO" => getTranslatedString("Logo", 'MultiCompany4you'),
        "MULTICOMPANY_STAMP" => getTranslatedString("Stamp", 'MultiCompany4you'),
        "MULTICOMPANY_BANKNAME" => getTranslatedString("BankName", 'MultiCompany4you'),
        "MULTICOMPANY_BANKACCOUNTNO" => getTranslatedString("BankAccountNo", 'MultiCompany4you'),
        "MULTICOMPANY_IBAN" => getTranslatedString("IBAN", 'MultiCompany4you'),
        "MULTICOMPANY_SWIFT" => getTranslatedString("SWIFT", 'MultiCompany4you'),
        "MULTICOMPANY_REGISTRATIONNO" => getTranslatedString("RegistrationNo", 'MultiCompany4you'),
        "MULTICOMPANY_VATNO" => getTranslatedString("VATNo", 'MultiCompany4you'),
        "MULTICOMPANY_TAXID" => getTranslatedString("TaxId", 'MultiCompany4you'),
        "MULTICOMPANY_ADDITIONALINFORMATIONS" => getTranslatedString("AdditionalInformations", 'MultiCompany4you'),
    );
    $smarty->assign("MULTICOMPANYINFORMATIONS", $MultiAcc_info);
    $smarty->assign("LBL_MULTICOMPANY", getTranslatedString('MultiCompany', 'MultiCompany4you'));
}

$sql_user_block = "SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid=29 ORDER BY sequence ASC";
$res_user_block = $adb->query($sql_user_block);
$user_block_info_arr = array();
while ($row_user_block = $adb->fetch_array($res_user_block)) {
    $sql_user_field = "SELECT fieldid, uitype FROM vtiger_field WHERE block=" . $row_user_block['blockid'] . " and (displaytype != 3 OR uitype = 55) ORDER BY sequence ASC";
    $res_user_field = $adb->query($sql_user_field);
    $num_user_field = $adb->num_rows($res_user_field);

    if ($num_user_field > 0) {
        $user_field_id_array = array();

        while ($row_user_field = $adb->fetch_array($res_user_field)) {
            $user_field_id_array[] = $row_user_field['fieldid'];
            // print_r($user_field_id_array);
        }

        $user_block_info_arr[$row_user_block['blocklabel']] = $user_field_id_array;
    }
}

// $UserOptgroupts = array();

if (file_exists("modules/Users/language/$default_language.lang.php"))  //kontrola na $default_language pretoze vo funkcii return_specified_module_language sa kontroluje $current_language a ak neexistuje tak sa pouzije $default_language
    $current_mod_strings = return_specified_module_language($current_language, "Users");
else
    $current_mod_strings = return_specified_module_language("en_us", "Users");

$b = 0;

foreach ($user_block_info_arr AS $block_label => $block_fields) {
    $b++;

    if (isset($current_mod_strings[$block_label]) AND $current_mod_strings[$block_label] != "")
        $optgroup_value = $current_mod_strings[$block_label];
    elseif (isset($app_strings[$block_label]) AND $app_strings[$block_label] != "")
        $optgroup_value = $app_strings[$block_label];
    elseif (isset($mod_strings[$block_label]) AND $mod_strings[$block_label] != "")
        $optgroup_value = $mod_strings[$block_label];
    else
        $optgroup_value = $block_label;

    if (count($block_fields) > 0) {
        $field_ids = implode(",", $block_fields);

        $sql1 = "SELECT * FROM vtiger_field WHERE fieldid IN (" . $field_ids . ")";
        $result1 = $adb->query($sql1);

        while ($row1 = $adb->fetchByAssoc($result1)) {
            $fieldname = $row1['fieldname'];
            $fieldlabel = $row1['fieldlabel'];

            $option_key = strtoupper("Users" . "_" . $fieldname);

            if (isset($current_mod_strings[$fieldlabel]) AND $current_mod_strings[$fieldlabel] != "")
                $option_value = $current_mod_strings[$fieldlabel];
            elseif (isset($app_strings[$fieldlabel]) AND $app_strings[$fieldlabel] != "")
                $option_value = $app_strings[$fieldlabel];
            else
                $option_value = $fieldlabel;

            $User_Info[$optgroup_value][$option_key] = $option_value;
            $Logged_User_Info[$optgroup_value]["R_" . $option_key] = $option_value;
        }
    }

    //variable RECORD ID added
    if ($b == 1) {
        $option_value = "Record ID";
        $option_key = strtoupper("USERS_CRMID");
        $User_Info[$optgroup_value][$option_key] = $option_value;
        $Logged_User_Info[$optgroup_value]["R_" . $option_key] = $option_value;
    }
    //end
}

// ****************************************** END: Company and User information **********************************

if (file_exists("modules/Users/language/$default_language.lang.php")) {
    $user_mod_strings = return_specified_module_language($current_language, "Users");
} else {
    $user_mod_strings = return_specified_module_language("en_us", "Users");
}

$smarty->assign("USERINFORMATIONS", $User_Info);
$smarty->assign("LOGGEDUSERINFORMATION", $Logged_User_Info);

$Invterandcon = array("" => $mod_strings["LBL_PLS_SELECT"],
    "TERMS_AND_CONDITIONS" => $mod_strings["LBL_TERMS_AND_CONDITIONS"]);

$smarty->assign("INVENTORYTERMSANDCONDITIONS", $Invterandcon);

//custom functions
$ready = false;
$function_name = "";
$function_params = array();
$functions = array();

$files = glob('modules/PDFMaker/functions/*.php');
foreach ($files as $file) {
    $filename = $file;
    $source = fread(fopen($filename, "r"), filesize($filename));
    $tokens = token_get_all($source);
    foreach ($tokens as $token) {
        if (is_array($token)) {
            if ($token[0] == T_FUNCTION)
                $ready = true;
            elseif ($ready) {
                if ($token[0] == T_STRING && $function_name == "")
                    $function_name = $token[1];
                elseif ($token[0] == T_VARIABLE)
                    $function_params[] = $token[1];
            }
        }
        elseif ($ready && $token == "{") {
            $ready = false;
            $functions[$function_name] = $function_params;
            $function_name = "";
            $function_params = array();
        }
    }
}

$customFunctions[""] = $mod_strings["LBL_PLS_SELECT"];
foreach ($functions as $funName => $params) {
    $parString = implode("|", $params);
    $custFun = trim($funName . "|" . str_replace("$", "", $parString), "|");
    $customFunctions[$custFun] = $funName;
}

$smarty->assign("CUSTOM_FUNCTIONS", $customFunctions);

//labels
$global_lang_labels = @array_flip($app_strings);
$global_lang_labels = @array_flip($global_lang_labels);
asort($global_lang_labels);
$smarty->assign("GLOBAL_LANG_LABELS", $global_lang_labels);

$module_lang_labels = array();
if ($select_module != "") {
    if (file_exists("modules/$select_module/language/$default_language.lang.php"))  //kontrola na $default_language pretoze vo funkcii return_specified_module_language sa kontroluje $current_language a ak neexistuje tak sa pouzije $default_language
        $mod_lang = return_specified_module_language($current_language, $select_module);
    else
        $mod_lang = return_specified_module_language("en_us", $select_module);

    $module_lang_labels = @array_flip($mod_lang);
    $module_lang_labels = @array_flip($module_lang_labels);
    asort($module_lang_labels);
}
else
    $module_lang_labels[""] = $mod_strings["LBL_SELECT_MODULE_FIELD"];

$smarty->assign("MODULE_LANG_LABELS", $module_lang_labels);

list($custom_labels, $languages) = $PDFMaker->GetCustomLabels();
$currLangId = "";
foreach ($languages as $langId => $langVal) {
    if ($langVal["prefix"] == $current_language) {
        $currLangId = $langId;
        break;
    }
}

$vcustom_labels = array();
if (count($custom_labels) > 0) {

    foreach ($custom_labels as $oLbl) {
        $currLangVal = $oLbl->GetLangValue($currLangId);
        if ($currLangVal == "")
            $currLangVal = $oLbl->GetFirstNonEmptyValue();

        $vcustom_labels[$oLbl->GetKey()] = $currLangVal;
    }
    asort($vcustom_labels);
}
else {
    $vcustom_labels = $mod_strings["LBL_SELECT_MODULE_FIELD"];
}
$smarty->assign("CUSTOM_LANG_LABELS", $vcustom_labels);

$Header_Footer_Strings = array("" => $mod_strings["LBL_PLS_SELECT"],
    "PAGE" => $app_strings["Page"],
    "PAGES" => $app_strings["Pages"],
);

$smarty->assign("HEADER_FOOTER_STRINGS", $Header_Footer_Strings);

//PDF FORMAT SETTINGS

$Formats = array("A3" => "A3",
    "A4" => "A4",
    "A5" => "A5",
    "A6" => "A6",
    "Letter" => "Letter",
    "Legal" => "Legal",
    "Custom" => "Custom");     // ITS4YOU VlZa

$smarty->assign("FORMATS", $Formats);
if (strpos($select_format, ";") > 0) {
    $tmpArr = explode(";", $select_format);

    $select_format = "Custom";
    $custom_format["width"] = $tmpArr[0];
    $custom_format["height"] = $tmpArr[1];
    $smarty->assign("CUSTOM_FORMAT", $custom_format);
}

$smarty->assign("SELECT_FORMAT", $select_format);

//PDF ORIENTATION SETTINGS

$Orientations = array("portrait" => $mod_strings["portrait"],
    "landscape" => $mod_strings["landscape"]);

$smarty->assign("ORIENTATIONS", $Orientations);

$smarty->assign("SELECT_ORIENTATION", $select_orientation);

//PDF STATUS SETTINGS
$Status = array("1" => $app_strings["Active"],
    "0" => $app_strings["Inactive"]);
$smarty->assign("STATUS", $Status);
$smarty->assign("IS_ACTIVE", $is_active);
if ($is_active == "0") {
    $smarty->assign("IS_DEFAULT_DV_CHECKED", 'disabled="disabled"');
    $smarty->assign("IS_DEFAULT_LV_CHECKED", 'disabled="disabled"');
} elseif ($is_default > 0) {
    $is_default_bin = str_pad(base_convert($is_default, 10, 2), 2, "0", STR_PAD_LEFT);
    $is_default_lv = substr($is_default_bin, 0, 1);
    $is_default_dv = substr($is_default_bin, 1, 1);
    if ($is_default_lv == "1")
        $smarty->assign("IS_DEFAULT_LV_CHECKED", 'checked="checked"');
    if ($is_default_dv == "1")
        $smarty->assign("IS_DEFAULT_DV_CHECKED", 'checked="checked"');
}

$smarty->assign("ORDER", $order);

if ($is_portal == "1")
    $smarty->assign("IS_PORTAL_CHECKED", 'checked="checked"');

if ($is_listview == "1")
    $smarty->assign("IS_LISTVIEW_CHECKED", 'checked="checked"');

//PDF MARGIN SETTINGS
if (isset($_REQUEST['templateid']) && $_REQUEST['templateid'] != '') {
    $Margins = array("top" => $pdftemplateResult["margin_top"],
        "bottom" => $pdftemplateResult["margin_bottom"],
        "left" => $pdftemplateResult["margin_left"],
        "right" => $pdftemplateResult["margin_right"]);

    $Decimals = array("point" => $pdftemplateResult["decimal_point"],
        "decimals" => $pdftemplateResult["decimals"],
        "thousands" => ($pdftemplateResult["thousands_separator"] != "sp" ? $pdftemplateResult["thousands_separator"] : " ")
    );
} else {
    $Margins = array("top" => "2", "bottom" => "2", "left" => "2", "right" => "2");
    $Decimals = array("point" => ",", "decimals" => "2", "thousands" => " ");
}
$smarty->assign("MARGINS", $Margins);
$smarty->assign("DECIMALS", $Decimals);

//PDF HEADER / FOOTER
$header = "";
$footer = "";
if (isset($_REQUEST['templateid']) && $_REQUEST['templateid'] != "") {
    $header = $pdftemplateResult["header"];
    $footer = $pdftemplateResult["footer"];
}
$smarty->assign("HEADER", $header);
$smarty->assign("FOOTER", $footer);

$hfVariables = array("##PAGE##" => $mod_strings["LBL_CURRENT_PAGE"],
    "##PAGES##" => $mod_strings["LBL_ALL_PAGES"],
    "##PAGE##/##PAGES##" => $mod_strings["LBL_PAGE_PAGES"]);

$smarty->assign("HEAD_FOOT_VARS", $hfVariables);

$dateVariables = array("##DD.MM.YYYY##" => $mod_strings["LBL_DATE_DD.MM.YYYY"],
    "##DD-MM-YYYY##" => $mod_strings["LBL_DATE_DD-MM-YYYY"],
    "##MM-DD-YYYY##" => $mod_strings["LBL_DATE_MM-DD-YYYY"],
    "##YYYY-MM-DD##" => $mod_strings["LBL_DATE_YYYY-MM-DD"]);

$smarty->assign("DATE_VARS", $dateVariables);

//PDF FILENAME FIELDS
$filenameFields = array("#TEMPLATE_NAME#" => $mod_strings["LBL_PDF_NAME"],
    "#DD-MM-YYYY#" => $mod_strings["LBL_CURDATE_DD-MM-YYYY"],
    "#MM-DD-YYYY#" => $mod_strings["LBL_CURDATE_MM-DD-YYYY"],
    "#YYYY-MM-DD#" => $mod_strings["LBL_CURDATE_YYYY-MM-DD"]
);
$smarty->assign("FILENAME_FIELDS", $filenameFields);
$smarty->assign("NAME_OF_FILE", $nameOfFile);

//Sharing
$template_owners = get_user_array(false);
$smarty->assign("TEMPLATE_OWNERS", $template_owners);
$smarty->assign("TEMPLATE_OWNER", $owner);

$sharing_types = Array("public" => $app_strings["PUBLIC_FILTER"],
    "private" => $app_strings["PRIVATE_FILTER"],
    "share" => $app_strings["SHARE_FILTER"]);
$smarty->assign("SHARINGTYPES", $sharing_types);
$smarty->assign("SHARINGTYPE", $sharingtype);

$cmod = return_specified_module_language($current_language, "Settings");
$smarty->assign("CMOD", $cmod);
//Constructing the Role Array
$roleDetails = getAllRoleDetails();
$i = 0;
$roleIdStr = "";
$roleNameStr = "";
$userIdStr = "";
$userNameStr = "";
$grpIdStr = "";
$grpNameStr = "";

foreach ($roleDetails as $roleId => $roleInfo) {
    if ($i != 0) {
        if ($i != 1) {
            $roleIdStr .= ", ";
            $roleNameStr .= ", ";
        }
        $roleName = $roleInfo[0];
        $roleIdStr .= "'" . $roleId . "'";
        $roleNameStr .= "'" . addslashes(decode_html($roleName)) . "'";
    }
    $i++;
}
//Constructing the User Array
$l = 0;
$userDetails = getAllUserName();
foreach ($userDetails as $userId => $userInfo) {
    if ($l != 0) {
        $userIdStr .= ", ";
        $userNameStr .= ", ";
    }
    $userIdStr .= "'" . $userId . "'";
    $userNameStr .= "'" . $userInfo . "'";
    $l++;
}
//Constructing the Group Array
$parentGroupArray = array();

$m = 0;
$grpDetails = getAllGroupName();
foreach ($grpDetails as $grpId => $grpName) {
    if (!in_array($grpId, $parentGroupArray)) {
        if ($m != 0) {
            $grpIdStr .= ", ";
            $grpNameStr .= ", ";
        }
        $grpIdStr .= "'" . $grpId . "'";
        $grpNameStr .= "'" . addslashes(decode_html($grpName)) . "'";
        $m++;
    }
}
$smarty->assign("ROLEIDSTR", $roleIdStr);
$smarty->assign("ROLENAMESTR", $roleNameStr);
$smarty->assign("USERIDSTR", $userIdStr);
$smarty->assign("USERNAMESTR", $userNameStr);
$smarty->assign("GROUPIDSTR", $grpIdStr);
$smarty->assign("GROUPNAMESTR", $grpNameStr);

if (count($sharingMemberArray) > 0) {
    $outputMemberArr = array();
    foreach ($sharingMemberArray as $setype => $shareIdArr) {
        foreach ($shareIdArr as $shareId) {
            switch ($setype) {
                case "groups":
                    $memberName = fetchGroupName($shareId);
                    $memberDisplay = "Group::";
                    break;

                case "roles":
                    $memberName = getRoleName($shareId);
                    $memberDisplay = "Roles::";
                    break;

                case "rs":
                    $memberName = getRoleName($shareId);
                    $memberDisplay = "RoleAndSubordinates::";
                    break;

                case "users":
                    $memberName = getUserName($shareId);
                    $memberDisplay = "User::";
                    break;
            }

            $outputMemberArr[] = $setype . "::" . $shareId;
            $outputMemberArr[] = $memberDisplay . $memberName;
        }
    }
    $smarty->assign("MEMBER", array_chunk($outputMemberArr, 2));
}

//Ignored picklist values
$pvsql = "SELECT value FROM vtiger_pdfmaker_ignorepicklistvalues";
$pvresult = $adb->query($pvsql);
$pvvalues = "";
while ($pvrow = $adb->fetchByAssoc($pvresult))
    $pvvalues.=$pvrow["value"] . ", ";
$smarty->assign("IGNORE_PICKLIST_VALUES", rtrim($pvvalues, ", "));

$More_Fields = array(/* "SUBTOTAL"=>$mod_strings["LBL_VARIABLE_SUM"], */
    "CURRENCYNAME" => $mod_strings["LBL_CURRENCY_NAME"],
    "CURRENCYSYMBOL" => $mod_strings["LBL_CURRENCY_SYMBOL"],
    "CURRENCYCODE" => $mod_strings["LBL_CURRENCY_CODE"],
    "TOTALWITHOUTVAT" => $mod_strings["LBL_VARIABLE_SUMWITHOUTVAT"],
    "TOTALDISCOUNT" => $mod_strings["LBL_VARIABLE_TOTALDISCOUNT"],
    "TOTALDISCOUNTPERCENT" => $mod_strings["LBL_VARIABLE_TOTALDISCOUNT_PERCENT"],
    "TOTALAFTERDISCOUNT" => $mod_strings["LBL_VARIABLE_TOTALAFTERDISCOUNT"],
    "VAT" => $mod_strings["LBL_VARIABLE_VAT"],
    "VATPERCENT" => $mod_strings["LBL_VARIABLE_VAT_PERCENT"],
    "VATBLOCK" => $mod_strings["LBL_VARIABLE_VAT_BLOCK"],
    "TOTALWITHVAT" => $mod_strings["LBL_VARIABLE_SUMWITHVAT"],
    "SHTAXTOTAL" => $mod_strings["LBL_SHTAXTOTAL"],
    "SHTAXAMOUNT" => $mod_strings["LBL_SHTAXAMOUNT"],
    "ADJUSTMENT" => $mod_strings["LBL_ADJUSTMENT"],
    "TOTAL" => $mod_strings["LBL_VARIABLE_TOTALSUM"]
);

//formatable VATBLOCK content
$vatblock_table = '<table border="1" cellpadding="3" cellspacing="0" style="border-collapse:collapse;">
                		<tr>
                            <td>' . $app_strings["Name"] . '</td>
                            <td>' . $mod_strings["LBL_VATBLOCK_VAT_PERCENT"] . '</td>
                            <td>' . $mod_strings["LBL_VATBLOCK_SUM"] . '</td>
                            <td>' . $mod_strings["LBL_VATBLOCK_VAT_VALUE"] . '</td>
                        </tr>
                		<tr>
                            <td colspan="4">#VATBLOCK_START#</td>
                        </tr>
                		<tr>
                			<td>$VATBLOCK_LABEL$</td>
                			<td>$VATBLOCK_VALUE$</td>
                			<td>$VATBLOCK_NETTO$</td>
                			<td>$VATBLOCK_VAT$</td>
                		</tr>
                		<tr>
                            <td colspan="4">#VATBLOCK_END#</td>
                        </tr>
                    </table>';

$vatblock_table = str_replace(array("\r\n", "\r", "\n", "\t"), "", $vatblock_table);
$vatblock_table = ereg_replace(" {2,}", ' ', $vatblock_table);
$smarty->assign("VATBLOCK_TABLE", $vatblock_table);

$ModCommentsModules = array();
$ModComments = is_numeric(getTabId("ModComments"));
if ($ModComments == true) {
    $sql = "SELECT relmodule FROM vtiger_fieldmodulerel WHERE module='ModComments' AND relmodule != 'ModComments'";
    $result = $adb->query($sql);
    while ($row = $adb->fetchByAssoc($result))
        $ModCommentsModules[$row["relmodule"]] = $row["relmodule"];
}

foreach ($ModuleIDS as $module => $IDS) {
    if ($module == 'Calendar')
        $sql1 = "SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid IN (9,16) ORDER BY sequence ASC";
    else
        $sql1 = "SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid=" . $IDS . " ORDER BY sequence ASC";
    $res1 = $adb->query($sql1);
    $block_info_arr = array();
    while ($row = $adb->fetch_array($res1)) {
        if ($row['blockid'] == '41' && $row['blocklabel'] == '')
            $row['blocklabel'] = 'LBL_EVENT_INFORMATION';
        $sql2 = "SELECT fieldid, uitype, columnname, fieldlabel
                 FROM vtiger_field
                 WHERE block=" . $row['blockid'] . "
                    AND (displaytype != 3 OR uitype = 55)
                 ORDER BY sequence ASC";
        $res2 = $adb->query($sql2);
        $num_rows2 = $adb->num_rows($res2);

        if ($num_rows2 > 0) {
            $field_id_array = array();

            while ($row2 = $adb->fetch_array($res2)) {
                $field_id_array[] = $row2['fieldid'];
                $tmpArr = Array($row2["columnname"], $row2["fieldlabel"]);
                switch ($row2['uitype']) {
                    case "51": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Accounts");
                        break;
                    case "57": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Contacts");
                        break;
                    case "58": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Campaigns");
                        break;
                    case "59": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Products");
                        break;
                    case "73": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Accounts");
                        break;
                    case "75": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Vendors");
                        break;
                    case "81": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Vendors");
                        break;
                    case "76": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Potentials");
                        break;
                    case "78": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Quotes");
                        break;
                    case "80": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "SalesOrder");
                        break;
                    case "68": $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Accounts");
                        $All_Related_Modules[$module][] = array_merge($tmpArr, (array) "Contacts");
                        break;
                    case "10": $fmrs = $adb->query('SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=' . $row2['fieldid']);
                        while ($rm = $adb->fetch_array($fmrs)) {
                            $All_Related_Modules[$module][] = array_merge($tmpArr, (array) $rm['relmodule']);
                        }
                        break;
                }
            }
            // ITS4YOU MaJu
            //$block_info_arr[$row['blocklabel']] = $field_id_array;
            if (!empty($block_info_arr[$row['blocklabel']])) {
                foreach ($field_id_array as $field_id_array_value)
                    $block_info_arr[$row['blocklabel']][] = $field_id_array_value;
            }
            else
                $block_info_arr[$row['blocklabel']] = $field_id_array;
            // ITS4YOU-END
        }
    }


    if ($module == "Quotes" || $module == "Invoice" || $module == "SalesOrder" || $module == "PurchaseOrder" || $module == "Issuecards" || $module == "Receiptcards" || $module == "Creditnote" || $module == "StornoInvoice")
        $block_info_arr["LBL_DETAILS_BLOCK"] = array();

    //ModComments support
    if (in_array($module, $ModCommentsModules)) {
        $block_info_arr["TEMP_MODCOMMENTS_BLOCK"] = array();
    }

    $ModuleFields[$module] = $block_info_arr;
}
//Permissions are taken into consideration when dealing with realted modules
$AllowedRelMods = array();
foreach ($All_Related_Modules as $Mod => $RelMods) {
    foreach ($RelMods as $RelModKey => $RelMod) {
        $RelModName = $RelMod[2];

        if (isPermitted($RelModName, '') == "yes")
            $AllowedRelMods[$Mod][$RelModKey] = $RelMod;
    }
}
$All_Related_Modules = $AllowedRelMods;

// Fix of emtpy selectbox in case of selected module does not have any related modules
foreach ($Modulenames as $key => $value) {
    if (!isset($All_Related_Modules[$key]))
        $All_Related_Modules[$key] = array();
}
$smarty->assign("ALL_RELATED_MODULES", $All_Related_Modules);

if ($select_module != "") {
    foreach ($All_Related_Modules[$select_module] AS $RelModArr) {
        $Related_Modules[$RelModArr[2] . "|" . $RelModArr[0]] = getTranslatedString($RelModArr[2]) . " (" . $RelModArr[1] . ")";
    }
}
$smarty->assign("RELATED_MODULES", $Related_Modules);

$tacModules = array();
$tac4you = is_numeric(getTabId("Tac4you"));
if ($tac4you == true) {
    $sql = "SELECT tac4you_module FROM vtiger_tac4you_module WHERE presence = 1";
    $result = $adb->query($sql);
    while ($row = $adb->fetchByAssoc($result))
        $tacModules[$row["tac4you_module"]] = $row["tac4you_module"];
}

$desc4youModules = array();
$desc4you = is_numeric(getTabId("Descriptions4you"));
if ($desc4you == true) {
    $sql = "SELECT b.name FROM vtiger_links AS a
             INNER JOIN vtiger_tab AS b USING (tabid)
             WHERE linktype = 'DETAILVIEWWIDGET'
                AND linkurl = 'block://ModDescriptions4you:modules/Descriptions4you/ModDescriptions4you.php'";
    $result = $adb->query($sql);
    while ($row = $adb->fetchByAssoc($result))
        $desc4youModules[$row["name"]] = $row["name"];
}

foreach ($ModuleFields AS $module => $Blocks) {
    $Optgroupts = array();

    if (file_exists("modules/$module/language/$default_language.lang.php"))  //kontrola na $default_language pretoze vo funkcii return_specified_module_language sa kontroluje $current_language a ak neexistuje tak sa pouzije $default_language
        $current_mod_strings = return_specified_module_language($current_language, $module);
    else
        $current_mod_strings = return_specified_module_language("en_us", $module);

    $b = 0;
    if ($module == 'Calendar') {
        $b++;
        $Optgroupts[] = '"' . getTranslatedString('Calendar') . '","' . $b . '"';
        $Convert_ModuleFields['Calendar|1'] .= ',"Record ID","CALENDAR_CRMID"';
        $SelectModuleFields['Calendar'][getTranslatedString('Calendar')]["CALENDAR_CRMID"] = "Record ID";
    }
    foreach ($Blocks AS $block_label => $block_fields) {
        $b++;

        $Options = array();

        if ($block_label != "TEMP_MODCOMMENTS_BLOCK") {
            if (isset($current_mod_strings[$block_label]) AND $current_mod_strings[$block_label] != "")
                $optgroup_value = $current_mod_strings[$block_label];
            elseif (isset($app_strings[$block_label]) AND $app_strings[$block_label] != "")
                $optgroup_value = $app_strings[$block_label];
            elseif (isset($mod_strings[$block_label]) AND $mod_strings[$block_label] != "")
                $optgroup_value = $mod_strings[$block_label];
            else
                $optgroup_value = $block_label;
        }
        else {
            $optgroup_value = $mod_strings["LBL_MODCOMMENTS_INFORMATION"];
        }

        $Optgroupts[] = '"' . $optgroup_value . '","' . $b . '"';

        if (count($block_fields) > 0) {
            $field_ids = implode(",", $block_fields);

            $sql1 = "SELECT * FROM vtiger_field WHERE fieldid IN (" . $field_ids . ")";
            $result1 = $adb->query($sql1);

            while ($row1 = $adb->fetchByAssoc($result1)) {
                $fieldname = $row1['fieldname'];
                $fieldlabel = $row1['fieldlabel'];

                if (getFieldVisibilityPermission($module, $current_user->id, $fieldname) != '0') {
                    if ($module == 'Calendar') {
                        if (getFieldVisibilityPermission('Events', $current_user->id, $fieldname) != '0') {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }

                $option_key = strtoupper($module . "_" . $fieldname);

                if (isset($current_mod_strings[$fieldlabel]) AND $current_mod_strings[$fieldlabel] != "")
                    $option_value = $current_mod_strings[$fieldlabel];
                elseif (isset($app_strings[$fieldlabel]) AND $app_strings[$fieldlabel] != "")
                    $option_value = $app_strings[$fieldlabel];
                else
                    $option_value = $fieldlabel;

                if ($module == 'Calendar') {
                    if ($option_key == 'CALENDAR_ACTIVITYTYPE' || $option_key == 'CALENDAR_DUE_DATE') {
                        $Convert_ModuleFields['Calendar|1'] .= ',"' . $option_value . '","' . $option_key . '"';
                        $SelectModuleFields['Calendar'][getTranslatedString('Calendar')][$option_key] = $option_value;
                        continue;
                    } elseif (!isset($Existing_ModuleFields[$option_key])) {
                        $Existing_ModuleFields[$option_key] = $optgroup_value;
                    } else {
                        $Convert_ModuleFields['Calendar|1'] .= ',"' . $option_value . '","' . $option_key . '"';
                        $SelectModuleFields['Calendar'][getTranslatedString('Calendar')][$option_key] = $option_value;
                        $Unset_Module_Fields[] = '"' . $option_value . '","' . $option_key . '"';
                        unset($SelectModuleFields['Calendar'][$Existing_ModuleFields[$option_key]][$option_key]);
                        continue;
                    }
                }
                $Options[] = '"' . $option_value . '","' . $option_key . '"';
                $SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
            }
        }

        //variable RECORD ID added
        if ($b == 1) {
            $option_value = "Record ID";
            $option_key = strtoupper($module . "_CRMID");
            $Options[] = '"' . $option_value . '","' . $option_key . '"';
            $SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
            $option_value = getTranslatedString('Created Time') . ' (' . getTranslatedString('Date & Time') . ')';
            $option_key = strtoupper($module . "_CREATEDTIME_DATETIME");
            $Options[] = '"' . $option_value . '","' . $option_key . '"';
            $SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
            $option_value = getTranslatedString('Modified Time') . ' (' . getTranslatedString('Date & Time') . ')';
            $option_key = strtoupper($module . "_MODIFIEDTIME_DATETIME");
            $Options[] = '"' . $option_value . '","' . $option_key . '"';
            $SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
        }
        //end

        if ($block_label == "LBL_TERMS_INFORMATION" && isset($tacModules[$module])) {
            $option_value = $mod_strings["LBL_TAC4YOU"];
            $option_key = strtoupper($module . "_TAC4YOU");
            $Options[] = '"' . $option_value . '","' . $option_key . '"';
            $SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
        }

        if ($block_label == "LBL_DESCRIPTION_INFORMATION" && isset($desc4youModules[$module])) {
            $option_value = $mod_strings["LBL_DESC4YOU"];
            $option_key = strtoupper($module . "_DESC4YOU");
            $Options[] = '"' . $option_value . '","' . $option_key . '"';
            $SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
        }
        //ModComments support
        if ($block_label == "TEMP_MODCOMMENTS_BLOCK" && in_array($module, $ModCommentsModules) == true) {
            $option_value = $mod_strings["LBL_MODCOMMENTS"];
            $option_key = strtoupper($module . "_MODCOMMENTS");
            $Options[] = '"' . $option_value . '","' . $option_key . '"';
            $SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
        }
        $Convert_RelatedModuleFields[$module . "|" . $b] = implode(",", $Options);

        $OptionsRelMod = array();
        if ($block_label == "LBL_DETAILS_BLOCK" && ($module == "Quotes" || $module == "Invoice" || $module == "SalesOrder" || $module == "PurchaseOrder" || $module == "Issuecards" || $module == "Receiptcards" || $module == "Creditnote" || $module == "StornoInvoice")) {
            foreach ($More_Fields AS $variable => $variable_name) {
                $variable_key = strtoupper($variable);
                $Options[] = '"' . $variable_name . '","' . $variable_key . '"';
                $SelectModuleFields[$module][$optgroup_value][$variable_key] = $variable_name;
                if ($variable_key != "VATBLOCK")
                    $OptionsRelMod[] = '"' . $variable_name . '","' . strtoupper($module) . '_' . $variable_key . '"';
            }
        }
        //this concatenation is because of need to have extra Details block in Inventory modules which are as related modules
        $Convert_RelatedModuleFields[$module . "|" . $b] .= implode(',', $OptionsRelMod);

        $Convert_ModuleFields[$module . "|" . $b] = implode(",", $Options);
    }
    if ($module == 'Calendar') {
        $Convert_ModuleFields['Calendar|1'] = str_replace(',"Record ID","CALENDAR_CRMID",', "", $Convert_ModuleFields['Calendar|1']);
        $Convert_ModuleFields['Calendar|1'] .= ',"Record ID","CALENDAR_CRMID"';
        unset($SelectModuleFields['Calendar'][getTranslatedString('Calendar')]["CALENDAR_CRMID"]);
        $SelectModuleFields['Calendar'][getTranslatedString('Calendar')]["CALENDAR_CRMID"] = "Record ID";
    }

    $Convert_ModuleBlocks[$module] = implode(",", $Optgroupts);
}
foreach ($Convert_ModuleFields as $cmf_key => $cmf_value) {
    if (substr($cmf_key, 0, 9) == 'Calendar|' && $cmf_key != 'Calendar|1') {
        foreach ($Unset_Module_Fields as $to_unset) {
            $cmf_value = str_replace($to_unset, '', $cmf_value);
            $cmf_value = str_replace(",,", ',', $cmf_value);
            $Convert_ModuleFields[$cmf_key] = trim($cmf_value, ',');
        }
    }
}

$smarty->assign("MODULE_BLOCKS", $Convert_ModuleBlocks);

$smarty->assign("RELATED_MODULE_FIELDS", $Convert_RelatedModuleFields);

$smarty->assign("MODULE_FIELDS", $Convert_ModuleFields);

//Product block fields start
// Product bloc templates
$sql = "SELECT * FROM vtiger_pdfmaker_productbloc_tpl";
$result = $adb->query($sql);
$Productbloc_tpl[""] = $mod_strings["LBL_PLS_SELECT"];
while ($row = $adb->fetchByAssoc($result)) {
    $Productbloc_tpl[$row["body"]] = $row["name"];
}
$smarty->assign("PRODUCT_BLOC_TPL", $Productbloc_tpl);

// $Article_Strings = array(""=>$mod_strings["LBL_PLS_SELECT"],
//                          "PRODUCTBLOC_START"=>$mod_strings["LBL_ARTICLE_START"],
//                          "PRODUCTBLOC_END"=>$mod_strings["LBL_ARTICLE_END"]
//                         );
//
// $smarty->assign("ARTICLE_STRINGS",$Article_Strings);
// $Product_Fields = array("PS_CRMID"=>$mod_strings["LBL_RECORD_ID"],
// 						"PS_NO"=>$mod_strings["LBL_PS_NO"],
//                         "PRODUCTPOSITION"=>$mod_strings["LBL_PRODUCT_POSITION"],
//                         "CURRENCYNAME"=>$mod_strings["LBL_CURRENCY_NAME"],
//                         "CURRENCYCODE"=>$mod_strings["LBL_CURRENCY_CODE"],
//                         "CURRENCYSYMBOL"=>$mod_strings["LBL_CURRENCY_SYMBOL"],
//                         "PRODUCTNAME"=>$mod_strings["LBL_VARIABLE_PRODUCTNAME"],
//                         "PRODUCTTITLE"=>$mod_strings["LBL_VARIABLE_PRODUCTTITLE"],
//                         "PRODUCTDESCRIPTION"=>$mod_strings["LBL_VARIABLE_PRODUCTDESCRIPTION"],
//                         "PRODUCTEDITDESCRIPTION"=>$mod_strings["LBL_VARIABLE_PRODUCTEDITDESCRIPTION"]);
//
// if($adb->num_rows($adb->query("SELECT tabid FROM vtiger_tab WHERE name='Pdfsettings'"))>0)
// 	$Product_Fields["CRMNOWPRODUCTDESCRIPTION"]=$mod_strings["LBL_CRMNOW_DESCRIPTION"];
//
// $Product_Fields["PRODUCTQUANTITY"]=$mod_strings["LBL_VARIABLE_QUANTITY"];
// $Product_Fields["PRODUCTUSAGEUNIT"]=$mod_strings["LBL_VARIABLE_USAGEUNIT"];
// $Product_Fields["PRODUCTLISTPRICE"]=$mod_strings["LBL_VARIABLE_LISTPRICE"];
// $Product_Fields["PRODUCTTOTAL"]=$mod_strings["LBL_PRODUCT_TOTAL"];
// $Product_Fields["PRODUCTDISCOUNT"]=$mod_strings["LBL_VARIABLE_DISCOUNT"];
// $Product_Fields["PRODUCTDISCOUNTPERCENT"]=$mod_strings["LBL_VARIABLE_DISCOUNT_PERCENT"];
// $Product_Fields["PRODUCTSTOTALAFTERDISCOUNT"]=$mod_strings["LBL_VARIABLE_PRODUCTTOTALAFTERDISCOUNT"];
// $Product_Fields["PRODUCTVATPERCENT"]=$mod_strings["LBL_PROCUCT_VAT_PERCENT"];
// $Product_Fields["PRODUCTVATSUM"]=$mod_strings["LBL_PRODUCT_VAT_SUM"];
// $Product_Fields["PRODUCTTOTALSUM"]=$mod_strings["LBL_PRODUCT_TOTAL_VAT"];
// $smarty->assign("SELECT_PRODUCT_FIELD",$Product_Fields);
// $smarty->assign("PRODUCTS_FIELDS",$SelectModuleFields["Products"]);
// $smarty->assign("SERVICES_FIELDS",$SelectModuleFields["Services"]);

$ProductBlockFields = $PDFMaker->GetProductBlockFields();
foreach ($ProductBlockFields as $smarty_key => $pbFields) {
    $smarty->assign($smarty_key, $pbFields);
}
//Product block fields end
//Related block postprocessing
$Related_Blocks = $PDFMaker->GetRelatedBlocks($select_module);
$smarty->assign("RELATED_BLOCKS", $Related_Blocks);
//Related blocks end

if ($templateid != "" || $select_module != "") {
    $smarty->assign("SELECT_MODULE_FIELD", $SelectModuleFields[$select_module]);
    $smf_filename = $SelectModuleFields[$select_module];
    if ($select_module == "Invoice" || $select_module == "Quotes" || $select_module == "SalesOrder" || $select_module == "PurchaseOrder" || $select_module == "Issuecards" || $select_module == "Receiptcards" || $select_module == "Creditnote" || $select_module == "StornoInvoice")
        unset($smf_filename["Details"]);
    $smarty->assign("SELECT_MODULE_FIELD_FILENAME", $smf_filename);
}

// header / footer display settings
$disp_optionsArr = array("DH_FIRST", "DH_OTHER");
$disp_header_bin = str_pad(base_convert($disp_header, 10, 2), 2, "0", STR_PAD_LEFT);
for ($i = 0; $i < count($disp_optionsArr); $i++) {
    if (substr($disp_header_bin, $i, 1) == "1")
        $smarty->assign($disp_optionsArr[$i], 'checked="checked"');
}
if ($disp_header == "3")
    $smarty->assign("DH_ALL", 'checked="checked"');

$disp_optionsArr = array("DF_FIRST", "DF_LAST", "DF_OTHER");
$disp_footer_bin = str_pad(base_convert($disp_footer, 10, 2), 3, "0", STR_PAD_LEFT);
for ($i = 0; $i < count($disp_optionsArr); $i++) {
    if (substr($disp_footer_bin, $i, 1) == "1")
        $smarty->assign($disp_optionsArr[$i], 'checked="checked"');
}
if ($disp_footer == "7")
    $smarty->assign("DF_ALL", 'checked="checked"');

$ListView_Block = array("" => $mod_strings["LBL_PLS_SELECT"],
    "LISTVIEWBLOCK_START" => $mod_strings["LBL_ARTICLE_START"],
    "LISTVIEWBLOCK_END" => $mod_strings["LBL_ARTICLE_END"],
    "CRIDX" => $mod_strings["LBL_COUNTER"],
);

$smarty->assign("LISTVIEW_BLOCK_TPL", $ListView_Block);

include("version.php");

$version_type = ucfirst($PDFMaker->GetVersionType());

$smarty->assign("VERSION", $version_type . " " . $version);

$tool_buttons = Button_Check($currentModule);
$smarty->assign('CHECK', $tool_buttons);

$category = getParentTab();
$smarty->assign("CATEGORY", $category);
$smarty->display(vtlib_getModuleTemplate($currentModule, 'EditPDFTemplate.tpl'));
