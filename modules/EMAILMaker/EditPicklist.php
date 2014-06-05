<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('Smarty_setup.php');
require_once("include/utils/utils.php");

global $mod_strings,$app_strings,$theme,$currentModule,$app_list_strings;
$smarty=new vtigerCRM_Smarty;
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$smarty->assign("THEME", $theme);

if (isset($_REQUEST["mode"]))
    $mode = addslashes($_REQUEST["mode"]);
else
    $mode = "view";

$Modules_List = array();

$sql = "SELECT vtiger_tab.* FROM vtiger_tab 
        INNER JOIN vtiger_links ON vtiger_links.tabid = vtiger_tab.tabid 
        WHERE vtiger_tab.isentitytype=1 
          AND vtiger_tab.tabid NOT IN (9, 10, 16, 28) 
          AND vtiger_tab.name != 'EMAILMaker'
          AND vtiger_links.linktype = 'DETAILVIEWWIDGET' 
          AND vtiger_links.linkurl LIKE 'module=EMAILMaker&action=EMAILMakerAjax&file=getEMAILActions&record=%'";
$result = $adb->query($sql);
while($row = $adb->fetchByAssoc($result))
{
    $tabid = $row['tabid']; 
    $tablabel = getTranslatedString($row['tablabel'],$row['name']);
    
    if ($tablabel == "") $tablabel = $row['tablabel'];
    
    if ($mode == "save")
    {
        $sql2 = "DELETE FROM vtiger_emakertemplates_picklists WHERE tabid = ?";
        $adb->pquery($sql2,array($tabid));
        
        if(isset($_REQUEST["email_picklist_value_".$tabid]) && $_REQUEST["email_picklist_value_".$tabid] != "")
        {
            $count = trim(addslashes($_REQUEST["email_picklist_value_".$tabid])); 
            
            if ($count < 1) $count = "";
            
            $sql3 = "INSERT INTO vtiger_emakertemplates_picklists (tabid, count) VALUES (?,?)";
            $adb->pquery($sql3,array($tabid,$count));   
        }
        else
        {
            $count = "";    
        }
    }
    else
    {
        $sql3 = "SELECT count FROM vtiger_emakertemplates_picklists WHERE tabid = ?";
        $result3 = $adb->pquery($sql3,array($tabid)); 
        $num_rows3 = $adb->num_rows($result3);
        if ($num_rows3 > 0)
            $count = $adb->query_result($result3,0,"count");
        else
            $count = "";
    }
        
    $Modules_List[] = array("name"=>$row['name'],
                            "tabid"=>$tabid,
                            "tablabel"=>$tablabel,
                            "count"=>$count);
}

$smarty->assign("MODULESLIST", $Modules_List);

if ($mode == "save")
{
    $mode = "view";
}

$smarty->assign("MODE", $mode);
$smarty->display(vtlib_getModuleTemplate($currentModule,'EditPicklist.tpl'));


?>