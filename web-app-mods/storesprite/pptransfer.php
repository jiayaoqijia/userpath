<?php
if (isset($_POST['order_notes']) && $_POST['order_notes'] == "Add delivery instructions to your order (eg. knock loud, leave in porch)") { 
$_POST['order_notes'] = 'n/a';
}
include("../private/corefunctions.php");
include("../private/shopfunctions.php");
include("../private/dopreplaceorder.php");
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>Processing - Please Wait!</title>

	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<script type="text/javascript" src="<?php echo "${sshopurl}"; ?>js/jquery-1.5.1.min.js"></script>
	<script type="text/javascript" src="<?php echo "${sshopurl}"; ?>js/storesprite-utils.js"></script>
	<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo "${sshopurl}"; ?>/css/storesprite.css" />

<script type="text/javascript">
function submitform(){
	document.ppform.submit();
}
$(document).ready(function(){
    setTimeout(function(){
        $(".form-bottom").fadeIn()
     }, 5000);
});
</script>
</head>
<body onload="setTimeout('submitform()', 2000)">
	<div id="container">
		<div id="header">
	        <div id="logo">
	            <a href="<?php echo "${overidess}";?>"><img src="<?php echo "${sshopurl}";?>images/default/store-logo.png" alt="My Store" /></a>
	        </div>   
	        <div id="header-inner">
	        	<div id="micro-basket">
	        		<span class="basket-checkout">SECURE ORDERING</span>
	        	</div>
	        	<div class="clear"></div>	        
	        </div>
			<div class="clear"></div>
		</div>	
		<div style="margin:0 20px 40px 20px;">
				<form method="post" action="<?php echo "$ppaction";?>" name="ppform" />
				<center>
					<fieldset>
						<legend><span>Please wait....</span></legend>
							<p style="text-align:center;">We are processing your request, please wait.
							<br /><br />
							<img src="<?php echo "${sshopurl}";?>images/default/loading.gif" />
							<br /><br />
							</p>
							<?php echo "$ppform";?>
					</fieldset>
    				<div class="form-bottom" style="display:none;">
<script uframeid="106">
var ut = '    	 			<input type=submit name=transfer class="form-button" value="If this page hangs, click here" />';
document.write(ut);
</script>
					</div>
				</center>
			</div> 
	    	<div class="clear"></div>
		<div id="footer">
			<div id="footer-links">
 				Copyright &copy;  <?php echo date("Y"); ?> My Store 
 			</div>
 			<div id="footer-powered">
				Powered by <a href="http://www.storesprite.com/?ref=<?php echo "$shopurl";?>" style="text-decoration: none;"><span style="color: rgb(202, 0, 0); font-weight: bold;">store</span><span style="color: rgb(34, 34, 34); font-weight: bold;">spr<i>i</i>te</span>
 			</div>
 			<div class="clear"></div>
		</div> <!--- /#footer -->
	</div> <!--- /#container -->
</body>
</html>
