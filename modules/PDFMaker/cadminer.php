<?php
/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

// global $dbconfig;

function adminer_object() {
    // required to run any plugin
    include_once "./adminer/plugins/plugin.php";
    include_once "../../config.inc.php";
//     global $dbconfig;

    // autoloader
    foreach (glob("adminer/plugins/*.php") as $filename) {
        include_once "./$filename";
    }

    $driver = "server";
    if($dbconfig["db_type"] != "mysql")
        $driver = "pgsql";

    $plugins = array(
        // specify enabled plugins here
        new AdminerFrames(true),
        new AdminerLoginServers($dbconfig["db_server"], $driver, $dbconfig["db_username"], $dbconfig["db_password"]),
        new AdminerDatabaseHide($dbconfig["db_name"])
    );

    /* It is possible to combine customization and plugins:
    class AdminerCustomization extends AdminerPlugin {
    }
    return new AdminerCustomization($plugins);
    */

    return new AdminerPlugin($plugins);
}

// include original Adminer or Adminer Editor
include "./adminer/adminer.php";

?>