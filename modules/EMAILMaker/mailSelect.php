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
require_once('modules/EMAILMaker/EMAILMaker.php');
require_once('modules/EMAILMaker/ConvertEMAIL.php');

global $app_strings,$current_user,$theme,$adb;
$image_path = 'themes/'.$theme.'/images/';

$EMAILMaker = new EmailMaker();  
$EMAILContent = new EMAILContent();

$email_permitted = isPermitted("Emails","index",'');

$email_active = vtlib_isModuleActive("Emails");

if($EMAILMaker->CheckPermissions("DETAIL") == false || $EMAILContent->control() == false || $email_permitted == "no" || $email_active != "1" )
{
  
  if (isset($_REQUEST["listview"]) && $_REQUEST["listview"] == "true")
  {
      $block_name = "EMAILListViewDiv";
      $div_handle = "EMAILListViewDivHandle";
  }
  else
  {
      $block_name = "sendemakermail_cont";
      $div_handle = "sendemakermail_cont_handle";
  }
  /*
  if ($email_permitted)
    $error_text
  else  
  */
  $output = '
  <table border=0 cellspacing=0 cellpadding=5 width=100% class=layerHeadingULine>
  <tr>
  	<td width="90%" align="left" class="genHeaderSmall" id="'.$div_handle.'" style="cursor:move;"></td>
  	<td width="10%" align="right">
  		<a href="javascript:fninvsh(\''.$block_name.'\');"><img title="'.$app_strings["LBL_CLOSE"].'" alt="'.$app_strings["LBL_CLOSE"].'" src="themes/images/close.gif" border="0"  align="absmiddle" /></a>
  	</td>
  </tr>
  </table>
  <table border=0 cellspacing=0 cellpadding=5 width=100% align=center>
      <tr><td class="small">
          <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
          <tr>
            <td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;text-align:center;">
              <strong>';
  
  if ($email_active == "1")            
      $output .= $app_strings["LBL_PERMISSION"];
  else
       $output .="<table border='0' cellpadding='5' cellspacing='0' width='98%'>
		<tbody><tr>
		<td rowspan='2' width='11%'><img src='". vtiger_imageurl('denied.gif', $theme) . "' ></td>
		<td nowrap='nowrap' width='70%'><span class='genHeaderSmall'>Emails ".$app_strings[VTLIB_MOD_NOT_ACTIVE]."</span></td>
		</tr></tbody></table>";
        
              
  $output .= '</strong>
            </td>
          </tr>
          <tr>
        		<td class="dvtCellInfo" style="width:100%;" align="center">
              <input type="button" class="crmbutton small cancel" value="'.$app_strings["LBL_CANCEL_BUTTON_LABEL"].'" onclick="fninvsh(\''.$block_name.'\');" />      
            </td>
      		</tr>      		
          </table>
      </td></tr>
  </table>
  ';
  die($output);
}

$smarty = new vtigerCRM_Smarty;

$pmodule=vtlib_purify($_REQUEST['return_module']);
$type =vtlib_purify($_REQUEST['type']);

if (trim($_REQUEST['idlist'])== "all")
{
    $single_record = false;
    $idlist = "all";
    
    $smarty->assign("EXE_REC", vtlib_purify($_REQUEST['excludedRecords']));
    $smarty->assign("VIEWID", vtlib_purify($_REQUEST['viewname']));
}
else
{
    $idlist = vtlib_purify(trim($_REQUEST['idlist'],";"));
    $ids=explode(';',$idlist); 
    $single_record = false;
    
    if(!strpos($idlist,';') && $type != "3")
    {
    	$single_record = true;
    }
}


$language = $_SESSION['authenticated_user_language'];
$mod_strings = return_module_language($language, "Emails");
$e_mod_strings = return_module_language($language, "EMAILMaker");

$userid =  $current_user->id;

$tabid = getTabId($pmodule);
unset(VTCacheUtils::$_fieldinfo_cache[$tabid]);                   

$ALL_Emails = array();  

