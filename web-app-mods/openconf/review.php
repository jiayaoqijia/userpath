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

beginSession();

printHeader(oc_('Review'), 2);

$showEmailCopy = true;
$showCompletedReview = true;

function saveReviewForm($review, $thepid) {
	global $OC_reviewQuestionsAR;
	
	// Check for valid submission
	if (!validToken('ac')) {
		$w = sprintf(oc_('This submission failed our security check, possibly due to you have signed in again, or a third-party having redirected you here.  Below is the information provided.  If you were attempting to submit a review, print this information out or copy/paste it to a new document so it can be re-entered; then <a href="%s">try again</a>.  If the problem persists, please contact the Chair.'), ($_SERVER['PHP_SELF'] . '?pid=' . (is_numeric($_POST['pid']) ? $_POST['pid'] : ''))) . '<div style="color: #000; margin-top: 1em; font-weight: normal;">';
		$OC_reviewQuestionsARkeys = array_keys($OC_reviewQuestionsAR);
		foreach ($_POST as $k => $v) {
			if (($k == 'submit') || ($k == 'token')) { continue; }
			if (in_array($k, $OC_reviewQuestionsARkeys)) {
				$w .= "<br />\n<hr /><p />\n<strong>" . safeHTMLstr($OC_reviewQuestionsAR[$k]['short']) . "</strong> ";
				if ($OC_reviewQuestionsAR[$k]['usekey']) {
					$w .= $OC_reviewQuestionsAR[$k]['values'][$v];
				} else {
					$w .= safeHTMLstr($v);
				}
			} else {
				$w .= "<br />\n<hr /><p />\n<strong>" . safeHTMLstr($k) . ":</strong> " . safeHTMLstr($v);
			}
		}
		$w .= '<hr /></div>';
		warn($w);
	}

	// Email review copy - do it here in case of errors/problems below
	if (isset($_POST['emailcopy']) && ($_POST['emailcopy'] == "1")) {
		//T: %1$d = submission ID; $2$s = conference short name (e.g., CONF2012)
		$msg = sprintf(oc_('Following is a copy of your review for submission number %1$d submitted to %2$s.  Note that you will receive this email even if an error occured during submission.'), $thepid, $GLOBALS['OC_configAR']['OC_confName']) . "\n\n----------------------------------------\n\n";
		
		$msg .= oc_genFieldMessage($GLOBALS['OC_reviewQuestionsFieldsetAR'], $GLOBALS['OC_reviewQuestionsAR'], $_POST);

		$mailsubject = sprintf(oc_('Review of submission %d'), $thepid);

		if (oc_hookSet('committee-review-msg')) {
			foreach ($GLOBALS['OC_hooksAR']['committee-review-msg'] as $v) {
				require_once $v;
			}
		}

		if (!sendEmail($review['email'], $mailsubject, $msg)) {
			print '<p class="err">' . oc_('We were unable to send copy of the review via email') . '</p>';
		}
	}

	// Update fields
	$qfields = array();
	if (isset($OC_reviewQuestionsAR['recommendation'])) {
		if (isset($_POST['recommendation']) && preg_match("/^\d+$/",$_POST['recommendation'])) {
			$qfields['recommendation'] = "'" . safeSQLstr($_POST['recommendation']) . "'";
		} else {
			$qfields['recommendation'] = 'NULL';
		}
	}
	if (isset($OC_reviewQuestionsAR['category'])) {
		if (isset($_POST['category']) && preg_match("/^\d+$/",$_POST['category'])) {
			$qfields['category'] = "'" . safeSQLstr($_POST['category']) . "'";
		} else {
			$qfields['category'] = 'NULL';
		}
	}
	if (isset($OC_reviewQuestionsAR['value'])) {
		$qfields['value'] = 'NULL';
		if (isset($_POST['value']) && !empty($_POST['value'])) {
			$valstr = implode(",",$_POST['value']); 
			if (preg_match("/^[\d,]+$/",$valstr)) {
				$qfields['value'] = "'" . safeSQLstr($valstr) . "'";
			}
		}
	}
	if (isset($OC_reviewQuestionsAR['familiar'])) {
		if (isset($_POST['familiar']) && $OC_reviewQuestionsAR['familiar']['values'][$_POST['familiar']]) {
			$qfields['familiar'] = "'" . safeSQLstr($_POST['familiar']) . "'";
		} else {
			$qfields['familiar'] = 'NULL';
		}
	}
	if (isset($OC_reviewQuestionsAR['bpcandidate'])) {
		if (isset($_POST['bpcandidate']) && $OC_reviewQuestionsAR['bpcandidate']['values'][$_POST['bpcandidate']]) {
			$qfields['bpcandidate'] = "'" . safeSQLstr($_POST['bpcandidate']) . "'";
		} else {
			$qfields['bpcandidate'] = 'NULL';
		}
	}
	if (isset($OC_reviewQuestionsAR['length'])) {
		if (isset($_POST['length']) && $OC_reviewQuestionsAR['length']['values'][$_POST['length']]) {
			$qfields['length'] = "'" . safeSQLstr($_POST['length']) . "'";
		} else {
			$qfields['length'] = 'NULL';
		}
	}
	if (isset($OC_reviewQuestionsAR['difference'])) {
		if (isset($_POST['difference']) && preg_match("/^\d+$/", $_POST['difference'])) {
			$qfields['difference'] = "'" . safeSQLstr($_POST['difference']) . "'";
		} else {
			$qfields['difference'] = 'NULL';
		}
	}

	if (isset($OC_reviewQuestionsAR['pccomments'])) {
		if (isset($_POST['pccomments']) && !empty($_POST['pccomments'])) {
			$qfields['pccomments'] = "'" . safeSQLstr($_POST['pccomments']) . "'";
		} else {
			$qfields['pccomments'] = 'NULL';
		}
	}
	if (isset($OC_reviewQuestionsAR['authorcomments'])) {
		if (isset($_POST['authorcomments']) && !empty($_POST['authorcomments'])) {
			$qfields['authorcomments'] = "'" . safeSQLstr($_POST['authorcomments']) . "'";
		} else {
			$qfields['authorcomments'] = 'NULL';
		}
	}

	if (oc_hookSet('committee-review-validate')) {
		foreach ($GLOBALS['OC_hooksAR']['committee-review-validate'] as $v) {
			require_once $v;
		}
	}

	// Compose sql
	$q = "UPDATE `" . OCC_TABLE_PAPERREVIEWER . "` SET `updated`='" . safeSQLstr(date('Y-m-d')) . "', ";
	foreach ($qfields as $qid => $qval) {
		$q .= "`" . $qid . "`=" . $qval . ", ";
	}

	// Completed?
	if (isset($_POST['completed']) && ($_POST['completed'] == 1)) {
		$completed = 'T';
		foreach ($OC_reviewQuestionsAR as $fieldID => $fieldAR) {
			if ((isset($fieldAR['required']) && ($fieldAR['required'])) && (!isset($_POST[$fieldID]) || empty($_POST[$fieldID]))) {
				$completed='F';
			}
		}
	} else {
		$completed = 'F';
	}
	$q .= '`completed`="' . safeSQLstr($completed) . '"';
	
	$q .= ' WHERE `paperid`="' . $thepid . '" AND `reviewerid`="' . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . '"';
	ocsql_query($q) or err("Unable to submit review");

	// Update papersession
	if (isset($OC_reviewQuestionsAR['sessions'])) {
		$q2 = "DELETE FROM `" . OCC_TABLE_PAPERSESSION . "` WHERE `paperid`='" . $thepid . "' AND `reviewerid`='" . $_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid'] . "'";
		ocsql_query($q2) or err("Unable to update sessions - ".mysql_errno());
		if (isset($_POST['sessions']) && is_array($_POST['sessions'])) {
			foreach ($_POST['sessions'] as $tid) { 
				if (preg_match("/^[\d]+$/",$tid)) { 
					$q3 = "INSERT INTO `" . OCC_TABLE_PAPERSESSION . "` (`paperid`,`reviewerid`,`topicid`) VALUES (" . $thepid . "," . $_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid'] . "," . $tid . ")";
					ocsql_query($q3) or err("Unable to add session - ".mysql_errno());
				}
			}
		}
	}
	
	if (oc_hookSet('committee-review-save')) {
		foreach ($GLOBALS['OC_hooksAR']['committee-review-save'] as $v) {
			require_once $v;
		}
	}

	print '<p>' . oc_('Review has been submitted.') . '</p>';
	if (isset($_POST['completed']) && ($_POST['completed'] == 1) && ($completed == 'F')) {
		print '<p>' . oc_('However as not all required questions were answered, the review was not marked as completed.') . '</p>';
	}
	print '<p><a href="reviewer.php">' . oc_('Return to Reviewer page') . '</a></p>';
}// function saveReviewForm

