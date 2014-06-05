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

global $mod_strings, $app_strings, $currentModule, $adb, $current_language, $vtiger_current_version;
Debugger::GetInstance()->Init();

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("VTIGER_VERSION", $vtiger_current_version);

$PDFMaker = new PDFMaker();
if (isset($_REQUEST["mode"])) {
    switch ($_REQUEST["mode"]) {
        case "edit":
        case "duplicate":
            //edit view
            $template = array();
            if (isset($_REQUEST["tplid"]) && $_REQUEST["tplid"] != "") {
                $sql = "SELECT * FROM vtiger_pdfmaker_productbloc_tpl WHERE id=?";
                $result = $adb->pquery($sql, array($_REQUEST["tplid"]));
                $row = $adb->fetchByAssoc($result);
                if ($_REQUEST["mode"] != "duplicate") {
                    $template["id"] = $row["id"];
                    $template["name"] = $row["name"];
                }
                $template["body"] = $row["body"];
            }

            //if no ID is specified then it is create view
            $smarty->assign("EDIT_TEMPLATE", $template);
            //PROPERTIES tab
            $ProductBlockFields = $PDFMaker->GetProductBlockFields();
            foreach ($ProductBlockFields as $smarty_key => $pbFields) {
                $smarty->assign($smarty_key, $pbFields);
            }
            //LABELS
            //global lang
            $global_lang_labels = array_flip($app_strings);
            $global_lang_labels = array_flip($global_lang_labels);
            asort($global_lang_labels);
            $smarty->assign("GLOBAL_LANG_LABELS", $global_lang_labels);
            //custom lang
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

            //VIEW HELPERS
            if ($PDFMaker->GetVersionType() == "professional")
                $type = "professional";
            else
                $type = "basic";
            $smarty->assign("TYPE", $type);

            $smarty->display(vtlib_getModuleTemplate($currentModule, 'ProductBlocksEdit.tpl'));
            break;

        case "save":
            if (isset($_REQUEST["tplid"]) && $_REQUEST["tplid"] != "") {
                $sql = "UPDATE vtiger_pdfmaker_productbloc_tpl SET name=?, body=? WHERE id=?";
                $adb->pquery($sql, array($_REQUEST["template_name"], $_REQUEST["body"], $_REQUEST["tplid"]));
            } else {
                $sql = "INSERT INTO vtiger_pdfmaker_productbloc_tpl(name, body) VALUES(?,?)";
                $adb->pquery($sql, array($_REQUEST["template_name"], $_REQUEST["body"]));
            }
            echo '<meta http-equiv="refresh" content="0;url=index.php?module=PDFMaker&action=ProductBlocks&parenttab=Settings">';
            break;

        case "delete":
            $sql = "DELETE FROM vtiger_pdfmaker_productbloc_tpl WHERE id IN (";
            $params = array();
            foreach ($_REQUEST as $key => $val) {
                if (substr($key, 0, 4) == "chx_" && $val == "on") {
                    list($dump, $id) = explode("_", $key, 2);
                    if (is_numeric($id)) {
                        $sql .= "?,";
                        array_push($params, $id);
                    }
                }
            }

            if (count($params) > 0) {
                $sql = rtrim($sql, ",") . ")";
                $adb->pquery($sql, $params);
            }
            echo '<meta http-equiv="refresh" content="0;url=index.php?module=PDFMaker&action=ProductBlocks&parenttab=Settings">';
            break;

        default:
            echo '<meta http-equiv="refresh" content="0;url=index.php?module=PDFMaker&action=ProductBlocks&parenttab=Settings">';
            break;
    }
} else {
    //listview
    $sql = "SELECT * FROM vtiger_pdfmaker_productbloc_tpl";
    $result = $adb->query($sql);
    while ($row = $adb->fetchByAssoc($result)) {
        $templates[$row["id"]]["name"] = $row["name"];
        $templates[$row["id"]]["body"] = html_entity_decode($row["body"], ENT_QUOTES);
    }
    $smarty->assign("PB_TEMPLATES", $templates);    
    $smarty->display(vtlib_getModuleTemplate($currentModule, 'ProductBlocks.tpl'));
}
