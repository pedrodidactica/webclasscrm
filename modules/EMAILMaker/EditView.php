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
require_once('include/utils/utils.php');
require_once('include/utils/UserInfoUtil.php');
require_once("include/Zend/Json.php");
require_once('modules/EMAILMaker/EMAILMaker.php');

global $log;
global $app_strings;
global $app_list_strings;
global $current_user;
global $currentModule;
global $default_charset;

$language = $_SESSION['authenticated_user_language']; 
$e_mod_strings = return_specified_module_language($language, "Emails");
$em_mod_strings = return_specified_module_language($language, "EMAILMaker");
$mod_strings = $em_mod_strings;

$EMAILMaker = new EmailMaker();

$focus = CRMEntity::getInstance("Emails");
$smarty = new vtigerCRM_Smarty();
$json = new Zend_Json();

if (isset($_REQUEST["type"]) && $_REQUEST["type"] != "")
    $type = $_REQUEST["type"];
else
    $type = "2";

$add_all = false;

$CrmidsFields = array();

if(isset($_REQUEST['pmodule']) && $_REQUEST['pmodule']!='') {
	$smarty->assign('select_module',vtlib_purify($_REQUEST['pmodule']));	
    $pmodule = vtlib_purify($_REQUEST['pmodule']);
}

$smarty->assign('SORCE_IDS',$_REQUEST["pid"]);

if ($type == "3" || $type == "4")
{
    $Pids = getPidsFromContacts($pmodule,$_REQUEST["pid"]);
    
    $smarty->assign('RELATED_TO',$_REQUEST["pid"]);
}
else
{
    if (isset($_REQUEST["pid"]) && $_REQUEST["pid"] == "all")
    {
        $_REQUEST["pid"] = "";
        $add_all = true;
    }  
    
    if (isset($_REQUEST["pid"]) && $_REQUEST["pid"] != "") 
    {
        $Pids = explode(";",$_REQUEST["pid"]);
    }    
    else
    {
        $Pids = array("0");
    }
}

if($_REQUEST['upload_error'] == true)
{
        echo '<br><b><font color="red"> The selected file has no data or a invalid file.</font></b><br>';
}

//Email Error handling
if($_REQUEST['mail_error'] != '') 
{
	require_once("modules/Emails/mail.php");
	echo parseEmailErrorString($_REQUEST['mail_error']);
}
//added to select the module in combobox of compose-popup
if(isset($_REQUEST['par_module']) && $_REQUEST['par_module']!=''){
	$smarty->assign('select_module',vtlib_purify($_REQUEST['par_module']));
}

if (count($Pids) > 1 && ($pmodule == "Accounts" || $pmodule == "Contacts"))
    $emailoptout = true;
else
    $emailoptout = false;


