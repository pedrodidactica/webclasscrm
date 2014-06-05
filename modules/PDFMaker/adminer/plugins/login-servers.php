<?php

/** Display constant list of servers in login form
* @link http://www.adminer.org/plugins/#use
* @author Jakub Vrana, http://www.vrana.cz/
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/
class AdminerLoginServers {
	/** @access protected */
	var $server, $driver, $user, $pass;
	
	/** Set supported servers
	* @param array array($domain) or array($domain => $description) or array($category => array())
	* @param string
	*/
	function AdminerLoginServers($server, $driver = "server", $user, $pass) {
		$this->server = $server;
		$this->driver = $driver;
		$this->user = $user;
		$this->pass = $pass;
	}
	
	function login($login, $password) {
		// check if server is allowed
        return SERVER == $this->server;
	}

	function loginForm() {
		?>
<input type="hidden" name="auth[driver]" value="<?php echo $this->driver; ?>">
<input type="hidden" name="auth[server]" value="<?php echo $this->server; ?>">
<input type="hidden" name="auth[username]" value="<?php echo $this->user; ?>">
<input type="hidden" name="auth[password]" value="<?php echo $this->pass; ?>">
<p><input type="submit" value="<?php echo lang(25); ?>">
<?php
		//echo checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], lang('Permanent login')) . "\n";
		return true;
	}

}
