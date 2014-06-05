<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('modules/EMAILMaker/EMAILMaker.php');

global $adb;
global $upload_maxsize;
global $theme,$default_charset;
global $current_language;
global $site_URL;

$EMAILMaker = new EmailMaker(); 

if($EMAILMaker->CheckPermissions("DETAIL") == false)
  $EMAILMaker->DieDuePermission();
  
if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) 
{
    if (isset($_REQUEST['templateid']) && $_REQUEST['templateid'] != "")
    {
        $templateid = $_REQUEST['templateid'];
        $sql = "delete from vtiger_emakertemplates where templateid=?";
    	$adb->pquery($sql, array($templateid));
    	
    	$sql = "delete from vtiger_emakertemplates_settings where templateid=?";
    	$adb->pquery($sql, array($templateid));
    }
    else
    {
        $idlist = $_REQUEST['idlist'];
        
        $id_array=explode(';', $idlist);
        
        for($i=0; $i < count($id_array)-1; $i++) {
        	$sql = "delete from vtiger_emakertemplates where templateid=?";
        	$adb->pquery($sql, array($id_array[$i]));
        	
        	$sql = "delete from vtiger_emakertemplates_settings where templateid=?";
        	$adb->pquery($sql, array($id_array[$i]));
        }
    }
}
header("Location:index.php?module=EMAILMaker&action=ListEmailTemplates&parenttab=Tools");

?>
