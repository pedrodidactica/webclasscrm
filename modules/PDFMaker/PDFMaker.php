<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
require_once("modules/PDFMaker/classes/Debugger.class.php");
require_once("include/nusoap/nusoap.php");

class PDFMaker extends CRMEntity {

    private $version_type;
    private $license_key;
    private $version_no;
    private $basicModules;
    private $pageFormats;
    private $profilesActions;
    private $profilesPermissions;
    var $log;
    var $db;

    // constructor of PDFMaker class
    function __construct() {
        $this->log = LoggerManager::getLogger('account');
        $this->db = PearDatabase::getInstance();

        $this->setLicenseInfo();
        // array of modules that are allowed for basic version type 		
        $this->basicModules = array("20", "21", "22", "23");
        // array of action names used in profiles permissions           
        $this->profilesActions = array("EDIT" => "EditView", // Create/Edit
            "DETAIL" => "DetailView", // View
            "DELETE" => "Delete", // Delete
            "EXPORT_RTF" => "Export", // Export to RTF
        );
        $this->profilesPermissions = array();
    }

    //Getters and Setters
    public function GetVersionType() {
        return $this->version_type;
    }

    public function GetLicenseKey() {
        return $this->license_key;
    }

    public function GetPageFormats() {
        return $this->pageFormats;
    }

    public function GetBasicModules() {
        return $this->basicModules;
    }

    public function GetProfilesActions() {
        return $this->profilesActions;
    }

    //PUBLIC METHODS SECTION
    //ListView data 
    public function GetListviewData($orderby = "templateid", $dir = "asc") {
        global $current_user, $mod_strings, $app_strings;

        $status_sql = "SELECT * FROM vtiger_pdfmaker_userstatus  
		             INNER JOIN vtiger_pdfmaker USING(templateid) 
		             WHERE userid=?";
        $status_res = $this->db->pquery($status_sql, array($current_user->id));
        $status_arr = array();
        while ($status_row = $this->db->fetchByAssoc($status_res)) {
            $status_arr[$status_row["templateid"]]["is_active"] = $status_row["is_active"];
            $status_arr[$status_row["templateid"]]["is_default"] = $status_row["is_default"];
            $status_arr[$status_row["templateid"]]["sequence"] = $status_row["sequence"];
        }

        $originOrderby = $orderby;
        $originDir = $dir;
        if ($orderby == "order") {
            $orderby = "module";
            $dir = "asc";
        }

        $sql = "SELECT templateid, description, filename, module 
		        FROM vtiger_pdfmaker 
		        ORDER BY " . $orderby . " " . $dir;
        $result = $this->db->pquery($sql, array());

        $return_data = Array();
        $num_rows = $this->db->num_rows($result);

        for ($i = 0; $i < $num_rows; $i++) {
            $currModule = $this->db->query_result($result, $i, 'module');
            $templateid = $this->db->query_result($result, $i, 'templateid');
            //in case of template module is not permitted for current user then skip it in list
            if ($this->CheckTemplatePermissions($currModule, $templateid, false) === false)
                continue;

            $pdftemplatearray = array();
            $suffix = "";

            if (isset($status_arr[$templateid])) {
                if ($status_arr[$templateid]["is_active"] == "0")
                    $pdftemplatearray['status'] = 0;
                else {
                    $pdftemplatearray['status'] = 1;
                    switch ($status_arr[$templateid]["is_default"]) {
                        case "1":
                            $suffix = " (" . $mod_strings["LBL_DEFAULT_NOPAR"] . " " . $mod_strings["LBL_FOR_DV"] . ")";
                            break;

                        case "2":
                            $suffix = " (" . $mod_strings["LBL_DEFAULT_NOPAR"] . " " . $mod_strings["LBL_FOR_LV"] . ")";
                            break;

                        case "3":
                            $suffix = " (" . $mod_strings["LBL_DEFAULT_NOPAR"] . ")";
                            break;
                    }
                }

                $pdftemplatearray['order'] = $status_arr[$templateid]["sequence"];
            } else {
                $pdftemplatearray['status'] = 1;
                $pdftemplatearray['order'] = 1;
            }


            $pdftemplatearray['status_lbl'] = ($pdftemplatearray['status'] == 1 ? $app_strings["Active"] : $app_strings["Inactive"]);

            $pdftemplatearray['templateid'] = $templateid;
            $pdftemplatearray['description'] = $this->db->query_result($result, $i, 'description');
            $pdftemplatearray['module'] = getTranslatedString($currModule);
            $pdftemplatearray['filename'] = "<a href=\"index.php?action=DetailViewPDFTemplate&module=PDFMaker&templateid=" . $templateid . "&parenttab=Tools\">" . $this->db->query_result($result, $i, 'filename') . $suffix . "</a>";
            if ($this->CheckPermissions("EDIT")) {
                $pdftemplatearray['edit'] = "<a href=\"index.php?action=EditPDFTemplate&module=PDFMaker&templateid=" . $templateid . "&parenttab=Tools\">" . $app_strings["LBL_EDIT_BUTTON"] . "</a> | "
                        . "<a href=\"index.php?action=EditPDFTemplate&module=PDFMaker&templateid=" . $templateid . "&isDuplicate=true&parenttab=Tools\">" . $app_strings["LBL_DUPLICATE_BUTTON"] . "</a>";
            }
            $return_data [] = $pdftemplatearray;
        }

        //in case of ordering the listview output according to order column we need to handle it manually
        if ($originOrderby == "order") {
            $modules = array();
            foreach ($return_data as $key => $templateArr)
                $modules[$templateArr["module"]][$key] = $templateArr["order"];

            $tmpArr = array();
            foreach ($modules as $orderArr) {
                if ($originDir == "asc")
                    asort($orderArr, SORT_NUMERIC);
                else
                    arsort($orderArr, SORT_NUMERIC);

                foreach ($orderArr as $rdIdx => $order)
                    $tmpArr[] = $return_data[$rdIdx];
            }
            $return_data = $tmpArr;
        }

        return $return_data;
    }

    //DetailView data
    public function GetDetailViewData($templateid) {
        global $mod_strings, $app_strings;
        $sql = "SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
			FROM vtiger_pdfmaker 
				LEFT JOIN vtiger_pdfmaker_settings ON vtiger_pdfmaker_settings.templateid = vtiger_pdfmaker.templateid
			WHERE vtiger_pdfmaker.templateid=?";

        $result = $this->db->pquery($sql, array($templateid));
        $pdftemplateResult = $this->db->fetch_array($result);

        $this->CheckTemplatePermissions($pdftemplateResult["module"], $templateid);

        $data = $this->getUserStatusData($templateid);
        if (count($data) > 0) {
            if ($data["is_active"] == "1") {
                $is_active = $app_strings["Active"];
                $activateButton = $mod_strings["LBL_SETASINACTIVE"];
            } else {
                $is_active = $app_strings["Inactive"];
                $activateButton = $mod_strings["LBL_SETASACTIVE"];
            }

            switch ($data["is_default"]) {
                case "0":
                    $is_default = $mod_strings["LBL_FOR_DV"] . '&nbsp;<img src="themes/images/no.gif" alt="no" />&nbsp;&nbsp;';
                    $is_default .= $mod_strings["LBL_FOR_LV"] . '&nbsp;<img src="themes/images/no.gif" alt="no" />';
                    $defaultButton = $mod_strings["LBL_SETASDEFAULT"];
                    break;

                case "1":
                    $is_default = $mod_strings["LBL_FOR_DV"] . '&nbsp;<img src="themes/images/yes.gif" alt="yes" />&nbsp;&nbsp;';
                    $is_default .= $mod_strings["LBL_FOR_LV"] . '&nbsp;<img src="themes/images/no.gif" alt="no" />';
                    $defaultButton = $mod_strings["LBL_UNSETASDEFAULT"];
                    break;

                case "2":
                    $is_default = $mod_strings["LBL_FOR_DV"] . '&nbsp;<img src="themes/images/no.gif" alt="no" />&nbsp;&nbsp;';
                    $is_default .= $mod_strings["LBL_FOR_LV"] . '&nbsp;<img src="themes/images/yes.gif" alt="yes" />';
                    $defaultButton = $mod_strings["LBL_UNSETASDEFAULT"];
                    break;

                case "3":
                    $is_default = $mod_strings["LBL_FOR_DV"] . '&nbsp;<img src="themes/images/yes.gif" alt="yes" />&nbsp;&nbsp;';
                    $is_default .= $mod_strings["LBL_FOR_LV"] . '&nbsp;<img src="themes/images/yes.gif" alt="yes" />';
                    $defaultButton = $mod_strings["LBL_UNSETASDEFAULT"];
                    break;
            }
        } else {
            $is_active = $app_strings["Active"];
            $activateButton = $mod_strings["LBL_SETASINACTIVE"];
            $is_default = $mod_strings["LBL_FOR_DV"] . '&nbsp;<img src="themes/images/no.gif" alt="no" />&nbsp;&nbsp;';
            $is_default .= $mod_strings["LBL_FOR_LV"] . '&nbsp;<img src="themes/images/no.gif" alt="no" />';
            $defaultButton = $mod_strings["LBL_SETASDEFAULT"];
        }

        $pdftemplateResult["is_active"] = $is_active;
        $pdftemplateResult["is_default"] = $is_default;
        $pdftemplateResult["activateButton"] = $activateButton;
        $pdftemplateResult["defaultButton"] = $defaultButton;
        $pdftemplateResult["templateid"] = $templateid;   // fix of empty templateid in case of NULL templateid in DB

        return $pdftemplateResult;
    }

    //EditView data
    public function GetEditViewData($templateid) {
        $sql = "SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
    			FROM vtiger_pdfmaker
    			LEFT JOIN vtiger_pdfmaker_settings ON vtiger_pdfmaker_settings.templateid = vtiger_pdfmaker.templateid
    			WHERE vtiger_pdfmaker.templateid=?";

        $result = $this->db->pquery($sql, array($templateid));
        $pdftemplateResult = $this->db->fetch_array($result);

        $data = $this->getUserStatusData($templateid);

        if (count($data) > 0) {
            $pdftemplateResult["is_active"] = $data["is_active"];
            $pdftemplateResult["is_default"] = $data["is_default"];
            $pdftemplateResult["order"] = $data["order"];
        } else {
            $pdftemplateResult["is_active"] = "1";
            $pdftemplateResult["is_default"] = "0";
            $pdftemplateResult["order"] = "1";
        }

        return $pdftemplateResult;
    }

    //function for getting the list of available user's templates
    public function GetAvailableTemplates($currModule, $forListView = false) {
        global $current_user;

        $where_lv = "";
        $is_listview = "";
        if ($forListView == false) {
            $where_lv = " AND is_listview=?";
            $is_listview = "0";
        }
        
        $status_sql = "SELECT templateid, is_active, is_default, sequence 
                        FROM vtiger_pdfmaker_userstatus
                        INNER JOIN vtiger_pdfmaker USING(templateid)
                        WHERE userid=?";
        $status_res = $this->db->pquery($status_sql, array($current_user->id));
        $status_arr = array();
        while ($status_row = $this->db->fetchByAssoc($status_res)) {
            $status_arr[$status_row["templateid"]]["is_active"] = $status_row["is_active"];
            $status_arr[$status_row["templateid"]]["is_default"] = $status_row["is_default"];
            $status_arr[$status_row["templateid"]]["sequence"] = $status_row["sequence"];
        }        

        $sql = "SELECT  templateid, filename                
                FROM vtiger_pdfmaker
                INNER JOIN vtiger_pdfmaker_settings USING ( templateid )                
                WHERE module=?" . $where_lv . "                                    
                ORDER BY filename, templateid";
        
        $params = array($currModule);
        if ($forListView == false)
            $params = array($currModule, $is_listview);

        $result = $this->db->pquery($sql, $params);
        $return_array = array();
        while ($row = $this->db->fetchByAssoc($result)) {
            $templateid = $row["templateid"];
            if ($this->CheckTemplatePermissions($currModule, $templateid, false) == false)
                continue;

            $pdftemplatearray = array();
            if (isset($status_arr[$templateid])) {
                $pdftemplatearray['status'] = $status_arr[$templateid]["is_active"];
                $pdftemplatearray['is_default'] = $status_arr[$templateid]["is_default"];
                $pdftemplatearray['order'] = $status_arr[$templateid]["sequence"];
            } else {
                $pdftemplatearray['status'] = "1";
                $pdftemplatearray['is_default'] = "0";
                $pdftemplatearray['order'] = "1";
            }

            if ($pdftemplatearray['status'] == "0")
                continue;

            $return_array[$row["templateid"]]["templatename"] = $row["filename"];
            $return_array[$row["templateid"]]["is_default"] = $pdftemplatearray["is_default"];
            $return_array[$row["templateid"]]["order"] = $pdftemplatearray["order"];
        }
//      when only one template is available for module, then set it as default
        if (count($return_array) == 1) {
            $tmp_arr = $return_array;
            reset($tmp_arr);
            $key = key($tmp_arr);
            $return_array[$key]["templatename"] = $tmp_arr[$key]["templatename"];
            $return_array[$key]["is_default"] = "3";
        } elseif(count($return_array) > 1) { //handle sorting if there is more than one template             
            $sortedArr = array();
            foreach ($return_array as $key => $templateArr)
                $sortedArr[$key] = $templateArr["order"]; 
            
            asort($sortedArr, SORT_NUMERIC);
            
            $tmpArr = array();
            foreach ($sortedArr as $rdIdx => $order)
                $tmpArr[$rdIdx] = $return_array[$rdIdx];
            
            $return_array = $tmpArr;
        }        
        return $return_array;
    }

