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
require_once('modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc');
require_once('modules/Emails/mail.php');

class VTPDFMakerMailTask extends VTTask {

    // Sending email takes more time, this should be handled via queue all the time.
    public $executeImmediately = false;

    public function getFieldNames() {
        return array("subject", "content", "recepient", 'emailcc', 'emailbcc', 'template', 'template_language'); // ITS4YOU-CR PDF Maker
    }

    public function doTask($entity) {
        global $adb, $current_user;
        $util = new VTWorkflowUtils();

        $result = $adb->query("select user_name, email1, email2 from vtiger_users where id=1");
        $from_email = $adb->query_result($result, 0, 'email1');
        $from_name = $adb->query_result($result, 0, 'user_name');

        $admin = $util->adminUser();
        $module = $entity->getModuleName();

        $entityCache = new VTEntityCache($admin);

        $et = new VTEmailRecipientsTemplate($this->recepient);
        $to_email = $et->render($entityCache, $entity->getId());
        $ecct = new VTEmailRecipientsTemplate($this->emailcc);
        $cc = $ecct->render($entityCache, $entity->getId());
        $ebcct = new VTEmailRecipientsTemplate($this->emailbcc);
        $bcc = $ebcct->render($entityCache, $entity->getId());
        if (strlen(trim($to_email, " \t\n,")) == 0 && strlen(trim($cc, " \t\n,")) == 0 &&
                strlen(trim($bcc, " \t\n,")) == 0) {
            return;
        }

        $st = new VTSimpleTemplate($this->subject);
        $subject = $st->render($entityCache, $entity->getId());
        $ct = new VTSimpleTemplate($this->content);
        $content = $ct->render($entityCache, $entity->getId());

        // ITS4YOU-CR PDF Maker
        $templateid = $this->template;

        if ($templateid != "0" && $templateid != "") {
            require_once('modules/PDFMaker/PDFMaker.php');
            require_once("modules/PDFMaker/mpdf/mpdf.php");
//         require_once("modules/PDFMaker/InventoryPDF.php");
            require_once("modules/PDFMaker/PDFMakerUtils.php");

            list($id3, $id) = explode("x", $entity->getId());

            $modFocus = CRMEntity::getInstance($module);

            $modFocus->retrieve_entity_info($id, $module);
            $modFocus->id = $id;

            $result = $adb->query("SELECT fieldname FROM vtiger_field WHERE uitype=4 AND tabid=" . getTabId($module));
            $fieldname = $adb->query_result($result, 0, "fieldname");
            if (isset($modFocus->column_fields[$fieldname]) && $modFocus->column_fields[$fieldname] != "") {
                $file_name = generate_cool_uri($modFocus->column_fields[$fieldname]) . ".pdf";
            } else {
                $file_name = $templateid . $focus->parentid . date("ymdHi") . ".pdf";
            }

            require_once('modules/Emails/Emails.php');
            require_once('include/logging.php');
            require_once('include/database/PearDatabase.php');


            $focus = new Emails();
            //assign the focus values
            $focus->filename = $file_name;
            $focus->column_fields["assigned_user_id"] = $current_user->id;
            $focus->column_fields["activitytype"] = "Emails";
            $focus->column_fields["subject"] = $subject;
            $focus->column_fields["description"] = $content;
            $focus->column_fields["date_start"] = date(getNewDisplayDate());

            $focus->save("Emails");

            $adb->pquery('insert into vtiger_seactivityrel values(?,?)', array($id, $focus->id));

            $to_email_up = '["' . str_replace(',', '","', trim($to_email, ",")) . '"]';
            $adb->query("UPDATE vtiger_emaildetails SET to_email = '" . $to_email_up . "' WHERE emailid = '" . $focus->id . "'");

            $language = $this->template_language;
            createPDFAndSaveFile($templateid, $focus, $modFocus, $file_name, $module, $language);

            send_mail($module, $to_email, $from_name, $from_email, $subject, $content, $cc, $bcc, 'all', $focus->id);
        } else {
            send_mail($module, $to_email, $from_name, $from_email, $subject, $content, $cc, $bcc);
        }
        // ITS4YOU-END

        $util->revertUser();
    }

}

?>