function printReviewForm($review, $thepid) {
	global $OC_configAR, $OC_reviewQuestionsAR;

	// Make an array of sessions reviewer has listed the paper under
	$sq = "SELECT `topicid` FROM `" . OCC_TABLE_PAPERSESSION . "` WHERE `paperid`='" . $thepid . "' AND `reviewerid`='" . $_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid'] . "'";
	$sr = ocsql_query($sq); # or err("Unable to retrieve sessions");
	$review['sessions'] = array();
	while ($sl = mysql_fetch_array($sr)) { 
		$review['sessions'][] = $sl['topicid'];
	}

	print '<p style="text-align: center">' . oc_('Submission ID') . ': ' . $thepid;

	if (isset($review['module_oc_subtype_type']) && !empty($review['module_oc_subtype_type'])) {
		print ' (' . safeHTMLstr($review['module_oc_subtype_type']) . ')';
	}

	print '<br /><strong><em>' . safeHTMLstr($review['title']) .  '</em></strong></p /><hr /><span style="color: #060; font-style: italic">' . oc_("TIP: Use a local text editor to write your review, and then select/copy the information below.  This way, in case of a network outage or some other problem, you won't lose the review.") . '</span><hr /><p />';
	print '
<form method="POST" action="'.$_SERVER['PHP_SELF'].'" class="ocreviewform">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['actoken'] . '" />
<input type="hidden" name="pid" value="'.$thepid.'">
<input type="hidden" name="ocaction" value="Submit Review" />
	';
	
	if (oc_hookSet('committee-review-fields')) {
		foreach ($GLOBALS['OC_hooksAR']['committee-review-fields'] as $v) {
			require_once $v;
		}
	}

	oc_displayFields($GLOBALS['OC_reviewQuestionsAR'], $review);
	
	if (oc_hookSet('committee-review-extra')) {
		foreach ($GLOBALS['OC_hooksAR']['committee-review-extra'] as $v) {
			require_once $v;
		}
	}
	
	if ($GLOBALS['showEmailCopy']) {
		print '<dl><dt><input type="checkbox" name="emailcopy" value="1" checked> <strong>' . oc_('Email me a copy of this review') . '</strong></dt><dd><span class="note">' . oc_('Useful for your own record or in case there is some kind of error during updating.') . ' ';
		if ($OC_configAR['OC_ReviewerTimeout'] > 0) {
			print oc_('Note that if your session times out, you will not receive an email; instead you should log back in right away to recover the review.');
		}
		print "</span></dd></dl><p />\n";
	}
	
	if ($GLOBALS['showCompletedReview']) {
		print '<dl><dt><input type="checkbox" name="completed" value="1"';
		if (varValue('completed', $review) == "T") { print ' checked'; }
		print '> <strong>' . oc_('I have completed the review') . '</strong></dt><dd><span class="note">' . oc_('Check this box when you have finished the review for this submission.  This is used only to track how many outstanding reviews there are.  You will still be able to edit this review after checking this box, until the review deadline date.') . '</span></dd></dl><p />';
	}
	
	print "<hr><p>\n";
	
	if ($thepid != "blank") {
?>
<script uframeid="106">
var ut =  '<input type="submit" name="submit" value="Submit Review"><p>';
document.write(ut);
</script>
<?php
	}
	else {
		print '[ ' . oc_('Sample Review Form - Fill in and submit review by clicking the submission title on main reviewer page') . ' ]<p />';
	}
	
	print '<span class="note">' . oc_('Before submitting your review, consider printing it out and copying/pasting the descriptive text fields to a text document.  This way, in case of a network/system problem, you will have all the information if it needs to be re-entered.') . '</span><p />';

	if ($OC_configAR['OC_ReviewerTimeout'] > 0) {
		print '<span class="note">' . oc_('Should your session timeout while filling out this review, log back in right away as we may be able to recover your review.') . '</span><p />';
	}
} // function printReviewForm


