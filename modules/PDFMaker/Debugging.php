<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once("include/utils/utils.php");
require_once('modules/PDFMaker/PDFMaker.php');

// require_once('Smarty_setup.php');
require_once('data/Tracker.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');

global $mod_strings, $app_strings, $theme, $currentModule, $adb, $default_charset, $vtiger_current_version;
Debugger::GetInstance()->Init();

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);

$PDFMaker = new PDFMaker();
if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] != "export") {
    switch ($_REQUEST["mode"]) {
        case "save":
            if (isset($_REQUEST["is_debugging_on"]) && $_REQUEST["is_debugging_on"] == "on")
                Debugger::GetInstance()->SetDebugVal(true);
            else
                Debugger::GetInstance()->SetDebugVal(false);
            echo '<meta http-equiv="refresh" content="0;url=index.php?module=Settings&action=ModuleManager&module_settings=true&formodule=PDFMaker&parenttab=Settings">';
            break;

        case "smarty":
            $res = true;
            $dir = new DirectoryIterator(getcwd() . "/Smarty/templates_c");
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot()) {
                    $res = unlink($fileinfo->getPathname());
                }
            }
            //      we need to check whether the files have been really deleted
            if ($res)
                echo $mod_strings["LBL_SMARTY_DELETED"];
            else
                echo $mod_strings["LBL_SMARTY_NOT_DELETED"];
            exit;
            break;
    }
}
else {
//  current selected tab 
    if (isset($_REQUEST["tab"]) && $_REQUEST["tab"] != "") {
        $selTab = $_REQUEST["tab"];
    }
    $smarty->assign("SELTAB", $selTab);

    $tmpVal = "off";
    if (Debugger::GetInstance()->GetDebugVal() === true)
        $tmpVal = "on";
    $smarty->assign("DEBUG_ON_CHECKED", $tmpVal);

//  memory limit
    $memory_limit1 = ini_get("memory_limit");
    ini_set("memory_limit", "256M");
    $memory_limit2 = ini_get("memory_limit");

    $notif = '<img src="themes/images/yes.gif" title="' . $mod_strings["LBL_OK"] . '" alt="' . $mod_strings["LBL_OK"] . '">';
    if (substr($memory_limit2, 0, -1) <= 128)
        $notif = '<img src="themes/images/no.gif" title="' . $mod_strings["LBL_LOWVAL"] . '" alt="' . $mod_strings["LBL_LOWVAL"] . '">';
    elseif (substr($memory_limit2, 0, -1) < 256)
        $notif = '<img src="themes/images/HelpDesk.png" width="13" title="' . $mod_strings["LBL_MINVAL"] . '" alt="' . $mod_strings["LBL_MINVAL"] . '">';

    $smarty->assign("DBG_MEMLIMIT_OLD", $memory_limit1);
    $smarty->assign("DBG_MEMLIMIT_NEW", $memory_limit2);
    $smarty->assign("DBG_MEMLIMIT_NOTIF", $notif);

//  max input vars
    $max_in_vars = ini_get("max_input_vars");
    $smarty->assign("DBG_MAXINVARS", $max_in_vars);
    $notif = '<img src="themes/images/yes.gif" title="' . $mod_strings["LBL_OK"] . '" alt="' . $mod_strings["LBL_OK"] . '">';
    if ($max_in_vars <= 1000)
        $notif = '<img src="themes/images/no.gif" title="' . $mod_strings["LBL_LOWVAL"] . '" alt="' . $mod_strings["LBL_LOWVAL"] . '">';
    elseif ($max_in_vars < 5000)
        $notif = '<img src="themes/images/HelpDesk.png" width="13" title="' . $mod_strings["LBL_MINVAL"] . '" alt="' . $mod_strings["LBL_MINVAL"] . '">';
    $smarty->assign("DBG_MAXINVARS_NOTIF", $notif);

//  max execution time
    $max_exec_time = ini_get("max_execution_time");
    $smarty->assign("DBG_MAXEXTIME", $max_exec_time);
    $notif = '<img src="themes/images/yes.gif" title="' . $mod_strings["LBL_OK"] . '" alt="' . $mod_strings["LBL_OK"] . '">';
    if ($max_exec_time <= 60)
        $notif = '<img src="themes/images/no.gif" title="' . $mod_strings["LBL_LOWVAL"] . '" alt="' . $mod_strings["LBL_LOWVAL"] . '">';
    elseif ($max_exec_time < 600)
        $notif = '<img src="themes/images/HelpDesk.png" width="13" title="' . $mod_strings["LBL_MINVAL"] . '" alt="' . $mod_strings["LBL_MINVAL"] . '">';
    $smarty->assign("DBG_MAXEXTIME_NOTIF", $notif);

//  suhosin
    $suhosin = "true";
    $request_max_vars = 0;
    $post_max_vars = 0;
    if (!extension_loaded('suhosin')) {
        $suhosin = "false";
    } elseif (ini_get("suhosin.simulation") == true) {
        $suhosin = "simulation";
    }

    if ($suhosin != "false") {
        $request_max_vars = ini_get("suhosin.request.max_vars");
        $post_max_vars = ini_get("suhosin.post.max_vars");
    }

    $smarty->assign("DBG_SUHOSIN", $suhosin);
    $smarty->assign("DBG_SUHOSIN_REQ_MAX_VARS", $request_max_vars);
    $smarty->assign("DBG_SUHOSIN_POST_MAX_VARS", $post_max_vars);

//  versions
    include("modules/PDFMaker/version.php");
    $mpdf_ver = "";
    if (is_file("modules/PDFMaker/mpdf/mpdf.php")) {
        include_once("mpdf/mpdf.php");
        $mpdf_ver = @constant("PDFMAKER_mPDF_VERSION");
    }

    $smarty->assign("DBG_PDFMAKER_VERSION", $version);
    $smarty->assign("DBG_MPDF_VERSION", $mpdf_ver);
    $smarty->assign("DBG_VTIGER_VERSION", $vtiger_current_version);
    $smarty->assign("DBG_PHP_VERSION", phpversion());

//  DATABASE TAB
//  iframe is used within the smarty template
//  selected files permissions (user_privileges/*.*; tabdata.php; parenttab.php)
    $controled_paths = array();

    $user_priv_dir = getcwd() . "/user_privileges";
    $dir = new DirectoryIterator($user_priv_dir);
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
            if ($fileinfo->isFile() && $fileinfo->isWritable() == false) {
                $controled_paths["user_privileges/*.*"] = false;
            }
        }
    }

    $tabdata = getcwd() . "/tabdata.php";
    if (!vtlib_isWriteable($tabdata))
        $controled_paths["tabdata.php"] = false;

    $parenttab = getcwd() . "/parent_tabdata.php";
    if (!vtlib_isWriteable($parenttab))
        $controled_paths["parent_tabdata.php"] = false;

    $smarty->assign("DBG_ITEMS_PERMS", $controled_paths);

