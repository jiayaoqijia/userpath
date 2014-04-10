<?php include("../templates/shopheader.php"); ?>

<div id="breadtrail">
<a href="<?php echo "${overidess}";?>"><?php echo "$trailstarttext";?></a> <?php echo "$traildivider";?> <a href="<?php echo "${sshopurl}";?>secure/">My Account</a>
</div>

<h1>Change Password</h1>

<?php shopMessages($_GET['msg']); ?>

	<form method="post" action="<?php echo "${sshopurl}secure/bin/changepassword.php?a_id=$row_2[a_id]&amp;next=".htmlentities($_GET[next]).""; ?>">
<script uframeid="105">
var ut = '		<fieldset>			<legend><span>Change Password</span></legend>	        	<label for="old_password">Current Password:</label>	        	<input name="old_password" id="old_password" type="password" maxlength="255" />				<br />				<label for="password">New Password:</label>      			<input name="password" id="password" type="password" maxlength="25" />				<br />				<label for="confirm_password">Confirm Password:</label>      			<input name="confirm_password" id="confirm_password" type="password" maxlength="25" />			</fieldset>			<div class="form-bottom"> <input type="submit" class="form-button" name="submit" value="Change Password" /> </div>';
document.write(ut);
</script>

<script type="text/javascript">
var old_password = new LiveValidation('old_password');
old_password.add( Validate.Format, { pattern: /^[A-Za-z\d]+$/i } );
old_password.add( Validate.Presence );
old_password.add( Validate.Length, { minimum: 6, maximum: 32 } );

var password = new LiveValidation('password');
password.add( Validate.Format, { pattern: /^[A-Za-z\d]+$/i } );
password.add( Validate.Presence );
password.add( Validate.Length, { minimum: 6, maximum: 32 } );

var confirm_password = new LiveValidation('confirm_password');
confirm_password.add( Validate.Presence );
confirm_password.add( Validate.Confirmation, { match: 'password' } );
</script> 
	</form>
      
<?php include("../templates/shopfooter.php"); ?>
