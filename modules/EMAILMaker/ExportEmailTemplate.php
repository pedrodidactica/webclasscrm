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

global $adb;
global $mod_strings;
global $app_strings;
global $current_language;

$Templates = explode(";",$_REQUEST['templates']);

sort($Templates);
$c = '';

if(count($Templates) > 0)
{
  	foreach ($Templates AS $templateid)
  	{
      	$sql = "SELECT * FROM vtiger_emakertemplates WHERE templateid=?";
            
      	$result = $adb->pquery($sql, array($templateid));
      	$num_rows = $adb->num_rows($result); 
      	
      	if ($num_rows > 0)
      	{
          	$templateResult = $adb->fetch_array($result);
                      
            $templatename = $templateResult["templatename"];
            $subject = $templateResult["subject"]; 
            $description = $templateResult["description"];
            $module = $templateResult["module"];
            
            $body = decode_html($templateResult["body"]);
            
            $c .= "<template>";
               $c .= "<type>EMAILMaker</type>";
               $c .= "<templatename>".cdataEncode($templatename)."</templatename>";
               $c .= "<subject>".cdataEncode($subject)."</subject>";
               $c .= "<description>".cdataEncode($description)."</description>";
               $c .= "<module>".cdataEncode($module)."</module>";
             
               $c .= "<body>";
                  $c .= cdataEncode($body,true);
               $c .= "</body>";
               
            $c .= "</template>";
   
        }
    }

}

header('Content-Type: application/xhtml+xml');
header("Content-Disposition: attachment; filename=export.xml");

echo "<?xml version='1.0'?".">";
echo "<export>";
echo $c;
echo "</export>";

exit;

function cdataEncode($text, $encode = false)
{
    $From = array("<![CDATA[", "]]>");
    $To   = array("<|!|[%|CDATA|[%|", "|%]|]|>");
    
    if ($text != "")
    {
        $pos1 = strpos("<![CDATA[", $text);
        $pos2 = strpos("]]>", $text);
        
        if ($pos1 === false && $pos2 === false && $encode == false)
        {
            $content = $text;
        }
        else
        {
            $encode_text = str_replace($From, $To, $text);
        
            $content = "<![CDATA[".$encode_text."]]>";
        }
    }
    else
    {
        $content = "";
    }
    
    return $content;
}

?>