if(isset($_REQUEST['record']) && $_REQUEST['record'] !='') 
{
	$focus->id = $_REQUEST['record'];
	$focus->mode = 'edit';
	$focus->retrieve_entity_info($_REQUEST['record'],"Emails");
	$query = 'select idlists,from_email,to_email,cc_email,bcc_email from vtiger_emaildetails where emailid =?';
	$result = $adb->pquery($query, array($focus->id));
	$from_email = $adb->query_result($result,0,'from_email');
	$smarty->assign('FROM_MAIL',$from_email);	
	$to_email = implode(',',$json->decode($adb->query_result($result,0,'to_email')));
	$smarty->assign('TO_MAIL',$to_email);
	$cc_add = implode(',',$json->decode($adb->query_result($result,0,'cc_email')));
	$smarty->assign('CC_MAIL',$cc_add);
	$bcc_add = implode(',',$json->decode($adb->query_result($result,0,'bcc_email')));
	$smarty->assign('BCC_MAIL',$bcc_add);
	$idlist = $adb->query_result($result,0,'idlists');
	$smarty->assign('IDLISTS',$idlist);
	$log->info("Entity info successfully retrieved for EditView.");
	$focus->name=$focus->column_fields['name'];
}
elseif(isset($_REQUEST['sendmail']) && $_REQUEST['sendmail'] !='')
{
    $pmodule = addslashes($_REQUEST["pmodule"]);

    $From_Sorce = array();
    $To_Email = array();
    $No_Rcpts = array();
    
    if (!$add_all)
    {
        if (count($Pids) > 0) 
        {
            foreach ($Pids AS $pid)
            {
                $Entries = array();
                $mailids = "";
                
                $entityNames = getEntityName($pmodule, $pid);
            	$pname = $entityNames[$pid];
        
                if(isset($_REQUEST["field_lists"]) && $_REQUEST["field_lists"] != "")
            	{
                    $Mailids = array();
        
                    $CrmidsFields = explode(":",$_REQUEST["field_lists"]);
                    
                    if (count($CrmidsFields) > 0)
                    {
                        foreach ($CrmidsFields AS $crmid_fieldid)
                        {
                            $CF = explode("@",$crmid_fieldid);
                            $crmid = $CF[0];
                            $fieldid = $CF[1];
                            
                            if ($crmid == "0") 
                            {
                                if ($CF[2] != "0")
                                    $crmid = getParentIDtoEMAIL($pid,$CF[2]);
                                else
                                    $crmid = $pid;
                            }
                            
                            //echo "crmid: $crmid => fields: $fieldid <br />" ;
                            
                            if ($crmid > 0 && (!isset($Entries[$crmid]) || (isset($Entries[$crmid]) && (!in_array($fieldid,$Entries[$crmid]))))) $Entries[$crmid][] = $fieldid;
                        }   
                    }
                    
                    if (count($Entries) > 0)
                    {
                        foreach ($Entries AS $crmid => $fields)
                        {
                             $Mailids = get_to_table_emailids($emailoptout,$pid,$Mailids,$crmid,$fields);
                        }
                    }  
                                
                    if (count($Mailids["mailds"]) > 0)
                    {
                        $mailids = implode("",$Mailids["mailds"]);
                    }
                    else
                    {
                        $No_Rcpts[] = $pname;
                    }
                    
                }

                $To_Email[$pid] = $mailids;
                
                $From_Sorce[$pid] = $pname;
            }
        }
    }
    else
    {
        $Mailids = array();
        
        $Id_Lists = getSelectedRecords($_REQUEST,vtlib_purify($_REQUEST['pmodule']),"all",vtlib_purify($_REQUEST['excludedRecords']));
        
        $CrmidsFields = explode(":",$_REQUEST["field_lists"]);
        
        if (count($CrmidsFields) > 0) 
        {       
            foreach ($CrmidsFields AS $crmid_fieldid)
            {
                $CF = explode("@",$crmid_fieldid);
                $fieldid = $CF[1];
                
                foreach ($Id_Lists AS $crmid)
                {
                    $Entries[$crmid][] = $fieldid;        
                }
            } 
        } 
        
        if (count($Entries) > 0) 
        { 
            foreach ($Entries AS $crmid => $fields)
            {
                 $Mailids = get_to_table_emailids($emailoptout,$crmid,$Mailids,$crmid,$fields);
            }
        }
        $mailids = implode("",$Mailids["mailds"]);
        $To_Email[0] = $mailids;
            
        $From_Sorce[0] = "";
    }
    
	$smarty->assign('TO_MAIL',$To_Email);
    $smarty->assign('FROM_SORCE',$From_Sorce);
	$focus->mode = '';

}

$coun_sorces = count($From_Sorce);

$smarty->assign('COUNT_SORCES',$coun_sorces);

global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$disp_view = getView($focus->mode);
$details = getBlocks("Emails",$disp_view,$mode,$focus->column_fields);
//changed this below line to view description in all language - bharath
$smarty->assign("BLOCKS",$details[$e_mod_strings['LBL_EMAIL_INFORMATION']]); 
$smarty->assign("MODULE","Emails");
$smarty->assign("SINGLE_MOD",$app_strings['Email']);
//id list of attachments while forwarding
$smarty->assign("ATT_ID_LIST",$att_id_list);

//needed when creating a new email with default values passed in
if (isset($_REQUEST['contact_name']) && is_null($focus->contact_name)) 
{
	$focus->contact_name = vtlib_purify($_REQUEST['contact_name']);
}
if (isset($_REQUEST['contact_id']) && is_null($focus->contact_id)) 
{
	$focus->contact_id = vtlib_purify($_REQUEST['contact_id']);
}
if (isset($_REQUEST['parent_name']) && is_null($focus->parent_name)) 
{
	$focus->parent_name = vtlib_purify($_REQUEST['parent_name']);
}
if (isset($_REQUEST['parent_id']) && is_null($focus->parent_id)) 
{
	$focus->parent_id = vtlib_purify($_REQUEST['parent_id']);
}
if (isset($_REQUEST['parent_type'])) 
{
	$focus->parent_type = vtlib_purify($_REQUEST['parent_type']);
}
if (isset($_REQUEST['filename']) && $_REQUEST['isDuplicate'] != 'true') 
{
        $focus->filename = vtlib_purify($_REQUEST['filename']);
}
elseif (is_null($focus->parent_type)) 
{
	$focus->parent_type = $app_list_strings['record_type_default_key'];
}

$log->info("Email detail view");

