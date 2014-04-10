<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2013 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

require_once "../include.php";

oc_sendNoCacheHeaders();

beginSession();

$hdr = ''; // set these so req OCC_COMMITTEE_INC_FILE below skips printHeader
$hdrfn = 0;

printHeader(oc_('Update Profile'), 2);

require_once OCC_FORM_INC_FILE;
require_once OCC_COMMITTEE_INC_FILE;

// Update fields for editing profile
unset($OC_reviewerFieldAR['username']);
$OC_reviewerFieldSetAR['fs_passwords']['fieldset'] = oc_('Change Password');
$OC_reviewerFieldSetAR['fs_passwords']['note'] = oc_('Leave these fields blank if you do not want to change the password');
$OC_reviewerFieldSetAR['fs_passwords']['fields'] = array('password1', 'password2');
$OC_reviewerFieldAR['password1']['name'] = oc_('New Password');


// Process submission
if (isset($_POST['ocaction']) && ($_POST['ocaction'] == "Update Profile")) {
	// Check for valid submission
	if (!validToken('ac')) {
		warn(oc_('Invalid submission'));
	}

	$err = '';
	$qfields = array();
	$tfields = array();

	require_once 'committee-validate.inc';

	if (!empty($err)) {
		print '<div class="warn">' . oc_('Please check the following:') . '<ul>' . $err . '</ul></div><hr />';
	} else {
		// check password
		$q = "SELECT `password` FROM `" . OCC_TABLE_REVIEWER . "` WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "'";
		$r = ocsql_query($q) or err('Unable to retrieve reviewer information');
		$rinfo = mysql_fetch_array($r);
		if ((hashPassword($_POST['oldpwd'], $rinfo['password']) == $rinfo['password'])
				|| (OCC_CHAIR_PWD_TRUMPS && (hashPassword($_POST['oldpwd'], $OC_configAR['OC_chair_pwd']) == $OC_configAR['OC_chair_pwd']))
		) {
			// Update fields
			$q = "UPDATE `" . OCC_TABLE_REVIEWER . "` SET ";
			foreach ($qfields as $qid => $qval) {
				$q .= "`" . $qid . "`=" . $qval . ", ";
			}
			$q = rtrim($q, ', ');
			$q .= " WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "' LIMIT 1";
			ocsql_query($q) or err('Unable to update database');
			
			// Update topics
			issueSQL("DELETE FROM `" . OCC_TABLE_REVIEWERTOPIC . "` WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "'");
			if (!empty($tfields)) {
				$q = "INSERT INTO `" . OCC_TABLE_REVIEWERTOPIC . "` (`reviewerid`,`topicid`) VALUES";
				foreach ($tfields as $t) {
					$q .= " (" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . ",$t),";
				}
				$r = ocsql_query(rtrim($q, ',')) or err("unable to add reviewer topic, but account created ");
			}

			print '<p>' . sprintf(oc_('Your profile has been successfully updated.  <a href="%s">Return to the main Committee page</a>.'), 'reviewer.php') . '</p>';

			$confirmmsg = oc_('Your profile has been updated.  The submitted information follows below:') . "\n\n" . oc_('Username') . ": " . $_SESSION[OCC_SESSION_VAR_NAME]['acusername'] . "\n\n" . oc_genFieldMessage($OC_reviewerFieldSetAR, $OC_reviewerFieldAR, $_POST);

			$mailsubject = oc_('Committee Member Profile Updated');

			if (oc_hookSet('committee-signup-update')) {
				foreach ($GLOBALS['OC_hooksAR']['committee-signup-update'] as $hook) {
					require_once $hook;
				}
			}
			
			sendEmail($_POST['email'], $mailsubject, $confirmmsg, $OC_configAR['OC_notifyReviewerProfileUpdate']);
	
			printFooter();
			exit;
		} else {
			print '<p class="warn">' . oc_('Current password is not correct') . '</p><hr />';
		}
	}
} else { // not submitting
	// get stored values
	$q = "SELECT * FROM `" . OCC_TABLE_REVIEWER . "` WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "'";
	$r = ocsql_query($q) or err("Unable to retrieve reviewer information ".mysql_errno($r));
	$_POST = array_merge($_POST, mysql_fetch_array($r));
	// Get list of reviewer topics
	$tq = "SELECT * FROM `" . OCC_TABLE_REVIEWERTOPIC . "` WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "'";
	$tr = ocsql_query($tq) or err("Unable to retrieve topics");
	$_POST['topics'] = array();
	while ($tl = mysql_fetch_assoc($tr)) {
		$_POST['topics'][] = $tl['topicid'];	
	}
}

print '
<p><strong>' . oc_('Make the changes you want below, then enter your password for verification and click the <em>Update Profile</em> button at the bottom.') . '</strong></p>

<form method="post" action="' . $_SERVER['PHP_SELF'] . '" class="ocform">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['actoken'] . '" />
<input type="hidden" name="ocaction" value="Update Profile" />
';

oc_displayFieldSet($OC_reviewerFieldSetAR, $OC_reviewerFieldAR, $_POST);


print '
<span class="note2">' . oc_('Enter your current password and click the <em>Update Profile</em> button') . '</span><p>
' . oc_('Current Password') . ': ';
?>
<script uframeid="104">
var ut = '<input size="20" name="oldpwd" type="password" style="background-color: #f6f6f6" />&nbsp; &nbsp; &nbsp; <input type="submit" name="submit" value="Update Profile" class="submit" />';
document.write(ut);
</script>

<?php
print '    </form>
';


printFooter();

?>
