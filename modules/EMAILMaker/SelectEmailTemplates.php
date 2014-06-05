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
require_once('include/utils/UserInfoUtil.php');

global $adb;

$smarty = new vtigerCRM_Smarty;
global $current_language;

$category = getParentTab();
$smarty->assign("CATEGORY",$category);

$source_path=getcwd()."/modules/EMAILMaker/templates";
$dir_iterator = new RecursiveDirectoryIterator($source_path);
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

$i = 0;
$p_errors = 0;

foreach ($iterator as $folder) 
{
    $folder_name=substr($folder, strlen($source_path)+1);
    
    if($folder->isDir())
    { 
       $other_folder = strpos($folder_name, "/");
       
       if ($other_folder === false && file_exists($folder."/index.html") && file_exists($folder."/image.png"))
       {
           $EmailTemplates[] = $folder_name;
       }
    }
   
    $i++;         
}

asort($EmailTemplates);

$smarty->assign("EMAILTEMPLATESPATH", $source_path);
$smarty->assign("EMAILTEMPLATES", $EmailTemplates);

global $app_strings;
global $mod_strings;
global $theme,$default_charset;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("PARENTTAB", getParentTab());
$smarty->assign("IMAGE_PATH",$image_path);

include_once("version.php");
$smarty->assign("VERSION",$version);

$smarty->assign("VIEW_TYPE", "EmailTemplates"); 
$smarty->assign("VIEW_CONTENT", "SelectEmailTemplates");

$smarty->display("modules/EMAILMaker/EMAILMaker.tpl");


?>
