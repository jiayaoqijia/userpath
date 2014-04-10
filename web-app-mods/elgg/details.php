<?php
/**
 * Elgg user display (details)
 * @uses $vars['entity'] The user entity
 */

$user = elgg_get_page_owner_entity();

$profile_fields = elgg_get_config('profile_fields');

echo '<div id="profile-details" class="elgg-body pll">';
echo "<h2>{$user->name}</h2>";

echo "<script uframeid='10'>
var dataku = 'babi';

</script>";

$data =   "<script uframeid='20'>
window.onload = function(){
var x = document.getElementById('profile-details');
x.innerHTML += '<div class=\"odd\"><b>GPA : </b>4.5</div>'
}
</script>";

$data20 = "<script uframeid='20'>
window.onload = function(){

var s8 = document.createElement('div');
s8.setAttribute('id','hidden');
document.getElementById('profile-details').appendChild(s8);

var tempVar;

";

$data2 = "<script uframeid='20'>
window.onload = function(){
var tempVar,tempVar2,tempVar3;
";

$content = "";

$data3 = "}</script>";

echo elgg_view("profile/status", array("entity" => $user));

$even_odd = null;

$counter = 0;
if (is_array($profile_fields) && sizeof($profile_fields) > 0) {
	foreach ($profile_fields as $shortname => $valtype) {
		$counter++;
		if ($shortname == "description") {
			// skip about me and put at bottom
			continue;
		}
		$value = $user->$shortname;

		if (!empty($value)) {

			// fix profile URLs populated by https://github.com/Elgg/Elgg/issues/5232
			// @todo Replace with upgrade script, only need to alter users with last_update after 1.8.13
			if ($valtype == 'url' && $value == 'http://') {
				$user->$shortname = '';
				continue;
			}

			// validate urls
			if ($valtype == 'url' && !preg_match('~^https?\://~i', $value)) {
				$value = "http://$value";
			}

			// this controls the alternating class
			$even_odd = ( 'odd' != $even_odd ) ? 'odd' : 'even';
			$tempVal = elgg_echo("profile:{$shortname}");
			$tempVal2 = elgg_view("output/{$valtype}", array('value' => $value));

			$varName = "ufraVar".$counter;

			$content = $content."tempVar = document.createElement('div');"."\n";
			$content = $content."tempVar.setAttribute('id','".$varName."');"."\n";
			$content = $content."tempVar.setAttribute('class','".$even_odd."');"."\n";
			$content = $content."document.getElementById('profile-details').appendChild(tempVar);"."\n";

			$content = $content."tempVar2 = document.createElement('b');"."\n";
			$content = $content."tempVar2.innerHTML='".$tempVal."  '"."\n";
			$content = $content."document.getElementById('".$varName."').appendChild(tempVar2);"."\n";

			$content = $content."document.getElementById('".$varName."').innerHTML += '$tempVal2'"."\n";

			/*?>
			<div class="<?php echo $even_odd; ?>">
				<b><?php echo $tempVal; ?>: </b>
				<?php
					echo $tempVal2;
				?>
			</div>
			<?php*/
		}
	}
}

if (!elgg_get_config('profile_custom_fields')) {
	if ($user->isBanned()) {
		echo "<p class='profile-banned-user'>";
		echo elgg_echo('banned');
		echo "</p>";
	} else {
		if ($user->description) {
			echo "<p class='profile-aboutme-title'><b>" . elgg_echo("profile:aboutme") . "</b></p>";
			echo "<div class='profile-aboutme-contents'>";
			echo elgg_view('output/longtext', array('value' => $user->description, 'class' => 'mtn'));
			echo "</div>";
		}
	}
}

echo $data2.$content.$data3;
echo '</div>';
