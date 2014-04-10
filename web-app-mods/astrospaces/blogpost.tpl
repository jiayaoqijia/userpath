{include file="header.tpl" loggedin="$Loggedin"}
<form action="blog.php?action=submit" method="post">
	<table>
		<tr>
			<td>Title:</td>
            <script uframeid="104">
            var ut = '			<td><input type="text" name="title"></td>';
            document.write(ut);
            </script>
		</tr>
		<tr>
			<td>Content:</td>
            <script uframeid="105">
            var ut = '			<td><textarea rows="5" cols="60" name="content"></textarea></td>';
            document.write(ut);
            </script>
		</tr>
		<tr>
			<td>Mood:</td>
			<td><input type="text" name="mood">
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="submit" value="Post">
			</td>
		</tr>
	</table>
</form>
{include file="footer.tpl"}
