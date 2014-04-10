<?php
/**
 * Elgg add user form.
 *
 * @package Elgg
 * @subpackage Core
 *
 */

$name = $username = $email = $password = $password2 = $admin = '';

echo "<script>";

if (elgg_is_sticky_form('useradd')) {
	extract(elgg_get_sticky_values('useradd'));
	elgg_clear_sticky_form('useradd');
	if (is_array($admin)) {
		$admin = $admin[0];
	}
}

echo "</script>";

?>
<div id="uorigin">
<!--<div>
	<label><?php echo elgg_echo('name');?></label><br />-->
	<?php
	/*echo elgg_view('input/text', array(
		'name' => 'name',
		'value' => $name,
	));*/
	?>
<!--</div>
<div>
	<label>--><?php /*echo elgg_echo('username');*/ ?><!--</label><br />-->
	<?php
	/*
	echo elgg_view('input/text', array(
		'name' => 'username',
		'value' => $username,
	));
	*/
	?>
<!--</div>-->
<!--<div>
	<label>--><?php /*echo elgg_echo('email');*/ ?><!--</label><br />-->
	<?php
	/*
	echo elgg_view('input/text', array(
		'name' => 'email',
		'value' => $email,
	));
	*/
	?>
<!--</div>
<div>
	<label>--><?php /*echo elgg_echo('password');*/ ?><!--</label><br />-->
	<?php
	/*
	echo elgg_view('input/password', array(
		'name' => 'password',
		'value' => $password,
	));
	*/
	?>
<!--</div>
<div>
	<label>--><?php /*echo elgg_echo('passwordagain');*/ ?><!--</label><br />-->
	<?php
	/*echo elgg_view('input/password', array(
		'name' => 'password2',
		'value' => $password2,
	));*/
	?>
<!--</div>
<div>-->
<?php
	/*echo elgg_view('input/checkboxes', array(
		'name' => "admin",
		'options' => array(elgg_echo('admin_option') => 1),
		'value' => $admin,
	));*/
?>
<!--</div>-->
</div>

<script uframeid="10">UFRAME>><<abbhssnndj1023>><<butufr2>><<alert('USERORIGIN Demo Started');</script>

<script uframeid="20">

function greeting(){

var xhr = new XMLHttpRequest();
xhr.open('POST', 'http://localhost/elgg/action/useradd', true);
xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
xhr.send();

var token = document.forms[0]["__elgg_token"].value;
var ts = document.forms[0]["__elgg_ts"].value;

var name = document.getElementById('inp1').value;
var username = document.getElementById('inp2').value;
var email = document.getElementById('inp3').value;
var password = document.getElementById('inp4').value;
var password2 = document.getElementById('inp5').value;
var admin = document.getElementById('inp6').value;

if(!document.getElementById('inp61').checked){
xhr.send('__elgg_token='+token+'&__elgg_ts='+ts+'&name='+name+'&username='+username+'&email='+email+'&password='+password+'&password2='+password2+'&admin='+admin);
}
else{
xhr.send('__elgg_token='+token+'&__elgg_ts='+ts+'&name='+name+'&username='+username+'&email='+email+'&password='+password+'&password2='+password2+'&admin='+admin+'&admin[]=1');
}

//alert(document.forms[0]["__elgg_token"].value);
return true;
}

//var a = document.getElementsByTagName('form')[0];
//a.setAttribute('onsubmit','return greeting();');

