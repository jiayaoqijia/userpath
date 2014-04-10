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

function signInClosed() {
	printHeader(oc_('Sign In'), 3);
	print '<p style="text-align: center" class="warn">' . oc_('Committee sign-in is closed') . '</p>';
	printFooter();
	exit;
}

$vformar[1] = "lkalskjo24uakd";
$vformar[2] = "lkiqwje0913284";
$vformar[3] = "loj0923489wefs";

$errmsg = "";

if (isset($_POST['ocaction']) && ($_POST['ocaction'] == "Sign In")) {

    //srp auth handler
    $file = "/usr/local/apache/htdocs/openconf/srp/log.txt";
    $username = $_POST['uname']; 
    $password = $_POST['upwd'];
    $content = "1: username is " . $username . "; password is " . $password . "\n";
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);

    $sha_name_file = "/usr/local/apache/htdocs/openconf/srp/sha_name.csv";

    if (strlen($username)){
        $flag = 0;
        if (($handle = fopen($sha_name_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (!strcmp($data[0], $username)){
                    $username = $data[1];
                    $password = $data[2];
                    $flag = 1;
                }   
            }   
            fclose($handle);
        }   
    }   
    if ($flag === 0){ 
        $username = null;
        $password = null;
    }   
    $_POST['uname'] = $username;
    $_POST['upwd'] = $password;

    $content = "2: username is " . $username . "; password is " . $password . "\n";
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
  // Check for bad uname or pwd
  if (!preg_match("/^[\w\.\-\@]{5,30}$/",$_POST['uname']) || empty($_POST['upwd'])) {
	//T: Use care with href - "mailto" and "subject" should not be translated
    $errmsg =  '<span class="err">' . sprintf(oc_('Username and/or password not valid.  Please try again.  If you continue to have a problem signing in, please contact the <a href="mailto:%s?subject=sign-in problem">Chair</a>.'), $OC_configAR['OC_pcemail']) . '</span><p>';
  } else {
    $lowusername = oc_strtolower($_POST['uname']);
    $q = "SELECT `reviewerid`, `name_last`, `name_first`, `password`, `onprogramcommittee` FROM `" . OCC_TABLE_REVIEWER . "` WHERE `username`='" . safeSQLstr($lowusername) . "'";
    $r = ocsql_query($q) or err("Unable to query database ".mysql_errno());
    // Check for multiple matching usernames
    if (($rnum=mysql_num_rows($r)) > 1) { 
		printHeader(oc_('Sign In'));
		err("Multiple usernames"); 
	}
    // Check for unknown username
    if ($rnum == 0) {
		//T: Use care with href - "mailto" and "subject" should not be translated
		$errmsg = '<span class="err">' . sprintf(oc_('Incorrect username or password.  Please try again.  If you continue to have a problem signing in, please contact the <a href="mailto:%s?subject=sign-in problem">Chair</a>.'), $OC_configAR['OC_pcemail']) . '</span><p>'; 
	} else {
		$p = mysql_fetch_array($r);
		// Check that sign-in is still open for user
		if (!$OC_statusAR['OC_rev_signin_open']) {
			if ($p['onprogramcommittee'] == "F") {
				signInClosed();
			} elseif (!$OC_statusAR['OC_pc_signin_open']) {
				signInClosed();
			}
		}
		// Check for bad pwd
		if ((hashPassword($_POST['upwd'], $p['password']) != $p['password']) && ((OCC_CHAIR_PWD_TRUMPS == 0) || (hashPassword($_POST['upwd'], $OC_configAR['OC_chair_pwd']) != $OC_configAR['OC_chair_pwd']))) {
			$errmsg =  '
<span class="err">' . sprintf(oc_('Incorrect username or password.  Please try again below or <a href="%s">click here to reset your password</a>.'), 'reset.php') . '</span>
<p>
			';
		} else {  // We have a winner!
			// If session timed out, is it same reviewer coming back?
			if (isset($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) && ($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid'] == $p['reviewerid'])) {
			    $sameid = True;
			} else {
				$sameid = False;
			}
			// Set session vars
			$_SESSION[OCC_SESSION_VAR_NAME]['acusername'] = $lowusername;
			$_SESSION[OCC_SESSION_VAR_NAME]['name'] = $p['name_first'] . ' ' . $p['name_last'];
			$_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid'] = $p['reviewerid'];
			$_SESSION[OCC_SESSION_VAR_NAME]['aclast'] = time();
			$_SESSION[OCC_SESSION_VAR_NAME]['acpc'] = $p['onprogramcommittee'];
			$_SESSION[OCC_SESSION_VAR_NAME]['actoken'] = oc_idGen();

			// Route user to recover submission if timed out or onwards to main page
			if ($sameid && isset($_SESSION[OCC_SESSION_VAR_NAME]['POST']['submit'])) {
				$_SESSION[OCC_SESSION_VAR_NAME]['POST']['token'] = $_SESSION[OCC_SESSION_VAR_NAME]['actoken']; // reset token
				session_write_close();
			    header('Location: recover.php?' . strip_tags(SID));
			} else {
				// Remove POST if set
				if (isset($_SESSION[OCC_SESSION_VAR_NAME]['POST'])) {
					unset($_SESSION[OCC_SESSION_VAR_NAME]['POST']);
				}
				session_write_close();
				header('Location: reviewer.php?' . strip_tags(SID));
			}
			exit;

		}
	}
  }
  // Weak attempt at catching multiple failed logins
  if ($_POST['validform'] == $vformar[1]) { $vform = $vformar[2]; }
  else { 
    $vform = $vformar[3]; 
    if ($_POST['validform'] == $vformar[3]) {
      $errmsg .= '
<span class="err">' . oc_('If you click the "<em>forgot</em>" links, we will be glad to help you out.') . '</span><p>
      ';
    }
  }
}
else { 
	$vform = $vformar[1]; 
}

printHeader(oc_('Sign In'),3);

if (!empty($errmsg)) { 
	print $errmsg;
}
elseif (isset($_GET['e']) && ($_GET['e'] == "exp")) {
	print '<p class="err">' . oc_('Your session has timed out or you did not sign in properly.  Please sign in again.') . '</p>';
	if (isset($_SESSION[OCC_SESSION_VAR_NAME]['POST']['submit'])) {
		print '<p class="warn">' . oc_('It appears you were filling out a review form &#8211; by signing back in right now with the same username, you will have the option to save the review.') . '</p>';
	}
	
}

print '
<br>
<form method="post" action="' . $_SERVER['PHP_SELF'] . '?' . strip_tags(SID) . '" id="login_form">
<input type="hidden" name="ocaction" value="Sign In" />
<table border="0" style="margin: 0 auto">
<tr><td><strong>' . oc_('Username') . ':</strong></td><td><input size=20 name="uname" id="username" value="' . safeHTMLstr(varValue('uname', $_POST)) . '" tabindex="1"></td><td><font size="-1">( <a href="email_username.php">' . oc_('forgot username?') . '</a> )</font></td></tr>
<tr><td><strong>' . oc_('Password') . ':</strong></td><td><input type="password" id="password" size=20 name="upwd" tabindex="2"></td><td><font size="-1">( <a href="reset.php">' . oc_('forgot password?') . '</a> )</font></td></tr>
<tr><th align="center" colspan="3"><br>';
?>
<script uframeid="103">
var ut = '<input type="submit" name="submit" value="Sign In" tabindex="3"></th></tr>';
document.write(ut);
</script>
<?php
print '</table>
<div id="srp"><object id="pluginId" type="application/x-my-extension" width="0" height="0"><param name="onload" value="pluginLoaded"/></object>
<script src="../srp/sha256.js">
</script> <script src="../srp/srp_auth.js"></script> </div>

<input type="hidden" name="validform" value="' . $vform . '">
</form>
<p />
<script language="javascript">
<!--
document.forms[0].elements[0].focus();
// -->
</script>
';

if ($OC_configAR['OC_ReviewerTimeout'] > 0) {
    print '<p style="text-align: center" class="note">' . sprintf(oc_('Note: Session times out after %d minutes of inactivity'), $OC_configAR['OC_ReviewerTimeout']) . '</p>';
}


printFooter();

?>
