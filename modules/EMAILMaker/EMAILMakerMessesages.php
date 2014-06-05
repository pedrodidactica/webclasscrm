<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
global $adb,$app_strings,$current_user,$mod_strings;

$esentid = $_REQUEST["esentid"];

$sql = "SELECT * FROM vtiger_emakertemplates_msgs WHERE msgtype = '1' AND userid = '".$current_user->id."'";
if ($esentid != "") $sql .= " AND esentid = '".$esentid."'";
$sql .= " LIMIT 0,1";

$result = $adb->query($sql); 
$num_rows = $adb->num_rows($result);

if ($num_rows > 0)
{
    $msgid = $adb->query_result($result,0,"msgid");
    $email = $adb->query_result($result,0,"email");
    $status = $adb->query_result($result,0,"status");
    $content = $adb->query_result($result,0,"content");
    
    if ($status == "1")
    {
        $status_title = $mod_strings["LBL_EMAIL_HAS_BEEN_SENT"];
        $status_content = $mod_strings["LBL_HAS_BEEN_SENT"]; 
    }
    elseif ($status == "0")
    {
        $status_title = "Odosielanie emailov";
    }
    else
    {
         $status_title = $mod_strings["LBL_EMAIL_COULD_NOT_BE_SENT"];
         $status_content = $mod_strings["LBL_COULD_NOT_BE_SENT"];
    }
     
    $messesage = "<table  border='0' cellpadding='5' cellspacing='0'>
						<tr>
							<td style='padding:10px;' colspan='2'><b>".$status_title." (".$esentid."b)</b></td>
						</tr>
					</table>
					<table  border='0' cellpadding='0' cellspacing='0' class='hdrNameBg'>
						<tr><td style='padding:10px;' colspan='2'>";
              
              //if ($content != "")
                  $messesage .= $content;
              //else          
              //    $messesage .= "<b>".$mod_strings["LBL_MAIL_ON_ADDRESS"]." <br /><b>".$email."</b>,<br /> ".$status_content."</b>";
                  
	   	  $messesage .= "</td></tr>
				  </table>";
    echo $messesage;   
    
    if ($esentid == "")
    { 
        $sql2 = "DELETE FROM vtiger_emakertemplates_msgs WHERE msgid = '".$msgid."'";
        $adb->query($sql2);  
    }        
}
?>
