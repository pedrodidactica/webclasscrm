<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

require_once('Smarty_setup.php');
require_once('include/utils/CommonUtils.php');
require_once("modules/EMAILMaker/EMAILMaker.php");

global $mod_strings;
global $app_strings;
global $app_list_strings;
global $current_user,$default_charset;

global $import_mod_strings;

$focus = 0;

global $theme;

global $import_dir;

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

if (!is_uploaded_file($_FILES['userfile']['tmp_name']) )
{
	show_error_import($mod_strings['LBL_IMPORT_MODULE_ERROR_NO_UPLOAD']);
	exit;
}
else if ($_FILES['userfile']['size'] > $upload_maxsize)
{
	show_error_import( $mod_strings['LBL_IMPORT_MODULE_ERROR_LARGE_FILE'] . " ". $upload_maxsize. " ". $mod_strings['LBL_IMPORT_MODULE_ERROR_LARGE_FILE_END']);
	exit;
}
if( !is_writable( $import_dir ))
{
	show_error_import($mod_strings['LBL_IMPORT_MODULE_NO_DIRECTORY'].$import_dir.$mod_strings['LBL_IMPORT_MODULE_NO_DIRECTORY_END']);
	exit;
}

$tmp_file_name = $import_dir. "IMPORT_".$current_user->id;

move_uploaded_file($_FILES['userfile']['tmp_name'], $tmp_file_name);

$fh = fopen($tmp_file_name,"r");
$xml_content = fread($fh, filesize($tmp_file_name));
fclose($fh);	

$xml = new SimpleXMLElement($xml_content);

foreach ($xml->template AS $data)
{
    //print_r($data);
    if ($data->type = "EMAILMaker")
    {
        $templatename = cdataDecode($data->templatename);
        $subject = cdataDecode($data->subject);
        $description = cdataDecode($data->description);
        $modulename = cdataDecode($data->module);
    
        $tabid = getTabId($modulename); 
        $body = cdataDecode($data->body);
        
        $templateid = $adb->getUniqueID('vtiger_pdfmaker');
      	$sql1 = "insert into vtiger_emakertemplates (templatename,subject,module,description,body,deleted,templateid) values (?,?,?,?,?,?,?)";
     	$params1 = array($templatename, $subject, $modulename, $description, $body, 0, $templateid);
    	$adb->pquery($sql1, $params1);
    }
}	

header("Location:index.php?module=EMAILMaker&action=ListEmailTemplates&parenttab=Tools");
exit;
	
function cdataDecode($text)
{
    $From = array("<|!|[%|CDATA|[%|", "|%]|]|>");
    $To = array("<![CDATA[", "]]>");
    
    $decode_text = str_replace($From, $To, $text);

    return $decode_text;
}	
	
?>
