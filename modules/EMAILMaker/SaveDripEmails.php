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
    $dripname = vtlib_purify($_REQUEST["dripname"]);
    $modulename = from_html($_REQUEST["modulename"]);
    $dripid = vtlib_purify($_REQUEST["dripid"]);
    $description = from_html($_REQUEST["description"]);
    $owner = from_html($_REQUEST["drip_owner"]);
    $sharingtype = from_html($_REQUEST["sharing"]);
    
    if(isset($dripid) && $dripid !='')
    {
    	$sql = "UPDATE vtiger_emakertemplates_drips SET dripname =?, description =?, owner=?, sharingtype = ? WHERE dripid =?";
    	$params = array($dripname, $description, $owner, $sharingtype, $dripid);
    	$adb->pquery($sql, $params);
    
        //SHARING
        $sql_s = "DELETE FROM vtiger_emakertemplates_sharing_drip WHERE templateid=?";
        $adb->pquery($sql_s, array($dripid));
    }
    else
    {
    	$dripid = $adb->getUniqueID('vtiger_emakertemplates_drips');
    	$sql2 = "insert into vtiger_emakertemplates_drips (dripid,dripname,module,description,deleted,owner,sharingtype) values (?,?,?,?,?,?,?)";
    	$params2 = array($dripid, $dripname, $modulename, $description, 0, $owner, $sharingtype);
    	$adb->pquery($sql2, $params2);
    }
    
    //SHARING
  
    if($sharingtype == "share" && isset($_REQUEST["sharingSelectedColumnsString"]))
    {
        $selected_col_string = 	$_REQUEST['sharingSelectedColumnsString'];
    	$member_array = explode(';',$selected_col_string);
        $groupMemberArray = constructSharingMemberArray($member_array);
        
        $sql8a ="INSERT INTO vtiger_emakertemplates_sharing_drip(dripid, shareid, setype) VALUES ";
        $sql8b = "";
        $params8 = array();
        foreach($groupMemberArray as $setype=>$shareIdArr)
        {
            foreach($shareIdArr as $shareId)
            {
                $sql8b .= "(?, ?, ?),";
                $params8[] = $dripid;
                $params8[] = $shareId;
                $params8[] = $setype;
            }
        }
        
        if($sql8b != "")
        {
            $sql8b = rtrim($sql8b, ",");
            $sql8 = $sql8a.$sql8b;
            $adb->pquery($sql8, $params8);
        }
    }
     
    header("Location:index.php?module=EMAILMaker&action=DetailViewDripEmails&parenttab=Tools&dripid=".$dripid);
}
else
{
    header("Location:index.php?module=EMAILMaker&action=ListDripEmails&parenttab=Tools");
}
exit;

function constructSharingMemberArray($member_array)
{
    global $adb;

	$groupMemberArray=Array();
	$roleArray=Array();
	$roleSubordinateArray=Array();
	$groupArray=Array();
	$userArray=Array();

	foreach($member_array as $member)
	{
		$memSubArray=explode('::',$member);
        switch($memSubArray[0])
        {
            case "groups":
                $groupArray[] = $memSubArray[1];
                break;

            case "roles":
                $roleArray[] = $memSubArray[1];
                break;
                
            case "rs":
                $roleSubordinateArray[] = $memSubArray[1];
                break;
                
            case "users":
                $userArray[] = $memSubArray[1];
                break;
        }
	}

	$groupMemberArray['groups']=$groupArray;
	$groupMemberArray['roles']=$roleArray;
	$groupMemberArray['rs']=$roleSubordinateArray;
	$groupMemberArray['users']=$userArray;

	return $groupMemberArray;
}
?>
