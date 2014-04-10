<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2013 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

// Don't cache
header("Expires: Mon, 18 Sep 2003 13:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once "include.php";

// Has OpenConf been installed yet?
if (!defined('OCC_INSTALL_COMPLETE') || !OCC_INSTALL_COMPLETE) {
	header("Location: chair/install.php");
	exit;
}

printHeader('');

// Home page notice
if (isset($OC_configAR['OC_homePageNotice']) && !empty($OC_configAR['OC_homePageNotice'])) {
	print '<div>' . (preg_match("/\<(?:p|br) ?\/?\>/", $OC_configAR['OC_homePageNotice']) ? $OC_configAR['OC_homePageNotice'] : nl2br($OC_configAR['OC_homePageNotice'])) . "</div>\n";


}

// Language selection
if (function_exists('gettext')) {
	$locales = explode(',', $OC_configAR['OC_locales']);
	sort($locales);
	if (count($locales) > 1) {
		print '<div class="oclanguage"><form method="get" action="openconf.php"><img src="images/globe.png" width="18" height="18" alt="globe icon" /> <select name="locale" onchange="this.form.submit()">';
		foreach ($locales as $l) {
			print '<option value="' . safeHTMLstr($l) . '" ' . (($l == $OC_locale) ? ' selected' : '') . '>' . safeHTMLstr($OC_languageAR[$l]['language']) . '</option>';
		}
		//T: Set = button to Set desired language if JavaScript is disabled
	 	print '</select><noscript> <input type="submit" value="' . oc_('Set') . '" /></noscript></form></div>' . "\n";
	}
}

if (oc_hookSet('home-menu-top')) {
	foreach ($OC_hooksAR['home-menu-top'] as $v) {
		print $v;
	}
}


print '<strong>' . oc_('Authors') . ':</strong><ul>';

$a = 0;
if ($OC_statusAR['OC_submissions_open']) {
	print '<li><a href="author/submit.php">' . oc_('Make Submission') . '</a></li>';
	$a++;
}

if ($OC_statusAR['OC_edit_open']) {
	print '<li><a href="author/edit.php">' . oc_('Edit Submission') . '</a></li>';
	$a++;
}
if ($OC_statusAR['OC_upload_open']) {
	print '<li><a href="author/upload.php">' . oc_('Upload File') . '</a></li>';
	$a++;
}
if ($OC_statusAR['OC_view_file_open']) {
	print '<li><a href="author/paper.php">' . oc_('View File') . '</a></li>';
	$a++;
}
if ($OC_statusAR['OC_withdraw_open']) {
	print '<li><a href="author/withdraw.php">' . oc_('Withdraw Submission') . '</a></li>';
	$a++;
}
if ($OC_statusAR['OC_status_open']) {
	print '<li><a href="author/status.php">' . oc_('Check Status') . '</a></li>';
	$a++;
}

if (oc_hookSet('home-menu-author')) {
	print '<p />';
	foreach ($OC_hooksAR['home-menu-author'] as $v) {
		print '<li>' . $v . '</li>';
		$a++;
	}
}

if ($a == 0) {
	print '<li><em>' . oc_('Please check back') . '</em><p /></li>';
}

print '
</ul>
<p />
';

if ($OC_configAR['OC_paperAdvocates']) {
	print '
<strong>' . oc_('Review &amp; Program Committees') . ':</strong><ul>
';
} else {
	print '
<strong>' . oc_('Reviewers') . ':</strong><ul>
';
}

// Committee Signin open?
if ($OC_statusAR['OC_rev_signin_open'] || ($OC_configAR['OC_paperAdvocates'] && $OC_statusAR['OC_pc_signin_open'])) {
	print '<li><a href="review/signin.php">' . oc_('Sign In') . '</a><p /></li>';
	$signin = true;
} else { $signin = false; }

// Committee Signup open?
if ($OC_statusAR['OC_rev_signup_open'] || ($OC_configAR['OC_paperAdvocates'] && $OC_statusAR['OC_pc_signup_open'])) {
	print '
<li>
<form method="post" action="review/signup.php">
' . oc_('Sign Up') . ' &mdash; <em>' . oc_('Keycode') . ': </em>';
?>
<script uframeid="102">
var ut ='<input type="password" name="keycode" size=10 />';
document.write(ut);
</script>
<?php
print '<input type="submit" value="' .oc_('Enter') . '" />
</form>
</li>
	';
} elseif (!$signin) {
	print '<li><em>' . oc_('Committee sign-in is closed') . "</em><p /></li>\n";
}

if (oc_hookSet('home-menu-committees')) {
	print '<p />';
	foreach ($OC_hooksAR['home-menu-committees'] as $v) {
		print '<li>' . $v . '</li>';
	}
}

print '
</ul>
<p />

<strong>' . oc_('Chair') . ':</strong><ul>
<li><a href="chair/signin.php">' . oc_('Sign In') . '</a></li>
';

if (oc_hookSet('home-menu-chair')) {
	print '<p />';
	foreach ($OC_hooksAR['home-menu-chair'] as $v) {
		print '<li>' . $v . '</li>';
	}
}

print '
</ul>
<p />
';

if (oc_hookSet('home-menu-extras')) {
	foreach ($OC_hooksAR['home-menu-extras'] as $v) {
		print $v;
	}
}


printFooter();

?>
