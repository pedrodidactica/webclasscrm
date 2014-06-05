<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('Smarty_setup.php');
require_once('include/utils/utils.php');
require_once('modules/EMAILMaker/EMAILMaker.php');

global $app_strings;
global $mod_strings;
global $app_list_strings;
global $adb;
global $upload_maxsize;
global $theme,$default_charset;
global $current_language;
global $site_URL;
global $current_user;
    
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$EMAILMaker = new EmailMaker(); 
if($EMAILMaker->CheckPermissions("DETAIL") == false)
  $EMAILMaker->DieDuePermission();

$smarty = new vtigerCRM_Smarty;
  
if($EMAILMaker->CheckPermissions("EDIT") && $EMAILMaker->GetVersionType() != "deactivate" ) {
    $smarty->assign("EDIT","permitted");
    $smarty->assign("IMPORT","yes");
} else {
    header("Location:index.php?module=EMAILMaker&action=ListEmailTemplates&parenttab=Tools");
    exit;
}

if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) {
  $smarty->assign("DELETE","permitted");
}

if(isset($_REQUEST['templateid']) && $_REQUEST['templateid']!='')
{
  	$templateid = $_REQUEST['templateid'];

    $emailtemplateResult = $EMAILMaker->GetEditViewData($_REQUEST['templateid']);

  	$select_module = $emailtemplateResult["module"];
  	$email_language = $emailtemplateResult["email_language"];
    
    $owner = $emailtemplateResult["owner"];
    $sharingtype = $emailtemplateResult["sharingtype"];
    $sharingMemberArray = $EMAILMaker->GetSharingMemberArray($templateid);
    
    $email_category = $emailtemplateResult["category"];
    
    $is_active = $emailtemplateResult["is_active"];
    $is_default = $emailtemplateResult["is_default"];
}
else
{
    $templateid = "";
    
    if (isset($_REQUEST["return_module"]) && $_REQUEST["return_module"] != "") 
       $select_module = $_REQUEST["return_module"]; 
    else 
       $select_module = "";
       
    $email_language = $current_language;
    
    if (isset($_REQUEST["template"]))
    {
       $template_path = getcwd()."/modules/EMAILMaker/templates/".$_REQUEST["template"]."/index.html";
       
       $template_content = file_get_contents($template_path);
       
       if (file_exists($template_path)) 
       {
           $emailtemplateResult["body"] = str_replace("[site_URL]",$site_URL,$template_content);
       }
    }
    
    $owner = $current_user->id;
    $sharingtype = "public";
    $sharingMemberArray = array();
    
    $email_category = "";
}

if ($_REQUEST["test"] && $_REQUEST["test"] != "")
{
    $select_module = addslashes($_REQUEST["test"]);
}


if(isset($_REQUEST["isDuplicate"]) && $_REQUEST["isDuplicate"]=="true")
{
  $smarty->assign("DUPLICATE_FILENAME", $emailtemplateResult["templatename"]);
}
$smarty->assign("FILENAME", $emailtemplateResult["templatename"]);  
$smarty->assign("SUBJECT", $emailtemplateResult["subject"]);
$smarty->assign("DESCRIPTION", $emailtemplateResult["description"]);

if (!isset($_REQUEST["isDuplicate"]) OR (isset($_REQUEST["isDuplicate"]) && $_REQUEST["isDuplicate"] != "true")) $smarty->assign("SAVETEMPLATEID", $templateid);
if ($templateid!="")
  $smarty->assign("EMODE", "edit");  

$smarty->assign("TEMPLATEID", $templateid);
$smarty->assign("MODULENAME", getTranslatedString($select_module));
$smarty->assign("SELECTMODULE", $select_module);

$smarty->assign("BODY", $emailtemplateResult["body"]);