    //function for getting allowed modules for an EditView
    //It returns two variables: array of modulenames
    //                          array of moduleids    
    public function GetAllModules() {
        $x13 = "f\x69\x6c\x65\x5fe\170\x69s\x74\163";
        $x14 = "i\x6e_\141\162\162a\x79";
        $x0b = "\x6d\x6f\144_\x73tri\x6e\x67s";
        global $$x0b;
        $x0c = $$x0b;
        $x0d = Array('' => $x0c["L\102L_\120LS_SE\x4cE\103\124"]);
        $x0e = '10, 28';
        if ($x14($_SESSION['VTIGER_DB_VERSION'], array('5.1.0', '5.2.0')))
            $x0e .= ', 9, 16';$x0f = "\x53\105L\105\103\124 \164\x61b\x69\x64\x2c \156\141\x6d\x65 \015
        \011\x09	\106R\x4f\x4d\040vt\151\x67\145\162_tab\x20\015
        \x09		\x57\110\105\x52\105 i\163\x65\156tit\171t\171\160\145\x3d\061\040\015\012	\x09	\011A\x4e\104\040\x70r\x65se\156c\145\x3d\x30\040\015
        \x09		\011\x41\x4eD\040t\141bi\144 \x4e\117T\040\x49\x4e\x20\050$x0e\x29\040\015\012	\x09\x09O\x52\x44\105\122 B\x59 \x6ea\155\x65\x20A\x53C";
        $x10 = $this->db->query($x0f);
        while ($x11 = $this->db->fetchByAssoc($x10)) {
            if ($x13("mo\144\165\x6c\145\x73/" . $x11['name'])) {
                if (isPermitted($x11['name'], '') != "\171e\x73")
                    continue;if ($x14($x11["\x74\x61\142\x69\144"], $this->basicModules) == true || $this->version_type == "p\162\157f\145\163\163\151o\156\x61\x6c")
                    $x0d[$x11['name']] = getTranslatedString($x11['name']);$x12[$x11['name']] = $x11['tabid'];
            }
        }return array($x0d, $x12);
    }

    //function for getting the mPDF object that contains prepared HTML output
    //returns the name of output filename - the file can be generated by calling mPDF->Output(..) method    
    public function GetPreparedMPDF(&$mpdf, $records, $templates, $module, $language, $preContent = "") {
        require_once("modules/PDFMaker/mpdf/mpdf.php");
        global $app_strings;

        $focus = CRMEntity::getInstance($module);
        $TemplateContent = array();
        $name = '';
        foreach ($records as $record) {
            foreach ($focus->column_fields as $cf_key => $cf_value) {
                $focus->column_fields[$cf_key] = '';
            }
            if ($module == 'Calendar') {
                $cal_res = $this->db->pquery("select activitytype from vtiger_activity where activityid=?", array($record));
                $cal_row = $this->db->fetchByAssoc($cal_res);
                if ($cal_row['activitytype'] == 'Task')
                    $focus->retrieve_entity_info($record, $module);
                else
                    $focus->retrieve_entity_info($record, 'Events');
            }
            else
                $focus->retrieve_entity_info($record, $module);
            $focus->id = $record;

            foreach ($templates AS $templateid) {
                $PDFContent = $this->GetPDFContentRef($templateid, $module, $focus, $language);

                $Settings = $PDFContent->getSettings();
                if ($name == "")
                    $name = $PDFContent->getFilename();

                //if current template is not available for current user then set the content
                if ($this->CheckTemplatePermissions($module, $templateid, false) == false) {
                    $header_html = "";
                    $body_html = $app_strings["LBL_PERMISSION"];
                    $footer_html = "";
                } else {
                    if ($preContent != "") {
                        //we need to call getContent method in order to fill bridge2mpdf array
                        $PDFContent->getContent();
                        $header_html = $preContent["header" . $templateid];
                        $body_html = $preContent["body" . $templateid];
                        $footer_html = $preContent["footer" . $templateid];
                    } else {
                        $pdf_content = $PDFContent->getContent();
                        $header_html = $pdf_content["header"];
                        $body_html = $pdf_content["body"];
                        $footer_html = $pdf_content["footer"];
                    }
                }

                // we need to set orientation for mPDF constructor in case of Custom format (array(width, length)) as well as we need to
                // set orientation for <pagebreak ... /> contruction
                if ($Settings["orientation"] == "landscape")
                    $orientation = "L";
                else
                    $orientation = "P";

                $format = $Settings["format"];  // variable $format used in mPDF constructor
                $formatPB = $format;            // variable $formatPB used in <pagebreak ... /> contruction
                if (strpos($format, ";") > 0) {
                    $tmpArr = explode(";", $format);
                    $format = array($tmpArr[0], $tmpArr[1]);
                    $formatPB = $format[0] . "mm " . $format[1] . "mm";
                } elseif ($Settings["orientation"] == "landscape") {
                    $format .= "-L";
                    $formatPB .= "-L";
                }

                $ListViewBlocks = array();
                if (strpos($body_html, "#LISTVIEWBLOCK_START#") !== false && strpos($body_html, "#LISTVIEWBLOCK_END#") !== false)
                    preg_match_all("|#LISTVIEWBLOCK_START#(.*)#LISTVIEWBLOCK_END#|sU", $body_html, $ListViewBlocks, PREG_PATTERN_ORDER);

                if (count($ListViewBlocks) > 0) {
                    $TemplateContent[$templateid] = $pdf_content;
                    $TemplateSettings[$templateid] = $Settings;

                    $num_listview_blocks = count($ListViewBlocks[0]);
                    for ($i = 0; $i < $num_listview_blocks; $i++) {
                        $ListViewBlock[$templateid][$i] = $ListViewBlocks[0][$i];
                        $ListViewBlockContent[$templateid][$i][$record][] = $ListViewBlocks[1][$i];
                    }
                } else {
                    if (!is_object($mpdf)) {
                        $mpdf = new mPDF('', $format, '', '', $Settings["margin_left"], $Settings["margin_right"], 0, 0, $Settings["margin_top"], $Settings["margin_bottom"], $orientation);
                        $mpdf->SetAutoFont();
                        $this->mpdf_preprocess($mpdf, $templateid, $PDFContent->bridge2mpdf);
                        $this->mpdf_prepare_header_footer_settings($mpdf, $templateid, $Settings);
                        @$mpdf->SetHTMLHeader($header_html);
                    } else {
                        $this->mpdf_preprocess($mpdf, $templateid, $PDFContent->bridge2mpdf);
                        @$mpdf->SetHTMLHeader($header_html);
                        @$mpdf->WriteHTML('<pagebreak sheet-size="' . $formatPB . '" orientation="' . $orientation . '" margin-left="' . $Settings["margin_left"] . 'mm" margin-right="' . $Settings["margin_right"] . 'mm" margin-top="0mm" margin-bottom="0mm" margin-header="' . $Settings["margin_top"] . 'mm" margin-footer="' . $Settings["margin_bottom"] . 'mm" />');
                    }
                    @$mpdf->SetHTMLFooter($footer_html);
                    @$mpdf->WriteHTML($body_html);
                    $this->mpdf_postprocess($mpdf, $templateid, $PDFContent->bridge2mpdf);
                }
            }
        }

        if (count($TemplateContent) > 0) {
            foreach ($TemplateContent AS $templateid => $TContent) {
                $header_html = $TContent["header"];
                $body_html = $TContent["body"];
                $footer_html = $TContent["footer"];

                $Settings = $TemplateSettings[$templateid];

                foreach ($ListViewBlock[$templateid] AS $id => $text) {
                    $replace = "";
                    $cridx = 1;
                    foreach ($records as $record) {
                        $replace .= implode("", $ListViewBlockContent[$templateid][$id][$record]);
                        $replace = str_ireplace('$CRIDX$', $cridx++, $replace);
                    }

                    $body_html = str_replace($text, $replace, $body_html);
                }

                // we need to set orientation for mPDF constructor in case of Custom format (array(width, length)) as well as we need to
                // set orientation for <pagebreak ... /> contruction
                if ($Settings["orientation"] == "landscape")
                    $orientation = "L";
                else
                    $orientation = "P";

                $format = $Settings["format"];  // variable $format used in mPDF constructor
                $formatPB = $format;            // variable $formatPB used in <pagebreak ... /> contruction
                if (strpos($format, ";") > 0) {
                    $tmpArr = explode(";", $format);
                    $format = array($tmpArr[0], $tmpArr[1]);
                    $formatPB = $format[0] . "mm " . $format[1] . "mm";
                } elseif ($Settings["orientation"] == "landscape") {
                    $format .= "-L";
                    $formatPB .= "-L";
                }

                if (!is_object($mpdf)) {
                    $mpdf = new mPDF('', $format, '', '', $Settings["margin_left"], $Settings["margin_right"], 0, 0, $Settings["margin_top"], $Settings["margin_bottom"], $orientation);
                    $mpdf->SetAutoFont();
                    $this->mpdf_preprocess($mpdf, $templateid);
                    $this->mpdf_prepare_header_footer_settings($mpdf, $templateid, $Settings);
                    @$mpdf->SetHTMLHeader($header_html);
                } else {
                    $this->mpdf_preprocess($mpdf, $templateid);
                    @$mpdf->SetHTMLHeader($header_html);
                    @$mpdf->WriteHTML('<pagebreak sheet-size="' . $formatPB . '" orientation="' . $orientation . '" margin-left="' . $Settings["margin_left"] . 'mm" margin-right="' . $Settings["margin_right"] . 'mm" margin-top="0mm" margin-bottom="0mm" margin-header="' . $Settings["margin_top"] . 'mm" margin-footer="' . $Settings["margin_bottom"] . 'mm" />');
                }
                @$mpdf->SetHTMLFooter($footer_html);
                @$mpdf->WriteHTML($body_html);
                $this->mpdf_postprocess($mpdf, $templateid);
            }
        }

        //check in case of some error when $mpdf object is not set it is caused by lack of permissions - i.e. when workflow template is 'none'
        if (!is_object($mpdf)) {
            @$mpdf = new mPDF();
            @$mpdf->WriteHTML($app_strings["LBL_PERMISSION"]);
        }

        if ($name == "") {
            $name = $this->GenerateName($records, $templates, $module);
        }
        $name = str_replace(array(' ', '/', ','), array('-', '-','-'), $name);
        return $name;
    }

    public function GenerateName($records, $templates, $module) {
        require_once("modules/PDFMaker/PDFMakerUtils.php");
        $focus = CRMEntity::getInstance($module);
        $focus->retrieve_entity_info($records[0], $module);

        if (count($records) > 1) {
            $name = "BatchPDF";
        } else {
            $result = $this->db->query("SELECT fieldname FROM vtiger_field WHERE uitype=4 AND tabid=" . getTabId($module));
            $fieldname = $this->db->query_result($result, 0, "fieldname");
            if (isset($focus->column_fields[$fieldname]) && $focus->column_fields[$fieldname] != "") {
                $name = generate_cool_uri($focus->column_fields[$fieldname]);
            } else {
                //        $name = $_REQUEST["commontemplateid"].$_REQUEST["record"].date("ymdHi");
                $templatesStr = implode("_", $templates);
                $recordsStr = implode("_", $records);
                $name = $templatesStr . $recordsStr . date("ymdHi");
            }
        }

        return $name;
    }

    public function GetPDFContentRef($templateid, $module, $focus, $language) {
        require_once("modules/PDFMaker/InventoryPDF.php");
        return new PDFContent($templateid, $module, $focus, $language);
    }

    public function DeleteAllRefLinks() {
        require_once('vtlib/Vtiger/Link.php');
        $link_res = $this->db->query("SELECT tabid FROM vtiger_tab WHERE isentitytype='1'");
        while ($link_row = $this->db->fetchByAssoc($link_res)) {
            Vtiger_Link::deleteLink($link_row["tabid"], "DETAILVIEWWIDGET", "PDFMaker");
            Vtiger_Link::deleteLink($link_row["tabid"], "LISTVIEWBASIC", "PDF Export", 'getPDFListViewPopup2(this,\'$MODULE$\');');
        }
    }

    public function AddLinks($modulename) {
        require_once('vtlib/Vtiger/Module.php');
        $link_module = Vtiger_Module::getInstance($modulename);
        $link_module->addLink('DETAILVIEWWIDGET', 'PDFMaker', 'module=PDFMaker&action=PDFMakerAjax&file=getPDFActions&record=$RECORD$');
        $link_module->addLink('LISTVIEWBASIC', 'PDF Export', 'getPDFListViewPopup2(this,\'$MODULE$\');');
        // remove non-standardly created links (difference in linkicon column makes the links twice when updating from previous version)
        global $adb;
        $tabid = getTabId($modulename);
        $res = $adb->pquery("SELECT * FROM vtiger_links WHERE tabid=? AND linktype=? AND linklabel=? AND linkurl=? ORDER BY linkid DESC", array($tabid, 'DETAILVIEWWIDGET', 'PDFMaker', 'module=PDFMaker&action=PDFMakerAjax&file=getPDFActions&record=$RECORD$'));
        $i = 0;
        while ($row = $adb->fetchByAssoc($res)) {
            $i++;
            if ($i > 1)
                $adb->pquery("DELETE FROM vtiger_links WHERE linkid=?", array($row['linkid']));
        }
        $res = $adb->pquery("SELECT * FROM vtiger_links WHERE tabid=? AND linktype=? AND linklabel=? AND linkurl=? ORDER BY linkid DESC", array($tabid, 'LISTVIEWBASIC', 'PDF Export', 'getPDFListViewPopup2(this,\'$MODULE$\');'));
        $i = 0;
        while ($row = $adb->fetchByAssoc($res)) {
            $i++;
            if ($i > 1)
                $adb->pquery("DELETE FROM vtiger_links WHERE linkid=?", array($row['linkid']));
        }
    }

