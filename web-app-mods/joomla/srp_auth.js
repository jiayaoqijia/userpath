var username = document.getElementById("mod-login-username");
var password = document.getElementById("mod-login-password");
var form = document.getElementById("form-login");
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
	password.value = null;
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
