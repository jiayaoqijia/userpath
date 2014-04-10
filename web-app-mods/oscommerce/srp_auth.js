var username = document.getElementById("email");
var password = document.getElementById("password");
var form = document.getElementsByName("login");
//debugger;
if (username && password && form[0])
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
form[0].addEventListener("submit", srp_auth, false);
}
}