if ($single_record)
{
    $main_crmid = $idlist;
    $focus2 = CRMEntity::getInstance($pmodule);
    $focus2->retrieve_entity_info($idlist,$pmodule);
    $focus2->id = $idlist;
}
else
    $main_crmid = ""; 

  
$Module_Emails = getEmailModuleName($pmodule, $main_crmid, $single_record);


if ($Module_Emails)
{
   $ALL_Emails[] = array("type" => "", "crmid" => $main_crmid, "fieldid" => "0", "data" => $Module_Emails);
}
    
if ($type != "1")
{   
    $querystr = "select uitype, fieldid, fieldname, fieldlabel, columnname from vtiger_field where tabid=? and uitype IN (50,51,57,73,75,81,68,10)";
    $res=$adb->pquery($querystr, array(getTabid($pmodule)));
    $numrows = $adb->num_rows($res);
    $returnvalue = Array();
    for($i = 0; $i < $numrows; $i++)
    {
    	$related_module = "";
        $uitype = $adb->query_result($res,$i,'uitype');
        $fieldname = $adb->query_result($res,$i,'fieldname');
        $fieldid = $adb->query_result($res,$i,'fieldid');
        $fieldlabel = $adb->query_result($res,$i,'fieldlabel');

        $name = getTranslatedString($fieldlabel);
        
        if ($single_record)
            $related_id = $focus2->column_fields[$fieldname];
        else
            $related_id = "";

        if ($uitype == "10" || $uitype == "68")
        {
            if ($single_record)
            {
                $related_module = getSalesEntityType($related_id); 
            
                $ALL_Emails = addAllModuleEmails($ALL_Emails,$related_module, $related_id, $single_record, $name, $fieldid);
            }
            else
            {
                if ($uitype == "68")
                {
                    $a_module_lang = getTranslatedString("Accounts","Accounts");
                    $c_module_lang = getTranslatedString("Contacts","Contacts");
                    $ALL_Emails = addAllModuleEmails($ALL_Emails,"Accounts", $related_id, $single_record, $name." (".$a_module_lang.")", $fieldid);
                    $ALL_Emails = addAllModuleEmails($ALL_Emails,"Contacts", $related_id, $single_record, $name." (".$c_module_lang.")", $fieldid);    
                }
                else
                {
                    $querystr2 = "select relmodule from vtiger_fieldmodulerel where fieldid=? and relmodule IN (?,?,?,?)";
                    $res2 = $adb->pquery($querystr2, array($fieldid,"Accounts","Contacts","Vendors","Leads"));
                    $num_rows2 = $adb->num_rows($res2);
                    
                    if ($num_rows2 > 0)
                    {
                        while($row2 = $adb->fetchByAssoc($res2))
                        {
                            $module_lang = getTranslatedString($row2["relmodule"],$row2["relmodule"]);
                                                        
                            $ALL_Emails = addAllModuleEmails($ALL_Emails,$row2["relmodule"], $related_id, $single_record, $name." (".$module_lang.")", $fieldid);
                        } 
                    }
                }
            }
        }
        else
        {
            switch ($uitype) 
            {
                case "50":
                case "51":
                case "73": $related_module = "Accounts"; break;
                case "57": $related_module = "Contacts"; break;
                case "75": $related_module = "Vendors"; break;
                case "81": $related_module = "Vendors"; break;
            }
            $ALL_Emails = addAllModuleEmails($ALL_Emails,$related_module, $related_id, $single_record, $name, $fieldid);
        }
    }
}    

if (count($ALL_Emails) > 0)
   $have_emails = "true";
else
   $have_emails = "false";


$have_one_email = "false";
if (count($ALL_Emails) == 1)
{
    if (count($ALL_Emails[0]["data"]["emails"]) == 1) $have_one_email = "true";
}

$smarty->assign('HAVE_EMAILS',$have_emails);
$smarty->assign('HAVE_ONE_EMAIL',$have_one_email);
$smarty->assign('MAILDATA',$ALL_Emails);
$smarty->assign('ONE_RECORD',$single_record);
$smarty->assign("MOD", $mod_strings);
$smarty->assign("EMOD", $e_mod_strings);
$smarty->assign("IDLIST", $idlist);
$smarty->assign("APP", $app_strings);
$smarty->assign("FROM_MODULE", $pmodule);
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("TYPE",$type);