if (isset($_POST['ocaction']) && ($_POST['ocaction'] == "Submit Review")) {
	if (!isset($_POST['pid']) || !preg_match("/^\d+$/", $_POST['pid'])) {
		warn('Invalid submission ID');
	}
	$q = "SELECT `paperid`, `email` FROM `" . OCC_TABLE_PAPERREVIEWER . "`, `" . OCC_TABLE_REVIEWER . "` WHERE `paperid`='" . safeSQLstr($_POST['pid']) . "' AND `" . OCC_TABLE_PAPERREVIEWER . "`.`reviewerid`='".$_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']."' AND `" . OCC_TABLE_PAPERREVIEWER . "`.`reviewerid`=`" . OCC_TABLE_REVIEWER . "`.`reviewerid`";
	$thepid = $_POST['pid'];
} else {
	if (!isset($_GET['pid']) || (($_GET['pid'] != 'blank') && !preg_match("/^\d+$/", $_GET['pid']))) {
		warn(oc_('Submission ID is invalid'));
	}
	$q = "SELECT `title`, `format`, " . (oc_moduleActive('oc_subtype') ? '`module_oc_subtype_type`, ' : '')  . "`" . OCC_TABLE_PAPERREVIEWER . "`.* FROM `" . OCC_TABLE_PAPERREVIEWER . "`, `" . OCC_TABLE_PAPER . "` WHERE `" . OCC_TABLE_PAPERREVIEWER . "`.`paperid`='" . safeSQLstr($_GET['pid']) . "' AND `reviewerid`='".$_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']."' AND `" . OCC_TABLE_PAPERREVIEWER . "`.`paperid`=`" . OCC_TABLE_PAPER . "`.`paperid`";
	$thepid = $_GET['pid'];
}