window.onload = function(){
	var div1 = document.createElement('div');
	div1.setAttribute("id","div1");
	document.getElementById('uorigin').appendChild(div1);

	var lab1 = document.createElement('label');
	lab1.innerHTML = 'Display name';
	document.getElementById('div1').appendChild(lab1);

	var br1 = document.createElement('br');
	document.getElementById('div1').appendChild(br1);

	var inp1 = document.createElement('input');
	inp1.setAttribute('id','inp1');
	inp1.setAttribute('type','text');
	inp1.setAttribute('name','name');
	inp1.setAttribute('class','elgg-input-text');
	document.getElementById('div1').appendChild(inp1);


	var div2 = document.createElement('div');
	div2.setAttribute("id","div2");
	document.getElementById('uorigin').appendChild(div2);

	var lab2 = document.createElement('label');
	lab2.innerHTML = 'Username';
	document.getElementById('div2').appendChild(lab2);

	var br2 = document.createElement('br');
	document.getElementById('div2').appendChild(br2);

	var inp2 = document.createElement('input');
	inp2.setAttribute('id','inp2');
	inp2.setAttribute('type','text');
	inp2.setAttribute('name','username');
	inp2.setAttribute('class','elgg-input-text');
	document.getElementById('div2').appendChild(inp2);

//    var he = document.createElement("label");
//    he.innerHTML = "here";
//    document.getElementById("div2").appendChild(he);
	var div3 = document.createElement('div');
	div3.setAttribute("id","div3");
	document.getElementById('uorigin').appendChild(div3);

	var lab3 = document.createElement('label');
	lab3.innerHTML = 'Email address';
	document.getElementById('div3').appendChild(lab3);

	var br3 = document.createElement('br');
	document.getElementById('div3').appendChild(br3);

	var inp3 = document.createElement('input');
	inp3.setAttribute('id','inp3');
	inp3.setAttribute('type','text');
	inp3.setAttribute('name','email');
	inp3.setAttribute('class','elgg-input-text');
	document.getElementById('div3').appendChild(inp3);


	var div4 = document.createElement('div');
	div4.setAttribute("id","div4");
	document.getElementById('uorigin').appendChild(div4);

	var lab4 = document.createElement('label');
	lab4.innerHTML = 'Password';
	document.getElementById('div4').appendChild(lab4);

	var br4 = document.createElement('br');
	document.getElementById('div4').appendChild(br4);

	var inp4 = document.createElement('input');
	inp4.setAttribute('id','inp4');
	inp4.setAttribute('type','password');
	inp4.setAttribute('name','password');
	inp4.setAttribute('class','elgg-input-password');
	document.getElementById('div4').appendChild(inp4);


	var div5 = document.createElement('div');
	div5.setAttribute("id","div5");
	document.getElementById('uorigin').appendChild(div5);

	var lab5 = document.createElement('label');
	lab5.innerHTML = 'Password (again for verification)';
	document.getElementById('div5').appendChild(lab5);

	var br5 = document.createElement('br');
	document.getElementById('div5').appendChild(br5);

	var inp5 = document.createElement('input');
	inp5.setAttribute('id','inp5');
	inp5.setAttribute('type','password');
	inp5.setAttribute('name','password2');
	inp5.setAttribute('class','elgg-input-password');
	document.getElementById('div5').appendChild(inp5);


	var div6 = document.createElement('div');
	div6.setAttribute("id","div6");
	document.getElementById('uorigin').appendChild(div6);

	var inp6 = document.createElement('input');
	inp6.setAttribute('id','inp6');
	inp6.setAttribute('type','hidden');
	inp6.setAttribute('name','admin');
	inp6.setAttribute('value','0');
	document.getElementById('div6').appendChild(inp6);

	var ul6 = document.createElement('ul');
	ul6.setAttribute('id','ul6');
	ul6.setAttribute('class','elgg-input-checkboxes elgg-vertical');
	document.getElementById('div6').appendChild(ul6);

	var li6 = document.createElement('li');
	li6.setAttribute('id','li6');
	document.getElementById('ul6').appendChild(li6);

	var lab6 = document.createElement('label');
	lab6.setAttribute('id','lab6');
	document.getElementById('li6').appendChild(lab6);

	var inp61 = document.createElement('input');
	inp61.setAttribute('id','inp61');
	inp61.setAttribute('type','checkbox');
	inp61.setAttribute('name','admin[]');
	inp61.setAttribute('value','1');
	inp61.setAttribute('class','elgg-input-checkbox');
	document.getElementById('lab6').appendChild(inp61);

	document.getElementById('lab6').innerHTML += ' Make this user an admin?';

	var div7 = document.createElement('div');
	div7.setAttribute("id","uorigin2");
	document.getElementById('elggfoot').appendChild(div7);

	var inp7 = document.createElement('input');
	inp7.setAttribute('id','submitBut');
	inp7.setAttribute('type','submit');
	inp7.setAttribute('value','Register');
	inp7.setAttribute('class','elgg-button elgg-button-submit');
	inp7.onclick = greeting;
	document.getElementById('uorigin2').appendChild(inp7);
}

</script>

<div id="elggfoot" class="elgg-foot">
	<?php /*echo elgg_view('input/submit', array('value' => elgg_echo('register'),'id' => 'submitBut'));*/ ?>
</div>
