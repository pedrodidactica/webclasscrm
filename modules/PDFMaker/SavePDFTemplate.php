<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('include/utils/utils.php');
require_once("modules/PDFMaker/PDFMaker.php");
Debugger::GetInstance()->Init();

global $adb, $current_user;
$PDFMaker = new PDFMaker();
// $adb->setDebug(TRUE);
// show($_REQUEST);exit;
//vtiger_pdfmaker_settings
$filename = vtlib_purify($_REQUEST["filename"]);
$modulename = from_html($_REQUEST["modulename"]);
$templateid = vtlib_purify($_REQUEST["templateid"]);
$description = from_html($_REQUEST["description"]);
$body = fck_from_html($_REQUEST["body"]);
$pdf_format = from_html($_REQUEST["pdf_format"]);
$pdf_orientation = from_html($_REQUEST["pdf_orientation"]);
$owner = from_html($_REQUEST["template_owner"]);
$sharingtype = from_html($_REQUEST["sharing"]);
//vtiger_pdfmaker_user_status
$is_active = from_html($_REQUEST["is_active"]);
$is_default_dv = (isset($_REQUEST["is_default_dv"]) ? "1" : "0");
$is_default_lv = (isset($_REQUEST["is_default_lv"]) ? "1" : "0");
$is_portal = (isset($_REQUEST["is_portal"]) ? "1" : "0");
$is_listview = (isset($_REQUEST["is_listview"]) ? "1" : "0");
$order = from_html($_REQUEST["tmpl_order"]);

$dh_first = (isset($_REQUEST["dh_first"]) ? "1" : "0");
$dh_other = (isset($_REQUEST["dh_other"]) ? "1" : "0");
$df_first = (isset($_REQUEST["df_first"]) ? "1" : "0");
$df_last = (isset($_REQUEST["df_last"]) ? "1" : "0");
$df_other = (isset($_REQUEST["df_other"]) ? "1" : "0");
// $no_header_first_page = (isset($_REQUEST["no_header_on_first_page"]) ? "1" : "0");
// $no_footer_last_page = (isset($_REQUEST["no_footer_on_last_page"]) ? "1" : "0");
// $footer_only_last_page = (isset($_REQUEST["footer_only_on_last_page"]) ? "1" : "0");

if (isset($templateid) && $templateid != '') {
    $sql = "update vtiger_pdfmaker set filename =?, module =?, description =?, body =? where templateid =?";
    $params = array($filename, $modulename, $description, $body, $templateid);
    $adb->pquery($sql, $params);

    $sql2 = "DELETE FROM vtiger_pdfmaker_settings WHERE templateid =?";
    $params2 = array($templateid);
    $adb->pquery($sql2, $params2);

    $sql21 = "DELETE FROM vtiger_pdfmaker_userstatus WHERE templateid=? AND userid=?";
    $adb->pquery($sql21, array($templateid, $current_user->id));
} else {
    $templateid = $adb->getUniqueID('vtiger_pdfmaker');
    $sql3 = "insert into vtiger_pdfmaker (filename,module,description,body,deleted,templateid) values (?,?,?,?,?,?)";
    $params3 = array($filename, $modulename, $description, $body, 0, $templateid);
    $adb->pquery($sql3, $params3);
}

if ($_REQUEST["margin_top"] > 0)
    $margin_top = $_REQUEST["margin_top"];
else
    $margin_top = 0;
if ($_REQUEST["margin_bottom"] > 0)
    $margin_bottom = $_REQUEST["margin_bottom"];
else
    $margin_bottom = 0;
if ($_REQUEST["margin_left"] > 0)
    $margin_left = $_REQUEST["margin_left"];
else
    $margin_left = 0;
if ($_REQUEST["margin_right"] > 0)
    $margin_right = $_REQUEST["margin_right"];
else
    $margin_right = 0;

$dec_point = $_REQUEST["dec_point"];
$dec_decimals = $_REQUEST["dec_decimals"];
$dec_thousands = ($_REQUEST["dec_thousands"] != " " ? $_REQUEST["dec_thousands"] : "sp");

$header = $_REQUEST["header_body"];
$footer = $_REQUEST["footer_body"];

$encoding = (isset($_REQUEST["encoding"]) ? $_REQUEST["encoding"] : "auto");
$nameOfFile = $_REQUEST["nameOfFile"];

// ITS4YOU-CR VlZa
//in case of allowed module make sure that only one template from that module is set for portal
if (($modulename == "Invoice" || $modulename == "Quotes") && $is_portal == "1") {
    $sql4a = "UPDATE vtiger_pdfmaker_settings 
            INNER JOIN vtiger_pdfmaker 
              USING(templateid)
            SET is_portal = '0' 
            WHERE is_portal = '1'
              AND module=?";
    $params4a = array($modulename);
    $adb->pquery($sql4a, $params4a);
}

if ($pdf_format == "Custom") {
    $pdf_cf_width = from_html($_REQUEST["pdf_format_width"]);
    $pdf_cf_height = from_html($_REQUEST["pdf_format_height"]);
    $pdf_format = $pdf_cf_width . ";" . $pdf_cf_height;
}

$disp_header = base_convert($dh_first . $dh_other, 2, 10);
$disp_footer = base_convert($df_first . $df_last . $df_other, 2, 10);

