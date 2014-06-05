<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

if (isset($_REQUEST["extid"]) && isset($_REQUEST["mode"])) {
//expects that extensions folder contains (<$_REQUEST["extid"]>.txt - manual or <$_REQUEST["extid"]>.zip - download) files      
    $fileext = "";
    $ct = "";
    switch ($_REQUEST["mode"]) {
        case "manual":
            $fileext = "txt";
            $ct = "text/plain";
            break;

        case "download":
            $fileext = "zip";
            $ct = "application/zip";
            break;
    }

    $filename = $_REQUEST["extid"] . "." . $fileext;
    $fullFileName = "modules/PDFMaker/extensions/" . $filename;
    if (file_exists($fullFileName)) {
        $disk_file_size = filesize($fullFileName);
        $filesize = $disk_file_size + ($disk_file_size % 1024);
        $fileContent = fread(fopen($fullFileName, "r"), $filesize);
        header("Content-type: $ct");
        header("Pragma: public");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Description: PHP Generated Data");
        echo $fileContent;
    } else {
        $_SESSION["download_error"] = "true";
        header("Location: index.php?module=PDFMaker&action=Extensions&parenttab=Settings");
    }
} else {
    $_SESSION["download_error"] = "true";
    header("Location: index.php?module=PDFMaker&action=Extensions&parenttab=Settings&download_error=true");
}
