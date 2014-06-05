<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

require_once('Smarty_setup.php');
require_once('include/database/PearDatabase.php');

global $adb, $current_user;

$smarty = new vtigerCRM_Smarty;

$rel_module = $_REQUEST["emailmodule"];

require_once('include/utils/UserInfoUtil.php');
global $app_strings;
global $mod_strings;
global $theme,$default_charset;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

global $current_language;

$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("PARENTTAB", getParentTab());
$smarty->assign("THEME_PATH",$theme_path);
$smarty->assign("IMAGE_PATH",$image_path);

if(isPermitted($currentModule,"EditView") == 'yes'){
	$editing = true;
	$smarty->assign("EDIT","permitted");
	$smarty->assign("EXPORT","yes");
  $smarty->assign("IMPORT","yes");
}
if(isPermitted($currentModule,"Delete") == 'yes'){
	$deleting = true;
	$smarty->assign("DELETE","permitted");
}


$rel_module_id = getTabid($rel_module);

$restricted_modules = array('Emails','Events','Webmails');
$Related_Modules = array();

$rsql = "SELECT vtiger_tab.name FROM vtiger_tab 
				INNER JOIN vtiger_relatedlists on vtiger_tab.tabid=vtiger_relatedlists.related_tabid 
				WHERE vtiger_tab.isentitytype=1 
				AND vtiger_tab.name NOT IN(".generateQuestionMarks($restricted_modules).") 
				AND vtiger_tab.presence=0 AND vtiger_relatedlists.label!='Activity History'
        AND vtiger_relatedlists.tabid = '".$rel_module_id."' AND vtiger_tab.tabid != '".$rel_module_id."'";
$relatedmodules = $adb->pquery($rsql,array($restricted_modules));

if($adb->num_rows($relatedmodules)) 
{
   while($resultrow = $adb->fetch_array($relatedmodules)) 
   {
	     $Related_Modules[] = $resultrow['name'];
	 }
}

$Related_Blocks =array();

$sql = "SELECT * FROM vtiger_emakertemplates_relblocks WHERE secmodule IN(".generateQuestionMarks($Related_Modules).") ORDER BY relblockid";
$result = $adb->pquery($sql,array($Related_Modules));
while($row = $adb->fetchByAssoc($result))
{
	 $Edits = array();
	 
   $Edits[] = "<a href='index.php?module=EMAILMaker&action=EMAILMakerAjax&file=EditRelatedBlock&mode=add&record=".$row["relblockid"]."'>".$mod_strings["LBL_INSERT_TO_TEXT"]."</a>";
	 
   if ($editing) $Edits[] = "<a href='index.php?module=EMAILMaker&action=EMAILMakerAjax&file=EditRelatedBlock&record=".$row["relblockid"]."'>".$app_strings["LBL_EDIT"]."</a>";
	 
	 if ($deleting) $Edits[] = "<a href='javascript: deleteRelBlock(".$row["relblockid"].");'>".$app_strings["LBL_DELETE"]."</a>";
	 
	 
	 $name = "<a href='index.php?module=EMAILMaker&action=EMAILMakerAjax&file=EditRelatedBlock&mode=add&record=".$row["relblockid"]."'>".$row["name"]."</a>";
	 
	 $edit = implode(" | ", $Edits);
	 
	 $secmodule = getTranslatedString($row["secmodule"]);
	 
   $Related_Blocks[] = array("blockname" => $name, "secmodule" => $secmodule, "edit" => $edit);
}

$smarty->assign("RELATEDBLOCKS",$Related_Blocks);

include_once("version.php");
$smarty->assign("VERSION",$version);



$smarty->assign("REL_MODULE",$rel_module);

$smarty->display("modules/EMAILMaker/ListRelatedBlocks.tpl");

?>