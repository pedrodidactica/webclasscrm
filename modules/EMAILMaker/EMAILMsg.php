<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once("include/Zend/Json.php");
require_once("include/utils/GetGroupUsers.php");
require_once("include/utils/UserInfoUtil.php");
require_once("modules/EMAILMaker/ConvertEMAIL.php");
require_once("modules/EMAILMaker/SavePDFIntoEmail.php");
require_once("modules/EMAILMaker/EMAILMaker.php");

global $current_user,$theme,$adb,$mod_strings;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'control')
{
    $sql = "SELECT DISTINCT tb1.esentid FROM vtiger_emakertemplates_sent AS tb1 INNER JOIN vtiger_emakertemplates_emails AS tb2 ON tb1.esentid = tb2.esentid WHERE tb2.status = '0' AND tb1.userid = ? AND tb1.drip_group = '0' AND tb1.drip_delay <= 0";
    $result = $adb->pquery($sql,array($current_user->id));

    $records=$adb->num_rows($result);
    
	if($records>0)
		echo $adb->query_result($result,0,"esentid");
	else
		echo 'NO';	
	
    exit;
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'show')
{
    $esentid = $_REQUEST["esentid"];
    
    $sql = "SELECT total_emails FROM vtiger_emakertemplates_sent WHERE esentid = '".$esentid."'";
    $result = $adb->query($sql);
    $total_emails = $adb->query_result($result,0,"total_emails");
    
    $sql2 = "SELECT count(emailid) as total FROM vtiger_emakertemplates_emails WHERE status = '1' AND esentid = '".$esentid."'";
    $result2 = $adb->query($sql2);
    $sent_emails = $adb->query_result($result2,0,"total");  
	 
    if ($sent_emails == $total_emails)
    {
        $status = "END";
        if ($total_emails > 1)
            $status_title = $mod_strings["LBL_EMAILS_HAS_BEEN_SENT"];
        else
            $status_title = $mod_strings["LBL_EMAIL_HAS_BEEN_SENT"];
    }
    else
    {
        $status_title = $mod_strings["LBL_EMAILS_DISTRIBUTION"];
        $status = "IN_PROCESS";
    }    
    
    $content = $sent_emails." ".$mod_strings["LBL_EMAILS_SENT_FROM"]." ".$total_emails;
	
    $messesage = $status."@<table  border='0' cellpadding='5' cellspacing='0'>
        						<tr>
        							<td style='padding:10px;' colspan='2'><b>".$status_title." </b></td>
        						</tr>
        					</table>
        					<table  border='0' cellpadding='0' cellspacing='0' class='hdrNameBg' style='width:100%'>
        						<tr><td style='padding:10px;' colspan='2'>";
                          $messesage .= $content;
                  if ($sent_emails != $total_emails) $messesage .= "<div style='text-align:center;padding:5px;'><a href='javascript:openPopUpForSent(".$esentid.");'>".$mod_strings["LBL_OPEN_EMAIL_POPUP"]."</a></div>";    
        	   	  $messesage .= "</td></tr>
        				  </table>";

    echo $messesage;
    exit;
}
?>