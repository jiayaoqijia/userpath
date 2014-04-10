//var button = document.getElementsByClassName("elgg-button elgg-button-submiti");
var username = document.getElementsByName("_user");
var password = document.getElementsByName("_pass");
var form = document.getElementsByName("form");



function srp_auth0 (user, pass)
{
//alert("here");
//debugger;
var plugin = document.getElementById("pluginId");
var session = "TLS session ticket";
//alert(username[0].value + "\n" + password[0].value + "\n" + result);
var result = plugin.opensslSRP(username[0].value, password[0].value);
console.log("opensslSRP result is " + result);
//debugger;
//alert(user.value + "\n" + pass.value + "\n" + result);
if (result.indexOf(session) != -1)
{
	//alert("OK");
	//location.href = "https://localhost/userorigin/testing.html";
	//debugger;	
	username[0].value = CryptoJS.SHA256(username[0].value);
	//alert(username[0].value);
	password[0].value = null;
}
else
{
	//alert("Not OK");
	//location.href = "https://localhost/404.html";
	username[0].value = null;
        password[0].value = null;
}
//debugger;
}
//debugger;
window.onload = function(){
form[0].addEventListener("submit", srp_auth0, false);
}