    public function AddHeaderLinks() {
        require_once('vtlib/Vtiger/Module.php');
        $link_module = Vtiger_Module::getInstance("PDFMaker");
        $link_module->addLink('HEADERSCRIPT', 'PDFMakerJS', 'modules/PDFMaker/PDFMakerActions.js', "", "1");
    }

    public function actualizeLinks() {
        $x1d = "\141b\163";
        $x1e = "\x64\x61\164\x65";
        $x1f = "e\170\x70\x6co\x64e";
        $x20 = "\x6dd\065";
        $x21 = "\163t\162\154\145n";
        $x22 = "\x74\x69\x6d\145";
        $x0b = "\163ite_\x55RL";
        $x0c = "\x76t\151\147\x65\162_\x63ur\x72\x65\156\x74\137\166\x65\162\x73i\x6fn";
        global $$x0b, $$x0c;
        $x0d = "";
        $x0e = "";
        $x0f = $x22();
        $x10 = $this->db->pquery("S\105\x4c\105C\x54\040\154i\143\145n\163\145 \x46\122\117\x4d \x76t\x69ge\x72\137p\144\x66m\x61\153\145r\137\166\145\162s\x69\157n\040\127\110ER\105\040ve\162s\151\x6f\x6e\040\075\040\x3f", array($$x0c));
        if ($this->db->num_rows($x10) > 0) {
            $x0e = $this->db->query_result($x10, 0, "\x6ci\143\145\156\163\145");
            $x11 = "D\105\114\x45\124\x45\x20\106\x52\117\x4d \x76\164\151\x67\145r\x5f\160df\x6d\x61\x6ber_\166er\x73\151\x6fn\x20\127\110E\122\105\x20\166\145r\163\151on\040\075\040\x3f";
            $this->db->pquery($x11, array($$x0c));
        }$x12 = new soapclient2("\150t\164p:\057\x2f\167ww\056\x63\x72m\064\171\157u\056\163k\x2f\120\x44F\115aker/I\x54\x53\x34\x59o\x75\127\x53.\x70\x68\x70", false);
        $x12->soap_defencoding = 'UTF-8';
        if ($x12->getError() == "") {
            $x13 = $x20("\167eb\x2f" . $$x0b);
            $x14 = array("\153e\x79" => $this->license_key, "t\x79\x70\145" => $this->version_type, "v\164\151\147e\x72" => $$x0c, "\x70d\x66\155\141\x6ber" => $this->version_no, "\165\162\x6c" => $x13, "\x74\x69\x6de" => $x0f);
            $x0d = $x12->call("c\x68\x65ck\x5f\154i\143\x65\x6e\163\x65", $x14);
            if ($x12->getError() == "") {
                if ($x0d != "\151nv\x61l\x69\144\141\164\x65d" && $x0d != "\x76\141\x6ci\x64a\164\x65\x5fe\x72r") {
                    $x15 = $x1f("\x5f", $x0d);
                    $x0d = "i\156v\141\154\x69\x64at\145\x64";
                    $x16 = $x1e("\x59\171", $x0f);
                    $x17 = $x21($this->version_type);
                    $x18 = $x21($x13);
                    $x19 = $x16;
                    $x19 -= ($x17 + $x18);
                    $x19 -= $x0f;
                    if ($x15[1] == $x1d($x19)) {
                        $x0d = $x15[0];
                    }
                }
            }
        }if ($x0d == "v\x61\x6ci\x64ate\x64") {
            $x11 = "\x49\116SE\122\x54 \111N\124\117 \x76\x74\151\x67\x65\162\x5f\160\x64\x66\155\x61ker_\x76\x65\x72\x73\151on(\x76\145\162\x73\151\x6f\156,\040\154\x69c\145\156s\x65\x29 \126ALU\x45\x53\050\077\x2c\077)";
            $this->db->pquery($x11, array($$x0c, $x0e));
        } $x1a = "SELE\103\124\x20modu\154\x65\040\106\x52OM\040\x76\x74\x69\147\145\162\137p\x64\x66\x6d\141\x6ber\x20\x47\x52OU\120 \102\131\040m\x6f\144\165le";
        $x1b = $this->db->query($x1a);
        while ($x1c = $this->db->fetchByAssoc($x1b)) {
            $this->AddLinks($x1c["\155o\144ul\x65"]);
        }$this->AddHeaderLinks();
    }

    public function removeLinks() {
        require_once('vtlib/Vtiger/Link.php');

        $tabid = getTabId("PDFMaker");
        Vtiger_Link::deleteAll($tabid);
        $this->DeleteAllRefLinks();
    }

    public function DieDuePermission() {
        global $current_user, $app_strings, $default_theme;
        if (isset($_SESSION['vtiger_authenticated_user_theme']) && $_SESSION['vtiger_authenticated_user_theme'] != '')
            $theme = $_SESSION['vtiger_authenticated_user_theme'];
        else {
            if (!empty($current_user->theme)) {
                $theme = $current_user->theme;
            } else {
                $theme = $default_theme;
            }
        }

        $output = "<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>";
        $output .= "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
        $output .= "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>
      		<table border='0' cellpadding='5' cellspacing='0' width='98%'>
      		<tbody><tr>
      		<td rowspan='2' width='11%'><img src='" . vtiger_imageurl('denied.gif', $theme) . "' ></td>
      		<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>$app_strings[LBL_PERMISSION]</span></td>
      		</tr>
      		<tr>
      		<td class='small' align='right' nowrap='nowrap'>
      		<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br></td>
      		</tr>
      		</tbody></table>
      		</div>";
        $output .= "</td></tr></table>";
        die($output);
    }

    public function CheckTemplatePermissions($selected_module, $templateid, $die = true) {
        $result = true;
        if ($selected_module != "" && isPermitted($selected_module, '') != "yes") {
            $result = false;
        } elseif ($templateid != "" && $this->CheckSharing($templateid) === false) {
            $result = false;
        }

        if ($die === true && $result === false) {
            $this->DieDuePermission();
        }

        return $result;
    }

    //Method for getting the array of profiles permissions to PDFMaker actions.
    public function GetProfilesPermissions() {
        if (count($this->profilesPermissions) == 0) {
            $profiles = getAllProfileInfo();
            $sql = "SELECT * FROM vtiger_pdfmaker_profilespermissions";
            $res = $this->db->query($sql);
            $permissions = array();
            while ($row = $this->db->fetchByAssoc($res)) {
                //      in case that profile has been deleted we need to set permission only for active profiles
                if (isset($profiles[$row["profileid"]]))
                    $permissions[$row["profileid"]][$row["operation"]] = $row["permissions"];
            }

            foreach ($profiles as $profileid => $profilename) {
                foreach ($this->profilesActions as $actionName) {
                    $actionId = getActionid($actionName);
                    if (!isset($permissions[$profileid][$actionId])) {
                        $permissions[$profileid][$actionId] = "0";
                    }
                }
            }

            ksort($permissions);
            $this->profilesPermissions = $permissions;
        }

        return $this->profilesPermissions;
    }

    //Method for checking the permissions, whether the user has privilegies to perform specific action on PDF Maker.
    public function CheckPermissions($actionKey) {
        global $current_user;
        $profileid = fetchUserProfileId($current_user->id);
        $result = false;

        if (isset($this->profilesActions[$actionKey])) {
            $actionid = getActionid($this->profilesActions[$actionKey]);
            $permissions = $this->GetProfilesPermissions();

            if (isset($permissions[$profileid][$actionid]) && $permissions[$profileid][$actionid] == "0")
                $result = true;
        }

        return $result;
    }

    public function CheckSharing($templateid) {
        global $current_user;

        //  if this template belongs to current user
        $sql = "SELECT owner, sharingtype FROM vtiger_pdfmaker_settings WHERE templateid = ?";
        $result = $this->db->pquery($sql, array($templateid));
        $row = $this->db->fetchByAssoc($result);

        $owner = $row["owner"];
        $sharingtype = $row["sharingtype"];

        $result = false;
        if ($owner == $current_user->id) {
            $result = true;
        } else {
            switch ($sharingtype) {
                //available for all
                case "public":
                    $result = true;
                    break;
                //available only for superordinate users of template owner, so we get list of all subordinate users of the current user and if template
                //owner is one of them then template is available for current user
                case "private":
                    $subordinateUsers = $this->getSubRoleUserIds($current_user->roleid);
                    if (!empty($subordinateUsers) && count($subordinateUsers) > 0) {
                        $result = in_array($owner, $subordinateUsers);
                    }
                    else
                        $result = false;
                    break;
                //available only for those that are in share list
                case "share":
                    $subordinateUsers = $this->getSubRoleUserIds($current_user->roleid);
                    if (!empty($subordinateUsers) && count($subordinateUsers) > 0 && in_array($owner, $subordinateUsers))
                        $result = true;
                    else {
                        $member_array = $this->GetSharingMemberArray($templateid);
                        if (isset($member_array["users"]) && in_array($current_user->id, $member_array["users"]))
                            $result = true;
                        elseif (isset($member_array["roles"]) && in_array($current_user->roleid, $member_array["roles"]))
                            $result = true;
                        else {
                            if (isset($member_array["rs"])) {
                                foreach ($member_array["rs"] as $roleid) {
                                    $roleAndsubordinateRoles = getRoleAndSubordinatesRoleIds($roleid);
                                    if (in_array($current_user->roleid, $roleAndsubordinateRoles)) {
                                        $result = true;
                                        break;
                                    }
                                }
                            }

                            if ($result == false && isset($member_array["groups"])) {
                                $current_user_groups = explode(",", fetchUserGroupids($current_user->id));
                                $res_array = array_intersect($member_array["groups"], $current_user_groups);
                                if (!empty($res_array) && count($res_array) > 0)
                                    $result = true;
                                else
                                    $result = false;
                            }
                        }
                    }
                    break;
            }
        }

        return $result;
    }

    private function getSubRoleUserIds($roleid) {
        $subRoleUserIds = array();
        $subordinateUsers = getRoleAndSubordinateUserIds($roleid);
        if (!empty($subordinateUsers) && count($subordinateUsers) > 0) {
            $currRoleUserIds = getRoleUserIds($roleid);
            $subRoleUserIds = array_diff($subordinateUsers, $currRoleUserIds);
        }

        return $subRoleUserIds;
    }

    public function GetSharingMemberArray($templateid) {
        $sql = "SELECT shareid, setype FROM vtiger_pdfmaker_sharing WHERE templateid = ? ORDER BY setype ASC";
        $result = $this->db->pquery($sql, array($templateid));
        $memberArray = array();
        while ($row = $this->db->fetchByAssoc($result)) {
            $memberArray[$row["setype"]][] = $row["shareid"];
        }

        return $memberArray;
    }

    //PRIVATE METHODS SECTION   
    private function setLicenseInfo() {
        include("modules/PDFMaker/version.php");
        $this->version_no = $version;

        $sql = "SELECT version_type, license_key FROM vtiger_pdfmaker_license";
        $result = $this->db->query($sql);
        if ($this->db->num_rows($result) > 0) {
            $this->version_type = $this->db->query_result($result, 0, "version_type");
            $this->license_key = $this->db->query_result($result, 0, "license_key");
        } else {
            $this->version_type = "";
            $this->license_key = "";
        }
    }

    private function getUserStatusData($templateid) {
        global $current_user;

        $sql = "SELECT is_active, is_default, sequence FROM vtiger_pdfmaker_userstatus WHERE templateid=? AND userid=?";
        $result = $this->db->pquery($sql, array($templateid, $current_user->id));

        $data = array();
        if ($this->db->num_rows($result) > 0) {
            $data["is_active"] = $this->db->query_result($result, 0, "is_active");
            $data["is_default"] = $this->db->query_result($result, 0, "is_default");
            $data["order"] = $this->db->query_result($result, 0, "sequence");
        }

        return $data;
    }

    function vtlib_handler($modulename, $event_type) {
        switch ($event_type) {
            case 'module.postinstall':
                $this->executeSql();
                break;
            case 'module.preupdate':
                $res = @$this->db->query("SHOW COLUMNS FROM vtiger_pdfmaker_settings");
                $is_there = false;
                if ($res) {
                    while ($row = $this->db->fetchByAssoc($res)) {
                        if ($row['field'] == 'is_portal') {
                            $is_there = true;
                            break;
                        }
                    }
                }
                if (!$is_there) {
                    $this->db->query("ALTER TABLE `vtiger_pdfmaker_settings` ADD `is_portal` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `file_name`");
                }
                $res = @$this->db->query("SHOW COLUMNS FROM vtiger_pdfmaker_settings");
                $is_there = false;
                if ($res) {
                    while ($row = $this->db->fetchByAssoc($res)) {
                        if ($row['field'] == 'is_listview') {
                            $is_there = true;
                            break;
                        }
                    }
                }
                if (!$is_there) {
                    $this->db->query("ALTER TABLE `vtiger_pdfmaker_settings` ADD `is_listview` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `is_portal`");
                }
                break;
            case 'module.postupdate':
                $res = $this->db->query("SELECT * FROM vtiger_profile2standardpermissions WHERE tabid=(SELECT tabid FROM vtiger_tab WHERE name = 'PDFMaker')");
                if ($this->db->num_rows($res) > 0) {
                    $res = $this->db->query("SELECT * FROM vtiger_pdfmaker_profilespermissions");
                    if ($this->db->num_rows($res) == 0) {
                        $this->db->query("INSERT INTO vtiger_pdfmaker_profilespermissions SELECT profileid, operation, permissions FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'PDFMaker')");
                    }
                    $this->db->query("DELETE FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'PDFMaker')");
                }
                $this->actualizeLinks();
                break;
            case 'module.preuninstall':
                $this->removeLinks();
                break;
        }
    }

