<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
require_once('include/utils/utils.php');
require_once('modules/EMAILMaker/EMAILMaker.php');
global $adb;

$EMAILMaker = new EmailMaker();

$is_delay_active = $EMAILMaker->controlActiveDelay();

if ($is_delay_active) 
    echo "yes";
else
    echo "no";

?>