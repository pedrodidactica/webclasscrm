<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once("include/utils/utils.php");
require_once('modules/PDFMaker/PDFMaker.php');

global $mod_strings, $app_strings, $theme, $currentModule, $adb, $current_language, $vtiger_current_version;
Debugger::GetInstance()->Init();

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("VTIGER_VERSION", $vtiger_current_version);

if (isset($_REQUEST["mode"])) {
    switch ($_REQUEST["mode"]) {
        case "save":
            $itemsCount = $_REQUEST["newItems"];
            if ($itemsCount != "") {
                $sql0 = "SELECT id FROM vtiger_language WHERE prefix = ? LIMIT 1";
                $currLangId = $adb->query_result($adb->pquery($sql0, array($current_language)), 0, "id");

                for ($i = 0; $i < $itemsCount; $i++) {
                    if ($_REQUEST["newLblKey" . $i] != "") {
                        $label_key = "C_" . $_REQUEST["newLblKey" . $i];

                        $sql1 = "INSERT IGNORE INTO vtiger_pdfmaker_label_keys (label_key) VALUES (?)";
                        $adb->pquery($sql1, array($label_key));

                        $sql2 = "SELECT label_id FROM vtiger_pdfmaker_label_keys WHERE label_key=?";
                        $label_id = $adb->query_result($adb->pquery($sql2, array($label_key)), 0, "label_id");

                        $sql3 = "INSERT IGNORE INTO vtiger_pdfmaker_label_vals (label_id, lang_id, label_value) VALUES (?, ?, ?)";
                        $adb->pquery($sql3, array($label_id, $currLangId, $_REQUEST["newLblVal" . $i]));
                    }
                }
            }
            break;

        case "delete":
            $sql1 = "DELETE FROM vtiger_pdfmaker_label_vals WHERE label_id IN (";
            $sql2 = "DELETE FROM vtiger_pdfmaker_label_keys WHERE label_id IN (";
            $params = array();
            foreach ($_REQUEST as $key => $val) {
                if (substr($key, 0, 4) == "chx_" && $val == "on") {
                    list($dump, $id) = explode("_", $key, 2);

                    if (is_numeric($id)) {
                        $sql1 .= "?,";
                        $sql2 .= "?,";
                        array_push($params, $id);
                    }
                }
            }

            if (count($params) > 0) {
                $sql1 = rtrim($sql1, ",") . ")";
                $sql2 = rtrim($sql2, ",") . ")";
                $adb->pquery($sql1, $params);
                $adb->pquery($sql2, $params);
            }
            break;

        case "otherLangs":
            $PDFMaker = new PDFMaker();
            list($oLabels, $languages) = $PDFMaker->GetCustomLabels();

            $currLangId = "";
            foreach ($languages as $langId => $langVal) {
                if ($langVal["prefix"] == $current_language) {
                    $currLangId = $langId;
                    break;
                }
            }

            $label_id = $_REQUEST["label_id"];
            $oLbl = $oLabels[$label_id];

            $output = '<table border=0 cellspacing=0 cellpadding=5 width=100% class=layerHeadingULine>
                        <tr>
                        	<td width="90%" align="left" class="genHeaderSmall" id="otherLangsDivHandle" style="cursor:move;">' . $oLbl->GetKey() . '</td>
                        	<td width="10%" align="right">
                        		<a href="javascript:hideOtherLangs();"><img title="' . $app_strings["LBL_CLOSE"] . '" alt="' . $app_strings["LBL_CLOSE"] . '" src="themes/images/close.gif" border="0"  align="absmiddle" /></a>
                        	</td>
                        </tr>
                        </table>';
            $output .= '<table class="tableHeading" border="0" cellpadding="5" cellspacing="0" width="100%">
                            <tr><th class="colHeader" width="30%">' . $mod_strings["LBL_LANG"] . '</th>
                                <th class="colHeader" width="70%">' . $mod_strings["LBL_VALUE"] . '</th>
                            </tr>';

            $langValsArr = $oLbl->GetLangValsArr();
            foreach ($langValsArr as $langId => $langVal) {
                if ($langId == $currLangId)
                    continue;

                $output.='<tr>
                            <td class="cellLabel" style="font-weight:bold;">' . $languages[$langId]["label"] . '</td>
                            <td class="cellText" align="left" id="mouseArea_' . $label_id . '_' . $langId . '" valign="top">
                                &nbsp;&nbsp;<span id="dtlview_' . $label_id . '_' . $langId . '">' . $langVal . '</span>
                                 <div id="editarea_' . $label_id . '_' . $langId . '" style="display:none;">
                                	<input class="detailedViewTextBox" type="text" id="txtbox_' . $label_id . '_' . $langId . '" name="' . $label_id . '_' . $langId . '" maxlength="100" value="' . $langVal . '"></input>
                                    <br><input name="button_' . $label_id . '_' . $langId . '" id="button_' . $label_id . '_' . $langId . '" type="button" class="crmbutton small save" value="' . $app_strings["LBL_SAVE_LABEL"] . '"/>' . $app_strings["LBL_OR"] . '
                                    <a href="javascript:;" id="anchor_' . $label_id . '_' . $langId . '" class="link">' . $app_strings["LBL_CANCEL_BUTTON_LABEL"] . '</a>
                                </div>
                            </td>
                            
                          </tr>';

                $output.='<script language="javascript" type="text/javascript">
                        jQuery("#mouseArea_' . $label_id . '_' . $langId . '").mouseover(function(){
                            hndMouseOver("1","' . $label_id . '_' . $langId . '");
                        });

                        jQuery("#mouseArea_' . $label_id . '_' . $langId . '").mouseleave(function(){
                            fnhide(\'crmspanid\');
                        });
                        
                        jQuery("#txtbox_' . $label_id . '_' . $langId . '").focus(function(){
                            jQuery(this).toggleClass("detailedViewTextBox");
                            jQuery(this).toggleClass("detailedViewTextBoxOn");
                        });
                        
                        jQuery("#txtbox_' . $label_id . '_' . $langId . '").blur(function(){
                            jQuery(this).toggleClass("detailedViewTextBox");
                            jQuery(this).toggleClass("detailedViewTextBoxOn");
                        });

                        jQuery("#button_' . $label_id . '_' . $langId . '").click(function(){
                            saveEdited(\'' . $label_id . '_' . $langId . '\');
                            fnhide(\'crmspanid\');
                        });

                        jQuery("#anchor_' . $label_id . '_' . $langId . '").click(function(){
                            hndCancel("dtlview_' . $label_id . '_' . $langId . '", "editarea_' . $label_id . '_' . $langId . '","' . $label_id . '_' . $langId . '");
                        });
                        
                      </script>';
            }
            $output.='<tr><td colspan="2" align="center">
                        <input type="button" onclick="hideOtherLangs();" class="crmbutton small cancel" id="cancelButt" value="' . $app_strings["LBL_CANCEL_BUTTON_LABEL"] . '" />
                      </td></tr></table>';


            echo $output;
            exit;
    }
}

$PDFMaker = new PDFMaker();
list($oLabels, $languages) = $PDFMaker->GetCustomLabels();
$currLang = array();
foreach ($languages as $langId => $langVal) {
    if ($langVal["prefix"] == $current_language) {
        $currLang["id"] = $langId;
        $currLang["name"] = $langVal["name"];
        $currLang["label"] = $langVal["label"];
        $currLang["prefix"] = $langVal["prefix"];
        break;
    }
}

$viewLabels = array();
foreach ($oLabels as $lblId => $oLabel) {
    $viewLabels[$lblId]["key"] = $oLabel->GetKey();
    $viewLabels[$lblId]["lang_values"] = $oLabel->GetLangValsArr();
}

$smarty->assign("LABELS", $viewLabels);
$smarty->assign("LANGUAGES", $languages);
$smarty->assign("CURR_LANG", $currLang);
$smarty->display(vtlib_getModuleTemplate($currentModule, 'CustomLabels.tpl'));
