<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('include/utils/utils.php');
require_once('modules/EMAILMaker/EMAILMaker.php');
global $adb;
global $current_user;

$EMAILMaker = new EmailMaker();

if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) 
{
    $dripid = vtlib_purify($_REQUEST["dripid"]);
    $driptplid = vtlib_purify($_REQUEST["driptplid"]);
    $templateid = from_html($_REQUEST["templateid"]);
    
    $days = $_REQUEST["delay_days"];
    $hours = $_REQUEST["delay_hours"];
    $minutes = $_REQUEST["delay_minutes"];

    $delay = ($days * 86400) + ($hours * 3600) + ($minutes * 60);

    
    if(isset($driptplid) && $driptplid !='')
    {
    	$sql = "UPDATE vtiger_emakertemplates_drip_tpls SET templateid =?, delay =? WHERE dripid = ? AND driptplid = ?";
    	$params = array($templateid, $delay, $dripid, $driptplid);
    	$adb->pquery($sql, $params);
    }
    else
    {
    	$driptplid = $adb->getUniqueID('tiger_emakertemplates_drip_tpls');
    	$sql2 = "INSERT INTO vtiger_emakertemplates_drip_tpls (driptplid, dripid, templateid, delay, deleted) values (?,?,?,?,?)";
    	$params2 = array($driptplid, $dripid, $templateid, $delay, 0);
    	$adb->pquery($sql2, $params2);
    }

    header("Location:index.php?module=EMAILMaker&action=DetailViewDripEmails&parenttab=Tools&dripid=".$dripid);
}
else
{
    header("Location:index.php?module=EMAILMaker&action=ListDripEmails&parenttab=Tools");
}
exit;

?>
