<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

function showEditPDFForm($PDFContents) {
    require_once("classes/simple_html_dom.php");
    global $adb, $mod_strings, $app_strings, $default_charset;

    $commontemplateids = trim($_REQUEST["commontemplateid"], ";");
    $Templateids = explode(";", $commontemplateids);

    if (isset($_REQUEST["idslist"]) && $_REQUEST["idslist"] != "") {   //generating from listview 
        $Records = explode(";", rtrim($_REQUEST["idslist"], ";"));
    } elseif (isset($_REQUEST['record'])) {
        $Records = array($_REQUEST["record"]);
    }

    echo '<html>
	      <head>
	    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	      <title>PDF Maker - vtiger CRM 5 - Commercial Open Source CRM</title>
	      <style type="text/css">@import url("themes/' . $_SESSION["vtiger_authenticated_user_theme"] . '/style.css");</style>
	      </head>
	      <body>';

    if (isset($_SESSION["VTIGER_DB_VERSION"]) AND $_SESSION["VTIGER_DB_VERSION"] == "5.1.0") {
        echo '<script type="text/javascript" src="include/fckeditor/fckeditor.js"></script>';
    } else {
        echo '<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>';
    }
    echo '<form action="index.php" method="POST">
	      <input type="hidden" id="action" name="action" value="CreatePDFFromTemplate">
	      <input type="hidden" name="module" value="PDFMaker">
	      <input type="hidden" name="commontemplateid" value="' . $_REQUEST["commontemplateid"] . '">
	      <input type="hidden" name="template_ids" value="' . $_REQUEST["commontemplateid"] . '">
	      <input type="hidden" name="idslist" value="' . implode(";", $Records) . '">
	      <input type="hidden" name="relmodule" value="' . $_REQUEST["relmodule"] . '">
	      <input type="hidden" name="language" value="' . $_REQUEST["language"] . '">
	      <input type="hidden" name="pmodule" value="' . $_REQUEST["relmodule"] . '" />
	      <input type="hidden" name="pid" value="' . $_REQUEST["record"] . '">
	      <input type="hidden" name="mode" value="edit">';

    $templates = implode(",", $Templateids);
    $sql = "SELECT * FROM vtiger_pdfmaker WHERE templateid IN ($templates)";
    $result = $adb->query($sql);
    $num_rows = $adb->num_rows($result);

    echo "<div id='editTemplate'>";
    echo "<div style='padding: 10px;'>";
    echo $mod_strings["LBL_TEMPLATE"] . ":&nbsp;";

    $st = '';
    if ($num_rows > 1) {
        echo "<select onChange='changeTemplate(this.value);'>";
        while ($row = $adb->fetchByAssoc($result)) {
            if ($st == "")
                $st = $row['templateid'];
            echo "<option value='" . $row['templateid'] . "'>" . $row['filename'] . "</option>";
        }
        echo "</select>";
    }
    else {
        $st = $adb->query_result($result, 0, "templateid");
        echo $adb->query_result($result, 0, "filename");
    }
    echo '</div>';

    $export_buttons = "<input type='submit' value='" . $app_strings["LBL_EXPORT_TO_PDF"] . "' class='crmbutton small edit'>&nbsp;&nbsp;<input type='button' value='" . $mod_strings["LBL_SAVEASDOC"] . "' onClick='showDocSettings();' class='crmbutton small edit'>";
    echo "<center>$export_buttons</center>";
    echo "<br />";

    echo '<table class="small" width="100%" border="0" cellpadding="3" cellspacing="0"><tr>
	            <td style="width: 10px;" nowrap="nowrap">&nbsp;</td>
	            <td style="width: 15%;" class="dvtSelectedCell" id="body_tab" onclick="showHideTab(\'body\');" width="75" align="center" nowrap="nowrap"><b>' . $mod_strings["LBL_BODY"] . '</b></td>
			        <td class="dvtUnSelectedCell" id="header_tab" onclick="showHideTab(\'header\');" align="center" nowrap="nowrap"><b>' . $mod_strings["LBL_HEADER_TAB"] . '</b></td>
			        <td class="dvtUnSelectedCell" id="footer_tab" onclick="showHideTab(\'footer\');" align="center" nowrap="nowrap"><b>' . $mod_strings["LBL_FOOTER_TAB"] . '</b></td>
	            <td style="width: 50%;" nowrap="nowrap">&nbsp;</td> 
	      </tr></table>';


    foreach ($PDFContents AS $templateid => $templatedata) {
        $sections = array("body", "header", "footer");
        foreach ($sections as $val) {
            $html = str_get_html($templatedata[$val]);
            $textTags = $html->find('text');
            foreach ($textTags as $text) {
                $text->outertext = str_replace("  ", "&nbsp;&nbsp;", $text->outertext);
                $text->outertext = str_replace("&nbsp; ", "&nbsp;&nbsp;", $text->outertext);
            }
            $templatedata[$val] = htmlentities($html->save(), ENT_QUOTES, $default_charset);
        }

        echo '<div style="display:none;" id="body_div' . $templateid . '"> 
	         <textarea name="body' . $templateid . '" id="body' . $templateid . '" style="width:90%;height:500px" class=small tabindex="5">' . $templatedata["body"] . '</textarea>
	       </div>
	
	       <div style="display:none;" id="header_div' . $templateid . '"> 
	         <textarea name="header' . $templateid . '" id="header' . $templateid . '" style="width:90%;height:500px" class=small tabindex="5">' . $templatedata["header"] . '</textarea>
	       </div>
	 
	       <div style="display:none;" id="footer_div' . $templateid . '"> 
	         <textarea name="footer' . $templateid . '" id="footer' . $templateid . '" style="width:90%;height:500px" class=small tabindex="5">' . $templatedata["footer"] . '</textarea>
	       </div>';

        if (isset($_SESSION["VTIGER_DB_VERSION"]) AND $_SESSION["VTIGER_DB_VERSION"] == "5.1.0") {
            echo '<script type="text/javascript" defer="1">
	        var oFCKeditor = new FCKeditor(\'body' . $templateid . '\', "860", "510");
	        oFCKeditor.BasePath="include/fckeditor/";
	        oFCKeditor.Config["CustomConfigurationsPath"] = "../../../modules/PDFMaker/fck_config.js"  ;
	        oFCKeditor.ToolbarSet="BodyToolbar";
	        oFCKeditor.ReplaceTextarea();  
	        
	        var headerFCK = new FCKeditor(\'header' . $templateid . '\', "860", "510");
	        headerFCK.BasePath="include/fckeditor/";
	        headerFCK.Config["CustomConfigurationsPath"] = "../../../modules/PDFMaker/fck_config_fh.js"  ;
	        headerFCK.ToolbarSet="HeaderToolbar";       
	        headerFCK.ReplaceTextarea();
	        
	        var footerFCK = new FCKeditor(\'footer' . $templateid . '\', "860", "510");
	        footerFCK.BasePath="include/fckeditor/";
	        footerFCK.Config["CustomConfigurationsPath"] = "../../../modules/PDFMaker/fck_config_fh.js"  ;
	        footerFCK.ToolbarSet="HeaderToolbar";       
	        footerFCK.ReplaceTextarea();
	      </script>';
        } else {
            echo '<script type="text/javascript">
	          	CKEDITOR.replace( \'body' . $templateid . '\',{customConfig:\'../../modules/PDFMaker/fck_popup_config.js\'} );
	            CKEDITOR.replace( \'header' . $templateid . '\',{customConfig:\'../../modules/PDFMaker/fck_popup_config.js\'} );
	            CKEDITOR.replace( \'footer' . $templateid . '\',{customConfig:\'../../modules/PDFMaker/fck_popup_config.js\'} );
	         </script>';
        }
    }
    echo "<br /><center>$export_buttons</center>";
    echo "</div>";

    $language = $_SESSION['authenticated_user_language'];
    $mod_strings = return_module_language($language, "Documents");
    $pdf_strings = return_module_language($language, "PDFMaker");

    $sql = "select foldername,folderid from vtiger_attachmentsfolder order by foldername";
    $res = $adb->pquery($sql, array());
    $options = "";
    for ($i = 0; $i < $adb->num_rows($res); $i++) {
        $fid = $adb->query_result($res, $i, "folderid");
        $fldr_name = $adb->query_result($res, $i, "foldername");
        $options.='<option value="' . $fid . '">' . $fldr_name . '</option>';
    }

    echo '<div id="docSettings" style="display:none;">
	<table border=0 cellspacing=0 cellpadding=5 width=100% class=layerHeadingULine>
	<tr>
		<td width="90%" align="left" class="genHeaderSmall" id="PDFDocDivHandle" style="cursor:move;">' . $pdf_strings["LBL_SAVEASDOC"] . '                 			
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
	    </td></tr>
	</table>
	<table border=0 cellspacing=0 cellpadding=5 width=100% class="layerPopupTransport">
	<tr><td align=center class="small">
		<input type="submit" value="' . $app_strings["LBL_SAVE_BUTTON_LABEL"] . '" class="crmbutton small create"/>&nbsp;&nbsp;
		<input type="button" name="' . $app_strings["LBL_CANCEL_BUTTON_LABEL"] . '" value="' . $app_strings["LBL_CANCEL_BUTTON_LABEL"] . '" class="crmbutton small cancel" onclick="hideDocSettings();" />
	</td></tr>
	</table>
	</div>';

    echo "</form>";
    echo "<script type=\"text/javascript\" src=\"modules/PDFMaker/fck_popup_config.js\"></script>
	      <script type=\"text/javascript\">
	      
	      document.getElementById('body_div$st').style.display='block';
	      
	      var selectedTab='body';
	      var selectedTemplate='$st';
	      
	      function changeTemplate(newtemplate)
	      {
	          document.getElementById(selectedTab+'_div'+selectedTemplate).style.display='none';
	          document.getElementById(selectedTab+'_div'+newtemplate).style.display='block';
	          
	          selectedTemplate = newtemplate;
	      }
	      
	      function showDocSettings()
	      {
	          document.getElementById('editTemplate').style.display='none';
	          document.getElementById('docSettings').style.display='block';
	          document.getElementById('action').value='SavePDFDoc';
	      }
	      
	      function hideDocSettings()
	      {
	          document.getElementById('editTemplate').style.display='block';
	          document.getElementById('docSettings').style.display='none';
	          document.getElementById('action').value='CreatePDFFromTemplate';
	      }
	      
	      function showHideTab(tabname)
	      {
	          document.getElementById(selectedTab+'_tab').className='dvtUnSelectedCell';    
	          document.getElementById(tabname+'_tab').className='dvtSelectedCell';
	          
	          document.getElementById(selectedTab+'_div'+selectedTemplate).style.display='none';
	          document.getElementById(tabname+'_div'+selectedTemplate).style.display='block';
	
	          var formerTab=selectedTab;
	          selectedTab=tabname;
	      }
	      </script>";
}
