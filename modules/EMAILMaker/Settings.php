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
require_once("modules/com_vtiger_workflow/VTWorkflowUtils.php");

global $mod_strings, $app_strings, $theme, $adb;
$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$smarty->assign("THEME", "$theme");
$smarty->assign("IMAGE_PATH", "themes/$theme/images/");

// Operation to be restricted for non-admin users.
global $current_user;
if(!is_admin($current_user)) {	
	$smarty->display(vtlib_getModuleTemplate('Vtiger','OperationNotPermitted.tpl'));	
} else {
	$module = vtlib_purify($_REQUEST['formodule']);

	$menu_array = Array();
	
	$menu_array['EMAILMakerSet']['location'] = 'index.php?module=EMAILMaker&action=EMAILButtons&parenttab=Settings';
	$menu_array['EMAILMakerSet']['image_src'] = 'themes/images/set-IcoTwoTabConfig.gif';
    $menu_array['EMAILMakerSet']['label'] = getTranslatedString('LBL_EMAIL_BUTTONS',$module);
	$menu_array['EMAILMakerSet']['desc'] = getTranslatedString('LBL_EMAIL_BUTTONS_DESC',$module);
	
    $menu_array['EMAILMakerPrivilegies']['location'] = 'index.php?module=EMAILMaker&action=ProfilesPrivilegies&parenttab=Settings';
	$menu_array['EMAILMakerPrivilegies']['image_src'] = 'themes/images/ico-profile.gif';
	$menu_array['EMAILMakerPrivilegies']['label'] = getTranslatedString('LBL_PROFILES',$module);
    $menu_array['EMAILMakerPrivilegies']['desc'] = getTranslatedString('LBL_PROFILES_DESC',$module);

    $menu_array['EMAILMakerPicklist']['location'] = 'index.php?module=EMAILMaker&action=EditPicklist&parenttab=Settings';
	$menu_array['EMAILMakerPicklist']['image_src'] = 'themes/images/picklist.gif';
	$menu_array['EMAILMakerPicklist']['label'] = getTranslatedString('LBL_PICKLIST_EDITOR',$module);
    $menu_array['EMAILMakerPicklist']['desc'] = getTranslatedString('LBL_PICKLIST_EDITOR_INFO',$module);
   
    $menu_array['EMAILMakerDefaultSettings']['location'] = 'index.php?module=EMAILMaker&action=DefaultSettings&parenttab=Settings';
	$menu_array['EMAILMakerDefaultSettings']['image_src'] = 'themes/images/set-IcoTwoTabConfig.gif';
	$menu_array['EMAILMakerDefaultSettings']['label'] = getTranslatedString('LBL_DEFAULT_SETTINGS',$module);
    $menu_array['EMAILMakerDefaultSettings']['desc'] = getTranslatedString('LBL_DEFAULT_SETTINGS_DESC',$module);
   
    $menu_array['License']['location'] = 'index.php?module='.$module.'&action=LicenseSettings&parenttab=Settings';
	$menu_array['License']['image_src'] = 'themes/images/vtlib_modmng.gif';
	$menu_array['License']['label'] = getTranslatedString('LICENSE_SETTINGS',$module);
    $menu_array['License']['desc'] = getTranslatedString('LICENSE_SETTINGS_INFO',$module);
   
    $menu_array['UpdateModule']['location'] = 'index.php?module=Settings&action=ModuleManager&module_update=Step1&src_module='.$module.'&parenttab=Settings';
	$menu_array['UpdateModule']['image_src'] = 'themes/images/vtlib_modmng.gif';
	$menu_array['UpdateModule']['label'] = getTranslatedString('LBL_UPGRADE',"Settings");
    $menu_array['UpdateModule']['desc'] = getTranslatedString('LBL_UPGRADE',"Settings")." ".$module;
    
    
	
    
	//add blanks for 3-column layout
	$count = count($menu_array)%3;
	if($count>0) {
		for($i=0;$i<3-$count;$i++) {
			$menu_array[] = array();
		}
	}
	
	$smarty->assign('MODULE',$module);
	$smarty->assign('MODULE_LBL',getTranslatedString($module));
	$smarty->assign('MENU_ARRAY', $menu_array);

	$smarty->display(vtlib_getModuleTemplate('Vtiger','Settings.tpl'));
}
// ITS4YOU-END
?>