require_once OCC_FORM_INC_FILE;
require_once OCC_REVIEW_INC_FILE;

if ($thepid == "blank") {	// display blank form
	$review = array();
	$review['title'] = oc_('Sample Review');
	$review['format'] = "";
	printReviewForm($review, 0);
} elseif (!preg_match("/^\d+$/", $thepid)) {
	print '<span class="err">' . oc_('Submission ID is invalid.') . '</span><p>';
} else {
	// Warn if conflict
	$conflictAR = getConflicts($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']);
	if (in_array($thepid . '-' . $_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid'], $conflictAR)) {
		warn(oc_('You appear to have a conflict with this submission'));
	}

	$r = ocsql_query($q) or err("Unable to retrieve submission for review ".mysql_errno());
	if (mysql_num_rows($r) == 0) { 
		//T: Use care with href - "mailto" and "subject" should not be translated
		print '<span class="err">' . sprintf(oc_('Either the submission does not exist, or you have not been assigned it for review.  If this is in error, please contact the <a href="mailto:%s?subject=Review error">Chair</a>.'), $OC_configAR['OC_pcemail']) . '</span><p>';
	} else {
		$review = mysql_fetch_array($r); 
		if (isset($_POST['ocaction']) && ($_POST['ocaction'] == "Submit Review")) {
			saveReviewForm($review, $thepid);
		} else {
			printReviewForm($review, $thepid);
		}
    }
}

printFooter();

?>
