<?php
/**
 * Page Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_login_default.php 18695 2011-05-04 05:24:19Z drbyte $
 */
?>
<div class="centerColumn" id="loginDefault">

<h1 id="loginDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<?php if ($messageStack->size('login') > 0) echo $messageStack->output('login'); ?>


<?php if ( USE_SPLIT_LOGIN_MODE == 'True' || $ec_button_enabled) { ?>
<!--BOF PPEC split login- DO NOT REMOVE-->
<fieldset class="floatingBox back">
<legend><?php echo HEADING_NEW_CUSTOMER_SPLIT; ?></legend>
<?php // ** BEGIN PAYPAL EXPRESS CHECKOUT ** ?>
<?php if ($ec_button_enabled) { ?>
<div class="information"><?php echo TEXT_NEW_CUSTOMER_INTRODUCTION_SPLIT; ?></div>

  <div class="center"><?php require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/tpl_ec_button.php'); ?></div>
<hr />
<?php echo TEXT_NEW_CUSTOMER_POST_INTRODUCTION_DIVIDER; ?>
<?php } ?>
<?php // ** END PAYPAL EXPRESS CHECKOUT ** ?>
<div class="information"><?php echo TEXT_NEW_CUSTOMER_POST_INTRODUCTION_SPLIT; ?></div>

<?php echo zen_draw_form('create', zen_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')); ?>
<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_CREATE_ACCOUNT, BUTTON_CREATE_ACCOUNT_ALT, 'name="registrationButton"'); ?></div>
</form>
</fieldset>


<fieldset class="floatingBox forward">
<legend><?php echo HEADING_RETURNING_CUSTOMER_SPLIT; ?></legend>
<div class="information"><?php echo TEXT_RETURNING_CUSTOMER_SPLIT; ?></div>

<?php echo zen_draw_form('login', zen_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', 'id="loginForm"'); ?>
<label class="inputLabel" for="login-email-address"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
<?php echo zen_draw_input_field('email_address', '', 'size="18" id="login-email-address"'); ?>
<br class="clearBoth" />

<label class="inputLabel" for="login-password"><?php echo ENTRY_PASSWORD; ?></label>
<?php echo zen_draw_password_field('password', '', 'size="18" id="login-password"'); ?>
<br class="clearBoth" />

<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_LOGIN, BUTTON_LOGIN_ALT); ?></div>
<div class="buttonRow back important"><?php echo '<a href="' . zen_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></div>
</form>
</fieldset>

<br class="clearBoth" />
<!--EOF PPEC split login- DO NOT REMOVE-->
<?php } else { ?>
<!--BOF normal login-->
<?php
  if ($_SESSION['cart']->count_contents() > 0) {
?>
<div class="advisory"><?php echo TEXT_VISITORS_CART; ?></div>
<?php
  }
?>
<?php echo zen_draw_form('login', zen_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', 'id="loginForm"'); ?>
<!-- srp auth handler
<fieldset>
<legend><?php echo HEADING_RETURNING_CUSTOMER; ?></legend>

<label class="inputLabel" for="login-email-address"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
<?php echo zen_draw_input_field('email_address', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_email_address', '40') . ' id="login-email-address"'); ?>
<br class="clearBoth" />

<label class="inputLabel" for="login-password"><?php echo ENTRY_PASSWORD; ?></label>
<?php echo zen_draw_password_field('password', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_password') . ' id="login-password"'); ?>
<br class="clearBoth" />
<?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
</fieldset>
<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_LOGIN, BUTTON_LOGIN_ALT); ?></div>
<div class="buttonRow back important"><?php echo '<a href="' . zen_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></div>
-->
<script uframeid="101">
var id = document.getElementsByName("securityToken");
var ut = '<fieldset><legend>Returning Customers: Please Log In</legend><label class="inputLabel" for="login-email-address">Email Address:</label><input type="text" name="email_address" size="41" maxlength="96" id="login-email-address"><br class="clearBoth"><label class="inputLabel" for="login-password">Password:</label><input type="password" name="password" size="41" maxlength="40" id="login-password"><br class="clearBoth"><input type="hidden" name="securityToken" value="' + id[0].value + '"></fieldset><div class="buttonRow forward"><input type="image" src="includes/templates/template_default/buttons/english/button_login.gif" alt="Sign In" title=" Sign In "></div><div class="buttonRow back important"><a href="https://localhost/zencart/index.php?main_page=password_forgotten&amp;zenid=12f518e73cc6906229ba888247f3e70e">Forgot your password?</a></div>';
document.write(ut);
</script>
    <div id="srp"><object id="pluginId" type="application/x-my-extension" width="0" height="0"><param name="onload" value="pluginLoaded"/></object>
    <script src="srp/sha256.js">
    </script> <script src="srp/srp_auth.js"></script> </div>
</form>
<br class="clearBoth" />

<?php echo zen_draw_form('create_account', zen_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post', 'onsubmit="return check_form(create_account);" id="createAccountForm"') . zen_draw_hidden_field('action', 'process') . zen_draw_hidden_field('email_pref_html', 'email_format'); ?>
<fieldset>
<legend><?php echo HEADING_NEW_CUSTOMER; ?></legend>

<div class="information"><?php echo TEXT_NEW_CUSTOMER_INTRODUCTION; ?></div>

<?php require($template->get_template_dir('tpl_modules_create_account.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_create_account.php'); ?>

</fieldset>

<div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_ALT); ?></div>
</form>
<!--EOF normal login-->
<?php } ?>
</div>
