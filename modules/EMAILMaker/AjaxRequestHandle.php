<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
global $adb,$current_language;
$module = addslashes($_REQUEST["langmod"]);

$mod_lang=return_specified_module_language($current_language,$module);

$module_lang_labels = array_flip($mod_lang);
$module_lang_labels = array_flip($module_lang_labels);
asort($module_lang_labels);             
    
$keys=implode('||',array_keys($module_lang_labels));
$values=implode('||',$module_lang_labels);
echo $keys.'|@|'.$values;
exit;
?>