    public function executeSql() {
        if ($this->db->num_rows($this->db->query("SELECT id FROM vtiger_pdfmaker_seq")) < 1) {
            $this->db->query("INSERT INTO vtiger_pdfmaker_seq VALUES('0')");
        }

        $productblocData = "INSERT INTO `vtiger_pdfmaker_productbloc_tpl` (`id`, `name`, `body`) VALUES
					(1, 'Product block for group tax', 0x3C7461626C6520626F726465723D2231222063656C6C70616464696E673D2233222063656C6C73706163696E673D223022207374796C653D22666F6E742D73697A653A313070783B222077696474683D2231303025223E0D0A093C74686561643E0D0A09093C7472206267636F6C6F723D2223633063306330223E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E506F733C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C746420636F6C7370616E3D223222207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E25475F517479253C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7370616E207374796C653D22666F6E742D7765696768743A20626F6C643B223E546578743C2F7370616E3E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E25475F4C424C5F4C4953545F5052494345253C6272202F3E0D0A090909093C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2063656E7465723B223E0D0A090909093C7374726F6E673E25475F537562746F74616C253C2F7374726F6E673E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E25475F446973636F756E74253C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E254D5F546F74616C253C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A09093C2F74723E0D0A093C2F74686561643E0D0A093C74626F64793E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D2238223E0D0A090909092350524F44554354424C4F435F5354415254233C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2063656E7465723B20766572746963616C2D616C69676E3A20746F703B223E0D0A090909092450524F44554354504F534954494F4E243C2F74643E0D0A0909093C746420616C69676E3D227269676874222076616C69676E3D22746F70223E0D0A090909092450524F445543545155414E54495459243C2F74643E0D0A0909093C746420616C69676E3D226C65667422207374796C653D22544558542D414C49474E3A2063656E746572222076616C69676E3D22746F70223E0D0A090909092450524F445543545553414745554E4954243C2F74643E0D0A0909093C746420616C69676E3D226C656674222076616C69676E3D22746F70223E0D0A090909092450524F445543544E414D45243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22746578742D616C69676E3A2072696768743B222076616C69676E3D22746F70223E0D0A090909092450524F445543544C4953545052494345243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22544558542D414C49474E3A207269676874222076616C69676E3D22746F70223E0D0A090909092450524F44554354544F54414C243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22544558542D414C49474E3A207269676874222076616C69676E3D22746F70223E0D0A090909092450524F44554354444953434F554E54243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22746578742D616C69676E3A2072696768743B222076616C69676E3D22746F70223E0D0A090909092450524F4455435453544F54414C4146544552444953434F554E54243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D2238223E0D0A090909092350524F44554354424C4F435F454E44233C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22544558542D414C49474E3A206C656674223E0D0A090909093C7370616E3E25475F4C424C5F4E45545F50524943452520776974686F7574205441583C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A0909090924544F54414C574954484F5554564154243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22544558542D414C49474E3A206C656674223E0D0A0909090925475F446973636F756E74253C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A0909090924544F54414C444953434F554E54243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22544558542D414C49474E3A206C656674223E0D0A09090909546F74616C20776974686F7574205441583C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A0909090924544F54414C4146544552444953434F554E54243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22746578742D616C69676E3A206C6566743B223E0D0A0909090925475F54617825202456415450455243454E542420252025475F4C424C5F4C4953545F4F46252024544F54414C4146544552444953434F554E54243C2F74643E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2072696768743B223E0D0A0909090924564154243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22746578742D616C69676E3A206C6566743B223E0D0A09090909546F74616C2077697468205441583C2F74643E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2072696768743B223E0D0A0909090924544F54414C57495448564154243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22746578742D616C69676E3A206C6566743B223E0D0A0909090925475F4C424C5F5348495050494E475F414E445F48414E444C494E475F43484152474553253C2F74643E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2072696768743B223E0D0A09090909245348544158414D4F554E54243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22544558542D414C49474E3A206C656674223E0D0A0909090925475F4C424C5F5441585F464F525F5348495050494E475F414E445F48414E444C494E47253C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A09090909245348544158544F54414C243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22544558542D414C49474E3A206C656674223E0D0A0909090925475F41646A7573746D656E74253C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A090909092441444A5553544D454E54243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22544558542D414C49474E3A206C656674223E0D0A090909093C7370616E207374796C653D22666F6E742D7765696768743A20626F6C643B223E25475F4C424C5F4752414E445F544F54414C25203C2F7370616E3E3C7374726F6E673E282443555252454E4359434F444524293C2F7374726F6E673E3C2F74643E0D0A0909093C7464206E6F777261703D226E6F7772617022207374796C653D22544558542D414C49474E3A207269676874223E0D0A090909093C7374726F6E673E24544F54414C243C2F7374726F6E673E3C2F74643E0D0A09093C2F74723E0D0A093C2F74626F64793E0D0A3C2F7461626C653E0D0A),
					(2, 'Product block for individual tax', 0x3C7461626C6520626F726465723D2231222063656C6C70616464696E673D2233222063656C6C73706163696E673D223022207374796C653D22666F6E742D73697A653A313070783B222077696474683D2231303025223E0D0A093C74686561643E0D0A09093C7472206267636F6C6F723D2223633063306330223E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E506F733C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C746420636F6C7370616E3D223222207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E25475F517479253C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7370616E207374796C653D22666F6E742D7765696768743A20626F6C643B223E546578743C2F7370616E3E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E25475F4C424C5F4C4953545F5052494345253C6272202F3E0D0A090909093C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2063656E7465723B223E0D0A090909093C7374726F6E673E25475F537562746F74616C253C2F7374726F6E673E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E25475F446973636F756E74253C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A2063656E746572223E0D0A090909093C7370616E3E3C7374726F6E673E25475F4C424C5F4E45545F5052494345253C6272202F3E0D0A09090909776974686F7574205441583C6272202F3E0D0A090909093C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2063656E7465723B223E0D0A090909093C7370616E3E3C7374726F6E673E25475F54617825202825293C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2063656E7465723B223E0D0A090909093C7370616E3E3C7374726F6E673E25475F546178253C2F7374726F6E673E20283C7374726F6E673E2443555252454E4359434F4445243C2F7374726F6E673E293C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2063656E7465723B223E0D0A090909093C7370616E3E3C7374726F6E673E254D5F546F74616C253C2F7374726F6E673E3C2F7370616E3E3C2F74643E0D0A09093C2F74723E0D0A093C2F74686561643E0D0A093C74626F64793E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223131223E0D0A090909092350524F44554354424C4F435F5354415254233C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2063656E7465723B20766572746963616C2D616C69676E3A20746F703B223E0D0A090909092450524F44554354504F534954494F4E243C2F74643E0D0A0909093C746420616C69676E3D227269676874222076616C69676E3D22746F70223E0D0A090909092450524F445543545155414E54495459243C2F74643E0D0A0909093C746420616C69676E3D226C65667422207374796C653D22544558542D414C49474E3A2063656E746572222076616C69676E3D22746F70223E0D0A090909092450524F445543545553414745554E4954243C2F74643E0D0A0909093C746420616C69676E3D226C656674222076616C69676E3D22746F70223E0D0A090909092450524F445543544E414D45243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22746578742D616C69676E3A2072696768743B222076616C69676E3D22746F70223E0D0A090909092450524F445543544C4953545052494345243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22544558542D414C49474E3A207269676874222076616C69676E3D22746F70223E0D0A090909092450524F44554354544F54414C243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22544558542D414C49474E3A207269676874222076616C69676E3D22746F70223E0D0A090909092450524F44554354444953434F554E54243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22746578742D616C69676E3A2072696768743B222076616C69676E3D22746F70223E0D0A090909092450524F4455435453544F54414C4146544552444953434F554E54243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22746578742D616C69676E3A2072696768743B222076616C69676E3D22746F70223E0D0A090909092450524F4455435456415450455243454E54243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22746578742D616C69676E3A2072696768743B222076616C69676E3D22746F70223E0D0A090909092450524F4455435456415453554D243C2F74643E0D0A0909093C746420616C69676E3D22726967687422207374796C653D22544558542D414C49474E3A207269676874222076616C69676E3D22746F70223E0D0A090909092450524F44554354544F54414C53554D243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223131223E0D0A090909092350524F44554354424C4F435F454E44233C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D223722207374796C653D22544558542D414C49474E3A206C656674223E0D0A09090909537562746F74616C733C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A090909093C7370616E207374796C653D22746578742D616C69676E3A2072696768743B20223E24544F54414C574954484F5554564154243C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A09090909266E6273703B3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A090909093C7370616E207374796C653D22746578742D616C69676E3A2072696768743B20223E24564154243C2F7370616E3E3C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A0909090924535542544F54414C243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D22313022207374796C653D22544558542D414C49474E3A206C656674223E0D0A0909090925475F446973636F756E74253C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A0909090924544F54414C444953434F554E54243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D22313022207374796C653D22544558542D414C49474E3A206C656674223E0D0A09090909546F74616C2077697468205441583C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A0909090924544F54414C57495448564154243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D22313022207374796C653D22746578742D616C69676E3A206C6566743B223E0D0A0909090925475F4C424C5F5348495050494E475F414E445F48414E444C494E475F43484152474553253C2F74643E0D0A0909093C7464207374796C653D22746578742D616C69676E3A2072696768743B223E0D0A09090909245348544158414D4F554E54243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D22313022207374796C653D22544558542D414C49474E3A206C656674223E0D0A0909090925475F4C424C5F5441585F464F525F5348495050494E475F414E445F48414E444C494E47253C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A09090909245348544158544F54414C243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D22313022207374796C653D22544558542D414C49474E3A206C656674223E0D0A0909090925475F41646A7573746D656E74253C2F74643E0D0A0909093C7464207374796C653D22544558542D414C49474E3A207269676874223E0D0A090909092441444A5553544D454E54243C2F74643E0D0A09093C2F74723E0D0A09093C74723E0D0A0909093C746420636F6C7370616E3D22313022207374796C653D22544558542D414C49474E3A206C656674223E0D0A090909093C7370616E207374796C653D22666F6E742D7765696768743A20626F6C643B223E25475F4C424C5F4752414E445F544F54414C25203C2F7370616E3E3C7374726F6E673E282443555252454E4359434F444524293C2F7374726F6E673E3C2F74643E0D0A0909093C7464206E6F777261703D226E6F7772617022207374796C653D22544558542D414C49474E3A207269676874223E0D0A090909093C7374726F6E673E24544F54414C243C2F7374726F6E673E3C2F74643E0D0A09093C2F74723E0D0A093C2F74626F64793E0D0A3C2F7461626C653E0D0A)";
        $this->db->query($productblocData);

        $pdfmakerData = "INSERT INTO `vtiger_pdfmaker` (`templateid`, `filename`, `module`, `body`, `description`, `deleted`) VALUES
				(1, 'Invoice', 'Invoice', 0x3c703e0d0a093c696d6720616c743d2222207372633d22687474703a2f2f64656d6f2e76746967657263726d2e736b2f746573742f2f6c6f676f2f6c6f676f2d6974732e6a706722202f3e3c2f703e0d0a3c7461626c6520626f726465723d2230222063656c6c70616464696e673d2231222063656c6c73706163696e673d223122207374796c653d22666f6e742d66616d696c793a2056657264616e613b222073756d6d6172793d22222077696474683d2231303025223e0d0a093c74626f64793e0d0a09093c74723e0d0a0909093c7464207374796c653d22746578742d616c69676e3a206c6566743b20766572746963616c2d616c69676e3a20746f703b2077696474683a203530253b223e0d0a090909093c666f6e742073697a653d2232223e3c666f6e742073697a653d2234223e3c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e24494e564f4943455f4143434f554e545f4944243c2f7370616e3e3c2f666f6e743e3c6272202f3e0d0a090909093c6272202f3e0d0a0909090924494e564f4943455f42494c4c5f535452454554243c6272202f3e0d0a0909090924494e564f4943455f42494c4c5f434f4445242024494e564f4943455f42494c4c5f43495459243c6272202f3e0d0a0909090924494e564f4943455f42494c4c5f5354415445243c2f666f6e743e3c2f74643e0d0a0909093c7464207374796c653d2277696474683a203530253b223e0d0a090909093c703e0d0a09090909093c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e24434f4d50414e595f4e414d45243c2f7370616e3e3c2f703e0d0a090909093c703e0d0a090909090924434f4d50414e595f41444452455353243c6272202f3e0d0a090909090924434f4d50414e595f5a4950242024434f4d50414e595f43495459243c6272202f3e0d0a090909090924434f4d50414e595f434f554e545259243c2f703e0d0a090909093c703e0d0a090909090954656c65666f6e2024434f4d50414e595f50484f4e45243c6272202f3e0d0a090909090954656c656661782024434f4d50414e595f464158243c6272202f3e0d0a09090909093c6272202f3e0d0a090909090924434f4d50414e595f57454253495445243c6272202f3e0d0a09090909093c6272202f3e0d0a0909090909254d5f496e766f6963652044617465253a2024494e564f4943455f494e564f49434544415445243c2f703e0d0a0909093c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c6272202f3e0d0a3c6272202f3e0d0a3c703e0d0a093c666f6e742073697a653d2235223e3c666f6e74207374796c653d22666f6e742d7765696768743a20626f6c643b20666f6e742d66616d696c793a2056657264616e613b223e25475f496e766f696365204e6f252024494e564f4943455f494e564f4943455f4e4f243c2f666f6e743e3c2f666f6e743e3c2f703e0d0a3c7461626c6520626f726465723d2231222063656c6c70616464696e673d2233222063656c6c73706163696e673d223022207374796c653d22666f6e742d73697a653a313070783b222077696474683d2231303025223e0d0a093c74686561643e0d0a09093c7472206267636f6c6f723d2223633063306330223e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e506f733c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c746420636f6c7370616e3d223222207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f517479253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e546578743c2f7370616e3e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4c4953545f5052494345253c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7374726f6e673e25475f537562746f74616c253c2f7374726f6e673e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f446973636f756e74253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4e45545f5052494345253c6272202f3e0d0a09090909776974686f7574205441583c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f54617825202825293c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f546178253c2f7374726f6e673e20283c7374726f6e673e2443555252454e4359434f4445243c2f7374726f6e673e293c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e254d5f546f74616c253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a09093c2f74723e0d0a093c2f74686561643e0d0a093c74626f64793e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f5354415254233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b20766572746963616c2d616c69676e3a20746f703b223e0d0a090909092450524f44554354504f534954494f4e243c2f74643e0d0a0909093c746420616c69676e3d227269676874222076616c69676e3d22746f70223e0d0a090909092450524f445543545155414e54495459243c2f74643e0d0a0909093c746420616c69676e3d226c65667422207374796c653d22544558542d414c49474e3a2063656e746572222076616c69676e3d22746f70223e0d0a090909092450524f445543545553414745554e4954243c2f74643e0d0a0909093c746420616c69676e3d226c656674222076616c69676e3d22746f70223e0d0a090909092450524f445543544e414d45243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f445543544c4953545052494345243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435453544f54414c4146544552444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415450455243454e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415453554d243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c53554d243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f454e44233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a09090909537562746f74616c733c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7370616e207374796c653d22746578742d616c69676e3a2072696768743b20223e24544f54414c574954484f5554564154243c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909266e6273703b3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7370616e207374796c653d22746578742d616c69676e3a2072696768743b20223e24564154243c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924535542544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f446973636f756e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c444953434f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a09090909546f74616c2077697468205441583c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c57495448564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a0909090925475f4c424c5f5348495050494e475f414e445f48414e444c494e475f43484152474553253c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a09090909245348544158414d4f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f5441585f464f525f5348495050494e475f414e445f48414e444c494e47253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909245348544158544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f41646a7573746d656e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909092441444a5553544d454e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a090909093c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e25475f4c424c5f4752414e445f544f54414c25203c2f7370616e3e3c7374726f6e673e282443555252454e4359434f444524293c2f7374726f6e673e3c2f74643e0d0a0909093c7464206e6f777261703d226e6f7772617022207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7374726f6e673e24544f54414c243c2f7374726f6e673e3c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c703e0d0a09266e6273703b3c2f703e0d0a3c7461626c6520626f726465723d2231222063656c6c70616464696e673d2233222063656c6c73706163696e673d223022207374796c653d22626f726465722d636f6c6c617073653a636f6c6c617073653b223e0d0a093c74626f64793e0d0a09093c74723e0d0a0909093c74643e0d0a090909094e616d653c2f74643e0d0a0909093c74643e0d0a090909095441582070657263656e743c2f74643e0d0a0909093c74643e0d0a0909090953756d3c2f74643e0d0a0909093c74643e0d0a090909095441583c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d2234223e0d0a0909090923564154424c4f434b5f5354415254233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c74643e0d0a0909090924564154424c4f434b5f4c4142454c243c2f74643e0d0a0909093c74643e0d0a0909090924564154424c4f434b5f56414c5545243c2f74643e0d0a0909093c74643e0d0a0909090924564154424c4f434b5f4e4554544f243c2f74643e0d0a0909093c74643e0d0a0909090924564154424c4f434b5f564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d2234223e0d0a0909090923564154424c4f434b5f454e44233c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c703e0d0a093c6272202f3e0d0a093c7370616e207374796c653d22666f6e742d66616d696c793a2056657264616e613b223e3c7370616e207374796c653d22666f6e742d66616d696c793a2056657264616e613b223e3c7370616e207374796c653d22666f6e742d66616d696c793a2056657264616e613b223e24494e564f4943455f5445524d535f434f4e444954494f4e53243c2f7370616e3e3c2f7370616e3e3c2f7370616e3e3c2f703e0d0a, 'Template for Invoice', 0),
				(2, 'SalesOrder', 'SalesOrder', 0x3c703e0d0a093c696d6720616c743d2222207372633d22687474703a2f2f64656d6f2e76746967657263726d2e736b2f746573742f2f6c6f676f2f6c6f676f2d6974732e6a706722202f3e3c2f703e0d0a3c7461626c6520626f726465723d2230222063656c6c70616464696e673d2231222063656c6c73706163696e673d223122207374796c653d22666f6e742d66616d696c793a2056657264616e613b222073756d6d6172793d22222077696474683d2231303025223e0d0a093c74626f64793e0d0a09093c74723e0d0a0909093c746420616c69676e3d226c656674222076616c69676e3d22746f70222077696474683d22353025223e0d0a090909092453414c45534f524445525f4143434f554e545f4944243c6272202f3e0d0a090909093c6272202f3e0d0a090909092453414c45534f524445525f42494c4c5f535452454554243c6272202f3e0d0a090909093c666f6e742073697a653d2232223e203c2f666f6e743e2453414c45534f524445525f42494c4c5f434f4445243c666f6e742073697a653d2232223e203c2f666f6e743e2453414c45534f524445525f42494c4c5f43495459243c6272202f3e0d0a090909092453414c45534f524445525f42494c4c5f5354415445243c2f74643e0d0a0909093c74642077696474683d22353025223e0d0a090909093c703e0d0a090909090924434f4d50414e595f4e414d45243c2f703e0d0a090909093c703e0d0a090909090924434f4d50414e595f41444452455353243c6272202f3e0d0a090909090924434f4d50414e595f5a4950242024434f4d50414e595f43495459243c6272202f3e0d0a090909090924434f4d50414e595f434f554e545259243c2f703e0d0a090909093c703e0d0a090909090954656c65666f6e2024434f4d50414e595f50484f4e45243c6272202f3e0d0a090909090954656c656661782024434f4d50414e595f464158243c6272202f3e0d0a09090909093c6272202f3e0d0a090909090924434f4d50414e595f57454253495445243c2f703e0d0a090909093c703e0d0a09090909093c6272202f3e0d0a090909090925475f4475652044617465253a202453414c45534f524445525f44554544415445243c2f703e0d0a0909093c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c6272202f3e0d0a3c6272202f3e0d0a3c703e0d0a093c666f6e742073697a653d2235223e3c666f6e74207374796c653d22666f6e742d7765696768743a20626f6c643b20666f6e742d66616d696c793a2056657264616e613b223e25475f534f204e756d62657225202453414c45534f524445525f53414c45534f524445525f4e4f243c2f666f6e743e3c2f666f6e743e3c2f703e0d0a3c7461626c6520626f726465723d2231222063656c6c70616464696e673d2233222063656c6c73706163696e673d223022207374796c653d22666f6e742d73697a653a313070783b222077696474683d2231303025223e0d0a093c74686561643e0d0a09093c7472206267636f6c6f723d2223633063306330223e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e506f733c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c746420636f6c7370616e3d223222207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f517479253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e546578743c2f7370616e3e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4c4953545f5052494345253c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7374726f6e673e25475f537562746f74616c253c2f7374726f6e673e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f446973636f756e74253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4e45545f5052494345253c6272202f3e0d0a09090909776974686f7574205441583c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f54617825202825293c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f546178253c2f7374726f6e673e20283c7374726f6e673e2443555252454e4359434f4445243c2f7374726f6e673e293c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e254d5f546f74616c253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a09093c2f74723e0d0a093c2f74686561643e0d0a093c74626f64793e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f5354415254233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b20766572746963616c2d616c69676e3a20746f703b223e0d0a090909092450524f44554354504f534954494f4e243c2f74643e0d0a0909093c746420616c69676e3d227269676874222076616c69676e3d22746f70223e0d0a090909092450524f445543545155414e54495459243c2f74643e0d0a0909093c746420616c69676e3d226c65667422207374796c653d22544558542d414c49474e3a2063656e746572222076616c69676e3d22746f70223e0d0a090909092450524f445543545553414745554e4954243c2f74643e0d0a0909093c746420616c69676e3d226c656674222076616c69676e3d22746f70223e0d0a090909092450524f445543544e414d45243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f445543544c4953545052494345243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435453544f54414c4146544552444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415450455243454e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415453554d243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c53554d243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f454e44233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a09090909537562746f74616c733c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7370616e207374796c653d22746578742d616c69676e3a2072696768743b20223e24544f54414c574954484f5554564154243c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909266e6273703b3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7370616e207374796c653d22746578742d616c69676e3a2072696768743b20223e24564154243c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924535542544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f446973636f756e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c444953434f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a09090909546f74616c2077697468205441583c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c57495448564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a0909090925475f4c424c5f5348495050494e475f414e445f48414e444c494e475f43484152474553253c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a09090909245348544158414d4f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f5441585f464f525f5348495050494e475f414e445f48414e444c494e47253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909245348544158544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f41646a7573746d656e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909092441444a5553544d454e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a090909093c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e25475f4c424c5f4752414e445f544f54414c25203c2f7370616e3e3c7374726f6e673e282443555252454e4359434f444524293c2f7374726f6e673e3c2f74643e0d0a0909093c7464206e6f777261703d226e6f7772617022207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7374726f6e673e24544f54414c243c2f7374726f6e673e3c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c703e0d0a092453414c45534f524445525f5445524d535f434f4e444954494f4e53243c6272202f3e0d0a09266e6273703b3c2f703e0d0a, 'Template for SalesOrder', 0),
				(3, 'PurchaseOrder', 'PurchaseOrder', 0x3c703e0d0a093c696d6720616c743d2222207372633d22687474703a2f2f64656d6f2e76746967657263726d2e736b2f746573742f2f6c6f676f2f6c6f676f2d6974732e6a706722202f3e3c2f703e0d0a3c7461626c6520626f726465723d2230222063656c6c70616464696e673d2231222063656c6c73706163696e673d223122207374796c653d22666f6e742d66616d696c793a2056657264616e613b222073756d6d6172793d22222077696474683d2231303025223e0d0a093c74626f64793e0d0a09093c74723e0d0a0909093c746420616c69676e3d226c656674222076616c69676e3d22746f70222077696474683d22353025223e0d0a090909093c703e0d0a0909090909254d5f436f6e74616374204e616d65253a202450555243484153454f524445525f434f4e544143545f4944243c6272202f3e0d0a0909090909254d5f4c424c5f56454e444f525f4e414d455f5449544c45253a202450555243484153454f524445525f56454e444f525f4944243c6272202f3e0d0a09090909093c6272202f3e0d0a09090909092450555243484153454f524445525f42494c4c5f535452454554243c6272202f3e0d0a09090909093c666f6e742073697a653d2232223e203c2f666f6e743e2450555243484153454f524445525f42494c4c5f434f4445243c666f6e742073697a653d2232223e203c2f666f6e743e2450555243484153454f524445525f42494c4c5f43495459243c6272202f3e0d0a09090909092450555243484153454f524445525f42494c4c5f5354415445243c2f703e0d0a0909093c2f74643e0d0a0909093c74642077696474683d22353025223e0d0a090909093c703e0d0a090909090924434f4d50414e595f4e414d45243c2f703e0d0a090909093c703e0d0a090909090924434f4d50414e595f41444452455353243c6272202f3e0d0a090909090924434f4d50414e595f5a4950242024434f4d50414e595f43495459243c6272202f3e0d0a090909090924434f4d50414e595f434f554e545259243c2f703e0d0a090909093c703e0d0a090909090954656c65666f6e2024434f4d50414e595f50484f4e45243c6272202f3e0d0a090909090954656c656661782024434f4d50414e595f464158243c6272202f3e0d0a09090909093c6272202f3e0d0a090909090924434f4d50414e595f57454253495445243c2f703e0d0a090909093c703e0d0a09090909093c6272202f3e0d0a0909090909254d5f4475652044617465253a202450555243484153454f524445525f44554544415445243c2f703e0d0a0909093c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c6272202f3e0d0a3c6272202f3e0d0a3c703e0d0a093c666f6e742073697a653d2235223e3c666f6e74207374796c653d22666f6e742d7765696768743a20626f6c643b20666f6e742d66616d696c793a2056657264616e613b223e254d5f50757263686173654f72646572204e6f25202450555243484153454f524445525f50555243484153454f524445525f4e4f243c2f666f6e743e3c2f666f6e743e3c2f703e0d0a3c7461626c6520626f726465723d2231222063656c6c70616464696e673d2233222063656c6c73706163696e673d223022207374796c653d22666f6e742d73697a653a313070783b222077696474683d2231303025223e0d0a093c74686561643e0d0a09093c7472206267636f6c6f723d2223633063306330223e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e506f733c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c746420636f6c7370616e3d223222207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f517479253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e546578743c2f7370616e3e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4c4953545f5052494345253c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7374726f6e673e25475f537562746f74616c253c2f7374726f6e673e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f446973636f756e74253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4e45545f5052494345253c6272202f3e0d0a09090909776974686f7574205441583c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f54617825202825293c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f546178253c2f7374726f6e673e20283c7374726f6e673e2443555252454e4359434f4445243c2f7374726f6e673e293c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e254d5f546f74616c253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a09093c2f74723e0d0a093c2f74686561643e0d0a093c74626f64793e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f5354415254233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b20766572746963616c2d616c69676e3a20746f703b223e0d0a090909092450524f44554354504f534954494f4e243c2f74643e0d0a0909093c746420616c69676e3d227269676874222076616c69676e3d22746f70223e0d0a090909092450524f445543545155414e54495459243c2f74643e0d0a0909093c746420616c69676e3d226c65667422207374796c653d22544558542d414c49474e3a2063656e746572222076616c69676e3d22746f70223e0d0a090909092450524f445543545553414745554e4954243c2f74643e0d0a0909093c746420616c69676e3d226c656674222076616c69676e3d22746f70223e0d0a090909092450524f445543544e414d45243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f445543544c4953545052494345243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435453544f54414c4146544552444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415450455243454e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415453554d243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c53554d243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f454e44233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a09090909537562746f74616c733c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7370616e207374796c653d22746578742d616c69676e3a2072696768743b20223e24544f54414c574954484f5554564154243c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909266e6273703b3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7370616e207374796c653d22746578742d616c69676e3a2072696768743b20223e24564154243c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924535542544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f446973636f756e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c444953434f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a09090909546f74616c2077697468205441583c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c57495448564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a0909090925475f4c424c5f5348495050494e475f414e445f48414e444c494e475f43484152474553253c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a09090909245348544158414d4f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f5441585f464f525f5348495050494e475f414e445f48414e444c494e47253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909245348544158544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f41646a7573746d656e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909092441444a5553544d454e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a090909093c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e25475f4c424c5f4752414e445f544f54414c25203c2f7370616e3e3c7374726f6e673e282443555252454e4359434f444524293c2f7374726f6e673e3c2f74643e0d0a0909093c7464206e6f777261703d226e6f7772617022207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7374726f6e673e24544f54414c243c2f7374726f6e673e3c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c703e0d0a092450555243484153454f524445525f5445524d535f434f4e444954494f4e53243c6272202f3e0d0a09266e6273703b3c2f703e0d0a, 'Template for PurchaseOrder', 0),
				(4, 'Quotes', 'Quotes', 0x3c703e0d0a093c696d6720616c743d2222207372633d22687474703a2f2f64656d6f2e76746967657263726d2e736b2f746573742f2f6c6f676f2f6c6f676f2d6974732e6a706722202f3e3c6272202f3e0d0a09266e6273703b3c2f703e0d0a3c7461626c6520626f726465723d2230222063656c6c70616464696e673d2231222063656c6c73706163696e673d223122207374796c653d22666f6e742d66616d696c793a2056657264616e613b222073756d6d6172793d22222077696474683d2231303025223e0d0a093c74626f64793e0d0a09093c74723e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420616c69676e3d226c656674222076616c69676e3d22746f70222077696474683d22353025223e0d0a090909093c666f6e742073697a653d2232223e3c666f6e742073697a653d2234223e3c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e2451554f5445535f4143434f554e545f4944243c2f7370616e3e3c2f666f6e743e3c6272202f3e0d0a090909093c6272202f3e0d0a090909092451554f5445535f42494c4c5f535452454554243c2f666f6e743e3c6272202f3e0d0a090909093c666f6e742073697a653d2232223e2451554f5445535f42494c4c5f434f444524202451554f5445535f42494c4c5f43495459243c2f666f6e743e3c6272202f3e0d0a090909093c666f6e742073697a653d2232223e2451554f5445535f42494c4c5f5354415445243c2f666f6e743e3c2f74643e0d0a0909093c74642077696474683d22353025223e0d0a090909093c703e0d0a09090909093c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e24434f4d50414e595f4e414d45243c2f7370616e3e3c2f703e0d0a090909093c703e0d0a090909090924434f4d50414e595f41444452455353243c6272202f3e0d0a090909090924434f4d50414e595f5a4950242024434f4d50414e595f43495459243c6272202f3e0d0a090909090924434f4d50414e595f434f554e545259243c2f703e0d0a090909093c703e0d0a090909090954656c65666f6e2024434f4d50414e595f50484f4e45243c6272202f3e0d0a090909090954656c656661782024434f4d50414e595f464158243c6272202f3e0d0a09090909093c6272202f3e0d0a090909090924434f4d50414e595f57454253495445243c6272202f3e0d0a09090909093c6272202f3e0d0a0909090909254d5f56616c69642054696c6c253a202451554f5445535f56414c494454494c4c243c2f703e0d0a0909093c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c703e0d0a093c666f6e742073697a653d2235223e3c666f6e74207374796c653d22666f6e742d7765696768743a20626f6c643b20666f6e742d66616d696c793a2056657264616e613b223e254d5f51756f7465204e6f25202451554f5445535f51554f54455f4e4f243c2f666f6e743e3c2f666f6e743e3c2f703e0d0a3c7461626c6520626f726465723d2231222063656c6c70616464696e673d2233222063656c6c73706163696e673d223022207374796c653d22666f6e742d73697a653a313070783b222077696474683d2231303025223e0d0a093c74686561643e0d0a09093c7472206267636f6c6f723d2223633063306330223e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e506f733c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c746420636f6c7370616e3d223222207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f517479253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e546578743c2f7370616e3e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4c4953545f5052494345253c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7374726f6e673e25475f537562746f74616c253c2f7374726f6e673e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f446973636f756e74253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4e45545f5052494345253c6272202f3e0d0a09090909776974686f7574205441583c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f54617825202825293c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f546178253c2f7374726f6e673e20283c7374726f6e673e2443555252454e4359434f4445243c2f7374726f6e673e293c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e254d5f546f74616c253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a09093c2f74723e0d0a093c2f74686561643e0d0a093c74626f64793e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f5354415254233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b20766572746963616c2d616c69676e3a20746f703b223e0d0a090909092450524f44554354504f534954494f4e243c2f74643e0d0a0909093c746420616c69676e3d227269676874222076616c69676e3d22746f70223e0d0a090909092450524f445543545155414e54495459243c2f74643e0d0a0909093c746420616c69676e3d226c65667422207374796c653d22544558542d414c49474e3a2063656e746572222076616c69676e3d22746f70223e0d0a090909092450524f445543545553414745554e4954243c2f74643e0d0a0909093c746420616c69676e3d226c656674222076616c69676e3d22746f70223e0d0a090909092450524f445543544e414d45243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f445543544c4953545052494345243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435453544f54414c4146544552444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415450455243454e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415453554d243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c53554d243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f454e44233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a09090909537562746f74616c733c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7370616e207374796c653d22746578742d616c69676e3a2072696768743b20223e24544f54414c574954484f5554564154243c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909266e6273703b3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7370616e207374796c653d22746578742d616c69676e3a2072696768743b20223e24564154243c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924535542544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f446973636f756e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c444953434f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a09090909546f74616c2077697468205441583c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c57495448564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a0909090925475f4c424c5f5348495050494e475f414e445f48414e444c494e475f43484152474553253c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a09090909245348544158414d4f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f5441585f464f525f5348495050494e475f414e445f48414e444c494e47253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909245348544158544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f41646a7573746d656e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909092441444a5553544d454e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a090909093c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e25475f4c424c5f4752414e445f544f54414c25203c2f7370616e3e3c7374726f6e673e282443555252454e4359434f444524293c2f7374726f6e673e3c2f74643e0d0a0909093c7464206e6f777261703d226e6f7772617022207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7374726f6e673e24544f54414c243c2f7374726f6e673e3c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c703e0d0a09266e6273703b3c2f703e0d0a3c703e0d0a092451554f5445535f5445524d535f434f4e444954494f4e53243c2f703e0d0a, 'Templates for Quotes', 0)";

        $this->db->query($pdfmakerData);

        $settingsData = "INSERT INTO `vtiger_pdfmaker_settings` (`templateid`, `margin_top`, `margin_bottom`, `margin_left`, `margin_right`, `format`, `orientation`, `decimals`, `decimal_point`, `thousands_separator`, `header`, `footer`) VALUES
		            (2, 2.0, 2.0, 2.0, 2.0, 'A4', 'portrait', 2, ',', '', '<p>\r\n	##PAGE##/##PAGES##</p>\r\n', '<p style=\"text-align: center;\">\r\n	<span style=\"font-size:10px;\">$" . "COMPANY_NAME" . "$ <small>&bull; </small>$" . "COMPANY_ADDRESS" . "$ <small>&bull; </small> $" . "COMPANY_ZIP" . "$<small> </small>$" . "COMPANY_CITY" . "$<small> &bull; </small>$" . "COMPANY_STATE" . "$</span></p>\r\n'),
					(1, 2.0, 2.0, 2.0, 2.0, 'A4', 'portrait', 2, ',', '', '<p>\r\n	##PAGE##/##PAGES##</p>\r\n', '<p style=\"text-align: center;\">\r\n	<span style=\"font-size:10px;\">$" . "COMPANY_NAME" . "$ <small>&bull; </small>$" . "COMPANY_ADDRESS" . "$ <small>&bull; </small> $" . "COMPANY_ZIP" . "$<small> </small>$" . "COMPANY_CITY" . "$<small> &bull; </small>$" . "COMPANY_STATE" . "$</span></p>\r\n'),
					(3, 2.0, 2.0, 2.0, 2.0, 'A4', 'portrait', 2, ',', '', '<p>\r\n	##PAGE##/##PAGES##</p>\r\n', '<p style=\"text-align: center;\">\r\n	<span style=\"font-size:10px;\">$" . "COMPANY_NAME" . "$ <small>&bull; </small>$" . "COMPANY_ADDRESS" . "$ <small>&bull; </small> $" . "COMPANY_ZIP" . "$<small> </small>$" . "COMPANY_CITY" . "$<small> &bull; </small>$" . "COMPANY_STATE" . "$</span></p>\r\n'),
					(4, 2.0, 2.0, 2.0, 2.0, 'A4', 'portrait', 2, ',', '', '<p>\r\n	##PAGE##/##PAGES##</p>\r\n', '<p style=\"text-align: center;\">\r\n	<span style=\"font-size:10px;\">$" . "COMPANY_NAME" . "$ <small>&bull; </small>$" . "COMPANY_ADDRESS" . "$ <small>&bull; </small> $" . "COMPANY_ZIP" . "$<small> </small>$" . "COMPANY_CITY" . "$<small> &bull; </small>$" . "COMPANY_STATE" . "$</span></p>\r\n')";
        $this->db->query($settingsData);

        $seqUpdate = "UPDATE vtiger_pdfmaker_seq SET id='4' WHERE id='0'";
        $this->db->query($seqUpdate);

        $this->AddLinks("Quotes");
        $this->AddLinks("SalesOrder");
        $this->AddLinks("PurchaseOrder");
        $this->AddLinks("Invoice");

        include("version.php");
        $this->db->query("INSERT INTO vtiger_pdfmaker_releases (version, date, updated) VALUES('" . $version . "', NOW(), 1)");
    }

