<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('include/utils/utils.php');
require_once('modules/PDFMaker/PDFMaker.php');
Debugger::GetInstance()->Init();
global $app_strings, $theme, $adb, $current_language;

if ($_REQUEST['idslist'] == 'all') {
    $returnmodule = vtlib_purify($_REQUEST['return_module']);
    $_REQUEST['idlist'] = $_REQUEST['idslist'];
    $idlist = vtlib_purify($_REQUEST['idlist']);
    $viewid = vtlib_purify($_REQUEST['viewname']);
    $excludedRecords = vtlib_purify($_REQUEST['excludedRecords']);
    $storearray = getSelectedRecords($_REQUEST, $returnmodule, $idlist, $excludedRecords);
    $_REQUEST['idslist'] = implode(";", $storearray);
}

$PDFMaker = new PDFMaker();
if ($PDFMaker->CheckPermissions("DETAIL") == false) {
    $output = '
  <table border=0 cellspacing=0 cellpadding=5 width=100% class=layerHeadingULine>
  <tr>
  	<td width="90%" align="left" class="genHeaderSmall" id="PDFListViewDivHandle" style="cursor:move;">' . $pdf_strings["LBL_PDF_ACTIONS"] . '                 			
  	</td>
  	<td width="10%" align="right">
  		<a href="javascript:fninvsh(\'PDFListViewDiv\');"><img title="' . $app_strings["LBL_CLOSE"] . '" alt="' . $app_strings["LBL_CLOSE"] . '" src="themes/images/close.gif" border="0"  align="absmiddle" /></a>
  	</td>
  </tr>
  </table>
  <table border=0 cellspacing=0 cellpadding=5 width=100% align=center>
      <tr><td class="small">
          <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
          <tr>
            <td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;text-align:center;">
              <strong>' . $app_strings["LBL_PERMISSION"] . '</strong>
            </td>
          </tr>
          <tr>
        		<td class="dvtCellInfo" style="width:100%;" align="center">
              <input type="button" class="crmbutton small cancel" value="' . $app_strings["LBL_CANCEL_BUTTON_LABEL"] . '" onclick="fninvsh(\'PDFListViewDiv\');" />      
            </td>
      		</tr>      		
          </table>
      </td></tr>
  </table>
  ';
    die($output);
}

$image_path = 'themes/' . $theme . '/images/';
$language = $_SESSION['authenticated_user_language'];
$pdf_strings = return_module_language($language, "PDFMaker");

//TEMPLATES BLOCK
$templates = $PDFMaker->GetAvailableTemplates($_REQUEST['return_module'], true);
$options = "";
foreach ($templates as $templateid => $valArr) {
    if ($valArr["is_default"] == "2" || $valArr["is_default"] == "3")
        $selected = ' selected="selected" ';
    else
        $selected = "";

    $options.='<option value="' . $templateid . '"' . $selected . '>' . $valArr['templatename'] . '</option>';
}

