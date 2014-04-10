window.addEventListener("load",function(){

//alert("hello there");
if(location.href.indexOf("http://localhost/owncloud/index.php/apps/contacts") !== -1){
//setTimeout(function(){
//alert("looking for tables");
//alert(document.getElementsByClassName('tel').length);


var telList = document.getElementsByClassName('tel');

var a;
var b;
for(var x = 0 ; x < telList.length ; x++){ 
        a = telList[x].parentNode;
        b = telList[x].cloneNode(false);
        b.innerHTML = telList[x].innerHTML;
        a.removeChild(telList[x]);
        a.appendChild(b);
}

var emailList = document.getElementsByClassName('email');

var a2;
var b2;
for(var x = 0 ; x < emailList.length ; x++){
        a2 = emailList[x].parentNode;
        b2 = emailList[x].cloneNode(false);
        b2.innerHTML = emailList[x].innerHTML;
        a2.removeChild(emailList[x]);
        a2.appendChild(b2);
}


//},1);

}

},false);
