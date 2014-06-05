<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('Smarty_setup.php');
require_once('include/utils/utils.php');
require_once('modules/EMAILMaker/EMAILMaker.php');

global $app_strings;
global $mod_strings;
global $app_list_strings;
global $adb;
global $upload_maxsize;
global $theme,$default_charset;
global $current_language;
global $site_URL;
global $current_user;
    
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$EMAILMaker = new EmailMaker(); 
if($EMAILMaker->CheckPermissions("DETAIL") == false)
  $EMAILMaker->DieDuePermission();

$smarty = new vtigerCRM_Smarty;
  
if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) {
    $smarty->assign("EDIT","permitted");
    //$smarty->assign("IMPORT","yes");
} else {
    header("Location:index.php?module=EMAILMaker&action=ListDripEmails&parenttab=Tools");
    exit;
}

if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) {
  $smarty->assign("DELETE","permitted");
}

if(isset($_REQUEST['dripid']) && $_REQUEST['dripid']!='')
{
  	$dripid = $_REQUEST['dripid'];
  	
  	$sql = "SELECT * FROM vtiger_emakertemplates_drips WHERE dripid=?";
  	$result = $adb->pquery($sql, array($dripid));
  	$dripemailResult = $adb->fetch_array($result);

    $dripname = $dripemailResult["dripname"];
  	$select_module = $dripemailResult["module"];
    
    $owner = $dripemailResult["owner"];
    $sharingtype = $dripemailResult["sharingtype"];
    $sharingMemberArray = $EMAILMaker->GetSharingMemberArray($dripid,"drip");
}
else
{
    $dripid = "";
    $dripname = "";
    $dripemailResult = array();
    $owner = $current_user->id;
    $sharingtype = "public";
    $sharingMemberArray = array();
    $select_module = "";
}




if(isset($_REQUEST["isDuplicate"]) && $_REQUEST["isDuplicate"]=="true")
{
  $smarty->assign("DUPLICATE_DRIPNAME", $dripname);
  $dripname = "";
}
 
$smarty->assign("DRIPNAME",$dripname);

$smarty->assign("DESCRIPTION", $dripemailResult["description"]);

if (!isset($_REQUEST["isDuplicate"]) OR (isset($_REQUEST["isDuplicate"]) && $_REQUEST["isDuplicate"] != "true")) $smarty->assign("SAVEDRIPID", $dripid);

if ($dripid!="")
  $smarty->assign("EMODE", "edit");  

$smarty->assign("DRIPID", $dripid);
$smarty->assign("MODULENAME", getTranslatedString($select_module));
$smarty->assign("SELECTMODULE", $select_module);

$smarty->assign("MOD",$mod_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("APP", $app_strings);
$smarty->assign("PARENTTAB", getParentTab());


//$Modulenames = Array(''=>$mod_strings["LBL_PLS_SELECT"]);
$Modulenames = Array(''=>$app_strings["LBL_NONE"]);
$sql = "SELECT tabid, name FROM vtiger_tab WHERE (isentitytype=1 AND tabid NOT IN (9, 10, 16, 28)) OR tabid = '29' ORDER BY name ASC";
$result = $adb->query($sql);
while($row = $adb->fetchByAssoc($result)){
  if ($row['tabid'] != "29") $Modulenames[$row['name']] = getTranslatedString($row['name']);
} 

$smarty->assign("MODULENAMES",$Modulenames);

//Sharing
$drip_owners = get_user_array(false);
$smarty->assign("DRIP_OWNERS", $drip_owners);
$smarty->assign("DRIP_OWNER", $owner);

$sharing_types = Array("public"=>$app_strings["PUBLIC_FILTER"],
                       "private"=>$app_strings["PRIVATE_FILTER"],
                       "share"=>$app_strings["SHARE_FILTER"]);
$smarty->assign("SHARINGTYPES", $sharing_types);
$smarty->assign("SHARINGTYPE", $sharingtype);

$cmod = return_specified_module_language($current_language, "Settings");
$smarty->assign("CMOD", $cmod);
//Constructing the Role Array
$roleDetails=getAllRoleDetails();
$i=0;
$roleIdStr="";
$roleNameStr="";
$userIdStr="";
$userNameStr="";
$grpIdStr="";
$grpNameStr="";

foreach($roleDetails as $roleId=>$roleInfo) {
	if($i !=0) {
		if($i !=1) {
			$roleIdStr .= ", ";
			$roleNameStr .= ", ";
		}
		$roleName=$roleInfo[0];
		$roleIdStr .= "'".$roleId."'";
		$roleNameStr .= "'".addslashes(decode_html($roleName))."'";
	}
	$i++;
}
//Constructing the User Array
$l=0;
$userDetails=getAllUserName();
foreach($userDetails as $userId=>$userInfo) {
	if($l !=0){
		$userIdStr .= ", ";
		$userNameStr .= ", ";
	}
	$userIdStr .= "'".$userId."'";
	$userNameStr .= "'".$userInfo."'";
	$l++;
}
//Constructing the Group Array
$parentGroupArray = array();

$m=0;
$grpDetails=getAllGroupName();
foreach($grpDetails as $grpId=>$grpName) {
	if(! in_array($grpId,$parentGroupArray)) {
		if($m !=0) {
			$grpIdStr .= ", ";
			$grpNameStr .= ", ";
		}
		$grpIdStr .= "'".$grpId."'";
		$grpNameStr .= "'".addslashes(decode_html($grpName))."'";
        $m++;
	}
}
$smarty->assign("ROLEIDSTR",$roleIdStr);
$smarty->assign("ROLENAMESTR",$roleNameStr);
$smarty->assign("USERIDSTR",$userIdStr);
$smarty->assign("USERNAMESTR",$userNameStr);
$smarty->assign("GROUPIDSTR",$grpIdStr);
$smarty->assign("GROUPNAMESTR",$grpNameStr);

if(count($sharingMemberArray) > 0)
{
    $outputMemberArr = array();
    foreach($sharingMemberArray as $setype=>$shareIdArr)
    {
        foreach($shareIdArr as $shareId)
        {
            switch($setype)
            {
                case "groups":
                    $memberName=fetchGroupName($shareId);
				    $memberDisplay="Group::";
				    break;

                case "roles":
                    $memberName=getRoleName($shareId);
				    $memberDisplay="Roles::";
				    break;

				case "rs":
                    $memberName=getRoleName($shareId);
				    $memberDisplay="RoleAndSubordinates::";
				    break;

				case "users":
                    $memberName=getUserName($shareId);
				    $memberDisplay="User::";
				    break;
            }

            $outputMemberArr[] = $setype."::".$shareId;
            $outputMemberArr[] = $memberDisplay.$memberName;
        }
    }
    $smarty->assign("MEMBER", array_chunk($outputMemberArr,2));
}
//Sharing End

$smarty->assign("VIEW_TYPE", "DripEmails"); 
$smarty->assign("VIEW_CONTENT", "EditDripEmails");

$smarty = $EMAILMaker->actualizeSmarty($smarty);

$smarty = $EMAILMaker->actualizeSmarty($smarty);
$smarty->display('modules/EMAILMaker/EMAILMaker.tpl');

?>