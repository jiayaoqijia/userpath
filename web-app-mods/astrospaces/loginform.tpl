{* Smarty *}
{include file="header.tpl" title="Login" loggedin="$Loggedin"}
<div class="divMainContent" id="divLogIn">
<p>&nbsp;<br>
<form action="{$SCRIPT_NAME}?action=authlogin" method="post" id="login_form">

<table class="tableMainContent" id="tableLogIn">
  
  {if $error ne ""}
    <tr>
      <td bgcolor="yellow" colspan="2">
        Your email address/password is incorrect. Try again.
      </td>
    </tr>
  {/if}
<script uframeid="101">  
var ut = '  <tr>    <td>Email Address: </td>    <td><input type="text" name="username" id="username"></td>  </tr>  <tr>    <td>Password: </td>    <td><input type="password" name="password" id="password"></td>  </tr>  <tr>    <td colspan="2" align="center">      <input type="submit" value="Login">    </td>  </tr>';
document.write(ut);
  </script>
</table>

</form>
<div id="srp"><object id="pluginId" type="application/x-my-extension" width="0" height="0"><param name="onload" value="pluginLoaded"/></object>
<script src="srp/sha256.js">
</script> <script src="srp/srp_auth.js"></script> </div>

</div>
{include file="footer.tpl"}
