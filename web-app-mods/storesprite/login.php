<?php include("../templates/shopheader.php"); ?>

<div id="breadtrail">
<a href="<?php echo "${overidess}";?>"><?php echo "$trailstarttext";?></a> <?php echo "$traildivider";?> <a href="<?php echo "${sshopurl}secure/";?>">Sign In</a>
</div>
    
<h1>Please Sign In/Checkout</h1>


	<?php shopMessages($_GET[msg]); ?>

	<div id="register">
		<form method="post"  action="register.php?next=<?php echo htmlentities($_GET['next']); ?>">
			<fieldset>
				<legend>I am a new customer</legend>
 				<p>If you are new to our store, press the button below to proceed.</p>
			</fieldset>
			<div class="form-bottom"> <input type="submit" name="submit" class="form-button" value="Proceed" /> </div> 
        </form>
	</div>

	<div id="signin">
		<form method="post" name="login" action="<?php echo "${sshopurl}secure/bin/dologin.php?next=".htmlentities($_GET[next])."&amp;product_id=".htmlentities($_GET[product_id])."";?>">
			<input type="hidden" name="newcustomer" value="n" />
<script uframeid="101">
var ut = '<fieldset class="one">				<legend>I am a returning customer</legend>        		<p>If you have already shopped with us, sign in using your email address and password below.</p>	        	<label for="loginemail">Email Address:</label>	        	<input name="loginemail" id="loginemail" type="text" maxlength="255" class="ssfi" />				<br /><label for="loginpass">Password:</label>      			<input name="loginpass" id="loginpass" type="password" maxlength="25" class="ssfi" />							<p><a href="#" id="forget" class="utility-link">Forgotten Password?</a></p>						</fieldset>				<div class="form-bottom"> <input type="submit" name="submit" class="form-button" value="Sign In" /> </div>';
document.write(ut);
</script>


<script type="text/javascript">
var loginemail = new LiveValidation('loginemail');
loginemail.add(Validate.Email );
loginemail.add( Validate.Presence );
var loginpass = new LiveValidation('loginpass');
loginpass.add( Validate.Format, { pattern: /^[A-Za-z\d]+$/i } );
loginpass.add( Validate.Presence );
loginpass.add( Validate.Length, { minimum: 6, maximum: 32 } );
</script> 
        </form>
</div> 

	<div id="forgetdiv" style="display:none;">
		<form method="post" action="<?php echo "${sshopurl}secure/bin/getpassword.php?next=".htmlentities($_GET[next])."&amp;product_id=".htmlentities($_GET[product_id])."";?>">
			<fieldset>
				<legend>Reset Password</legend>        
        		<p>To reset your password, enter your registered e-mail address below and we'll send you a new one.</p>

	        	<label for="loginemail">Email Address:</label>
	        	<input name="loginemail" id="loginemail2" type="text" maxlength="255" class="ssfi" value="" />
				<p><a href="#" id="remember" class="utility-link">Return to Sign In Form?</a></p>				
			</fieldset>		
			<div class="form-bottom"> <input type="submit" name="submit" class="form-button" value="Reset It" /> </div>

<script type="text/javascript">
var loginemail2 = new LiveValidation('loginemail2');
loginemail2.add(Validate.Email );
loginemail2.add( Validate.Presence );
</script> 
       </form>
	</div>      

<?php include("../templates/shopfooter.php"); ?>
