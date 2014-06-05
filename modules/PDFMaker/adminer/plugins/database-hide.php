<?php

/** Show only selected database.
* @link http://www.adminer.org/plugins/#use
* @author Jakub Vrana, http://www.vrana.cz/
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/
class AdminerDatabaseHide {
	protected $enabled;
	
	/**
	* @param array case insensitive database names in values
	*/
	function AdminerDatabaseHide($enabled) {
		$this->enabled = $enabled;
	}
	
	function databases($flush = true) {
		$return = array();
		foreach (get_databases($flush) as $db) {
			if ($db == $this->enabled) {
				$return[] = $db;
				break;
			}
		}
		return $return;
	}
	
	/** Print homepage
	* @return bool whether to print default homepage
	*/
	function homepage() {
		//echo '<p>' . ($_GET["ns"] == "" ? '<a href="' . h(ME) . 'database=">' . lang('Alter database') . "</a>\n" : "");
		//echo (support("scheme") ? "<a href='" . h(ME) . "scheme='>" . ($_GET["ns"] != "" ? lang('Alter schema') : lang('Create schema')) . "</a>\n" : "");
		echo ($_GET["ns"] !== "" ? '<a href="' . h(ME) . 'schema=">' . lang('Database schema') . "</a>\n" : "");
		//echo (support("privileges") ? "<a href='" . h(ME) . "privileges='>" . lang('Privileges') . "</a>\n" : "");
		return true;
	}
}