$smarty->assign("EMOD", $em_mod_strings);
$smarty->assign("MOD", $e_mod_strings);
$smarty->assign("APP", $app_strings);
if (isset($focus->name)) $smarty->assign("NAME", $focus->name);
else $smarty->assign("NAME", "");


if($focus->mode == 'edit')
{
	$smarty->assign("UPDATEINFO",updateInfo($focus->id));
	if(((!empty($_REQUEST['forward']) || !empty($_REQUEST['reply'])) &&
			$focus->column_fields['email_flag'] != 'SAVED') || (empty($_REQUEST['forward']) &&
			empty($_REQUEST['reply']) && $focus->column_fields['email_flag'] != 'SAVED')) {
		$mode = '';
	} else {
		$mode = $focus->mode;
	}
	$smarty->assign("MODE", $mode);
}

// Unimplemented until jscalendar language vtiger_files are fixed

$smarty->assign("CALENDAR_LANG", $app_strings['LBL_JSCALENDAR_LANG']);
$smarty->assign("CALENDAR_DATEFORMAT", parse_calendardate($app_strings['NTC_DATE_FORMAT']));

if(isset($_REQUEST['return_module'])) $smarty->assign("RETURN_MODULE", vtlib_purify($_REQUEST['return_module']));
else $smarty->assign("RETURN_MODULE",'Emails');
if(isset($_REQUEST['return_action'])) $smarty->assign("RETURN_ACTION", vtlib_purify($_REQUEST['return_action']));
else $smarty->assign("RETURN_ACTION",'index');
if(isset($_REQUEST['return_id'])) $smarty->assign("RETURN_ID", vtlib_purify($_REQUEST['return_id']));
if (isset($_REQUEST['return_viewname'])) $smarty->assign("RETURN_VIEWNAME", vtlib_purify($_REQUEST['return_viewname']));

$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("PRINT_URL", "phprint.php?jt=".session_id().$GLOBALS['request_string']);
$smarty->assign("ID", $focus->id);
$smarty->assign("ENTITY_ID", vtlib_purify($_REQUEST["record"]));
$smarty->assign("ENTITY_TYPE",vtlib_purify($_REQUEST["email_directing_module"]));
$smarty->assign("OLD_ID", $old_id );
//Display the RTE or not? -- configure $USE_RTE in config.php
$USE_RTE = vt_hasRTE();
$smarty->assign("USE_RTE",$USE_RTE);

if(empty($focus->filename))
{
        $smarty->assign("FILENAME_TEXT", "");
        $smarty->assign("FILENAME", "");
}
else
{
        $smarty->assign("FILENAME_TEXT", "(".$focus->filename.")");
        $smarty->assign("FILENAME", $focus->filename);
}
if($ret_error == 1) {
	require_once('modules/Webmails/MailBox.php');
	$smarty->assign("RET_ERROR",$ret_error);
	if($ret_parentid != ''){
		$smarty->assign("IDLISTS",$ret_parentid);
	}
	if($ret_toadd != '')
                $smarty->assign("TO_MAIL",$ret_toadd);
	$ret_toadd = '';
	if($ret_subject != '')
		$smarty->assign("SUBJECT",$ret_subject);
	if($ret_ccaddress != '')
        	$smarty->assign("CC_MAIL",$ret_ccaddress);
	if($ret_bccaddress != '')
        	$smarty->assign("BCC_MAIL",$ret_bccaddress);
	if($ret_description != '')
        	$smarty->assign("DESCRIPTION", $ret_description);
	$temp_obj = new MailBox($mailbox);
	$temp_id = $temp_obj->boxinfo['mail_id'];
	if($temp_id != '')
		$smarty->assign('from_add',$temp_id);
}
$check_button = Button_Check($module);
$smarty->assign("CHECK", $check_button);

if(file_exists("modules/Calendar/language/$default_language.lang.php"))
    $Calendar_Mod_Strings = return_specified_module_language($current_language, "Calendar");    
else 
    $Calendar_Mod_Strings = return_specified_module_language("en_us", "Calendar");

$smarty->assign("CMOD",$Calendar_Mod_Strings);


$description = "";
$is_drip = "no";

$Att_Documents = array();
 