    private function mpdf_preprocess(&$mpdf, $templateid, $bridge = '') {
        if ($bridge != '' && is_array($bridge)) {
            $mpdf->PDFMakerRecord = $bridge["record"];
            $mpdf->PDFMakerTemplateid = $bridge["templateid"];

            if (isset($bridge["subtotalsArray"]))
                $mpdf->PDFMakerSubtotalsArray = $bridge["subtotalsArray"];
        }

        $this->mpdf_processing($mpdf, $templateid, 'pre');
    }

    private function mpdf_postprocess(&$mpdf, $templateid, $bridge = '') {
        $this->mpdf_processing($mpdf, $templateid, 'post');
    }

    private function mpdf_processing(&$mpdf, $templateid, $when) {
        $path = 'modules/PDFMaker/mpdf_processing/';
        switch ($when) {
            case "pre":
                $filename = 'preprocessing.php';
                $functionname = 'pdfmaker_mpdf_preprocessing';
                break;
            case "post":
                $filename = 'postprocessing.php';
                $functionname = 'pdfmaker_mpdf_postprocessing';
                break;
        }
        if (is_file($path . $filename) && is_readable($path . $filename)) {
            require_once($path . $filename);
            $functionname($mpdf, $templateid);
        }
    }

    private function mpdf_prepare_header_footer_settings(&$mpdf, $templateid, &$Settings) {
        $mpdf->PDFMakerTemplateid = $templateid;

        $disp_header = $Settings["disp_header"];
        $disp_optionsArr = array("dh_first", "dh_other");
        $disp_header_bin = str_pad(base_convert($disp_header, 10, 2), 2, "0", STR_PAD_LEFT);
        for ($i = 0; $i < count($disp_optionsArr); $i++) {
            if (substr($disp_header_bin, $i, 1) == "1")
                $mpdf->PDFMakerDispHeader[$disp_optionsArr[$i]] = true;
            else
                $mpdf->PDFMakerDispHeader[$disp_optionsArr[$i]] = false;
        }

        $disp_footer = $Settings["disp_footer"];
        $disp_optionsArr = array("df_first", "df_last", "df_other");
        $disp_footer_bin = str_pad(base_convert($disp_footer, 10, 2), 3, "0", STR_PAD_LEFT);
        for ($i = 0; $i < count($disp_optionsArr); $i++) {
            if (substr($disp_footer_bin, $i, 1) == "1")
                $mpdf->PDFMakerDispFooter[$disp_optionsArr[$i]] = true;
            else
                $mpdf->PDFMakerDispFooter[$disp_optionsArr[$i]] = false;
        }
    }

