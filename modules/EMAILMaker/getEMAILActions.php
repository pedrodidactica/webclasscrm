<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('Smarty_setup.php');
require_once('data/Tracker.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');
require_once('modules/EMAILMaker/EMAILMaker.php');

global $adb;
global $mod_strings;
global $app_strings;
global $current_language, $current_user;
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$EMAILMaker = new EmailMaker();
if($EMAILMaker->CheckPermissions("DETAIL") == false)
{
  $output =  '<table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
              <tr>
                <td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;text-align:center;">
                  <strong>'.$app_strings["LBL_PERMISSION"].'</strong>
                </td>
              </tr>              		
              </table>';
  die($output);
}

$record = $_REQUEST["record"];

$sql = "SELECT setype FROM vtiger_crmentity WHERE crmid = '".$record."'";
$relmodule = $adb->query_result($adb->query($sql),0,"setype");
$tabid = getTabid($relmodule);

$smarty = new vtigerCRM_smarty;

$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("MOD", $mod_strings);

$smarty->assign("MODULE", $relmodule);
$smarty->assign("IMAGE_PATH", $image_path);

$smarty->assign("ID", $_REQUEST["record"]);

require('user_privileges/user_privileges_'.$current_user->id.'.php');

$smarty->assign('EMAILMAKER_MOD',return_module_language($current_language,"EMAILMaker"));
      

$userid=0;
if(isset($_SESSION["authenticated_user_id"]))
	$userid = $_SESSION["authenticated_user_id"];

$use_template = $EMAILMaker->GetAvailableTemplates($relmodule);

if(count($use_template)>0)
	$no_templates_exist = 0;
else 
	$no_templates_exist = 1;

$smarty->assign('CRM_TEMPLATES',$use_template);
$smarty->assign('CRM_TEMPLATES_EXIST',$no_templates_exist);

//04.06
$default_template = "";

$default_sql = "SELECT templateid FROM vtiger_emakertemplates  
        INNER JOIN vtiger_emakertemplates_userstatus USING ( templateid )
        WHERE module = ? AND userid = ? AND is_default IN (1,3) AND is_active = 1";

$default_res=$adb->pquery($default_sql,array($relmodule,$current_user->id));
while($default_row = $adb->fetchByAssoc($default_res))
{
  $default_template = $default_row["templateid"];
}

$smarty->assign('DEFAULT_TEMPLATE',$default_template);
//04.06

$category = getParentTab();
$smarty->assign("CATEGORY",$category);

$sql3 = "SELECT count FROM vtiger_emakertemplates_picklists WHERE tabid = ?";
$result3 = $adb->pquery($sql3,array($tabid)); 
$num_rows3 = $adb->num_rows($result3);
if ($num_rows3 > 0)
    $picklist_count = $adb->query_result($result3,0,"count");
else
    $picklist_count = "5";
            
$smarty->assign("PICKLIST_COUNT",$picklist_count);

if ($relmodule == "Campaigns")
{
    $s = false;
    
    if (isPermitted('Contacts', 'index', '') == 'yes')
    {
        echo "<a href=\"javascript:;\" onClick=\"getEMAILCampaignPopup(this,'Contacts','$record')\">".$mod_strings["LBL_SEND_EMAILS_TO_CONTACTS"]."</a><br />";
        $s = true;
    }
    
    if (isPermitted('Leads', 'index', '') == 'yes')
    {
        echo "<a href=\"javascript:;\" onClick=\"getEMAILCampaignPopup(this,'Leads','$record')\">".$mod_strings["LBL_SEND_EMAILS_TO_LEADS"]."</a><br />";
        $s = true;
    }
    
    if (isPermitted('Accounts', 'index', '') == 'yes')
    {
        echo "<a href=\"javascript:;\" onClick=\"getEMAILCampaignPopup(this,'Accounts','$record')\">".$mod_strings["LBL_SEND_EMAILS_TO_ACCOUNTS"]."</a><br />";
        $s = true;
    }
    
    if ($s) echo "<hr>";
}
$smarty->display("modules/EMAILMaker/EmailMakerActions.tpl");
?>