if (isset($_REQUEST["commontemplateid"]) && $_REQUEST["commontemplateid"] != "")
{
    $EMAIL_Template_Ids = array();
    
    $Subjects = array();
    $smarty->assign('WEBMAIL',"true");
    
    $commontemplateids = trim($_REQUEST["commontemplateid"],";");  
 
    if(substr($commontemplateids, 0, 5) == "drip_")
    {
        $is_drip = "yes";
        $dripid = $commontemplateids = substr($commontemplateids, 5);
    
        $sql = "SELECT * FROM vtiger_emakertemplates_drips WHERE dripid =?";
        $result = $adb->pquery($sql, array($commontemplateids));
        $num_rows = $adb->num_rows($result);
    
        if ($num_rows > 0)
        {
            $subject = $adb->query_result($result,0,"dripname"); 
            $selected_module = $adb->query_result($result,0,"module"); 
            
            $Email_Templates = $EMAILMaker->getDripEmailTemplates($dripid,false);
            
            $smarty->assign("EMAIL_TEMPLATES", $Email_Templates);

            $Email_Templates_To_Drip = $EMAILMaker->getEmailTemplatesToDrip($selected_module);
            
            $smarty->assign("EMAIL_TEMPLATES_TO_DRIP", $Email_Templates_To_Drip);
        }
    }
    else
    {
        $Templateids = explode(";",$commontemplateids);
        $template_ids = implode(",",$Templateids);
        $parent_type = $_REQUEST["pmodule"];
        
        include_once("modules/EMAILMaker/ConvertEMAIL.php");
        
        $sql = "SELECT templateid, subject, body FROM vtiger_emakertemplates WHERE templateid IN (".$template_ids.")";
        $result = $adb->pquery($sql, array());
        $num_rows = $adb->num_rows($result);
        
        for($i=0;$i < $num_rows; $i++)
        {	
            $templateid = $adb->query_result($result,$i,'templateid');
            $t_subject = $adb->query_result($result,$i,'subject');
            $body = $adb->query_result($result,$i,'body');

            $ListViewBlocks = array();
            if(strpos($body,"#LISTVIEWBLOCK_START#") !== false && strpos($body,"#LISTVIEWBLOCK_END#") !== false)
            {
                $Main_Email_Content = new EMAILContent();
                $body = $Main_Email_Content->convertListViewBlock($body);
                
                preg_match_all("|#LISTVIEWBLOCK_START#(.*)#LISTVIEWBLOCK_END#|sU", $body, $ListViewBlocks, PREG_PATTERN_ORDER);
            }
              
            if (count($ListViewBlocks) > 0)
            {
                $type = 1;

                $To_Email = array();
                $Entries = array();
                $mailids = "";
                
                $entityNames = getEntityName($pmodule, $pid);
            	$pname = $entityNames[$pid];
        
                if(isset($_REQUEST["field_lists"]) && $_REQUEST["field_lists"] != "")
            	{
                    $Mailids = array();
        
                    $CrmidsFields = explode(":",$_REQUEST["field_lists"]);
                    
                    if (count($CrmidsFields) > 0)
                    {
                        foreach ($CrmidsFields AS $crmid_fieldid)
                        {
                            $CF = explode("@",$crmid_fieldid);
                            $crmid = $CF[0];
                            $fieldid = $CF[1];
                            
                            if ($crmid == "0") 
                            {
                                if ($CF[2] != "0")
                                    $crmid = getParentIDtoEMAIL($pid,$CF[2]);
                                else
                                    $crmid = $pid;
                            }
                            
                            if ($crmid > 0 && (!isset($Entries[$crmid]) || (isset($Entries[$crmid]) && (!in_array($fieldid,$Entries[$crmid]))))) $Entries[$crmid][] = $fieldid;
                        }   
                    }
                    
                    if (count($Entries) > 0)
                    {
                        foreach ($Entries AS $crmid => $fields)
                        {
                             $Mailids = get_to_table_emailids($emailoptout,$pid,$Mailids,$crmid,$fields);
                        }
                    }  
                                
                    if (count($Mailids["mailds"]) > 0)
                    {
                        $mailids = implode("",$Mailids["mailds"]);
                    }
                }

                $To_Email["0"] = $mailids;

                $smarty->assign('TO_MAIL',$To_Email);


                
                $num_listview_blocks = count($ListViewBlocks[0]);
                for($i=0; $i<$num_listview_blocks; $i++)
                {
                    $cridx = 1;
                    $replace = "";
                    $listview_block = $ListViewBlocks[0][$i];
                    $listview_block_content = $ListViewBlocks[1][$i];
                    foreach ($Pids AS $pid)
                    {
                        $Email_Content = clone $Main_Email_Content;
                        $Email_Content->setContent($listview_block_content, "", $parent_type, $pid);
                        $replace .= $Email_Content->getContent(false);
                        $replace = str_ireplace('$CRIDX$', $cridx++, $replace); 
                        
                        unset($Email_Content); 
                    }
                    $body = str_replace($listview_block,$replace,$body);
                } 
            }
            else
            { 
                if (count($Pids) == 1 && $add_all == false)
                { 
                    $Email_Content = new EMAILContent();
                    $Email_Content->setContent($t_subject."|@{[&]}@|".$body, "", $parent_type, $Pids[0]);
                    $convert_content = $Email_Content->getContent(false); 
                    list($t_subject,$body) = explode("|@{[&]}@|",$convert_content);
                }
            }
            
            $EMAIL_Template_Ids[] = $templateid; 
            
            if ($t_subject != "") $Subjects[] = $t_subject;
            $description .= $body;
            
            //LOAD DOCUMENTS
            $sql3 = "SELECT vtiger_notes.notesid, vtiger_notes.title FROM vtiger_notes 
                      INNER JOIN vtiger_crmentity 
                         ON vtiger_crmentity.crmid = vtiger_notes.notesid
                      INNER JOIN vtiger_emakertemplates_documents 
                         ON vtiger_emakertemplates_documents.documentid = vtiger_notes.notesid
                      WHERE vtiger_crmentity.deleted = '0' AND vtiger_emakertemplates_documents.templateid = ?";
            $result3 = $adb->pquery($sql3, array($templateid));
            $num_rows3 = $adb->num_rows($result3); 
            
            if ($num_rows3 > 0)
            {
                while($row3 = $adb->fetchByAssoc($result3))
                {
                	$Att_Documents[$row3['notesid']] = $row3['title'];
                }
            }
        }
        
        $smarty->assign("DESCRIPTION", $description);
        
        $subject = implode(", ",$Subjects);
    }
    $smarty->assign("SUBJECT", $subject);
}