$sql4 = "INSERT INTO vtiger_pdfmaker_settings (templateid, margin_top, margin_bottom, margin_left, margin_right, format, orientation, 
                                               decimals, decimal_point, thousands_separator, header, footer, encoding, file_name, is_portal,
                                               is_listview, owner, sharingtype, disp_header, disp_footer)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$params4 = array($templateid, $margin_top, $margin_bottom, $margin_left, $margin_right, $pdf_format, $pdf_orientation,
    $dec_decimals, $dec_point, $dec_thousands, $header, $footer, $encoding, $nameOfFile, $is_portal,
    $is_listview, $owner, $sharingtype, $disp_header, $disp_footer);
$adb->pquery($sql4, $params4);
// ITS4YOU-END
//ignored picklist values
$adb->query("DELETE FROM vtiger_pdfmaker_ignorepicklistvalues");
$pvvalues = explode(",", $_REQUEST["ignore_picklist_values"]);
foreach ($pvvalues as $value)
    $adb->query("INSERT INTO vtiger_pdfmaker_ignorepicklistvalues(value) VALUES('" . trim($value) . "')");
// end ignored picklist values
//unset the former default template because only one template can be default per user x module
$is_default_bin = $is_default_lv . $is_default_dv;
$is_default_dec = intval(base_convert($is_default_bin, 2, 10)); // convert binary format xy to decimal; where x stands for is_default_lv and y stands for is_default_dv
if ($is_default_dec > 0) {
    $sql5 = "UPDATE vtiger_pdfmaker_userstatus
            INNER JOIN vtiger_pdfmaker USING(templateid)
            SET is_default=?
            WHERE is_default=? AND userid=? AND module=?";

    switch ($is_default_dec) {
//      in case of only is_default_dv is checked
        case 1:
            $adb->pquery($sql5, array("0", "1", $current_user->id, $modulename));
            $adb->pquery($sql5, array("2", "3", $current_user->id, $modulename));
            break;
//      in case of only is_default_lv is checked
        case 2:
            $adb->pquery($sql5, array("0", "2", $current_user->id, $modulename));
            $adb->pquery($sql5, array("1", "3", $current_user->id, $modulename));
            break;
//      in case of both is_default_* are checked
        case 3:
            $sql5 = "UPDATE vtiger_pdfmaker_userstatus
                    INNER JOIN vtiger_pdfmaker USING(templateid)
                    SET is_default=?
                    WHERE is_default > ? AND userid=? AND module=?";
            $adb->pquery($sql5, array("0", "0", $current_user->id, $modulename));
    }
}

$sql6 = "INSERT INTO vtiger_pdfmaker_userstatus(templateid, userid, is_active, is_default, sequence) VALUES(?,?,?,?,?)";
$adb->pquery($sql6, array($templateid, $current_user->id, $is_active, $is_default_dec, $order));

//SHARING
$sql7 = "DELETE FROM vtiger_pdfmaker_sharing WHERE templateid=?";
$adb->pquery($sql7, array($templateid));

if ($sharingtype == "share" && isset($_REQUEST["sharingSelectedColumnsString"])) {
    $selected_col_string = $_REQUEST['sharingSelectedColumnsString'];
    $member_array = explode(';', $selected_col_string);
    $groupMemberArray = constructSharingMemberArray($member_array);

    $sql8a = "INSERT INTO vtiger_pdfmaker_sharing(templateid, shareid, setype) VALUES ";
    $sql8b = "";
    $params8 = array();
    foreach ($groupMemberArray as $setype => $shareIdArr) {
        foreach ($shareIdArr as $shareId) {
            $sql8b .= "(?, ?, ?),";
            $params8[] = $templateid;
            $params8[] = $shareId;
            $params8[] = $setype;
        }
    }

    if ($sql8b != "") {
        $sql8b = rtrim($sql8b, ",");
        $sql8 = $sql8a . $sql8b;
        $adb->pquery($sql8, $params8);
    }
}

$PDFMaker->AddLinks($modulename);

if (isset($_REQUEST["redirect"]) && $_REQUEST["redirect"] == "false") {
    header("Location:index.php?module=PDFMaker&action=EditPDFTemplate&parenttab=Tools&applied=true&templateid=" . $templateid);
} else {
    header("Location:index.php?module=PDFMaker&action=DetailViewPDFTemplate&parenttab=Tools&templateid=" . $templateid);
}

exit;

function constructSharingMemberArray($member_array) {

    $groupMemberArray = Array();
    $roleArray = Array();
    $roleSubordinateArray = Array();
    $groupArray = Array();
    $userArray = Array();

    foreach ($member_array as $member) {
        $memSubArray = explode('::', $member);
        switch ($memSubArray[0]) {
            case "groups":
                $groupArray[] = $memSubArray[1];
                break;

            case "roles":
                $roleArray[] = $memSubArray[1];
                break;

            case "rs":
                $roleSubordinateArray[] = $memSubArray[1];
                break;

            case "users":
                $userArray[] = $memSubArray[1];
                break;
        }
    }

    $groupMemberArray['groups'] = $groupArray;
    $groupMemberArray['roles'] = $roleArray;
    $groupMemberArray['rs'] = $roleSubordinateArray;
    $groupMemberArray['users'] = $userArray;

    return $groupMemberArray;
}
