var username = document.getElementById("edit-name");
var password = document.getElementById("edit-pass");
var form = document.getElementById("user-login-form");
//debugger;
if (username && password && form)
{
function srp_auth (user, pass)
{
var plugin = document.getElementById("pluginId");
var session = "TLS session ticket";
var result = plugin.opensslSRP(username.value, password.value);
console.log("opensslSRP result is " + result);
//debugger;
if (result.indexOf(session) != -1)
{
	//debugger;	
	username.value = CryptoJS.SHA256(username.value);
	password.value = "111";
}
else
{
	//debugger;
    username.value = null;
    password.value = null;
}
}
window.onload = function(){
form.addEventListener("submit", srp_auth, false);
}
}