if(isset($_REQUEST["listview"]) && $_REQUEST["listview"] == "true")
    $for_listview = "true";
else
    $for_listview = "false";
 
if ($for_listview == "true" || $type == "3") 
{
    $default_sql = "SELECT templateid FROM vtiger_emakertemplates  
                    INNER JOIN vtiger_emakertemplates_userstatus USING ( templateid )
                    WHERE module = ? AND userid = ? AND is_default IN (2,3) AND is_active = 1";
    
    $default_res=$adb->pquery($default_sql,array($pmodule,$current_user->id));
    while($default_row = $adb->fetchByAssoc($default_res))
    {
        $default_template = $default_row["templateid"];
    }
    
    $smarty->assign('DEFAULT_TEMPLATE',$default_template);
    
    $Use_Template = $EMAILMaker->GetAvailableTemplates($pmodule,true);
    $smarty->assign('EMAIL_TEMPLATES',$Use_Template);
} 
    
$smarty->assign("FOR_LISTVIEW",$for_listview);

$pdfmaker = false;
$pdf_data = "";
if($type != "5" && ($for_listview == "true" || $type == "3"))
{
    require_once('user_privileges/user_privileges_'.$current_user->id.'.php');
    
    if(is_dir("modules/PDFMaker/mpdf"))
    {
    	require_once('modules/PDFMaker/PDFMaker.php');
        
        if($is_admin == true || $profileGlobalPermission[2]==0 || $profileGlobalPermission[1]==0 || $profileTabsPermission[getTabId("PDFMaker")]==0)
            $pdfmaker = true;
    }
    
    if ($pdfmaker)
    {
        $language = $_SESSION['authenticated_user_language'];
        $pdf_strings = return_module_language($language, "PDFMaker");
        $smarty->assign('PDFMAKER_MOD',$pdf_strings);
        
        $status_sql="SELECT templateid FROM vtiger_pdfmaker_userstatus  
                     INNER JOIN vtiger_pdfmaker USING(templateid) 
                     WHERE userid=? AND is_active=0"; 
        $status_res=$adb->pquery($status_sql,array($current_user->id));
        while($status_row = $adb->fetchByAssoc($status_res))
        {
          $inactive_arr[$status_row["templateid"]] = $status_row["templateid"];
        }
        
        $default_sql="SELECT templateid FROM vtiger_pdfmaker_userstatus  
                     INNER JOIN vtiger_pdfmaker USING(templateid) 
                     WHERE userid=? AND is_default=1"; 
        $default_res=$adb->pquery($default_sql,array($current_user->id));
        while($default_row = $adb->fetchByAssoc($default_res))
        {
          $default_template = $default_row["templateid"];
        }
        
        $temp_sql = "SELECT templateid, filename AS templatename
                     FROM vtiger_pdfmaker
                     WHERE module = '".$_REQUEST['return_module']."'";
        if(isset($inactive_arr)){
          $temp_sql.=" AND templateid NOT IN (".implode($inactive_arr,",").")";
        }      
        $temp_result = $adb->query($temp_sql);
        
        //TEMPLATES BLOCK
        $options="";
        while($temp_row = $adb->fetchByAssoc($temp_result)){
          if(isset($default_template) AND $default_template == $temp_row['templateid']){
            $selected=' selected="selected" ';
          } else {
            $selected="";
          }
          $options.='<option value="'.$temp_row['templateid'].'"'.$selected.'>'.$temp_row['templatename'].'</option>';
        }
        
        $template_output="";
        $language_output="";
        $generate_pdf="";
        if($adb->num_rows($temp_result)>0)
        {
            $language_output='';
            $template_output='<select name="use_email_pdf_template_'.$type.'" id="use_email_pdf_template_'.$type.'" class="detailedViewTextBox" multiple style="width:90%;" size="5">'.$options.'</select>';    
        
            $temp_res = $adb->query("SELECT label, prefix FROM vtiger_language WHERE active=1");
            while($temp_row = $adb->fetchByAssoc($temp_res)) {
              $template_languages[$temp_row["prefix"]]=$temp_row["label"];
            }
        
            //LANGUAGES BLOCK  
            if(count($template_languages) > 1)
            {
                  $options="";
                  foreach($template_languages as $prefix=>$label)
                  {
                    if($current_language!=$prefix)
                      $options.='<option value="'.$prefix.'">'.$label.'</option>';
                    else
                      $options.='<option value="'.$prefix.'" selected="selected">'.$label.'</option>';
                  }
                  
                  $language_output='<select name="email_pdf_template_language_'.$type.'" id="email_pdf_template_language_'.$type.'" class="detailedViewTextBox" style="width:90%;" size="1">'.$options.'</select>';
            }
            else
            {   
                foreach($template_languages as $prefix=>$label)       
                  $template_output.='<input type="hidden" name="email_pdf_template_language" id="template_language" value="'.$prefix.'"/>';
            }
            
            $smarty->assign("PDF_LANGUAGE_OUTPUT",$language_output);
        }
        else 
        {
            $template_output= $pdf_strings["CRM_TEMPLATES_DONT_EXIST"];
          
            if(isPermitted("PDFMaker","EditView") == 'yes')
            {
                $template_output.='<br />'.$pdf_strings["CRM_TEMPLATES_ADMIN"].'
                                   <a href="index.php?module=PDFMaker&action=EditPDFTemplate&return_module='.$_REQUEST["return_module"].'&parenttab=Tools" class="webMnu">'.$pdf_strings["TEMPLATE_CREATE_HERE"].'</a>'; 
            }                		            
        }
        
        $smarty->assign("PDF_TEMPLATE_OUTPUT",$template_output);
    }
}
else
{
  if (isset($_REQUEST["pdftemplateid"]) && $_REQUEST["pdftemplateid"] != "")
  {
      $pdf_data = "<input name='use_email_pdf_template' id='use_email_pdf_template' type='hidden' value='".addslashes($_REQUEST["pdftemplateid"])."'>";
      $pdf_data .= "<input name='email_pdf_template_language' id='email_pdf_template_language' type='hidden' value='".addslashes($_REQUEST["language"])."'>";
      
  }
}
$smarty->assign("ENABLE_PDFMAKER",$pdfmaker);
$smarty->assign("PDF_DATA",$pdf_data);

