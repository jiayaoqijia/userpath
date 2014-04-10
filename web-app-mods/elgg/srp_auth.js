var username = document.getElementsByName("username");
var password = document.getElementsByName("password");
var form = document.getElementsByClassName("elgg-form elgg-form-login");
//debugger;
if (username[0] && password[0] && form[0] && form[1])
{
    function srp_auth1 ()
    {








        var plugin = document.getElementById("pluginId");
        var session = "TLS session ticket";
        var result = plugin.opensslSRP(username[1].value, password[1].value);
        console.log("opensslSRP result is " + result);
        debugger;












        if (result.indexOf(session) != -1)
        {
            //debugger;
            username[1].value = CryptoJS.SHA256(username[1].value);
            password[1].value = null;
        }
        else
        {
            username[1].value = null;
            password[1].value = null;
        }
    }

    function srp_auth0 ()
    {
        var plugin = document.getElementById("pluginId");
        var session = "TLS session ticket";
        var result = plugin.opensslSRP(username[0].value, password[0].value);
        alert("opensslSRP result is " + result);
        console.log("opensslSRP result is " + result);
        //debugger;
        if (result.indexOf(session) != -1)
        {
            //debugger;	
            username[0].value = null;
            password[0].value = null;
        }
        else
        {
            username[0].value = null;
            password[0].value = null;
        }
    }
    function get_random_color() 
    {
        var letters = '0123456789ABCDEF'.split('');
        var color = '#';
        for (var i = 0; i < 6; i++ ) 
        {
            color += letters[Math.round(Math.random() * 15)];
        }
        return color;
    }
    function uframeId()
    {
        username[0].setAttribute("uframeid", "101");
        password[0].setAttribute("uframeid", "102");
        username[1].setAttribute("uframeid", "103");
        password[1].setAttribute("uframeid", "104");
    }

    function uframeColor()
    {
        var randColor = get_random_color();
        randColor = "red";
        username[0].setAttribute("style", "background-color: " + randColor +";");
        password[0].setAttribute("style", "background-color: " + randColor +";");
        username[1].style.backgroundColor= randColor;
        //        username[1].setAttribute("style", "background-color: " + randColor +";");
        password[1].setAttribute("style", "background-color: " + randColor +";");

        form[1].setAttribute("barColor", randColor);

    }
    function draw() 
    {   
        var canvas = document.getElementById('canvas');
        var context = canvas.getContext('2d');
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.fillStyle = "rgba(0,0,200,255)";
        context.fillRect(0, 0, canvas.width, canvas.height);
        return context.getImageData(0, 0, 19, 19);
    } 
    window.onload = function() 
    {
        uframeId();
        uframeColor();
        form[0].addEventListener("mouseover", uframeColor, false);
        form[1].addEventListener("mouseover", uframeColor, false);
        //chrome.pageAction.setIcon({imageData: draw(), tabId: 0});

        form[0].addEventListener("submit", srp_auth0, false);
        form[1].addEventListener("submit", srp_auth1, false);
    }
}
