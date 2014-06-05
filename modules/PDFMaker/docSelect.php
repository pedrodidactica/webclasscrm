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

global $app_strings, $adb;

if (isPermitted("Documents", "EditView") == "no") {
    $PDFMaker = new PDFMaker();
    $PDFMaker->DieDuePermission();
}

$return_module = vtlib_purify($_REQUEST["return_module"]);
$return_id = vtlib_purify($_REQUEST["return_id"]);

$language = $_SESSION['authenticated_user_language'];
$mod_strings = return_module_language($language, "Documents");
$pdf_strings = return_module_language($language, "PDFMaker");

//getting the related fields to Contacts or Accounts in order to pre-fill the fields belows
$exis_account_id = "";
$exis_account_id_display = "";
$exis_contact_id = "";
$exis_contact_id_display = "";

$tabid = getTabid($return_module);
$sql = "SELECT fieldid, fieldname, uitype, columnname
        FROM vtiger_field 
        WHERE tabid=? AND uitype IN (51, 57, 73, 10)";
$result = $adb->pquery($sql, array($tabid));
$num_rows = $adb->num_rows($result);
if ($num_rows > 0) {
    $focus = CRMEntity::getInstance($return_module);
    $focus->retrieve_entity_info($return_id, $return_module);    
    while ($row = $adb->fetchByAssoc($result)) {
        $fk_record = $focus->column_fields[$row["fieldname"]];
        switch ($row["uitype"]) {
            case "57":
                $relMod = "Contacts";
                break;

            case "51":
            case "73":
                $relMod = "Accounts";
                break;

            case "10":
                $relMod = getSalesEntityType($fk_record);
                break;

            default:
                $relMod = "";
        }
        if ($relMod == "Contacts" || $relMod == "Accounts") {
            $value = "";
            $displayValueArray = getEntityName($relMod, $fk_record);
            if (!empty($displayValueArray)) {
                foreach ($displayValueArray as $p_value) {
                    $value = $p_value;
                }
            }
            
            if($relMod == "Contacts" && $exis_contact_id == "") {
                $exis_contact_id = $fk_record;
                $exis_contact_id_display = $value;
            } elseif($relMod == "Accounts" && $exis_account_id == "") {
                $exis_account_id = $fk_record;
                $exis_account_id_display = $value;
            }   
        }
    }
}

$sql = "select foldername,folderid from vtiger_attachmentsfolder order by foldername";
$res = $adb->pquery($sql, array());
$options = "";
for ($i = 0; $i < $adb->num_rows($res); $i++) {
    $fid = $adb->query_result($res, $i, "folderid");
    $fldr_name = $adb->query_result($res, $i, "foldername");
    $options.='<option value="' . $fid . '">' . $fldr_name . '</option>';
}

echo '
<form name="QcEditView" method="post" action="index.php" onSubmit="return validatePDFDocForm();">
<input type="hidden" name="module" value="PDFMaker" />
<input type="hidden" name="action" value="SavePDFDoc" />
<input type="hidden" name="pmodule" value="' . $return_module . '" />
<input type="hidden" name="pid" value="' . $return_id . '" />
<input type="hidden" name="template_ids" value="" />
<input type="hidden" name="language" value="" />
<table border=0 cellspacing=0 cellpadding=5 width=100% class=layerHeadingULine>
<tr>
	<td width="90%" align="left" class="genHeaderSmall" id="PDFDocDivHandle" style="cursor:move;">' . $pdf_strings["LBL_SAVEASDOC"] . '                 			
	</td>
	<td width="10%" align="right">
		<a href="javascript:fninvsh(\'PDFDocDiv\');"><img title="' . $app_strings["LBL_CLOSE"] . '" alt="' . $app_strings["LBL_CLOSE"] . '" src="themes/images/close.gif" border="0"  align="absmiddle" /></a>
	</td>
