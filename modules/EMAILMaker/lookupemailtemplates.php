<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

require_once('include/utils/utils.php');

global $theme,$current_user;
$theme_path="themes/".$theme."/";

$language = $_SESSION['authenticated_user_language'];
$mod_strings = return_specified_module_language($language,  "Emails");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en">
<head>
  <title><?php echo $mod_strings['LBL_EMAIL_TEMPLATES_LIST']; ?></title>
  <link type="text/css" rel="stylesheet" href="<?php echo $theme_path ?>/style.css"/>
</head>
<body>
            <form action="index.php" onsubmit="VtigerJS_DialogBox.block();">
	     <div class="lvtHeaderText"><?php echo $mod_strings['LBL_EMAIL_TEMPLATES']; ?></div>
		<hr noshade="noshade" size="1">
		
             <input type="hidden" name="module" value="Users">
		<table style="background-color: rgb(204, 204, 204);" class="small" border="0" cellpadding="5" cellspacing="1" width="100%">
		<tr>
		<th width="35%" class="lvtCol"><b><?php echo $mod_strings['LBL_TEMPLATE_NAME']; ?></b></th>
        <th width="10%" class="lvtCol"><b><?php echo $app_strings['LBL_MODULE']; ?></b></th>
        <th width="55%" class="lvtCol"><b><?php echo $mod_strings['LBL_DESCRIPTION']; ?></b></th>
        </tr>
<?php

$pid = $_REQUEST["pid"];

if (isset($_REQUEST["formodule"]) && $_REQUEST["formodule"] != "")
{
   //$formodule = getSalesEntityType($_REQUEST["pid"]);
   $formodule = $_REQUEST["formodule"];
   $where = "OR module = '".$formodule."'";
}
else
{
   $formodule = "";
   $where = '';
}

$sqlA = "select 'emailmaker' AS email_type, templateid, templatename, description, foldername, module from vtiger_emakertemplates WHERE module IS NULL OR module = '' $where ";
$sqlB = "select 'standard' AS email_type, templateid, templatename, description, foldername, '' AS module from vtiger_emailtemplates";

$sql = $sqlA." UNION ".$sqlB." ORDER BY module DESC, templateid desc";

$result = $adb->pquery($sql, array());
$temprow = $adb->fetch_array($result);
   
$cnt=1;

require_once('include/utils/UserInfoUtil.php');
require('user_privileges/user_privileges_'.$current_user->id.'.php');
do
{
	$templatename = $temprow["templatename"];
	if($is_admin == false)
	{
		$folderName = $temprow['foldername'];
		if($folderName != 'Personal')
		{
			printf("<tr class='lvtColData' onmouseover=\"this.className='lvtColDataHover'\" onmouseout=\"this.className='lvtColData'\" bgcolor='white'> <td height='25' nowrap>");
			echo "<a href=\"javascript:submittemplate(".$temprow['templateid'].",'".$temprow['email_type']."');\">".$temprow["templatename"]."</a></td>";
			printf("<td height='25'>%s</td>",$temprow["module"]);
            printf("<td height='25'>%s</td>",$temprow["description"]);
		}
	}
	else
	{
		printf("<tr class='lvtColData' onmouseover=\"this.className='lvtColDataHover'\" onmouseout=\"this.className='lvtColData'\" bgcolor='white'> <td height='25' nowrap>");
		echo "<a href=\"javascript:submittemplate(".$temprow['templateid'].",'".$temprow['email_type']."');\">".$temprow["templatename"]."</a></td>";
		printf("<td height='25'>%s</td>",$temprow["module"]);
        printf("<td height='25'>%s</td>",$temprow["description"]);
	}
    
    
    $cnt++;

}while($temprow = $adb->fetch_array($result));
?>
</table>
</body>
<script>
function submittemplate(templateid, email_type)
{
	window.document.location.href = 'index.php?module=EMAILMaker&action=EMAILMakerAjax&file=TemplateMerge&pid=<?PHP echo $pid; ?>&pmodule=<?PHP echo $formodule; ?>&templateid='+templateid+'&email_type='+email_type;
}
</script>
</html>
