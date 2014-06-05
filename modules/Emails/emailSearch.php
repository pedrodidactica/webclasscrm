<?php


global $adb;

$parent_name = $_REQUEST['term'];
$selectModule = $_REQUEST['selectmodule'];

if ($selectModule == 'Contacts') {

	$sql = ("SELECT vtiger_contactdetails.email, vtiger_contactdetails.contactid
		FROM vtiger_contactdetails
		INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
		WHERE deleted =0
		AND (
			lastname LIKE '%$parent_name%'
			OR firstname LIKE '%$parent_name%'
		)
	AND email LIKE '%@%'");

	$result = $adb->query($sql);

	if ($result) {

		$noofrows = $adb->num_rows($result);


		for ($i = 0; $i <= $noofrows; $i++) {

			$contactid = $adb->query_result($result, $i, 'contactid');
			$email = $adb->query_result($result, $i, 'email');
			$SetName= getContactName($contactid);

			$orders['value'] = "{$email}";
			$orders['label'] = "{$SetName}";
			$matches[] = $orders;
		}
	}
}else if ($selectModule == 'Leads') {


	$sql = ("SELECT vtiger_leaddetails.email,vtiger_leaddetails.leadid
		FROM vtiger_leaddetails
		INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
		WHERE deleted =0
		AND (firstname LIKE '%$parent_name%'
			OR lastname LIKE '%$parent_name%')
	AND email LIKE '%@%'   ");

	$result = $adb->query($sql);


	if ($result) {

		$noofrows = $adb->num_rows($result);


		for ($i = 0; $i <= $noofrows; $i++) {

			$leaid = $adb->query_result($result, $i, 'leadid');
			$email = $adb->query_result($result, $i, 'email');
			$SetName=  getLeadName($leaid);

			$orders['value'] = "{$email}";
			$orders['label'] = "{$SetName}";
			$matches[] = $orders;
		}
	}

} else if ($selectModule == 'Accounts') {


	$sql = ("SELECT vtiger_account.email1,vtiger_account.accountid,	accountname
		FROM vtiger_account
		INNER JOIN vtiger_crmentity ON vtiger_account.accountid = vtiger_crmentity.crmid
		WHERE deleted =0
		AND accountname LIKE '%$parent_name%'
		AND email1 LIKE '%@%'   ");

	$result = $adb->query($sql);


	if ($result) {

		$noofrows = $adb->num_rows($result);


		for ($i = 0; $i <= $noofrows; $i++) {

			$accountid = $adb->query_result($result, $i, 'accountid');
			$email = $adb->query_result($result, $i, 'email1');
			$SetName=  $adb->query_result($result, $i, 'accountname');

			$orders['value'] = "{$email}";
			$orders['label'] = "{$SetName}";
			$matches[] = $orders;
		}
	}

} elseif ($selectModule == 'Vendors') {


	$sql = ("SELECT vtiger_vendor.email,vtiger_vendor.vendorname
		FROM vtiger_vendor
		INNER JOIN vtiger_crmentity ON vtiger_vendor.vendorid = vtiger_crmentity.crmid
		WHERE deleted =0
		AND vendorname LIKE '%$parent_name%'
		AND email LIKE '%@%'
		");

	$result = $adb->query($sql);
//If there is text in the search field, this code is executed every time the input changes.

	if ($result) {

		$noofrows = $adb->num_rows($result);


		for ($i = 0; $i <= $noofrows; $i++) {

			$name = $adb->query_result($result, $i, 'vendorname');
			$email = $adb->query_result($result, $i, 'email');

			$orders['value'] = "{$email}";
			$orders['label'] = "{$name}";
			$matches[] = $orders;
		}
	}

} elseif ($selectModule == 'Users') {

	$sql = ("SELECT vtiger_users.email1,vtiger_users.id
		FROM vtiger_users
		WHERE (first_name LIKE '%$parent_name%' 
			OR last_name LIKE '%$parent_name%')
	AND email1 LIKE '%@%'
	");



	$result = $adb->query($sql);
//If there is text in the search field, this code is executed every time the input changes.

	if ($result) {

		$noofrows = $adb->num_rows($result);


		for ($i = 0; $i <= $noofrows; $i++) {

			$userid = $adb->query_result($result, $i, 'id');
			$email = $adb->query_result($result, $i, 'email1');

			$name = getUserFullName($userid);


			$orders['value'] = "{$email}";
			$orders['label'] = "{$name}";
			$matches[] = $orders;
		}
	}
} 

print json_encode($matches);
?>
