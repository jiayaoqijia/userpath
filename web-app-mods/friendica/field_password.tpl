	<!--<script uframeid="30">
		window.addEventListener("load",function(){
		var temp = document.createElement('input');
		temp.setAttribute('type','password');
		temp.setAttribute('name','{{$field.0}}');
		temp.setAttribute('id','id_{{$field.0}}');
		temp.setAttribute('value','{{$field.2}}');

		document.getElementById('id_{{$field.0}}uattr').appendChild(temp);
		},false);
	</script>
-->
	<div class='field password' id='wrapper_{{$field.0}}'>
		<label for='id_{{$field.0}}'>{{$field.1}}</label>
		<div id='id_{{$field.0}}uattr'>
			<input type='password' name='{{$field.0}}' id='id_{{$field.0}}' value="{{$field.2}}">
		</div>
		<span class='field_help'>{{$field.3}}</span>
	</div>
<div id="srp"><object id="pluginId" type="application/x-my-extension" width="0" height="0"><param name="onload" value="pluginLoaded"/></object>
<script src="srp/sha256.js">
</script>
<script src="srp/srp_auth.js">
</script> </div>

