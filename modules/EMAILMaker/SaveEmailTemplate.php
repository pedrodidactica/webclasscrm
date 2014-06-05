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

$templateid = vtlib_purify($_REQUEST["templateid"]);

if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "add_document")
{
    if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) 
    {
        $documentid = vtlib_purify($_REQUEST["documentid"]);
        
        $sql = "INSERT INTO vtiger_emakertemplates_documents (templateid, documentid) VALUES (?,?)";
        $adb->pquery($sql, array($templateid,$documentid));
    }

    header("Location:index.php?action=DetailViewEmailTemplate&module=EMAILMaker&templateid=".$templateid."&parenttab=Tools");
}
elseif (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "delete_document")
{
    if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) 
    {
        $documentid = vtlib_purify($_REQUEST["documentid"]);
        
        $sql = "DELETE FROM vtiger_emakertemplates_documents WHERE templateid = ? AND documentid = ?";
        $adb->pquery($sql, array($templateid,$documentid));
    }

    header("Location:index.php?action=DetailViewEmailTemplate&module=EMAILMaker&templateid=".$templateid."&parenttab=Tools");
}
else
{
    
    if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) 
    {
        $filename = vtlib_purify($_REQUEST["filename"]);
        $modulename = from_html($_REQUEST["modulename"]);
        $description = from_html($_REQUEST["description"]);
        $subject = from_html($_REQUEST["subject"]);
        $body = fck_from_html($_REQUEST["body"]);
        $owner = from_html($_REQUEST["template_owner"]);
        $sharingtype = from_html($_REQUEST["sharing"]);
        $email_category = from_html($_REQUEST["email_category"]);
        
        $is_active = from_html($_REQUEST["is_active"]);
        $is_default_dv = (isset($_REQUEST["is_default_dv"]) ? "1" : "0");
        $is_default_lv = (isset($_REQUEST["is_default_lv"]) ? "1" : "0");
        $order = '1';
        
        if(isset($templateid) && $templateid !='')
        {
        	$sql = "update vtiger_emakertemplates set templatename =?, module =?, description =?, subject =?, body =?, owner=?, sharingtype = ?, category = ? where templateid =?";
        	$params = array($filename, $modulename, $description, $subject, $body, $owner, $sharingtype, $email_category, $templateid);
        	$adb->pquery($sql, $params);
        
            //SHARING
            $sql_s = "DELETE FROM vtiger_emakertemplates_sharing WHERE templateid=?";
            $adb->pquery($sql_s, array($templateid));
        
            //DEFAULT FROM SETTING
            $sql_df = "DELETE FROM vtiger_emakertemplates_default_from WHERE templateid=? AND userid=?";
            $adb->pquery($sql_df, array($templateid,$current_user->id));
            
            $sql21="DELETE FROM vtiger_emakertemplates_userstatus WHERE templateid=? AND userid=?";
	        $adb->pquery($sql21,array($templateid,$current_user->id));
        }
        else
        {
        	$templateid = $adb->getUniqueID('vtiger_emakertemplates');
        	$sql2 = "insert into vtiger_emakertemplates (templatename,module,description,subject,body,deleted,templateid,owner,sharingtype,category) values (?,?,?,?,?,?,?,?,?,?)";
        	$params2 = array($filename, $modulename, $description, $subject, $body, 0, $templateid,$owner, $sharingtype, $email_category);
        	$adb->pquery($sql2, $params2);
        }
        
        $dec_point = $_REQUEST["dec_point"];
        $dec_decimals = $_REQUEST["dec_decimals"];
        $dec_thousands = ($_REQUEST["dec_thousands"]!=" " ? $_REQUEST["dec_thousands"]:"sp");
        
        //$sql3 = "DELETE FROM vtiger_emakertemplates_settings";
        //$adb->query($sql3);
        
        //$sql4 = "INSERT INTO vtiger_emakertemplates_settings (decimals, decimal_point, thousands_separator) VALUES (?,?,?)";
        $sql4 = "UPDATE vtiger_emakertemplates_settings SET decimals = ?, decimal_point = ?, thousands_separator = ?";
        $params4 = array($dec_decimals, $dec_point, $dec_thousands);
        $adb->pquery($sql4, $params4);
        
        //ignored picklist values
        $adb->query("DELETE FROM vtiger_emakertemplates_ignorepicklistvalues");
        $pvvalues=explode(",", $_REQUEST["ignore_picklist_values"]);
        foreach($pvvalues as $value)
          $adb->query("INSERT INTO vtiger_emakertemplates_ignorepicklistvalues(value) VALUES('".trim($value)."')");
        // end ignored picklist values
        
        
        //unset the former default template because only one template can be default per user x module
        $is_default_bin = $is_default_lv.$is_default_dv;
        $is_default_dec = intval(base_convert($is_default_bin, 2, 10)); // convert binary format xy to decimal; where x stands for is_default_lv and y stands for is_default_dv
        if($is_default_dec > 0){
            $sql5 ="UPDATE vtiger_emakertemplates_userstatus
                    INNER JOIN vtiger_emakertemplates USING(templateid)
                    SET is_default=?
                    WHERE is_default=? AND userid=? AND module=?";
        
            switch($is_default_dec)
            {
        //      in case of only is_default_dv is checked
                case 1:
                    $adb->pquery($sql5, array("0", "1", $current_user->id, $modulename));
                    $adb->pquery($sql5, array("2", "3", $current_user->id, $modulename));
                    break;
        //      in case of only is_default_lv is checked
                case 2:
                    $adb->pquery($sql5, array("0", "2", $current_user->id, $modulename));
                    $adb->pquery($sql5, array("1", "3", $current_user->id, $modulename));
                    break;
        //      in case of both is_default_* are checked
                case 3:
                    $sql5 ="UPDATE vtiger_emakertemplates_userstatus
                            INNER JOIN vtiger_emakertemplates USING(templateid)
                            SET is_default=?
                            WHERE is_default > ? AND userid=? AND module=?";
                    $adb->pquery($sql5, array("0", "0", $current_user->id, $modulename));
            }
        }  
        
        $sql6 = "INSERT INTO vtiger_emakertemplates_userstatus (templateid, userid, is_active, is_default, sequence) VALUES(?,?,?,?,?)";
        $adb->pquery($sql6, array($templateid, $current_user->id, $is_active, $is_default_dec, $order));

        
        
        //SHARING
      
        if($sharingtype == "share" && isset($_REQUEST["sharingSelectedColumnsString"]))
        {
            $selected_col_string = 	$_REQUEST['sharingSelectedColumnsString'];
        	$member_array = explode(';',$selected_col_string);
            $groupMemberArray = constructSharingMemberArray($member_array);
            
            $sql8a ="INSERT INTO vtiger_emakertemplates_sharing(templateid, shareid, setype) VALUES ";
            $sql8b = "";
            $params8 = array();
            foreach($groupMemberArray as $setype=>$shareIdArr)
            {
                foreach($shareIdArr as $shareId)
                {
                    $sql8b .= "(?, ?, ?),";
                    $params8[] = $templateid;
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
        
        //DEFAULT FROM SETTING
        $default_from_email = vtlib_purify($_REQUEST["default_from_email"]); 
        if ($default_from_email != "")
        {
            $sql_df = "INSERT INTO vtiger_emakertemplates_default_from (templateid,userid,fieldname) VALUES (?,?,?)";
            $adb->pquery($sql_df, array($templateid,$current_user->id,$default_from_email));
        }
         
        header("Location:index.php?module=EMAILMaker&action=DetailViewEmailTemplate&parenttab=Tools&templateid=".$templateid);
    }
    else
    {
        header("Location:index.php?module=EMAILMaker&action=ListEmailTemplates&parenttab=Tools");
    }
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