    public function GetReleasesNotif() {
        global $mod_strings, $app_strings;

        $mpdf_ver = "";
        $releases = "";
        $notif = "";

        $user_prefs = $this->GetUserSettings();
        if ($user_prefs["is_notified"] == "0")
            return $notif;

        if (is_file("modules/PDFMaker/mpdf/mpdf.php")) {
            include_once("mpdf/mpdf.php");
            $mpdf_ver = @constant("PDFMAKER_mPDF_VERSION");
        }

        if ($this->version_type != "deactivate") {
            $client = new soapclient2("http://www.crm4you.sk/PDFMaker/ITS4YouWS.php", false);
            $client->soap_defencoding = 'UTF-8';
            $err = $client->getError();

//             $this->version_no = 2;
//             $mpdf_ver = 2;

            $params = array("pdfmaker" => $this->version_no,
                "mpdf" => $mpdf_ver
            );

            $releases = $client->call("check_last_releases", $params);
            $checkArr = explode("_", $releases);
            if (count($checkArr) == 4) {
                if ($checkArr[1] != "ok")
                    $notif = '<a href="' . $checkArr[0] . '" onclick="return confirm(\'' . $app_strings["ARE_YOU_SURE"] . '\');" title="PDF Maker download" style="color:red;">' . $mod_strings["LBL_NEW_PDFMAKER"] . " " . $checkArr[1] . " " . $mod_strings["LBL_AVAILABLE"] . ".</a><br />";
                if ($checkArr[3] != "ok")
                    $notif .= '<a href="javascript:void(0)" onclick="downloadNewRelease(\'mpdf\', \'' . $checkArr[2] . '\', \'' . $app_strings["ARE_YOU_SURE"] . '\');" title="mPDF download" style="color:red;">' . $mod_strings["LBL_NEW_MPDF"] . " " . $checkArr[3] . " " . $mod_strings["LBL_AVAILABLE"] . ".</a><br />";
            }
        }

        return $notif;
    }

