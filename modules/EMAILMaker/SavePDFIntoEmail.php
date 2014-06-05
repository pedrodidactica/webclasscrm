<?php

function savePDFIntoEmail($focus,$parentid,$template_ids,$language,$pmodule = '')
{
    require_once("modules/PDFMaker/InventoryPDF.php");
    require_once("modules/PDFMaker/mpdf/mpdf.php");
    $db="adb";
    $vcv="vtiger_current_version";
    $salt="site_URL";
    $cu="current_user";
    
    global $$db, $$vcv, $$salt, $$cu;
    
    if (is_array($parentid))
    {
        $modFocus = $parentid;
        $f_date = date("ymdHi");
        $file_name = "doc_".$f_date.".pdf";
    }
    else
    {
        $pmodule=getSalesEntityType($parentid);
        
        $modFocus = CRMEntity::getInstance($pmodule);  
        if(isset($parentid)) 
        {
            $modFocus->retrieve_entity_info($parentid,$pmodule);
            $modFocus->id = $parentid;
        } 
    
        $result=$$db->query("SELECT fieldname FROM vtiger_field WHERE uitype=4 AND tabid=".getTabId($pmodule));
        $fieldname=$$db->query_result($result,0,"fieldname");
        if(isset($modFocus->column_fields[$fieldname]) && $modFocus->column_fields[$fieldname]!="")
        {
            $file_name = generate_cool_uri($modFocus->column_fields[$fieldname]).".pdf";
        }
        else
        {
            $f_date = date("ymdHi");
            $file_name = "doc_".$f_date.".pdf";
        }
    }
    createPDFAndSaveFile($template_ids,$focus,$modFocus,$file_name,$pmodule,$language);   
}
?>