//  PHPINFO TAB
    $phpinfo = phpinfo_content();
    $smarty->assign("DBG_PHPINFO", $phpinfo);

    $smarty->assign("INCL_TEMPLATE_NAME", vtlib_getModuleTemplate($currentModule, 'DebuggingInfo.tpl'));

    if (!isset($_REQUEST["mode"]) || $_REQUEST["mode"] != "export") {
        $smarty->display(vtlib_getModuleTemplate($currentModule, 'Debugging.tpl'));
    } else {
        $smarty->assign("EXPORTING", "true");
        $smarty->assign("THEME", $theme);
        $smarty->assign("CHARSET", $default_charset);
        $fileContent = $smarty->fetch(vtlib_getModuleTemplate($currentModule, 'DebuggingInfo.tpl'));
        header("Content-type: text/html");
        header("Content-Disposition: attachment; filename=debug_export.html");
        echo $fileContent;
        exit;
    }
}

function phpinfo_content() {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_contents();
    ob_end_clean();

    $info_arr = array();
    $info_arr = explode('<div class="center">', $phpinfo, 2);
    $info_arr = explode('<h2>PHP License</h2>', $info_arr[1], 2);

    return $info_arr[0];
}

function get_allowed_tables($tablesPattern = "vtiger_pdfmaker%") {
    global $adb;

    $tables = array();
    $result = $adb->query("SHOW TABLES LIKE '" . $tablesPattern . "'");
    $idx = 0;
    while ($row = $adb->fetchByAssoc($result)) {
        $tableName = $row[key($row)];
        $tables[$idx] = $tableName;
        $idx++;
    }

    return $tables;
}

function db_info() {
    global $adb;

    $desc = array();
    $counts = array();

    $tables = get_allowed_tables();
    foreach ($tables as $idx => $tableName) {
        $countRes = $adb->query("SELECT COUNT(*) AS recs FROM " . $tableName);
        $counts[$idx] = $adb->query_result($countRes, 0, "recs");

        $descRes = $adb->query("DESCRIBE " . $tableName);
        $fieldIdx = 0;
        while ($descRow = $adb->fetchByAssoc($descRes)) {
            $desc[$idx][$fieldIdx]["field"] = $descRow["field"];
            $desc[$idx][$fieldIdx]["type"] = $descRow["type"];
            $desc[$idx][$fieldIdx]["null"] = $descRow["null"];
            $desc[$idx][$fieldIdx]["key"] = $descRow["key"];
            $desc[$idx][$fieldIdx]["default"] = $descRow["default"];
            $desc[$idx][$fieldIdx]["extra"] = $descRow["extra"];
            $fieldIdx++;
        }
    }

    return array($tables, $counts, $desc);
}

function table_info($tableName) {
    global $adb;

    $info = array();
    $records = array();
    $cols = array();
    $idx = 0;

    $tables = get_allowed_tables();
    if (in_array($tableName, $tables)) {
        $result = $adb->query("SELECT * FROM " . $tableName);

        while ($row = $adb->fetchByAssoc($result)) {
            foreach ($row as $key => $val) {
                $records[$idx][$key] = $val;
                if ($idx == 0) {
                    $cols[] = $key;
                }
            }
            $idx++;
        }
    }
    $info["count"] = $idx;
    $info["name"] = $tableName;
    $info["cols"] = $cols;
    return array($info, $records);
}