$smarty->assign("ATT_DOCUMENTS", $Att_Documents);
$smarty->assign("IS_DRIP", $is_drip);

$show_pdf_templates = "false";
if (isset($_REQUEST["pdftemplateid"]) && $_REQUEST["pdftemplateid"] != "")
{
    $PDF_Maker_Templates = array();
    $PDF_Template_Ids = array();
    $pdftemplateids = addslashes(trim($_REQUEST["pdftemplateid"],";"));
    $pdftemplateids = str_replace(";",",",$pdftemplateids);
    $sql = "SELECT templateid, filename FROM vtiger_pdfmaker WHERE templateid IN (".$pdftemplateids.")";
	$result = $adb->query($sql);
    while($row = $adb->fetchByAssoc($result))
    {
    	$PDF_Maker_Templates[$row['templateid']] = $row['filename'];
        $PDF_Template_Ids[] = $row['templateid'];
    }
    
    $smarty->assign("PDFMakerTemplates", $PDF_Maker_Templates);
    
    if (count($PDF_Maker_Templates) > 0)
    {
        $show_pdf_templates = "true";
        
        $pdf_template_ids = implode(";",$PDF_Template_Ids);
        $smarty->assign("PDF_TEMPLATE_IDS", $pdf_template_ids);
        
        $pdf_language = addslashes($_REQUEST["language"]);
        $smarty->assign("PDF_LANGUAGE", $pdf_language);
    }
}
$smarty->assign("SHOW_PDF_TEMPLATES", $show_pdf_templates);



//Default From
$selected_default_from = "";
$saved_default_from = "";

if (count($EMAIL_Template_Ids) == 1)
{
    foreach ($EMAIL_Template_Ids AS $templateid)
    {
        $sql_lfn = "SELECT fieldname FROM vtiger_emakertemplates_default_from WHERE templateid = ? AND userid = ?";
        $result_lfn = $adb->pquery($sql_lfn, array($templateid, $current_user->id));
        $num_rows_lfn = $adb->num_rows($result_lfn); 
        
        if ($num_rows_lfn > 0)
        {
            $saved_default_from = $adb->query_result($result_lfn,0,"fieldname");
        }
    }    
}

$full_name = trim($current_user->first_name." ".$current_user->last_name);

$sql_fm = "SELECT fieldname, fieldlabel FROM vtiger_field WHERE tabid= 29 AND uitype IN (104,13) ORDER BY fieldid ASC ";
$result_fm = $adb->query($sql_fm);

while($row_fm = $adb->fetchByAssoc($result_fm))
{
	if ($current_user->column_fields[$row_fm['fieldname']] != "")
    {
        $from_key = $row_fm['fieldname']."::".$current_user->id;
        $From_Emails[$from_key] = $full_name." <".$current_user->column_fields[$row_fm['fieldname']].">";
    
        if ($saved_default_from == "1_".$row_fm['fieldname']) $selected_default_from = $from_key;
    }
}



$sql_a="select * from vtiger_systems where from_email_field != ? AND server_type = ?";
$result_a = $adb->pquery($sql_a, array('','email'));
$from_email_field = $adb->query_result($result_a,0,"from_email_field");   