$template_output = "";
$language_output = "";
$generate_pdf = "";
if ($options != "") {
    $template_output = '
    <tr>
        <td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;">
        <select name="use_common_template" id="use_common_template" class="detailedViewTextBox" multiple style="width:100%;" size="5">
        ' . $options . '
        </select>        
        </td>
    </tr>';

    $temp_res = $adb->query("SELECT label, prefix FROM vtiger_language WHERE active=1");
    while ($temp_row = $adb->fetchByAssoc($temp_res)) {
        $template_languages[$temp_row["prefix"]] = $temp_row["label"];
    }

    //LANGUAGES BLOCK  
    if (count($template_languages) > 1) {
        $options = "";
        foreach ($template_languages as $prefix => $label) {
            if ($current_language != $prefix)
                $options.='<option value="' . $prefix . '">' . $label . '</option>';
            else
                $options.='<option value="' . $prefix . '" selected="selected">' . $label . '</option>';
        }

        $language_output = '
        <tr>
            <td class="dvtCellInfo" style="width:100%;">    	
            <select name="template_language" id="template_language" class="detailedViewTextBox" style="width:100%;" size="1">
            ' . $options . '
            </select>
            </td>
        </tr>';
    }
    else {
        foreach ($template_languages as $prefix => $label)
            $language_output.='<input type="hidden" name="template_language" id="template_language" value="' . $prefix . '"/>';
    }

    //GENERATE PDF ACTION BLOCK
    $exportToRTFButt = "";
    if($PDFMaker->CheckPermissions("EXPORT_RTF") === true) {
        $exportToRTFButt = '<input type="button" class="crmbutton small save" value="' . $pdf_strings["LBL_EXPORT_TO_RTF"] . '" onclick="if(getSelectedTemplates()==\'\') alert(\'' . $pdf_strings["SELECT_TEMPLATE"] . '\'); else document.location.href=\'index.php?module=PDFMaker&relmodule=' . $_REQUEST["return_module"] . '&action=CreatePDFFromTemplate&&idslist=' . $_REQUEST["idslist"] . '&commontemplateid=\'+getSelectedTemplates()+\'&language=\'+document.getElementById(\'template_language\').value+\'&type=rtf\';fninvsh(\'PDFListViewDiv\');" />';
    }
    
    $generate_pdf = '
    <tr>
        <td class="dvtCellInfo" style="width:100%;" align="center">   		    
          <input type="button" class="crmbutton small save" value="' . $app_strings["LBL_EXPORT_TO_PDF"] . '" onclick="if(getSelectedTemplates()==\'\') alert(\'' . $pdf_strings["SELECT_TEMPLATE"] . '\'); else document.location.href=\'index.php?module=PDFMaker&relmodule=' . $_REQUEST["return_module"] . '&action=CreatePDFFromTemplate&&idslist=' . $_REQUEST["idslist"] . '&commontemplateid=\'+getSelectedTemplates()+\'&language=\'+document.getElementById(\'template_language\').value;fninvsh(\'PDFListViewDiv\');" />            
          '.$exportToRTFButt.'
          <input type="button" class="crmbutton small cancel" value="' . $app_strings["LBL_CANCEL_BUTTON_LABEL"] . '" onclick="fninvsh(\'PDFListViewDiv\');" />      
        </td>
    </tr>';
} else {
    $template_output = '
    <tr><td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;">' . $pdf_strings["CRM_TEMPLATES_DONT_EXIST"];
//   if(isPermitted("PDFMaker","EditView") == 'yes')
    if ($PDFMaker->CheckPermissions("EDIT")) {
        $template_output.='<br />' . $pdf_strings["CRM_TEMPLATES_ADMIN"] . '
                      <a href="index.php?module=PDFMaker&action=EditPDFTemplate&return_module=' . $_REQUEST["return_module"] . '&parenttab=Tools" class="webMnu">' . $pdf_strings["TEMPLATE_CREATE_HERE"] . '</a>';
    }

    $template_output.='</td></tr>';

    //GENERATE PDF ACTION BLOCK IN CASE NO TEMPLATES EXIST
    $generate_pdf = '
    <tr>
        <td class="dvtCellInfo" style="width:100%;" align="center">
            <input type="button" class="crmbutton small cancel" value="' . $app_strings["LBL_CANCEL_BUTTON_LABEL"] . '" onclick="fninvsh(\'PDFListViewDiv\');" />      
        </td>
    </tr>';
}

$output = '
<table border=0 cellspacing=0 cellpadding=5 width=100% class=layerHeadingULine>
<tr>
	<td width="90%" align="left" class="genHeaderSmall" id="PDFListViewDivHandle" style="cursor:move;">' . $pdf_strings["LBL_PDF_ACTIONS"] . '                 			
	</td>
	<td width="10%" align="right">
		<a href="javascript:fninvsh(\'PDFListViewDiv\');"><img title="' . $app_strings["LBL_CLOSE"] . '" alt="' . $app_strings["LBL_CLOSE"] . '" src="themes/images/close.gif" border="0"  align="absmiddle" /></a>
	</td>
</tr>
</table>
<table border=0 cellspacing=0 cellpadding=5 width=100% align=center>
    <tr><td class="small">
        <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
        ' . $template_output . $language_output . $generate_pdf . '
        </table>
    </td></tr>
</table>
';

echo $output;
exit;
