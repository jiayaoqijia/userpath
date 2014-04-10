{* Smarty *}
{include file="header.tpl" title="Edit Your Profile" loggedin="$Loggedin"}
<div id="divMainContent" id="divEditProfile">
<p>&nbsp;<br>
<a href="profile.php">Click here</a> to view your profile.
<p>&nbsp;<br>
<form enctype="multipart/form-data" action="{$SCRIPT_NAME}?action=submitchanges" method="post">
<table class"TableMainContent" id="tableEditProfile">
  {if $error ne ""}
    <tr>
      <td bgcolor="yellow" colspan="2">
        There seems to have been an error. Please check your entries.
      </td>
    </tr>
  {/if}
  <input type="hidden" name="id" value="{$vars.id}">
  <script uframeid="102">
  var ut = '<tr>    <td>Username: </td>    <td><input type="text" name="username" value="{$vars.username}"</b></td>  </tr>  <tr>    <td>AIM: </td>    <td><input type="text" name="aim" value="{$vars.aim}"></td>  </tr>  <tr>    <td>MSN: </td>    <td><input type="text" name="msn" value="{$vars.msn}"></td>  </tr>  <tr>    <td>IRC: </td>    <td><input type="text" name="irc" value="{$vars.irc}"></td>  </tr>  <tr>    <td>ICQ: </td>    <td><input type="text" name="icq" value="{$vars.icq}"></td>  </tr>';
  document.write(ut);
  </script>
  <tr>
    <td>
      Upload Icon:
    </td>
    <td>
      <input type="hidden" name="MAX_FILE_SIZE" value="100000">
      <input type="file" name="uploadedfile">
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" value="Submit">
    </td>
  </tr>
</table>
</div>
{include file="footer.tpl"}