if($from_email_field != "")
{
    $sql2="select * from vtiger_organizationdetails where organizationname != ''";
    $result2 = $adb->pquery($sql2, array());

    while($row2 = $adb->fetchByAssoc($result2))
    {
        $from_key = "a::".$row2['organizationname'];
        $From_Emails[$from_key] = $row2['organizationname']." <".$from_email_field.">";
    
        if ($saved_default_from == "0_organization_email") $selected_default_from = $from_key;
    }
}

$smarty->assign("SELECTED_DEFAULT_FROM", $selected_default_from);
$smarty->assign("FROM_EMAILS", $From_Emails);

if ($pmodule != "")
{
    $selected_module = getTranslatedString($pmodule,$pmodule);
    $smarty->assign("SELECTMODULE", $selected_module);
    
    $RecipientModulenames = array(""=>$em_mod_strings["LBL_PLS_SELECT"],
                                  "Contacts" => $app_strings["COMBO_CONTACTS"],
                                  "Accounts" => $app_strings["COMBO_ACCOUNTS"],
                                  "Vendors" => $app_strings["Vendors"],
                                  "Leads" => $app_strings["COMBO_LEADS"],
                                  "Users" => $app_strings["COMBO_USERS"]);
    
    $ptabid = getTabId($pmodule);
    
    $EMAILMaker->createModuleFields($pmodule,$ptabid);
    
    foreach ($RecipientModulenames AS $rmodule => $ml)
    {
        if ($rmodule != "" && !isset($EMAILMaker->ModuleFields[$rmodule]))
        {
            if (isPermitted($rmodule,'index') == "yes")
            {
                $rtabid = getTabId($rmodule);
                if ($rtabid != "")
                    $EMAILMaker->createModuleFields($rmodule,$rtabid);
                else 
                    unset($RecipientModulenames[$rmodule]);   
            }
            else
            {
                unset($RecipientModulenames[$rmodule]);
            }
        }
        
    }
    
    $smarty->assign("RECIPIENTMODULENAMES",$RecipientModulenames);
    
    $Related_Modules = array(); 
	
    if (count($EMAILMaker->All_Related_Modules[$pmodule]) > 0)
    {	                  
        foreach ($EMAILMaker->All_Related_Modules[$pmodule] AS $rel_data)
        {		                  
             $rel_tabid = getTabId($rel_data["module"]);
             if (isPermitted($rel_data["module"],'index') == "yes" && $rel_tabid != "") 
             {
                 if (!isset($EMAILMaker->ModuleFields[$rel_data["module"]]))
                 {
                     $EMAILMaker->createModuleFields($rel_data["module"],$rel_tabid);
                 }
                 
                 $Related_Modules[$rel_data["fieldlabel"]][$rel_data["module"]."--".$rel_data["fieldname"]] = $rel_data["modulelabel"];
             }
        }
        
        $smarty->assign("RELATED_MODULES",$Related_Modules);
        
        $body_variables_display = "table-row";
    }
    else
    {
        $body_variables_display = "none";
    }
    $smarty->assign("BODY_VARIABLES_DISPLAY", $body_variables_display);
    
    $EMAILMaker->convertModuleFields();
    
    $smarty->assign("MODULE_BLOCKS",$EMAILMaker->Convert_ModuleBlocks);
    
    $smarty->assign("RELATED_MODULE_FIELDS",$EMAILMaker->Convert_RelatedModuleFields);
    
    $smarty->assign("MODULE_FIELDS",$EMAILMaker->Convert_ModuleFields);
    
    //EMAIL SUBJECT FIELDS
    $smarty->assign("SUBJECT_FIELDS",$EMAILMaker->getSubjectFields());
    
    
    $smarty->assign("SELECT_MODULE_FIELD",$EMAILMaker->SelectModuleFields[$pmodule]);
    $smf_filename = $EMAILMaker->SelectModuleFields[$pmodule];
	if($pmodule=="Invoice" || $pmodule=="Quotes" || $pmodule=="SalesOrder" || $pmodule=="PurchaseOrder" || $pmodule=="Issuecards" || $pmodule=="Receiptcards" || $pmodule == "Creditnote" || $pmodule == "StornoInvoice")
		unset($smf_filename["Details"]);
	$smarty->assign("SELECT_MODULE_FIELD_SUBJECT",$smf_filename); 
    
    $Related_Data = $EMAILMaker->getRelatedData();
    
    $smarty->assign("ALL_RELATED_MODULES",$Related_Data[0]);
    
    $smarty->assign("MODULE_RELATED_FIELDS",$Related_Data[1]);
  
}

include_once("vtigerversion.php");
$smarty->assign("VTIGER_VERSION",$vtiger_current_version);

