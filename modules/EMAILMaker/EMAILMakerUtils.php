<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 
function getEmailToAdressat($mycrmid,$temp, $pmodule = "")
{
    global $adb;
    
    if ($temp == "-1")
    {
        $emailadd = getUserEmail($mycrmid);
    }
    else
    {
        if ($pmodule == "") $pmodule=getSalesEntityType($mycrmid);
    
    	$myquery='Select columnname from vtiger_field where fieldid = ? and vtiger_field.presence in (0,2)';
    	$fresult=$adb->pquery($myquery, array($temp));			
    	if ($pmodule=='Contacts')
    	{
    		require_once('modules/Contacts/Contacts.php');
    		$myfocus = new Contacts();
    		$myfocus->retrieve_entity_info($mycrmid,"Contacts");
    	}
    	elseif ($pmodule=='Accounts')
    	{
    		require_once('modules/Accounts/Accounts.php');
    		$myfocus = new Accounts();
    		$myfocus->retrieve_entity_info($mycrmid,"Accounts");
    	} 
    	elseif ($pmodule=='Leads')
    	{
    		require_once('modules/Leads/Leads.php');
    		$myfocus = new Leads();
    		$myfocus->retrieve_entity_info($mycrmid,"Leads");
    	}
    	elseif ($pmodule=='Vendors')
        {
                require_once('modules/Vendors/Vendors.php');
                $myfocus = new Vendors();
                $myfocus->retrieve_entity_info($mycrmid,"Vendors");
        }
        else 
        {
        	// vtlib customization: Enabling mail send from other modules
        	$myfocus = CRMEntity::getInstance($pmodule);
        	$myfocus->retrieve_entity_info($mycrmid, $pmodule);
        	// END
        }
    	$fldname=$adb->query_result($fresult,0,"columnname");
    	$emailadd=br2nl($myfocus->column_fields[$fldname]);
    }
    return $emailadd;
}

function getCountEmailsStatus($esentid)
{
    global $adb;
    $sql = "SELECT count(emailid) as total FROM vtiger_emakertemplates_emails WHERE status = '1' AND esentid = '".$esentid."'";
    $result = $adb->query($sql);
    
    return $adb->query_result($result,0,"total");  
}

function saveDocumentsIntoEmail($id,$documents)
{
    global $adb;
    $Documents = explode(",",$documents);
    
    foreach ($Documents AS $document_id)
    {
        if ($document_id!="")
        {
            $sql1='replace into vtiger_seattachmentsrel values(?,?)';
            $adb->pquery($sql1, array($id, $document_id));
        }
    }
}

function SaveAttachmentIntoEmail($id,$file_name,$filetype,$filetmp_name)
{
	global $adb, $current_user;

	$date_var = date("Y-m-d H:i:s");

	$ownerid = $current_user->id;

	$current_id = $adb->getUniqueID("vtiger_crmentity");

	$filename = ltrim(basename(" ".$file_name)); 
	$upload_file_path = decideFilePath();

    if (copy($filetmp_name, $upload_file_path.$current_id."_".$file_name)) 
    {
        $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
    	$params1 = array($current_id, $current_user->id, $ownerid, "Email Attachment", "", $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));		
    
    	$adb->pquery($sql1, $params1);
    
    	$sql2="insert into vtiger_attachments(attachmentsid, name, type, path) values(?, ?, ?, ?)";
    	$params2 = array($current_id, $filename, $filetype, $upload_file_path);
    	$result=$adb->pquery($sql2, $params2);
    
    	$sql3='replace into vtiger_seattachmentsrel values(?,?)';
    	$adb->pquery($sql3, array($id, $current_id));
    }
}

function checkIfContactExists($mailid)
{
	global $log;
	$log->debug("Entering checkIfContactExists(".$mailid.") method ...");
	global $adb;
	$sql = "select contactid from vtiger_contactdetails inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid where vtiger_crmentity.deleted=0 and email= ?";
	$result = $adb->pquery($sql, array($mailid));
	$numRows = $adb->num_rows($result);
	if($numRows > 0)
	{
		$log->debug("Exiting checkIfContactExists method ...");
		return $adb->query_result($result,0,"contactid");
	}
	else
	{
		$log->debug("Exiting checkIfContactExists method ...");
		return -1;
	}
} 
 
function getEmailRelFieldByReportLabel($module, $label) {
	
	// this is required so the internal cache is populated or reused.
	getColumnFields($module);
	//lookup all the accessible fields
	$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
	if(empty($cachedModuleFields)) {
		return null;
	}
	foreach ($cachedModuleFields as $fieldInfo) {
		$fieldLabel = str_replace(' ', '_', $fieldInfo['fieldlabel']);
		if($label == $fieldLabel) {
			return $fieldInfo;
		}
	}
	return null;
}  
?>