if (count($ALL_Emails) > 0 || $for_listview == "true" || $type == "3")
    $smarty->display("modules/EMAILMaker/SelectEmail.tpl");
else
    echo "No Mail Ids";
    
function getEmailModuleName($pmodule, $idlist, $single_record)
{
    global $adb, $userid;
    
    $val_cnt = 0;
    $columnlists = array();
    $Column_Lists = array();
    
    $querystr = "select fieldid, fieldname, fieldlabel, columnname from vtiger_field where tabid=? and uitype IN (13) and vtiger_field.presence in (0,2)";
    $res=$adb->pquery($querystr, array(getTabid($pmodule)));
    $numrows = $adb->num_rows($res);
    
    if ($numrows > 0)
    {
        $returnvalue = Array();
        for($i = 0; $i < $numrows; $i++)
        {
        	$value = Array();
        	$fieldname = $adb->query_result($res,$i,"fieldname");
        	$permit = getFieldVisibilityPermission($pmodule, $userid, $fieldname);
        	if($permit == '0')
        	{
                $temp=$adb->query_result($res,$i,'columnname');
        		$columnlists [] = $temp;
        		$Column_Lists[$temp]["fieldid"] = $adb->query_result($res,$i,'fieldid');
        		$fieldlabel =$adb->query_result($res,$i,'fieldlabel');
        		$Column_Lists[$temp]["label"] = getTranslatedString($fieldlabel);
        	}
        }
    
        if ($idlist != "")
        {
            switch($pmodule)
        	{
        		case 'Accounts':
        			$query = 'select accountname,'.implode(",",$columnlists).' from vtiger_account left join vtiger_accountscf on vtiger_accountscf.accountid = vtiger_account.accountid where vtiger_account.accountid = ?';
        			$result=$adb->pquery($query, array($idlist));
        		    foreach($columnlists as $columnname)
        			{
                        $acc_eval = $adb->query_result($result,0,$columnname);
        				if(($single_record && $acc_eval != "") || !$single_record)
                        {
                            $Column_Lists[$columnname]["value"] = $acc_eval;
            				if($acc_eval != "") $val_cnt++;
                        }
                        else
                        {
                            unset($Column_Lists[$columnname]);
                        }
        			}
        			$entity_name = $adb->query_result($result,0,'accountname');
        			break;
        		case 'Leads':
        			$query = 'select concat(firstname," ",lastname) as leadname,'.implode(",",$columnlists).' from vtiger_leaddetails left join vtiger_leadscf on vtiger_leadscf.leadid = vtiger_leaddetails.leadid where vtiger_leaddetails.leadid = ?';
        			$result=$adb->pquery($query, array($idlist));
        		    foreach($columnlists as $columnname)	
        			{
        				$lead_eval = $adb->query_result($result,0,$columnname);
        				if(($single_record && $lead_eval != "") || !$single_record)
                        {
                            $Column_Lists[$columnname]["value"] = $lead_eval;
        				    if($lead_eval != "") $val_cnt++;
                        } 
                        else
                        {
                            unset($Column_Lists[$columnname]);
                        }   
        			}
        			$entity_name = $adb->query_result($result,0,'leadname');
        			break;
        		case 'Contacts':
        			$query = 'select concat(firstname," ",lastname) as contactname,'.implode(",",$columnlists).' from vtiger_contactdetails left join vtiger_contactscf on vtiger_contactscf.contactid = vtiger_contactdetails.contactid where vtiger_contactdetails.contactid = ?';
        			$result=$adb->pquery($query, array($idlist));
        		    foreach($columnlists as $columnname)	
        			{
        				$con_eval = $adb->query_result($result,0,$columnname);
        				if(($single_record && $con_eval != "") || !$single_record)
                        {
                            $Column_Lists[$columnname]["value"] = $con_eval;
            				if($con_eval != "") $val_cnt++;
                        }
                        else
                        {
                            unset($Column_Lists[$columnname]);
                        }
        			}	
        			$entity_name = $adb->query_result($result,0,'contactname');
        			break;
                case 'Vendors':
        			$query = 'select vendorname,'.implode(",",$columnlists).' from vtiger_vendor left join vtiger_vendorcf on vtiger_vendorcf.vendorid = vtiger_vendor.vendorid where vtiger_vendor.vendorid = ?';
        			$result=$adb->pquery($query, array($idlist));
        		    foreach($columnlists as $columnname)	
        			{
        				$ven_eval = $adb->query_result($result,0,$columnname);
        				if(($single_record && $ven_eval != "") || !$single_record)
                        {
                            $Column_Lists[$columnname]["value"] = $ven_eval;
            				if($ven_eval != "") $val_cnt++;
                        }
                        else
                        {
                            unset($Column_Lists[$columnname]);
                        }
        			}	
        			$entity_name = $adb->query_result($result,0,'vendorname');
        			break;   	
        	} 
        }
        
        if ($val_cnt > 0 || !$single_record)
        {
            return array("name" => $entity_name, "emails" => $Column_Lists);
        } 
        else
        {
            return false;
        }  
    }
    else
    {
        return false;
    }    
}    

function addAllModuleEmails($ALL_Emails,$related_module, $related_id, $single_record, $name, $fieldid)
{            
    $Module_Emails = getEmailModuleName($related_module, $related_id, $single_record);

    if ($Module_Emails)
    {
        $ALL_Emails[] = array("type" => $name, "crmid" => $related_id, "fieldid" => $fieldid, "data" => $Module_Emails);
    }
    
    return $ALL_Emails;
}
?>