$is_delay_active = $EMAILMaker->controlActiveDelay();
$smarty->assign("IS_DELAY_ACTIVE",$is_delay_active);

$Delay_Minutes = array("0"=>"0","15"=>"15","30"=>"30","45"=>"45");
$smarty->assign("DELAY_MINUTES",$Delay_Minutes);


if ($type == "1" || $type == "3" || $type == "5")
    $set_recipients_list = "group";
else
    $set_recipients_list = "separatli";

$s_type = "1";
if (count($CrmidsFields) == 0 && $type == "2")
{
    $set_recipients_list = "group";
       
    if (count($From_Sorce) > 0) $smarty->assign("EMAILS_LIST_TITLE",implode(", ",$From_Sorce)); 
    $s_type = "2";
    $type = "1";
}

$smarty->assign("SET_RECIPIENTS_LIST",$set_recipients_list);


$smarty->assign("TYPE", $type);
$smarty->assign("S_TYPE",$s_type);

$smarty->display("modules/EMAILMaker/ComposeEmail.tpl");

function get_to_table_emailids($emailoptout,$pid,$Mailids,$crmid,$field_lists)
{
	global $image_path,$adb;

    $module = getSalesEntityType($crmid);
    $module_tabid = getTabId($module);

	$query = 'select tabid,columnname,fieldid from vtiger_field where fieldid in ('. generateQuestionMarks($field_lists) .') and presence in (0,2)';
	$result = $adb->pquery($query, array($field_lists));
    
    $columns = Array();
	$idlists = '';

	while($row = $adb->fetch_array($result))
	{
		if ($module_tabid == $row['tabid'])
        {
            $columns[]=$row['columnname'];
    		$fieldid[]=$row['fieldid'];
        }
	}
	
    if (count($columns) > 0)
    {
        $columnlists = implode(',',$columns);
        
    	switch($module)
    	{
    		case 'Leads':
    			$query = 'select concat(lastname," ",firstname) as entityname,'.$columnlists.' from vtiger_leaddetails inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_leaddetails.leadid left join vtiger_leadscf on vtiger_leadscf.leadid = vtiger_leaddetails.leadid where vtiger_crmentity.deleted=0 and vtiger_crmentity.crmid = ?';
    			break;
    		case 'Contacts':
    			//email opt out funtionality works only when we do mass mailing.
    			if($emailoptout)
    			$concat_qry = 'vtiger_contactdetails.emailoptout != 1 and ';
    			else
    			$concat_qry = '';
                
    			$query = 'select concat(lastname," ",firstname) as entityname,'.$columnlists.' from vtiger_contactdetails inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid left join vtiger_contactscf on vtiger_contactscf.contactid = vtiger_contactdetails.contactid where vtiger_crmentity.deleted=0 and '.$concat_qry.'  vtiger_crmentity.crmid = ?';
    			break;
    		case 'Accounts':
    			//added to work out email opt out functionality.
    			if($emailoptout)
    				$concat_qry = 'vtiger_account.emailoptout != 1 and ';
    			else
    				$concat_qry = '';
    				
    			$query = 'select accountname as entityname,'.$columnlists.' from vtiger_account inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_account.accountid left join vtiger_accountscf on vtiger_accountscf.accountid = vtiger_account.accountid where vtiger_crmentity.deleted=0 and '.$concat_qry.' vtiger_crmentity.crmid = ?';
    			break;
            case 'Vendors':
    			$query = 'select vendorname as entityname,'.$columnlists.' from vtiger_vendor inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_vendor.vendorid left join vtiger_vendorcf on vtiger_vendorcf.vendorid = vtiger_vendor.vendorid where vtiger_crmentity.deleted=0 and vtiger_vendor.vendorid = ?';
    	        break;
        }

        $result = $adb->pquery($query, array($crmid));
        
        $border_top = '';
        $border_bottom = '';
        $border_left = '';
        $border_right = '';
        $td_style = $border_top.$border_bottom; 
        
        
        while($row = $adb->fetch_array($result))
    	{
    		$name = $row['entityname'];
    		for($i=0;$i<count($columns);$i++)
    		{
    			if($row[$columns[$i]] != NULL && $row[$columns[$i]] !='')
    			{
    				//echo $crmid.'@'.$fieldid[$i]."-".$row[$columns[$i]]."<br>";
                    $selectbox = getEMakerSelectbox($pid.'_'.$crmid.'_'.$fieldid[$i]);
                    $clear_btn = '<img src="'.$image_path.'clear_field.gif" alt="'.$app_strings["LBL_CLEAR"].'" title="'.$app_strings["LBL_CLEAR"].'" LANGUAGE=javascript onClick="clearEmailFromTable(\''.$pid.'\',\''.$crmid.'\',\''.$fieldid[$i].'\',this); return false;" align="absmiddle" style="cursor:hand;cursor:pointer">';
                    
                    $Mailids["idlists"][] = $crmid.'@'.$fieldid[$i]; 
                    
                    $Mailids["mailds"][] = '<tr id="emailadress_'.$pid.'_'.$crmid.'_'.$fieldid[$i].'" title="'.$name.' - '.$row[$columns[$i]].'"><td width="10px" valign="top" style="'.$td_style.$border_left.'">'.$selectbox.'</td><td style="font-size:10px;line-height:12px;'.$td_style.'">'.$name.' <i>&lt;'.$row[$columns[$i]].'&gt;</i></td><td width="10px" align="right" style="'.$td_style.$border_right.'">'.$clear_btn.'</td></tr>';
                }
    		}
    	}
    }
    
	return $Mailids;
}

