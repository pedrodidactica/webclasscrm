<?php

function generate_cool_uri($name) {
    $Search = array("$", "€", "&", "%", ")", "(", ".", " - ", "/", " ", ",", "ľ", "š", "č", "ť", "ž", "ý", "á", "í", "é", "ó", "ö", "ů", "ú", "ü", "ä", "ň", "ď", "ô", "ŕ", "Ľ", "Š", "Č", "Ť", "Ž", "Ý", "Á", "Í", "É", "Ó", "Ú", "Ď", "\"", "°", "ß");
    $Replace = array("", "", "", "", "", "", "-", "-", "-", "-", "-", "l", "s", "c", "t", "z", "y", "a", "i", "e", "o", "o", "u", "u", "u", "a", "n", "d", "o", "r", "l", "s", "c", "t", "z", "y", "a", "i", "e", "o", "u", "d", "", "", "ss");
    $return = str_replace($Search, $Replace, $name);
    // echo $return;
    return $return;
}

function createPDFAndSaveFile($templates, $focus, $modFocus, $file_name, $moduleName, $language) {
    $db = "adb";
    $cu = "current_user";
    $dl = "default_language";
    global $$db, $$cu, $$dl;

    require_once("modules/PDFMaker/PDFMaker.php");
    $PDFMaker = new PDFMaker();

    $date_var = date("Y-m-d H:i:s");
    //to get the owner id
    $ownerid = $focus->column_fields["assigned_user_id"];
    if (!isset($ownerid) || $ownerid == "")
        $ownerid = $$cu->id;

    $current_id = $$db->getUniqueID("vtiger_crmentity");
    $templates = rtrim($templates, ";");

    //workflow - in case that value 'none' in selectbox has been selected, because it was only one value due to permission restrictions
    if ($templates != "0")
        $Templateids = explode(";", $templates);
    else
        $Templateids = array();

    $name = "";

    if (!$language || $language == "")
        $language = $$dl;

    $preContent = "";
    if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "edit" && isset($_REQUEST["module"]) && $_REQUEST["module"] == "PDFMaker") {
        foreach ($Templateids as $templateid) {
            $preContent["header" . $templateid] = $_REQUEST["header" . $templateid];
            $preContent["body" . $templateid] = $_REQUEST["body" . $templateid];
            $preContent["footer" . $templateid] = $_REQUEST["footer" . $templateid];
        }
    }
    //called function GetPreparedMPDF returns the name of PDF and fill the variable $mpdf with prepared HTML output
    $mpdf = "";
    $Records = array($modFocus->id);

    $name = $PDFMaker->GetPreparedMPDF($mpdf, $Records, $Templateids, $moduleName, $language, $preContent);
    $name = generate_cool_uri($name);

    $upload_file_path = decideFilePath();

    if ($name != "")
        $file_name = $name . ".pdf";

    $mpdf->Output($upload_file_path . $current_id . "_" . $file_name);

    $filesize = filesize($upload_file_path . $current_id . "_" . $file_name);
    $filetype = "application/pdf";

    $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
    $params1 = array($current_id, $$cu->id, $ownerid, "Documents Attachment", $focus->column_fields["description"], $$db->formatDate($date_var, true), $$db->formatDate($date_var, true));

    $$db->pquery($sql1, $params1);

    $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
    $params2 = array($current_id, $file_name, $focus->column_fields["description"], $filetype, $upload_file_path);
    $$db->pquery($sql2, $params2);

    $sql3 = 'insert into vtiger_seattachmentsrel values(?,?)';
    $$db->pquery($sql3, array($focus->id, $current_id));

    $sql4 = "UPDATE vtiger_notes SET filesize=?, filename=? WHERE notesid=?";
    $$db->pquery($sql4, array($filesize, $file_name, $focus->id));

    return true;
}

function getTranslatedStringInLang($str, $emodule, $language) {
    if ($emodule != "Products/Services") {
        $app_lang = return_application_language($language);
        $mod_lang = return_specified_module_language($language, $emodule);
    } else {
        $app_lang = return_specified_module_language($language, "Services");
        $mod_lang = return_specified_module_language($language, "Products");
    }

    $trans_str = ($mod_lang[$str] != '') ? $mod_lang[$str] : (($app_lang[$str] != '') ? $app_lang[$str] : $str);
    return $trans_str;
}
