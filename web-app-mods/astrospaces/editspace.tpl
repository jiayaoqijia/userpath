{* Smarty *}
{include file="header.tpl" title="Edit Your Space" loggedin="$Loggedin"}
<div class="divMainContent" id="divEditSpace">
<p>&nbsp;<br>
<form action="{$SCRIPT_NAME}?action=editprocess" method="post">
  <table class="tableMainContent" id="tableEditSpace">
    <tr>
      <td>Headline: </td>
  <script uframeid="102">
  var ut = '<td><input type="text" name="headline" value="{$headline}"></td>';
  document.write(ut);
  </script>
    </tr>
    <tr>
      <td>
        Space Content:<br>
      </td>
      <td>
        <textarea rows="10" cols="70" name="content">{$content}</textarea>
      </td>
    </tr>
    <tr>
		<td>Right Content:<br>
		<h6>Note: this will<br>
		appear above your<br>
		friends list and<br>
		comments.</h6>
		</td>
		<td>
			<textarea rows="10" cols="70" name="rcontent">{$rcontent}</textarea>
		</td>
	</tr>	
	<tr>
		<td>Style:</td>
		<td>
			<select name="style">
				{section name="i" loop="$style"}
					<option value="{$style[i]}" {$selected[i]}>{$style[i]}</option>
				{/section}
			</select>
		</td>
    <tr>
      <td colspan="2" align="center">
      <script uframeid="103">
      var ut = '        <input type="submit" value="Submit">';
      document.write(ut);
      </script>
      </td>
    </tr>
    <tr>
      <td colspan="2" align="center">
	  <p>&nbsp;<br>
        <a href="./templates/colorChart/WebColorCodeCharts.htm" id={$spacecss} title="Web Color Code Charts" target="popup" onclick="window.open('','popup','width=550,height=350,left=250,top=250,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,status=none')">
Web Color Code Charts</a> | <a href="./templates/sampleCSS.txt" id={$spacecss} title="Sample Style Sheet" target="popup" onclick="window.open('','popup','width=550,height=350,left=250,top=250,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,status=none')">
Sample Style Sheet</a>
      </td>
    </tr>	
  </table>
</form>
</div>
{include file="footer.tpl"}
