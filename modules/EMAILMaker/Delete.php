<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the 
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Accounts/Delete.php,v 1.5 2005/03/10 09:28:34 shaw Exp $
 * Description:  Deletes an Account record and then redirects the browser to the 
 * defined return URL.
 ********************************************************************************/
global $adb;
global $upload_maxsize;
global $theme,$default_charset;
global $current_language;
global $site_URL;
global $currentModule;
$focus = CRMEntity::getInstance($currentModule);

global $mod_strings;

require_once('include/logging.php');
$log = LoggerManager::getLogger('emailmaker_delete');

require_once('modules/EMAILMaker/EMAILMaker.php');


$EMAILMaker = new EmailMaker(); 

if($EMAILMaker->CheckPermissions("DETAIL") == false)
  $EMAILMaker->DieDuePermission();

if($EMAILMaker->CheckPermissions("DELETE") && $EMAILMaker->GetVersionType() != "deactivate" ) 
{
    if (isset($_REQUEST['dripid']) && $_REQUEST['dripid'] != "")
    {
        $dripid = $_REQUEST['dripid'];
        
        if (isset($_REQUEST['driptplid']) && $_REQUEST['driptplid'] != "")
        {
            $driptplid = $_REQUEST['driptplid'];
            
            $sql = "UPDATE vtiger_emakertemplates_drip_tpls SET deleted = '1' WHERE driptplid=?";
        	$adb->pquery($sql, array($driptplid));
            
            header("Location:index.php?module=EMAILMaker&action=DetailViewDripEmails&dripid=".$dripid."&parenttab=Tools");
            exit;
        } 
        else
        {
            $sql = "UPDATE vtiger_emakertemplates_drips SET deleted = '1' WHERE dripid=?";
        	$adb->pquery($sql, array($dripid));
            
            $sql = "UPDATE vtiger_emakertemplates_drip_tpls SET deleted = '1' WHERE dripid=?";
        	$adb->pquery($sql, array($dripid));
        }    
    }
    else
    {
        $idlist = $_REQUEST['idlist'];
        
        $id_array=explode(';', $idlist);
        
        for($i=0; $i < count($id_array)-1; $i++) {
        	$sql = "UPDATE vtiger_emakertemplates_drips SET deleted = '1' WHERE dripid=?";
        	$adb->pquery($sql, array($id_array[$i]));
        	
        	$sql = "UPDATE vtiger_emakertemplates_drip_tpls SET deleted = '1' WHERE dripid=?";
        	$adb->pquery($sql, array($id_array[$i]));
        }
    }
}

header("Location:index.php?module=EMAILMaker&action=ListDripEmails&parenttab=Tools");
?>