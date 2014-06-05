<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/com_vtiger_workflow/VTSimpleTemplate.inc');

require_once('modules/PDFMaker/PDFMaker.php');
require_once("modules/PDFMaker/mpdf/mpdf.php");

class VTPDFMakerTask extends VTTask {

    public $executeImmediately = true;

    public function getFieldNames() {
        return array('title', 'description', 'folder', 'template', 'template_language');
    }

    function getAdmin() {
        $user = new Users();
        $user->retrieveCurrentUserInfoFromFile(1);
        global $current_user;
        $this->originalUser = $current_user;
        $current_user = $user;
        return $user;
    }

    public function doTask($entityData) {
        global $adb, $current_user, $log, $root_directory;

        $userId = $entityData->get('assigned_user_id');
        if ($userId === null) {
            $userId = vtws_getWebserviceEntityId('Users', 1);
            ;
        }

        $moduleName = $entityData->getModuleName();
        $adminUser = $this->getAdmin();
        $id = $entityData->getId();

        list($id2, $assigned_user_id) = explode("x", $userId);
        list($id3, $parentid) = explode("x", $id);

        require_once('modules/Documents/Documents.php');
        $focus = new Documents();

        $focus->parentid = $parentid;

        $modFocus = CRMEntity::getInstance($moduleName);
        if (isset($focus->parentid)) {
            $modFocus->retrieve_entity_info($focus->parentid, $moduleName);
            $modFocus->id = $focus->parentid;
        }

        $templateid = $this->template;

        $foldername = $adb->getOne("SELECT foldername FROM vtiger_attachmentsfolder WHERE folderid='" . $this->folder . "'", 0, "foldername");

        $fieldname = $adb->getOne("SELECT fieldname FROM vtiger_field WHERE uitype=4 AND tabid=" . getTabId($moduleName), 0, "fieldname");
//     require_once("modules/PDFMaker/InventoryPDF.php");
        require_once("modules/PDFMaker/PDFMakerUtils.php");
        if (isset($modFocus->column_fields[$fieldname]) && $modFocus->column_fields[$fieldname] != "") {
            $file_name = generate_cool_uri($foldername . "_" . $modFocus->column_fields[$fieldname]) . ".pdf";
        } else {
            $file_name = generate_cool_uri($foldername . "_" . $templateid . $focus->parentid . date("ymdHi")) . ".pdf";
        }

        $focus->column_fields['notes_title'] = $this->title;
        $focus->column_fields['assigned_user_id'] = $assigned_user_id;
        $focus->column_fields['filename'] = $file_name;
        $focus->column_fields['notecontent'] = $this->description;
        $focus->column_fields['filetype'] = 'application/pdf';
        $focus->column_fields['filesize'] = '';
        $focus->column_fields['filelocationtype'] = 'I';
        $focus->column_fields['fileversion'] = '';
        $focus->column_fields['filestatus'] = 'on';
        $focus->column_fields['folderid'] = $this->folder;

        $focus->save('Documents');

        $language = $this->template_language;
        createPDFAndSaveFile($templateid, $focus, $modFocus, $file_name, $moduleName, $language);
    }

}

?>