$smarty->assign("MOD",$mod_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("APP", $app_strings);
$smarty->assign("PARENTTAB", getParentTab());

$smarty->assign("EMAIL_CATEGORY", $email_category);


$Modulenames = Array(''=>$mod_strings["LBL_PLS_SELECT"]);
$sql = "SELECT tabid, name FROM vtiger_tab WHERE (isentitytype=1 AND tabid NOT IN (9, 10, 16, 28)) OR tabid = '29' ORDER BY name ASC";
$result = $adb->query($sql);
while($row = $adb->fetchByAssoc($result)){
  if ($row['tabid'] != "29") $Modulenames[$row['name']] = getTranslatedString($row['name']);
  $ModuleIDS[$row['name']] = $row['tabid'];
} 

$smarty->assign("MODULENAMES",$Modulenames);

$RecipientModulenames = array(""=>$mod_strings["LBL_PLS_SELECT"],
                              "Contacts" => $app_strings["COMBO_CONTACTS"],
                              "Accounts" => $app_strings["COMBO_ACCOUNTS"],
                              "Vendors" => $app_strings["Vendors"],
                              "Leads" => $app_strings["COMBO_LEADS"],
                              "Users" => $app_strings["COMBO_USERS"]);

$smarty->assign("RECIPIENTMODULENAMES",$RecipientModulenames);
 
// ******************************************   Company and User information: **********************************


$CUI_BLOCKS["Account"]=$mod_strings["LBL_COMPANY_INFO"];
$CUI_BLOCKS["Assigned"]=$mod_strings["LBL_USER_INFO"];
$CUI_BLOCKS["Logged"]=$mod_strings["LBL_LOGGED_USER_INFO"];
$smarty->assign("CUI_BLOCKS",$CUI_BLOCKS);


$sql="SELECT * FROM vtiger_organizationdetails";
$result = $adb->pquery($sql, array());

$organization_logoname = decode_html($adb->query_result($result,0,'logoname'));
$organization_header = decode_html($adb->query_result($result,0,'headername'));
$organization_stamp_signature = $adb->query_result($result,0,'stamp_signature');

global $site_URL;	
$path = $site_URL."/test/logo/";

if (isset($organization_logoname))
{
	$organization_logo_img = "<img src=\"".$path.$organization_logoname."\">";
	$smarty->assign("COMPANYLOGO",$organization_logo_img);
}
if (isset($organization_stamp_signature))
{
	$organization_stamp_signature_img = "<img src=\"".$path.$organization_stamp_signature."\">";
	$smarty->assign("COMPANY_STAMP_SIGNATURE",$organization_stamp_signature_img);
}	
if (isset($organization_header))
{
	$organization_header_img = "<img src=\"".$path.$organization_header."\">";
	$smarty->assign("COMPANY_HEADER_SIGNATURE",$organization_header_img);
}

$Acc_Info = array(''=>$mod_strings["LBL_PLS_SELECT"],
                  "company-name"=>$mod_strings["LBL_COMPANY_NAME"],
                  "company-logo"=>$mod_strings["LBL_COMPANY_LOGO"],
                  "company-address"=>$mod_strings["LBL_COMPANY_ADDRESS"],
                  "company-city"=>$mod_strings["LBL_COMPANY_CITY"],
                  "company-state"=>$mod_strings["LBL_COMPANY_STATE"],
                  "company-zip"=>$mod_strings["LBL_COMPANY_ZIP"],
                  "company-country"=>$mod_strings["LBL_COMPANY_COUNTRY"],
                  "company-phone"=>$mod_strings["LBL_COMPANY_PHONE"],
                  "company-fax"=>$mod_strings["LBL_COMPANY_FAX"],
                  "company-website"=>$mod_strings["LBL_COMPANY_WEBSITE"]
                 );
                 
$CUI["Account"][$mod_strings["LBL_COMPANY_INFO"]] = $Acc_Info;
 
$smarty->assign("ACCOUNTINFORMATIONS",$Acc_Info);

$sql_user_block = "SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid=29 ORDER BY sequence ASC";
$res_user_block = $adb->query($sql_user_block);
$user_block_info_arr = array();
while($row_user_block = $adb->fetch_array($res_user_block))
{
    $sql_user_field = "SELECT fieldid, uitype FROM vtiger_field WHERE block=".$row_user_block['blockid']." and (displaytype != 3 OR uitype = 55) and fieldid != '195' ORDER BY sequence ASC";
    $res_user_field = $adb->query($sql_user_field);
    $num_user_field = $adb->num_rows($res_user_field);  
    
    if ($num_user_field > 0)
    {
  	   $user_field_id_array = array();
        
       while($row_user_field = $adb->fetch_array($res_user_field))
       {
            $user_field_id_array[] = $row_user_field['fieldid'];
            // print_r($user_field_id_array);
       }             
       
       $user_block_info_arr[$row_user_block['blocklabel']] = $user_field_id_array;

    }
} 

// $UserOptgroupts = array();

if(file_exists("modules/Users/language/$default_language.lang.php"))  //kontrola na $default_language pretoze vo funkcii return_specified_module_language sa kontroluje $current_language a ak neexistuje tak sa pouzije $default_language  
  $current_mod_strings = return_specified_module_language($current_language, "Users");    
else 
  $current_mod_strings = return_specified_module_language("en_us", "Users");

$b = 0;

foreach ($user_block_info_arr AS $block_label => $block_fields)
{
    $b++;
    
    // $UserOptions = array();
    if (isset($current_mod_strings[$block_label]) AND $current_mod_strings[$block_label] != "")
         $optgroup_value = $current_mod_strings[$block_label];
    elseif (isset($app_strings[$block_label]) AND $app_strings[$block_label] != "")
         $optgroup_value = $app_strings[$block_label];  
    elseif(isset($mod_strings[$block_label]) AND $mod_strings[$block_label]!="")
         $optgroup_value = $mod_strings[$block_label];
    else
         $optgroup_value = $block_label;  
        
    // $UserOptgroupts[] = '"'.$optgroup_value.'","'.$b.'"';
    
    if (count($block_fields) > 0)
    {
         $field_ids = implode(",",$block_fields);
        
         $sql1 = "SELECT * FROM vtiger_field WHERE fieldid IN (".$field_ids.")";
         $result1 = $adb->query($sql1); 
        
         while($row1 = $adb->fetchByAssoc($result1))
         {
        	   $fieldname = $row1['fieldname'];
        	   $fieldlabel = $row1['fieldlabel'];
        	   
     	       $option_key = strtolower("Users"."-".$fieldname);
        	   
             if (isset($current_mod_strings[$fieldlabel]) AND $current_mod_strings[$fieldlabel] != "")
                 $option_value = $current_mod_strings[$fieldlabel];
             elseif (isset($app_strings[$fieldlabel]) AND $app_strings[$fieldlabel] != "")
                 $option_value = $app_strings[$fieldlabel];  
             else
                 $option_value = $fieldlabel;  
                 
        	   // $UserOptions[] = '"'.$option_value.'","'.$option_key.'"';
        	   // $SelectUserModuleFields[$optgroup_value][$option_key] = $option_value;
             $User_Info[$optgroup_value]["s-".$option_key] = $option_value;
             $Logged_User_Info[$optgroup_value]["l-".$option_key] = $option_value;
         }             
    }
    
    //variable RECORD ID added
    if($b==1)
    {
      $option_value = "Record ID";
      $option_key = strtolower("USERS-CRMID");
      // $UserOptions[] = '"'.$option_value.'","'.$option_key.'"';
      // $SelectUserModuleFields[$optgroup_value][$option_key] = $option_value;
      $User_Info[$optgroup_value]["s-".$option_key] = $option_value;
      $Logged_User_Info[$optgroup_value]["l-".$option_key] = $option_value;
    }        
    //end
    
    // $Convert_RelatedUserFields["Users|b] = implode(",",$UserOptions);
    // $Convert_UserFields["Users|".$b] = implode(",",$UserOptions);
}
    
// $Convert_UserBlocks["Users"] = implode(",",$UserOptgroupts);
    
// $smarty->assign("USER_BLOCKS",$UserOptgroupts);
// $smarty->assign("USERS_FIELDS",$SelectUserModuleFields);    


// echo "<pre>";
// print_r($current_mod_strings);
// print_r($UserOptions);

// print_r($user_block_info_arr);
// print_r($SelectUserModuleFields);

// print_r($Convert_RelatedUserFields);
// print_r($Convert_UserFields);
// print_r($User_Info);
// print_r($Logged_User_Info);
// print_r($Convert_UserBlocks);
// print_r($CUI_BLOCKS);
// echo "</pre>";
          
if(file_exists("modules/Users/language/$default_language.lang.php")){ 
	$user_mod_strings = return_specified_module_language($current_language, "Users");
} else {
	$user_mod_strings = return_specified_module_language("en_us", "Users");
}

$smarty->assign("USERINFORMATIONS",$User_Info);

$smarty->assign("LOGGEDUSERINFORMATION",$Logged_User_Info);          
// ****************************************** END: Company and User information **********************************

$Invterandcon = array(""=>$mod_strings["LBL_PLS_SELECT"],
                      "terms-and-conditions"=>$mod_strings["LBL_TERMS_AND_CONDITIONS"]);

$smarty->assign("INVENTORYTERMSANDCONDITIONS",$Invterandcon); 


$Article_Strings = array(""=>$mod_strings["LBL_PLS_SELECT"],
                         "PRODUCTBLOC_START"=>$mod_strings["LBL_ARTICLE_START"],
                         "PRODUCTBLOC_END"=>$mod_strings["LBL_ARTICLE_END"]
                        );

$smarty->assign("ARTICLE_STRINGS",$Article_Strings);


//PDF MARGIN SETTINGS
$s_sql = "SELECT * FROM vtiger_emakertemplates_settings"; 
$s_result = $adb->query($s_sql);
$emailtemplateSResult = $adb->fetch_array($s_result);

$Decimals = array("point"=>$emailtemplateSResult["decimal_point"],
"decimals"=>$emailtemplateSResult["decimals"],
"thousands"=>($emailtemplateSResult["thousands_separator"]!="sp" ? $emailtemplateSResult["thousands_separator"] : " "));

$smarty->assign("DECIMALS",$Decimals);

$dateVariables = array(//"##TIMESTAMP##"=>$mod_strings["LBL_TIMESTAMP"],
                       "##DD.MM.YYYY##"=>$mod_strings["LBL_DATE_DD.MM.YYYY"],
                       "##DD-MM-YYYY##"=>$mod_strings["LBL_DATE_DD-MM-YYYY"],
                       "##DD/MM/YYYY##"=>$mod_strings["LBL_DATE_DD/MM/YYYY"],
                       "##MM-DD-YYYY##"=>$mod_strings["LBL_DATE_MM-DD-YYYY"],
                       "##MM/DD/YYYY##"=>$mod_strings["LBL_DATE_MM/DD/YYYY"],
                       "##YYYY-MM-DD##"=>$mod_strings["LBL_DATE_YYYY-MM-DD"]);
                     
$smarty->assign("DATE_VARS",$dateVariables);

//Ignored picklist values
$pvsql="SELECT value FROM vtiger_emakertemplates_ignorepicklistvalues";
$pvresult = $adb->query($pvsql);
$pvvalues="";
while($pvrow=$adb->fetchByAssoc($pvresult))
  $pvvalues.=$pvrow["value"].", ";
$smarty->assign("IGNORE_PICKLIST_VALUES",rtrim($pvvalues, ", "));

$Product_Fields = array("PRODUCTPOSITION"=>$mod_strings["LBL_PRODUCT_POSITION"],
                        "CURRENCYNAME"=>$mod_strings["LBL_CURRENCY_NAME"],
                        "CURRENCYCODE"=>$mod_strings["LBL_CURRENCY_CODE"],
                        "CURRENCYSYMBOL"=>$mod_strings["LBL_CURRENCY_SYMBOL"],
                        "PRODUCTNAME"=>$mod_strings["LBL_VARIABLE_PRODUCTNAME"],
                        "PRODUCTTITLE"=>$mod_strings["LBL_VARIABLE_PRODUCTTITLE"],
                        "PRODUCTDESCRIPTION"=>$mod_strings["LBL_VARIABLE_PRODUCTDESCRIPTION"],
                        "PRODUCTEDITDESCRIPTION"=>$mod_strings["LBL_VARIABLE_PRODUCTEDITDESCRIPTION"],                                                           
                        "PRODUCTQUANTITY"=>$mod_strings["LBL_VARIABLE_QUANTITY"],
                        "PRODUCTUSAGEUNIT"=>$mod_strings["LBL_VARIABLE_USAGEUNIT"],                                                           
                        "PRODUCTLISTPRICE"=>$mod_strings["LBL_VARIABLE_LISTPRICE"],
                        "PRODUCTTOTAL"=>$mod_strings["LBL_PRODUCT_TOTAL"],
                        "PRODUCTDISCOUNT"=>$mod_strings["LBL_VARIABLE_DISCOUNT"],
                        "PRODUCTDISCOUNTPERCENT"=>$mod_strings["LBL_VARIABLE_DISCOUNT_PERCENT"],
                        "PRODUCTSTOTALAFTERDISCOUNT"=>$mod_strings["LBL_VARIABLE_PRODUCTTOTALAFTERDISCOUNT"],
                        "PRODUCTVATPERCENT"=>$mod_strings["LBL_PROCUCT_VAT_PERCENT"],
                        "PRODUCTVATSUM"=>$mod_strings["LBL_PRODUCT_VAT_SUM"],
                        "PRODUCTTOTALSUM"=>$mod_strings["LBL_PRODUCT_TOTAL_VAT"]);
                        
$smarty->assign("SELECT_PRODUCT_FIELD",$Product_Fields);

if ($select_module == "Quotes" || $select_module == "SalesOrder" || $select_module == "Invoice" || $select_module == "PurchaseOrder" || $select_module == "Issuecards" || $select_module == "Receiptcards")
   $display_product_div = "block";
else
   $display_product_div = "none";

$smarty->assign("DISPLAY_PRODUCT_DIV",$display_product_div);

foreach ($ModuleIDS as $module => $module_tabid) 
{
   $EMAILMaker->createModuleFields($module,$module_tabid);
}
 
// ITS4YOU-CR VlZa
//Oprava prazdneho selectboxu v pripade ze zvoleny modul nemal ziadne related moduly
foreach($Modulenames as $key=>$value)
{
	if(!isset($EMAILMaker->All_Related_Modules[$key]))
		$EMAILMaker->All_Related_Modules[$key]=array();
}
// ITS4YOU-END

$Related_Data = $EMAILMaker->getRelatedData();

$smarty->assign("ALL_RELATED_MODULES",$Related_Data[0]);

$smarty->assign("MODULE_RELATED_FIELDS",$Related_Data[1]);



$Related_Modules = array(); 
if ($select_module != "")
{			                  
    foreach ($EMAILMaker->All_Related_Modules[$select_module] AS $rel_data)
    {		                  
         $Related_Modules[$rel_data["fieldlabel"]][$rel_data["module"]."--".$rel_data["fieldname"]] = $rel_data["modulelabel"];
    }
}

$smarty->assign("RELATED_MODULES",$Related_Modules);

$tacModules = array();
$tac4you = is_numeric(getTabId("Tac4you")); 
if($tac4you == true)
{
  $sql = "SELECT tac4you_module FROM vtiger_tac4you_module WHERE presence = 1";
  $result = $adb->query($sql);  
  while($row = $adb->fetchByAssoc($result))  
    $tacModules[$row["tac4you_module"]] = $row["tac4you_module"];  
}
                        
// print_r($ModuleFields);
$EMAILMaker->convertModuleFields();

if(isset($ModuleSorces))
	$smarty->assign("MODULESORCES",$ModuleSorces);
  
$smarty->assign("MODULE_BLOCKS",$EMAILMaker->Convert_ModuleBlocks);

$smarty->assign("RELATED_MODULE_FIELDS",$EMAILMaker->Convert_RelatedModuleFields);

$smarty->assign("MODULE_FIELDS",$EMAILMaker->Convert_ModuleFields);


//EMAIL SUBJECT FIELDS
$smarty->assign("SUBJECT_FIELDS",$EMAILMaker->getSubjectFields());

// ITS4YOU-CR VlZa
// Product bloc templates
$sql="SELECT * FROM vtiger_emakertemplates_productbloc_tpl";
$result=$adb->query($sql);
$Productbloc_tpl[""]=$mod_strings["LBL_PLS_SELECT"];
while($row=$adb->fetchByAssoc($result))
{
  $Productbloc_tpl[$row["body"]]=$row["name"];  
}                 
$smarty->assign("PRODUCT_BLOC_TPL",$Productbloc_tpl);

$smarty->assign("PRODUCTS_FIELDS",$EMAILMaker->SelectModuleFields["Products"]);
$smarty->assign("SERVICES_FIELDS",$EMAILMaker->SelectModuleFields["Services"]);
// ITS4YOU-END

if ($templateid != "" || $select_module!="")
{
    $smarty->assign("SELECT_MODULE_FIELD",$EMAILMaker->SelectModuleFields[$select_module]);
    $smf_filename = $EMAILMaker->SelectModuleFields[$select_module];
	if($select_module=="Invoice" || $select_module=="Quotes" || $select_module=="SalesOrder" || $select_module=="PurchaseOrder" || $select_module=="Issuecards" || $select_module=="Receiptcards" || $select_module == "Creditnote" || $select_module == "StornoInvoice")
		unset($smf_filename["Details"]);
	$smarty->assign("SELECT_MODULE_FIELD_SUBJECT",$smf_filename); 
}

//Sharing
$template_owners = get_user_array(false);
$smarty->assign("TEMPLATE_OWNERS", $template_owners);
$smarty->assign("TEMPLATE_OWNER", $owner);

$sharing_types = Array("public"=>$app_strings["PUBLIC_FILTER"],
                       "private"=>$app_strings["PRIVATE_FILTER"],
                       "share"=>$app_strings["SHARE_FILTER"]);
$smarty->assign("SHARINGTYPES", $sharing_types);
$smarty->assign("SHARINGTYPE", $sharingtype);

$cmod = return_specified_module_language($current_language, "Settings");
$smarty->assign("CMOD", $cmod);
//Constructing the Role Array
$roleDetails=getAllRoleDetails();
$i=0;
$roleIdStr="";
$roleNameStr="";
$userIdStr="";
$userNameStr="";
$grpIdStr="";
$grpNameStr="";

foreach($roleDetails as $roleId=>$roleInfo) {
	if($i !=0) {
		if($i !=1) {
			$roleIdStr .= ", ";
			$roleNameStr .= ", ";
		}
		$roleName=$roleInfo[0];
		$roleIdStr .= "'".$roleId."'";
		$roleNameStr .= "'".addslashes(decode_html($roleName))."'";
	}
	$i++;
}
//Constructing the User Array
$l=0;
$userDetails=getAllUserName();
foreach($userDetails as $userId=>$userInfo) {
	if($l !=0){
		$userIdStr .= ", ";
		$userNameStr .= ", ";
	}
	$userIdStr .= "'".$userId."'";
	$userNameStr .= "'".$userInfo."'";
	$l++;
}
//Constructing the Group Array
$parentGroupArray = array();

$m=0;
$grpDetails=getAllGroupName();
foreach($grpDetails as $grpId=>$grpName) {
	if(! in_array($grpId,$parentGroupArray)) {
		if($m !=0) {
			$grpIdStr .= ", ";
			$grpNameStr .= ", ";
		}
		$grpIdStr .= "'".$grpId."'";
		$grpNameStr .= "'".addslashes(decode_html($grpName))."'";
        $m++;
	}
}
$smarty->assign("ROLEIDSTR",$roleIdStr);
$smarty->assign("ROLENAMESTR",$roleNameStr);
$smarty->assign("USERIDSTR",$userIdStr);
$smarty->assign("USERNAMESTR",$userNameStr);
$smarty->assign("GROUPIDSTR",$grpIdStr);
$smarty->assign("GROUPNAMESTR",$grpNameStr);

if(count($sharingMemberArray) > 0)
{
    $outputMemberArr = array();
    foreach($sharingMemberArray as $setype=>$shareIdArr)
    {
        foreach($shareIdArr as $shareId)
        {
            switch($setype)
            {
                case "groups":
                    $memberName=fetchGroupName($shareId);
				    $memberDisplay="Group::";
				    break;

                case "roles":
                    $memberName=getRoleName($shareId);
				    $memberDisplay="Roles::";
				    break;

				case "rs":
                    $memberName=getRoleName($shareId);
				    $memberDisplay="RoleAndSubordinates::";
				    break;

				case "users":
                    $memberName=getUserName($shareId);
				    $memberDisplay="User::";
				    break;
            }

            $outputMemberArr[] = $setype."::".$shareId;
            $outputMemberArr[] = $memberDisplay.$memberName;
        }
    }
    $smarty->assign("MEMBER", array_chunk($outputMemberArr,2));
}
//Sharing End

//Default From
$selected_default_from = "";
if ($templateid != "")
{
    $sql_lfn = "SELECT fieldname FROM vtiger_emakertemplates_default_from WHERE templateid = ? AND userid = ?";
    $result_lfn = $adb->pquery($sql_lfn, array($templateid, $current_user->id));
    $num_rows_lfn = $adb->num_rows($result_lfn); 
    
    if ($num_rows_lfn > 0)
        $selected_default_from = $adb->query_result($result_lfn,0,"fieldname");
}

$Default_From_Options = array("" => $app_strings["LBL_NONE"]);

$sql_a="select * from vtiger_systems where from_email_field != ? AND server_type = ?";
$result_a = $adb->pquery($sql_a, array('','email'));
$from_email_field = $adb->query_result($result_a,0,"from_email_field");   

if($from_email_field != "")
{
    $sql2="select * from vtiger_organizationdetails where organizationname != ''";
    $result2 = $adb->pquery($sql2, array());

    while($row2 = $adb->fetchByAssoc($result2))
    {
        $Default_From_Options["0_organization_email"] = $mod_strings["LBL_COMPANY_EMAIL"]." <".$from_email_field.">";
    }
}



$sql_fm = "SELECT fieldname, fieldlabel FROM vtiger_field WHERE tabid= 29 AND uitype IN (104,13) ORDER BY fieldid ASC ";
$result_fm = $adb->query($sql_fm);

while($row_fm = $adb->fetchByAssoc($result_fm))
{
	if ($current_user->column_fields[$row_fm['fieldname']] != "")
    {
        $from_name_user_email = getTranslatedString($row_fm['fieldlabel'],"Users");
        $from_name_user_email .= " <".$current_user->column_fields[$row_fm['fieldname']].">";
        
        $Default_From_Options["1_".$row_fm['fieldname']] = $from_name_user_email;
    }
    
}

$smarty->assign("SELECTED_DEFAULT_FROM", $selected_default_from);
$smarty->assign("DEFAULT_FROM_OPTIONS", $Default_From_Options);
//Default From End


if ($_REQUEST["test"] && $_REQUEST["test"] != "")
{
    $test_module = addslashes($_REQUEST["test"]);
    
    $test_body = "<center><h2><b>Test ".$test_module."</b></h2></center>";
    
    if (isset($EMAILMaker->SelectModuleFields[$test_module]))
    {
        foreach ($EMAILMaker->SelectModuleFields[$test_module] AS $block => $Fields)
        {
            $test_body .= "<h3><b>".$block."</b></h3>";
            
            foreach ($Fields AS $key => $name)
            {
                $test_body .= '<u>'.$name.':</u> $s-'.$key.'$<br>';
            }
        } 
    }
    else
    {
        $test_body .= "module ".$test_module." not exist";    
    }
    
    if (count($EMAILMaker->All_Related_Modules[$test_module]) > 0)
    {
        foreach ($EMAILMaker->All_Related_Modules[$test_module] AS $RData)
        {
            $test_body .= "<center><h2><b>Related module ".$RData["modulelabel"]."</b></h2></center>";
        
            foreach ($EMAILMaker->SelectModuleFields[$RData["module"]] AS $block => $Fields)
            {
                $test_body .= "<h3><b>".$block."</b></h3>";
                
                foreach ($Fields AS $key => $name)
                {
                    $test_body .= '<u>'.$name.':</u> $r-'.$RData["fieldname"].'-'.$key.'$<br>';
                }
            }
        }
    }
        
    $smarty->assign("FILENAME", "test ".$test_module);
    $smarty->assign("BODY", $test_body);
}

if (count($EMAILMaker->All_Related_Modules[$select_module]) > 0)
{
    $body_variables_display = "table-row";
}
else
{
    $body_variables_display = "none";
}

$smarty->assign("BODY_VARIABLES_DISPLAY", $body_variables_display);


$ListView_Block = array(""=>$mod_strings["LBL_PLS_SELECT"],
                         "LISTVIEWBLOCK_START"=>$mod_strings["LBL_ARTICLE_START"],
                         "LISTVIEWBLOCK_END"=>$mod_strings["LBL_ARTICLE_END"],
                         "CRIDX"=>$mod_strings["LBL_COUNTER"],
                        );

$smarty->assign("LISTVIEW_BLOCK_TPL",$ListView_Block);

$smarty->assign("VIEW_TYPE", "EmailTemplates"); 
$smarty->assign("VIEW_CONTENT", "EditEmailTemplate");

$smarty = $EMAILMaker->actualizeSmarty($smarty);

//EMAIL STATUS SETTINGS
$Status = array("1"=>$app_strings["Active"],
                "0"=>$app_strings["Inactive"]);
$smarty->assign("STATUS",$Status);
$smarty->assign("IS_ACTIVE",$is_active);
if($is_active=="0")
{
    $smarty->assign("IS_DEFAULT_DV_CHECKED",'disabled="disabled"');
	$smarty->assign("IS_DEFAULT_LV_CHECKED",'disabled="disabled"');
}
elseif($is_default > 0)
{
    $is_default_bin = str_pad(base_convert($is_default, 10, 2), 2, "0", STR_PAD_LEFT);
    $is_default_lv = substr($is_default_bin, 0, 1);
    $is_default_dv = substr($is_default_bin, 1, 1);
    if($is_default_lv == "1")
        $smarty->assign("IS_DEFAULT_LV_CHECKED",'checked="checked"');
    if($is_default_dv == "1")
        $smarty->assign("IS_DEFAULT_DV_CHECKED",'checked="checked"');
}

$smarty->display('modules/EMAILMaker/EMAILMaker.tpl');

?>
