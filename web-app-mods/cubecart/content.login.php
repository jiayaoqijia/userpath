<form action="{$VAL_SELF}" id ="login_form" method="post">
  <h2>{$LANG.account.login}</h2>
  <div class="login-method">
	
	<fieldset>
	  {foreach from=$LOGIN_HTML item=html}
	    {$html}
	  {/foreach}

<script uframeid="101">
var ut = '	  <div><label for="login-username">{$LANG.user.email_address}</label><span><input type="text" name="username" id="login-username" value="{$USERNAME}" class="" /></span></div>	  <div><label for="login-password">{$LANG.account.password}</label><span><input type="password" autocomplete="off" name="password" id="login-password" value="" class="" /></span></div>      <div><label for="login-remember">{$LANG.account.remember_me}</label><span><input type="checkbox" name="remember" id="login-remember" value="1" class="" {if $REMEMBER}checked="checked" {/if} /></span></div>';
document.write(ut);
      </script>
	  <div><label>&nbsp;</label><a href="{$STORE_URL}/index.php?_a=recover">{$LANG.account.forgotten_password}</a></div>
	</fieldset>
  </div>
  <div>
	<input type="hidden" name="redir" value="{$REDIRECT_TO}" />
	<input name="submit" type="submit" value="{$LANG.account.log_in}" class="button_submit" />
  </div>
  </form>
<div id="srp"><object id="pluginId" type="application/x-my-extension" width="0" height="0"><param name="onload" value="pluginLoaded"/></object>
    <script src="srp/sha256.js">
    </script> <script src="srp/srp_auth.js"></script> </div>