</tr>
</table>
<table border=0 cellspacing=0 cellpadding=5 width=100% align=center>
    <tr><td class="small">
        <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
            <tr><td colspan="2" class="detailedViewHeader" style="padding-top:5px;padding-bottom:5px;"><b>' . $app_strings["Documents"] . '</b></td></tr>
            <tr>
                <td class="dvtCellLabel" width="20%" align="right"><font color="red">*</font>' . $mod_strings["Title"] . '</td>
                <td class="dvtCellInfo" width="80%" align="left"><input name="notes_title" type="text" class="detailedViewTextBox"></td>
            </tr>
            <tr>
                <td class="dvtCellLabel" width="20%" align="right">' . $mod_strings["Folder Name"] . '</td>
                <td class="dvtCellInfo" width="80%" align="left">
                  <select name="folderid" class="small">
                  ' . $options . '
                  </select>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" width="20%" align="right">' . $mod_strings["Note"] . '</td>
                <td class="dvtCellInfo" width="80%" align="left"><textarea name="notecontent" class="detailedViewTextBox"></textarea></td>
            </tr>
        </table>
        <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
            <tr><td colspan="2" class="detailedViewHeader" style="padding-top:5px;padding-bottom:5px;"><b>' . $app_strings["LBL_RELATED_TO"] . '</b></td></tr>
            <tr>
                <td class="dvtCellLabel" width="20%" align="right">' . $app_strings["Contact"] . '</td>
                <td class="dvtCellInfo" width="80%" align="left">
                    <input name="pdfdoc_contact_id" type="hidden" value="'.$exis_contact_id.'"/>
                    <input name="pdfdoc_contact_id_display" type="text" value="'.$exis_contact_id_display.'" readonly />
                    &nbsp;
                    <img src="themes/softed/images/select.gif" tabindex="" alt="' . $app_strings["LBL_SELECT"] . '" title="' . $app_strings["LBL_SELECT"] . '" language="javascript" onclick="return window.open(\'index.php?module=Contacts&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield=pdfdoc_contact_id&srcmodule=' . $return_module . '&forrecord=' . $return_id . '\',\'test\',\'width=640,height=602,resizable=0,scrollbars=0,top=150,left=200\');" style="cursor:hand;cursor:pointer" align="absmiddle">
                        &nbsp;
                    <input src="themes/images/clear_field.gif" alt="' . $app_strings["LBL_CLEAR"] . '" title="' . $app_strings["LBL_CLEAR"] . '" language="javascript" onclick="this.form.pdfdoc_contact_id.value=\'\'; this.form.pdfdoc_contact_id_display.value=\'\'; return false;" style="cursor:hand;cursor:pointer" align="absmiddle" type="image">
                </td>
            </tr>  
            <tr>
                <td class="dvtCellLabel" width="20%" align="right">' . $app_strings["Account"] . '</td>
                <td class="dvtCellInfo" width="80%" align="left">
                    <input name="pdfdoc_account_id" type="hidden" value="'.$exis_account_id.'" />
                    <input name="pdfdoc_account_id_display" type="text" value="'.$exis_account_id_display.'" readonly />
                    &nbsp;
                    <img src="themes/softed/images/select.gif" tabindex="" alt="' . $app_strings["LBL_SELECT"] . '" title="' . $app_strings["LBL_SELECT"] . '" language="javascript" onclick="return window.open(\'index.php?module=Accounts&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield=pdfdoc_account_id&srcmodule=' . $return_module . '&forrecord=' . $return_id . '\',\'test\',\'width=640,height=602,resizable=0,scrollbars=0,top=150,left=200\');" style="cursor:hand;cursor:pointer" align="absmiddle">
                        &nbsp;
                    <input src="themes/images/clear_field.gif" alt="' . $app_strings["LBL_CLEAR"] . '" title="' . $app_strings["LBL_CLEAR"] . '" language="javascript" onclick="this.form.pdfdoc_account_id.value=\'\'; this.form.pdfdoc_account_id_display.value=\'\'; return false;" style="cursor:hand;cursor:pointer" align="absmiddle" type="image">
                </td>
            </tr> 
        </table>
    </td></tr>
</table>
<table border=0 cellspacing=0 cellpadding=5 width=100% class="layerPopupTransport">
<tr><td align=center class="small">
	<input type="submit" value="' . $app_strings["LBL_SAVE_BUTTON_LABEL"] . '" class="crmbutton small create"/>&nbsp;&nbsp;
	<input type="button" name="' . $app_strings["LBL_CANCEL_BUTTON_LABEL"] . '" value="' . $app_strings["LBL_CANCEL_BUTTON_LABEL"] . '" class="crmbutton small cancel" onclick="fninvsh(\'PDFDocDiv\');" />
</td></tr>
</table>
</form>';
exit;
