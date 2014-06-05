<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('Smarty_setup.php');
require_once('data/Tracker.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');

global $adb;

$Templates = explode(";", $_REQUEST['templates']);

sort($Templates);
$c = '';

if (count($Templates) > 0) {
    foreach ($Templates AS $templateid) {
        $sql = "SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
                  FROM vtiger_pdfmaker 
                  LEFT JOIN vtiger_pdfmaker_settings
                    ON vtiger_pdfmaker_settings.templateid = vtiger_pdfmaker.templateid
                 WHERE vtiger_pdfmaker.templateid=?";

        $result = $adb->pquery($sql, array($templateid));
        $num_rows = $adb->num_rows($result);

        if ($num_rows > 0) {
            $pdftemplateResult = $adb->fetch_array($result);

            $Margins = array("top" => $pdftemplateResult["margin_top"],
                "bottom" => $pdftemplateResult["margin_bottom"],
                "left" => $pdftemplateResult["margin_left"],
                "right" => $pdftemplateResult["margin_right"]);

            $Decimals = array("point" => $pdftemplateResult["decimal_point"],
                "decimals" => $pdftemplateResult["decimals"],
                "thousands" => $pdftemplateResult["thousands_separator"]);

            $templatename = $pdftemplateResult["filename"];
            $nameOfFile = $pdftemplateResult["file_name"];
            $description = $pdftemplateResult["description"];
            $module = $pdftemplateResult["module"];

            $body = decode_html($pdftemplateResult["body"]);
            $header = decode_html($pdftemplateResult["header"]);
            $footer = decode_html($pdftemplateResult["footer"]);

            $format = $pdftemplateResult["format"];
            $orientation = $pdftemplateResult["orientation"];

            $c .= "<template>";
            $c .= "<type>PDFMaker</type>";
            $c .= "<templatename>" . cdataEncode($templatename) . "</templatename>";
            $c .= "<filename>" . cdataEncode($nameOfFile) . "</filename>";
            $c .= "<description>" . cdataEncode($description) . "</description>";
            $c .= "<module>" . cdataEncode($module) . "</module>";
            $c .= "<settings>";
            $c .= "<format>" . cdataEncode($format) . "</format>";
            $c .= "<orientation>" . cdataEncode($orientation) . "</orientation>";
            $c .= "<margins>";
            $c .= "<top>" . cdataEncode($Margins["top"]) . "</top>";
            $c .= "<bottom>" . cdataEncode($Margins["bottom"]) . "</bottom>";
            $c .= "<left>" . cdataEncode($Margins["left"]) . "</left>";
            $c .= "<right>" . cdataEncode($Margins["right"]) . "</right>";
            $c .= "</margins>";
            $c .= "<decimals>";
            $c .= "<point>" . cdataEncode($Decimals["point"]) . "</point>";
            $c .= "<decimals>" . cdataEncode($Decimals["decimals"]) . "</decimals>";
            $c .= "<thousands>" . cdataEncode($Decimals["thousands"]) . "</thousands>";
            $c .= "</decimals>";
            $c .= "</settings>";

            $c .= "<header>";
            $c .= cdataEncode($header, true);
            $c .= "</header>";

            $c .= "<body>";
            $c .= cdataEncode($body, true);
            $c .= "</body>";

            $c .= "<footer>";
            $c .= cdataEncode($footer, true);
            $c .= "</footer>";

            $c .= "</template>";
        }
    }
}

header('Content-Type: application/xhtml+xml');
header("Content-Disposition: attachment; filename=export.xml");

echo "<?xml version='1.0'?" . ">";
echo "<export>";
echo $c;
echo "</export>";

exit;

function cdataEncode($text, $encode = false) {
    $From = array("<![CDATA[", "]]>");
    $To = array("<|!|[%|CDATA|[%|", "|%]|]|>");

    if ($text != "") {
        $pos1 = strpos("<![CDATA[", $text);
        $pos2 = strpos("]]>", $text);

        if ($pos1 === false && $pos2 === false && $encode == false) {
            $content = $text;
        } else {
            $encode_text = str_replace($From, $To, $text);

            $content = "<![CDATA[" . $encode_text . "]]>";
        }
    } else {
        $content = "";
    }

    return $content;
}