function getEMakerSelectbox($name)
{
    global $e_mod_strings;
    $s = '<select name="ToEmails[]" id="to_email_'.$name.'" style="font-size:10px;">';
    $s .= '<option value="normal_'.$name.'">'.$e_mod_strings["LBL_TO"].'</option>';
    $s .= '<option value="cc_'.$name.'">'.$e_mod_strings["LBL_CC"].'</option>';
    $s .= '<option value="bcc_'.$name.'">'.$e_mod_strings["LBL_BCC"].'</option>';
    $s .= '</select>';
     
    return $s;
}


function getParentIDtoEMAIL($crmid,$field_id)
{
	global $adb;

	$query1 = "SELECT vtiger_tab.name, vtiger_field.tablename, vtiger_field.columnname, vtiger_field.fieldid FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_field.fieldid = ? AND vtiger_field.presence IN (0,2)";
	$result1 = $adb->pquery($query1, array($field_id));
	$Field = $adb->fetchByAssoc($result1, 0); 
    
    $p_focus = CRMEntity::getInstance($Field["name"]);
    
    $main_coln = $p_focus->tab_name_index[$Field["tablename"]];
    
    $query2 = "SELECT ".$Field["columnname"]." FROM ".$Field["tablename"]." WHERE ".$main_coln." = ?";
    $result2 = $adb->pquery($query2, array($crmid));
    
    $parent_id = $adb->query_result($result2,0,$Field["columnname"]);
    
    return $parent_id;
}

function getPidsFromContacts($pmodule,$id)
{
    global $adb;
    
    $Pids = array();
    
    switch($pmodule)
    {
        case "Accounts":
            $query = "SELECT vtiger_crmentity.crmid FROM vtiger_account
        		  	  INNER JOIN vtiger_campaignaccountrel ON vtiger_campaignaccountrel.accountid = vtiger_account.accountid
        			  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
        		  	  LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
        			  LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
        			  LEFT JOIN vtiger_accountbillads ON vtiger_accountbillads.accountaddressid = vtiger_account.accountid
        			  LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignaccountrel.campaignrelstatusid
        			  WHERE vtiger_campaignaccountrel.campaignid = ".$id." AND vtiger_crmentity.deleted=0";
        break;      
    
       case "Contacts":
            $query = "SELECT vtiger_crmentity.crmid FROM vtiger_contactdetails
    				  INNER JOIN vtiger_campaigncontrel ON vtiger_campaigncontrel.contactid = vtiger_contactdetails.contactid
    				  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
    			  	  LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
    				  LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
    				  LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid
    				  LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaigncontrel.campaignrelstatusid
    				  WHERE vtiger_campaigncontrel.campaignid = ".$id." AND vtiger_crmentity.deleted=0";
        break;
        
        case "Leads":
            $query = "SELECT vtiger_crmentity.crmid FROM vtiger_leaddetails
        			  INNER JOIN vtiger_campaignleadrel ON vtiger_campaignleadrel.leadid=vtiger_leaddetails.leadid
        			  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
    				  INNER JOIN vtiger_leadsubdetails  ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid
    				  INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leadsubdetails.leadsubscriptionid
    				  LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
    				  LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
    				  LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignleadrel.campaignrelstatusid
    				  WHERE vtiger_crmentity.deleted=0 AND vtiger_campaignleadrel.campaignid =".$id;
        break;
    } 
    
    $result = $adb->query($query);   
    $num_rows = $adb->num_rows($result);  
    
    if ($num_rows > 0)
    {
        while($row = $adb->fetchByAssoc($result))
        {
        	$Pids[] = $row['crmid'];
        } 
    }
    
    return $Pids;    
}

?>