    public function GetCustomLabels() {
        require_once("\x6d\x6f\x64\165l\145s/\120\104\x46Ma\153\145\x72/\x63la\163s\x65\163\x2f\x50\x44\x46\115a\x6be\162L\x61b\x65\154\056\x63l\x61\x73\163.\160\x68\x70");
        $x0b = array();
        $x0c = array();
        if ($this->version_type == "profe\163\x73i\x6fna\x6c") {
            $x0d = "\x53\105\x4cE\103\x54\x20k\x2el\x61\x62el\x5fid\054 \153\x2e\x6ca\x62e\x6c_ke\171, v\x2e\154\141n\x67\137\x69\144,\x20\x76.\x6ca\x62\145\154\137v\141l\x75\145\015
        \x20  \x20\x20\040  \x20\040\x20\x20\x20\040\x20\x20\040\106\122OM vtig\145\162\x5f\160\x64\x66\155\x61\153\x65r\x5f\x6ca\x62el_ke\x79\x73 \x41\x53\x20\153\015\012\040\x20 \040 \x20\x20 \040 \040 \040\040 \x20\040\040\x20\040\114\x45\x46\x54\x20\112O\x49N \166\164\x69g\145\162\137\x70\144\x66\155\141\x6ber_\x6c\141b\145\154\x5f\166a\154\x73\040\x41\123 v\x0d\x0a\040\040\x20\040\x20  \x20\x20\x20\x20    \040\040 \x20   \x20\040\125\123\111N\x47\x28\154\x61\x62\145l\x5fi\144)";
            $x0e = $this->db->query($x0d);
            while ($x0f = $this->db->fetchByAssoc($x0e)) {
                if (!isset($x0b[$x0f["l\x61\x62e\154\137id"]])) {
                    $x10 = new PDFMakerLabel($x0f["\x6ca\x62el_\x69d"], $x0f["\154\x61\142\x65\x6c\x5f\153e\x79"]);
                    $x0b[$x0f["\154\x61\x62\145\154_\x69\x64"]] = $x10;
                } else {
                    $x10 = $x0b[$x0f["\154\x61b\145\x6c\137i\x64"]];
                }$x10->SetLangValue($x0f["\154\141\x6e\147_\x69d"], $x0f["l\x61b\x65\154_\x76\x61\154\x75\x65"]);
            } $x0d = "\123E\x4c\x45C\124\x20\x2a\040\106ROM\040vt\x69g\145\x72\137\x6c\141\x6e\147\x75\141\147\x65 \127\x48\x45\122\x45\x20\x61\x63\x74\151\166\x65\x20\x3d \x31\x20ORDER\x20\x42Y\x20\x69\x64 \x41\123\x43";
            $x0e = $this->db->query($x0d);
            while ($x0f = $this->db->fetchByAssoc($x0e)) {
                $x0c[$x0f["i\x64"]]["\x6e\141\155\145"] = $x0f["\156\141m\x65"];
                $x0c[$x0f["\151d"]]["\160r\x65\146\151x"] = $x0f["p\x72\x65\x66i\170"];
                $x0c[$x0f["\x69\x64"]]["\154a\142\x65\x6c"] = $x0f["la\142\145\x6c"];
                foreach ($x0b as $x12) {
                    if ($x12->IsLangValSet($x0f["\x69d"]) == false)
                        $x12->SetLangValue($x0f["i\144"], "");
                }
            }
        }return array($x0b, $x0c);
    }

    public function GetAvailableSettings() {
        $x0b = array();
        $x0b["\x50\104\106\115\x61\153\145\162\105\x78\x74\x65\x6e\x73io\156s"]["\154o\x63atio\156"] = "\x69\156d\145x\x2ep\150\x70\077\155\x6f\144\x75l\145\x3d\120\104\106M\141\x6b\x65\162\x26ac\164ion=\105\x78\164\145n\163\151\x6f\156s\046p\141re\x6e\x74\164a\142=Set\x74\151n\x67\x73";
        $x0b["\120D\106\x4da\x6b\145\162\x45\x78te\156\x73ions"]["\x69\x6d\x61ge\x5f\x73r\x63"] = "\x74hem\145\x73\057i\x6d\x61g\x65\x73/s\x65\x74\055\111co\124\x77\157TabC\157n\x66i\147.gi\x66";
        $x0b["PD\x46M\x61k\x65\x72\105\170te\x6es\x69\x6f\x6e\163"]["\144\x65\163\143"] = getTranslatedString("L\102L\137\x45\x58\x54\x45\116\123\111O\x4eS_D\x45\123\x43", "\120\104\x46M\141\x6be\x72");
        $x0b["PD\x46\115a\153\x65rE\x78t\145\156\x73\x69\x6f\x6e\x73"]["\154\x61\x62\x65\x6c"] = getTranslatedString("L\102\x4c\x5fE\130\124\x45\x4e\123IO\x4eS", "\120D\106\115a\x6be\x72");
        $x0b["\120D\106\115\141ke\x72\x50\162\x69\166i\x6c\x65g\151\x65\x73"]["\x6c\x6f\x63\x61t\x69\x6f\156"] = "\x69\156\x64e\x78\x2e\160\150\160\077m\x6f\x64\x75\154\145=\x50\104\106\x4d\141\x6be\162&\141c\x74\151o\x6e\075\120\x72of\151\154es\x50\x72\x69\x76\151\x6c\145g\151es\x26\x70a\x72\x65\x6e\x74\x74\141\142=\x53ett\x69\156\147\x73";
        $x0b["\x50\x44F\115ak\145\x72\120\x72i\x76\x69\x6ce\x67\x69\x65\163"]["\151m\x61\x67e\x5fsr\143"] = "th\145\x6d\145s/images\x2f\x69c\x6f-\x70rofi\x6ce.\147i\x66";
        $x0b["\120DF\115\141\153\145r\x50ri\x76\x69legi\145\x73"]["\144\145\x73c"] = getTranslatedString("\x4c\102\x4c\x5fPR\117FILES\x5fD\105SC", "P\x44\106\115ak\145\162");
        $x0b["\x50\x44\x46\x4d\x61\153\x65\162P\x72\151v\151\x6c\145gi\x65\163"]["l\141\x62e\154"] = getTranslatedString("\114\x42\114\x5fP\122O\106I\114\105S", "\120DF\x4d\141\153e\162");
        if ($this->version_type == "\160\162\157\146\145\163s\151on\141\154") {
            $x0b["P\104FM\141\153\x65\x72\103\165\x73t\157m\x4c\141b\x6c\145\x73"]["locat\151\x6fn"] = "i\156de\x78.\x70\150\x70\x3f\155\x6fd\x75\x6c\x65\x3dP\104F\115ak\145\162\046\141\x63ti\157\156\075\x43u\163t\x6f\155\x4ca\x62\145\154s&par\x65nt\164ab=\123e\164\x74\151\156\x67s";
            $x0b["\120\104F\115\141k\x65rCu\x73t\157\x6d\x4c\x61\142\x6c\145\163"]["\151\155\x61ge\137\163\x72\143"] = "\164\150\x65\155\145s\057i\155\141g\145s/\160i\x63kl\x69s\164\x2e\147\x69\x66";
            $x0b["P\104F\115\x61\x6b\145\x72\x43\165\163\x74\x6f\155\x4c\141b\154\x65s"]["\x64es\143"] = getTranslatedString("\114\x42\114\x5fC\125STO\115\x5f\x4c\x41\102E\x4cS\x5f\x44E\x53\103", "\120DF\x4da\x6b\x65\x72");
            $x0b["P\104\x46\x4d\x61ker\103\165\x73to\x6dL\141\x62\x6ces"]["\x6c\x61\142\x65l"] = getTranslatedString("\114\x42\114\x5fC\125\x53\124O\x4d_\x4cA\x42\105LS", "\120\x44F\x4da\x6b\145\162");
        } $x0b["\x50\x44\106\x4dak\145\x72\120roductBl\x6f\143\153Tp\x6c"]["l\x6f\143a\164\151o\x6e"] = "in\x64\145\170.\160\x68p\077\x6do\x64\x75l\x65=\x50\x44\x46\115\141ker\x26\x61\x63\164\x69\x6f\x6e=Pr\x6fd\x75\x63\164\102l\157ck\x73\046\x70\x61\162\145n\164\164\x61\142\x3dS\x65tt\x69\156\x67\x73";
        $x0b["\x50\104\x46\x4d\141ke\162P\x72o\x64\165\143\x74\102\154o\x63\x6b\124\x70\x6c"]["i\155\x61ge_s\x72\x63"] = "t\150\x65m\x65\x73\x2fi\155\x61\x67\x65\x73\057\164\x65\x72ms.\147i\x66";
        $x0b["P\104\x46Ma\x6b\x65\x72\120\x72od\x75\143t\102\x6c\157\143\x6b\x54\x70\154"]["\x64\x65sc"] = getTranslatedString("\x4c\x42\114_P\122\117\x44UCT\102\114O\103\113\x54PL\137D\105\123\103", "\x50\x44\x46\115\141\153\145\x72");
        $x0b["\120\x44\106\115\x61\x6b\x65\162\120\x72\157d\x75\x63\x74\x42\x6c\x6f\143\x6b\124\160\x6c"]["l\x61\142\x65\x6c"] = getTranslatedString("\114\x42L\137\120R\x4f\104\125\103\124B\x4c\x4f\103\x4b\124\120\x4c", "\120D\106\x4d\141k\x65\x72");
        $x0b["\120D\106\115a\153\x65\162\x4d\171Pr\x65\146\145\x72\x65\x6e\x63\x65\x73"]["\x6c\x6f\143\x61\164io\156"] = "\x69\156\x64\145\x78\x2ep\150\x70\077\x6d\x6f\x64u\x6ce\x3dP\x44F\115\141\153\x65r\x26\x61\143\x74io\x6e\075\x4d\171P\162\x65f\145\x72\x65n\x63\145\x73&\160are\x6e\164\164ab\075\x53\145tt\151\156\x67\163";
        $x0b["\x50DF\x4dak\145\162\115\x79P\162\145\x66\145r\145n\x63\x65\163"]["\x69\x6da\147\x65\137s\x72c"] = "\164\x68e\x6de\163\057ima\x67e\163\057i\143\157\x2d\x72\x6f\154\x65\163\056gif";
        $x0b["\x50\x44\106M\141\x6b\145r\115\171Pr\145\146\x65\x72\145n\x63\145\x73"]["\x64\145s\143"] = getTranslatedString("LBL_\115\131\x50R\105\x46\x5f\104\x45\x53\x43", "P\104\x46Ma\x6b\x65\162");
        $x0b["\x50\104F\x4d\141\x6b\x65\x72M\171\120\x72e\146e\162\145\156\143\x65\163"]["lab\145l"] = getTranslatedString("\x4cB\114\137\115Y\x50\122E\x46", "\120\104F\x4d\x61k\x65\x72");
        $x0b["\120\x44FMa\x6be\162\125p\147r\141de"]["\x6co\143\141\x74i\x6fn"] = "i\156\x64\145\x78\056\160\150\x70\077\x6d\157\x64u\x6c\x65\075\x53e\x74t\151\x6e\x67\x73\046\x61\143ti\x6fn=\x4d\157d\165\154e\x4d\141\156\x61\147e\162\046\x6do\x64\x75\154\x65\x5fup\x64at\145=\x53\164\x65\x701\x26\x73r\143\x5f\x6d\157\144\x75\x6c\145=PD\106\x4d\x61\153e\162&\x70a\162\145\x6e\164\164\x61\142\x3d\123\x65\x74\x74\151\x6e\147\x73";
        $x0b["P\x44\x46\x4d\x61\153e\x72U\160\x67r\x61\x64\x65"]["\x69\155\141\147\x65_\x73rc"] = "\164\x68eme\x73\x2f\x69\x6d\141\x67\x65s\057v\x74\x6ci\142\x5fm\157\x64m\156\147.\x67\x69\x66";
        $x0b["\x50D\x46\x4da\x6b\x65\162Upgr\x61d\145"]["\x64e\x73\x63"] = getTranslatedString("L\x42\x4c\x5f\125P\x47R\x41\104\105", "\x53\145\x74\164i\x6egs") . "\x20P\x44\x46M\x61\x6b\x65\162";
        $x0b["\120D\x46M\x61\153\x65\162\125\x70\x67r\x61\144\x65"]["la\x62\x65l"] = getTranslatedString("\114\x42\x4c_U\120\107\x52\x41\x44\x45", "Se\x74t\x69\x6e\147\163");
        $x0b["PD\x46\x4d\141\x6b\x65\162\x44\145\142\x75\x67\147\x69n\147"]["\154o\143\x61ti\157\156"] = "\x69\156dex\056\x70\150\160?\155\x6f\x64\x75\x6ce\075P\x44FMa\153\x65\162\x26\141\143t\x69\x6fn=D\x65b\x75\147gin\x67\046\x70a\x72\145\x6e\x74ta\x62=\123e\164t\151\x6egs";
        $x0b["P\104\x46\115\141\153\145\x72\x44\145\142u\147g\x69\x6e\147"]["\x69m\141g\145_\163r\143"] = "the\x6d\x65\x73\x2f\x69mag\145\163\057\x73\145\164-\x49\x63\157\x4c\x6fg\x69\x6e\110i\x73\164o\162y.g\x69\146";
        $x0b["P\x44F\x4dak\145\162De\142\x75\x67\147\x69\156g"]["d\145sc"] = getTranslatedString("L\102\x4c\137\x44E\102\x55\x47_D\105S\103", "\120DFMa\x6b\x65\162");
        $x0b["\120\x44\x46\115\x61\153e\x72\104\145b\165\147g\x69n\147"]["\x6c\141b\x65l"] = getTranslatedString("\114B\114\x5f\104EB\125\107", "P\x44\106\115\141\x6b\x65r");
        $x0b["P\104\x46Ma\153e\162\114\151\x63e\156\163\145"]["\x6c\x6f\x63\141\x74\x69o\x6e"] = "\x69\x6e\144\145\x78\x2e\x70\150\160\x3fm\x6fdu\154\x65\x3d\x50\x44FMa\x6be\162&act\151o\156\x3d\x4c\x69c\145\156\163\145\046par\x65\156tt\141\142\075S\x65\x74t\x69\156\147\x73";
        $x0b["\x50\104\106\x4d\141k\x65\x72\x4ci\x63\145\156s\x65"]["\x69\x6dage_s\x72c"] = "\164\150\145\155\145\163/\x69mag\x65\163\x2f\x70\x72\157\170\171\x2e\x67i\146";
        $x0b["P\x44\x46\x4d\x61k\x65r\x4ci\x63en\163e"]["desc"] = getTranslatedString("\x4c\x42\x4c\137\114\111C\x45\x4e\x53E_D\105\123\x43", "PD\106\115\141\153\145r");
        $x0b["\x50\104F\x4d\141\x6b\x65\x72\x4c\151\143\145\156\x73\x65"]["\154\x61\x62\145\154"] = getTranslatedString("LB\x4c\137\x4cI\x43\x45\x4eS\x45", "P\x44\106\x4d\141\153e\x72");
        $x0b["\x50D\106\x4d\x61k\145rU\x6e\151\156\x73t\141l\154"]["\x6c\x6fca\164io\x6e"] = "i\x6ed\145x.p\150p?\x6do\x64\x75l\x65=P\x44F\x4dake\x72&ac\x74i\x6f\156\075U\156\151\x6e\x73\164a\154\x6c&\160\141\162\145\156\x74\x74\141\x62\x3d\x53\x65t\164\x69\156\x67s";
        $x0b["\120\104\x46M\141\x6b\x65\x72\125nin\x73t\141\x6c\154"]["i\155\x61\x67e\x5fs\x72c"] = "\x74\150\x65\155\145\x73\057\151\155\x61\147\145\x73\x2fsy\x73\164\x65m.\147i\146";
        $x0b["\x50\104\106M\141k\x65\x72\x55\x6e\151\x6e\x73\x74\x61l\154"]["\x64\x65\163\143"] = getTranslatedString("\114\x42\114\x5f\x55\116I\116\x53\124\x41\114\114\137\x44\105\x53C", "\120\x44\106\x4d\x61\x6b\145\x72");
        $x0b["PD\106\x4d\141\x6b\145\x72\x55\x6e\x69n\163t\x61\x6c\154"]["\x6c\x61b\145\154"] = getTranslatedString("\x4c\x42L_UNI\116S\124\101L\114", "\x50\x44\106\115\141ke\162");
        return $x0b;
    }

    public function GetProductBlockFields() {
        global $mod_strings, $current_user;
        $result = array();

        //Product block
        $Article_Strings = array("" => $mod_strings["LBL_PLS_SELECT"],
            "PRODUCTBLOC_START" => $mod_strings["LBL_ARTICLE_START"],
            "PRODUCTBLOC_END" => $mod_strings["LBL_ARTICLE_END"]
        );
        $result["ARTICLE_STRINGS"] = $Article_Strings;

        //Common fields for product and services
        $Product_Fields = array("PS_CRMID" => $mod_strings["LBL_RECORD_ID"],
            "PS_NO" => $mod_strings["LBL_PS_NO"],
            "PRODUCTPOSITION" => $mod_strings["LBL_PRODUCT_POSITION"],
            "CURRENCYNAME" => $mod_strings["LBL_CURRENCY_NAME"],
            "CURRENCYCODE" => $mod_strings["LBL_CURRENCY_CODE"],
            "CURRENCYSYMBOL" => $mod_strings["LBL_CURRENCY_SYMBOL"],
            "PRODUCTNAME" => $mod_strings["LBL_VARIABLE_PRODUCTNAME"],
            "PRODUCTTITLE" => $mod_strings["LBL_VARIABLE_PRODUCTTITLE"],
            "PRODUCTDESCRIPTION" => $mod_strings["LBL_VARIABLE_PRODUCTDESCRIPTION"],
            "PRODUCTEDITDESCRIPTION" => $mod_strings["LBL_VARIABLE_PRODUCTEDITDESCRIPTION"]);

        if ($this->db->num_rows($this->db->query("SELECT tabid FROM vtiger_tab WHERE name='Pdfsettings'")) > 0)
            $Product_Fields["CRMNOWPRODUCTDESCRIPTION"] = $mod_strings["LBL_CRMNOW_DESCRIPTION"];

        $Product_Fields["PRODUCTQUANTITY"] = $mod_strings["LBL_VARIABLE_QUANTITY"];
        $Product_Fields["PRODUCTUSAGEUNIT"] = $mod_strings["LBL_VARIABLE_USAGEUNIT"];
        $Product_Fields["PRODUCTLISTPRICE"] = $mod_strings["LBL_VARIABLE_LISTPRICE"];
        $Product_Fields["PRODUCTTOTAL"] = $mod_strings["LBL_PRODUCT_TOTAL"];
        $Product_Fields["PRODUCTDISCOUNT"] = $mod_strings["LBL_VARIABLE_DISCOUNT"];
        $Product_Fields["PRODUCTDISCOUNTPERCENT"] = $mod_strings["LBL_VARIABLE_DISCOUNT_PERCENT"];
        $Product_Fields["PRODUCTSTOTALAFTERDISCOUNT"] = $mod_strings["LBL_VARIABLE_PRODUCTTOTALAFTERDISCOUNT"];
        $Product_Fields["PRODUCTVATPERCENT"] = $mod_strings["LBL_PROCUCT_VAT_PERCENT"];
        $Product_Fields["PRODUCTVATSUM"] = $mod_strings["LBL_PRODUCT_VAT_SUM"];
        $Product_Fields["PRODUCTTOTALSUM"] = $mod_strings["LBL_PRODUCT_TOTAL_VAT"];
        $result["SELECT_PRODUCT_FIELD"] = $Product_Fields;

        //Available fields for products
        $prod_fields = array();
        $serv_fields = array();

        $in = '0';
        if (vtlib_isModuleActive('Products'))
            $in = getTabId('Products');
        if (vtlib_isModuleActive('Services')) {
            if ($in == '0')
                $in = getTabId('Services');
            else
                $in .= ', ' . getTabId('Services');
        }
        $sql = "SELECT  t.tabid, t.name,
                        b.blockid, b.blocklabel,
                        f.fieldname, f.fieldlabel
                FROM vtiger_tab AS t
                INNER JOIN vtiger_blocks AS b USING(tabid)
                INNER JOIN vtiger_field AS f ON b.blockid = f.block
                WHERE t.tabid IN (" . $in . ")
                    AND (f.displaytype != 3 OR f.uitype = 55)
                ORDER BY t.name ASC, b.sequence ASC, f.sequence ASC, f.fieldid ASC";
        $res = $this->db->query($sql);
        while ($row = $this->db->fetchByAssoc($res)) {
            $module = $row["name"];
            $fieldname = $row["fieldname"];
            if (getFieldVisibilityPermission($module, $current_user->id, $fieldname) != '0')
                continue;

            $trans_field_nam = strtoupper($module) . "_" . strtoupper($fieldname);
            switch ($module) {
                case "Products":
                    $trans_block_lbl = getTranslatedString($row["blocklabel"], 'Products');
                    $trans_field_lbl = getTranslatedString($row["fieldlabel"], 'Products');
                    $prod_fields[$trans_block_lbl][$trans_field_nam] = $trans_field_lbl;
                    break;

                case "Services":
                    $trans_block_lbl = getTranslatedString($row["blocklabel"], 'Services');
                    $trans_field_lbl = getTranslatedString($row["fieldlabel"], 'Services');
                    $serv_fields[$trans_block_lbl][$trans_field_nam] = $trans_field_lbl;
                    break;

                default:
                    continue;
            }
        }
        $result["PRODUCTS_FIELDS"] = $prod_fields;
        $result["SERVICES_FIELDS"] = $serv_fields;

        return $result;
    }

    public function GetRelatedBlocks($select_module) {
        global $mod_strings;

        $Related_Blocks[""] = $mod_strings["LBL_PLS_SELECT"];
        if ($select_module != "") {
            $rel_module_id = getTabid($select_module);
            $restricted_modules = array('Emails', 'Events', 'Webmails');
            $Related_Modules = array();

            $rsql = "SELECT t.name FROM vtiger_tab AS t
                    INNER JOIN vtiger_relatedlists AS rl ON t.tabid = rl.related_tabid
                    WHERE t.isentitytype=1
                        AND t.name NOT IN(" . generateQuestionMarks($restricted_modules) . ")
                        AND t.presence=0 AND rl.label!='Activity History'
                        AND rl.tabid = ? AND t.tabid != ?";

            $params = $restricted_modules;
            array_push($params, $rel_module_id, $rel_module_id);
            $relatedmodules = $this->db->pquery($rsql, $params);

            if ($this->db->num_rows($relatedmodules)) {
                while ($resultrow = $this->db->fetch_array($relatedmodules)) {
                    $Related_Modules[] = $resultrow['name'];
                }
            }

            if (count($Related_Modules) > 0) {
                $sql = "SELECT * FROM vtiger_pdfmaker_relblocks
                        WHERE secmodule IN(" . generateQuestionMarks($Related_Modules) . ")
                            AND deleted = 0
                        ORDER BY relblockid";
                $result = $this->db->pquery($sql, $Related_Modules);
                while ($row = $this->db->fetchByAssoc($result)) {
                    $Related_Blocks[$row["relblockid"]] = $row["name"];
                }
            }
        }

        return $Related_Blocks;
    }

    public function GetUserSettings($userid = "") {
        global $current_user;

        $userid = ($userid == "" ? $current_user->id : $userid);

        $sql = "SELECT * FROM vtiger_pdfmaker_usersettings WHERE userid = ?";
        $result = $this->db->pquery($sql, array($userid));

        $settings = array();
        if ($this->db->num_rows($result) > 0) {
            while ($row = $this->db->fetchByAssoc($result)) {
                $settings["is_notified"] = $row["is_notified"];
            }
        } else {
            $settings["is_notified"] = "0";
        }

        return $settings;
    }

}
