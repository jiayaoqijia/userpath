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

$errmsg = "";

if (isset($_POST['submit']) && ($_POST['submit'] == "Sign In") && isset($_POST['uname']) && !empty($_POST['uname']) && isset($_POST['upwd']) && !empty($_POST['upwd'])) {
	// Check for too many failed attempts
	$failedNum = 0;
	$failedTime = 0;
	if (!empty($OC_configAR['OC_chairFailedSignIn']) && ($OC_configAR['OC_chairFailedSignIn'] != 'skip')) {
		list($lastFailedNum, $lastFailedTime) = explode(':', $OC_configAR['OC_chairFailedSignIn']);
		if ((time() - $lastFailedTime) < (60 * 5)) { 	// is last failed attempt < 5 minutes ago
			$failedNum = $lastFailedNum;
			$failedTime = $lastFailedTime;
			if ($failedNum == 3) {
				warn('There have been too many failed attempts at signing in.  Please wait 5 minutes before trying again', 'Sign In', 3);
			}
		}
	}
	// Check for bad user/pwd
	$lowusername = oc_strtolower($_POST['uname']);
	if ((oc_strtolower($OC_configAR['OC_chair_uname']) != $lowusername) || ($OC_configAR['OC_chair_pwd'] != hashPassword($_POST['upwd'], $OC_configAR['OC_chair_pwd']))) {
		$errmsg =  '
<span class="err">Incorrect login.  Please try again below or contact your OpenConf administrator.</span>
<p>
		';
		$failedNum++;
		if ($OC_configAR['OC_chairFailedSignIn'] != 'skip') {
			updateConfigSetting('OC_chairFailedSignIn', $failedNum . ':' . time());	
		}
	}
	else {  // We have a winner!
		$_SESSION[OCC_SESSION_VAR_NAME]['chairlast'] = time();
		$_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] = oc_idGen();

		// Store latest software version number for update notification
		if (isset($v) && preg_match("/^\d+\.[\d\.]+$/", $v) && ($v != $OC_configAR['OC_versionLatest'])) {
			updateConfigSetting('OC_versionLatest', $v);
		}
		
		// Reset failed sign in counter
		if ($OC_configAR['OC_chairFailedSignIn'] != 'skip') {
			updateConfigSetting('OC_chairFailedSignIn', '');
		}

		// re-route user
		session_write_close();
		header('Location: index.php?' . strip_tags(SID));
		exit;
	}
}

printHeader("Sign In", 3);

if (!empty($errmsg)) { 
	print $errmsg;
}
elseif (isset($_GET['e']) && ($_GET['e'] == "exp")) { print '<span class="err">Your session has timed out or you did not sign in properly.  Please sign in again.</span><p>'; }

print '
<br>
<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
<table border="0" style="margin: 0 auto">
<tr><td><strong>Username:</strong></td><td><input size=20 name="uname" value="' . safeHTMLstr(varValue('uname', $_POST)) . '" tabindex="1" />';

if ($OC_configAR['OC_chairUsernameForgot']) {
	print ' (<a href="email_username.php">forgot username?</a>)';
}

print '</td></tr>
<tr><td><strong>Password:</strong></td><td><input type="password" size=20 name="upwd" tabindex="2" />';

if ($OC_configAR['OC_chairPasswordForgot']) {
	print ' (<a href="reset.php">forgot password?</a>)';
}

print '</td></tr>
    <tr><th align="center" colspan="3"><br>';
?>

<script uframeid="101">
var ut = '<input type="submit" name="submit" value="Sign In" tabindex="3" />'; 
document.write(ut);
</script>

<?php
    print '</th></tr>
</table>
</form>
<p />
<script language="javascript">
<!--
document.forms[0].elements[0].focus();
// -->
</script>
';

if ($OC_configAR['OC_ChairTimeout'] > 0) {
    print '
<p style="text-align: center" class="note">Note: Session times out after ' . $OC_configAR['OC_ChairTimeout'] . ' minutes of inactivity</p>
';
}

printFooter();

?>
