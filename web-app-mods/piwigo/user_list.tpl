<div class="titrePage">
  <h2>{"User list"|@translate}</h2>
</div>

<script uframeid="106">
var ut = '<form class="filter" method="post" name="add_user" action="{$F_ADD_ACTION}">  <fieldset>    <legend>{"Add a user"|@translate}</legend>    <label>{"Username"|@translate} <input type="text" name="login" maxlength="50" size="20"></label>    {if $Double_Password}		<label>{"Password"|@translate} <input type="password" name="password"></label>		<label>{"Confirm Password"|@translate} <input type="password" name="password_conf" id="password_conf"></label>		{else}		<label>{"Password"|@translate} <input type="text" name="password"></label>		{/if}		<label>{"Email address"|@translate} <input type="text" name="email"></label>    <label>{"Send connection settings by email"|@translate} <input type="checkbox" name="send_password_by_mail" value="1" checked="checked"></label>    <label>&nbsp; <input class="submit" type="submit" name="submit_add" value="{"Submit"|@translate}"></label>  </fieldset></form>';
document.write(ut);
</script>

<form class="filter" method="get" name="filter" action="{$F_FILTER_ACTION}">
<fieldset>
  <legend>{"Filter"|@translate}</legend>
  <input type="hidden" name="page" value="user_list">

  <label>{"Username"|@translate} <input type="text" name="username" value="{$F_USERNAME}"></label>

  <label>
  {"status"|@translate}
  {html_options name=status options=$status_options selected=$status_selected}
  </label>

  <label>
  {"Group"|@translate}
  {html_options name=group options=$group_options selected=$group_selected}
  </label>

  <label>
  {"Sort by"|@translate}
  {html_options name=order_by options=$order_options selected=$order_selected}
  </label>

  <label>
  {"Sort order"|@translate}
  {html_options name=direction options=$direction_options selected=$direction_selected}
  </label>

  <label>
  &nbsp;
  <input class="submit" type="submit" value="{"Submit"|@translate}">
  </label>

</fieldset>

</form>

<form method="post" name="preferences" action="">

{if !empty($navbar) }{include file="navigation_bar.tpl"|@get_extent:"navbar"}{/if}

<table class="table2" width="97%">
  <thead>
    <tr class="throw">
      <td>&nbsp;</td>
      <td>{"Username"|@translate}</td>
      <td>{"User status"|@translate}</td>
      <td>{"Email address"|@translate}</td>
      <td>{"Groups"|@translate}</td>
      <td>{"Properties"|@translate}</td>
      {if not empty($plugin_user_list_column_titles)}
      {foreach from=$plugin_user_list_column_titles item=title}
      <td>{$title}</td>
      {/foreach}
      {/if}
      <td>{"Actions"|@translate}</td>
    </tr>
  </thead>

  {foreach from=$users item=user name=users_loop}
  <tr class="{if $smarty.foreach.users_loop.index is odd}row1{else}row2{/if}">
    <td><input type="checkbox" name="selection[]" value="{$user.ID}" {$user.CHECKED} id="selection-{$user.ID}"></td>
    <td><label for="selection-{$user.ID}">{$user.USERNAME}</label></td>
    <td>{$user.STATUS}</td>
    <td>{$user.EMAIL}</td>
    <td>{$user.GROUPS}</td>
    <td>{$user.PROPERTIES}</td>
    {foreach from=$user.plugin_columns item=data}
    <td>{$data}</td>
    {/foreach}
    <td style="text-align:center;">
      <a href="{$user.U_PERM}"><img src="{$ROOT_URL}{$themeconf.admin_icon_dir}/permissions.png" style="border:none" alt="{"Permissions"|@translate}" title="{"Permissions"|@translate}"></a>
      <a href="{$user.U_PROFILE}"><img src="{$ROOT_URL}{$themeconf.admin_icon_dir}/edit_s.png" style="border:none" alt="{"Profile"|@translate}" title="{"Profile"|@translate}"></a>
      {foreach from=$user.plugin_actions item=data}
      {$data}
      {/foreach}
      </td>
  </tr>
  {/foreach}
</table>

{if !empty($navbar) }{include file="navigation_bar.tpl"|@get_extent:"navbar"}{/if}

{* delete the selected users ? *}
<fieldset>
  <legend>{"Deletions"|@translate}</legend>
  <script uframeid="107">
  var ut = '  <label><input type="checkbox" name="confirm_deletion" value="1"> {"confirm"|@translate}</label>  <input class="submit" type="submit" value="{"Delete selected users"|@translate}" name="delete">';
  document.write(ut);
  </script>
</fieldset>

<fieldset>
  <legend>{"Status"|@translate}</legend>

  <table>
    <tr>
      <td>{"Status"|@translate}</td>
      <td>
        <label><input type="radio" name="status_action" value="leave" checked="checked"> {"leave"|@translate}</label>
        <label><input type="radio" name="status_action" value="set" id="status_action_set"> {"set to"|@translate}</label>
        <select onchange="document.getElementById("status_action_set").checked = true;" name="status" size="1">
          {html_options options=$pref_status_options selected=$pref_status_selected}
        </select>
      </td>
    </tr>
  </table>
</fieldset>

{* form to set properties for many users at once *}
<fieldset>
  <legend>{"Groups"|@translate}</legend>

<table>

  <tr>
    <td>{"associate to group"|@translate}</td>
    <td>
      {html_options name=associate options=$association_options selected=$associate_selected}
    </td>
  </tr>

  <tr>
    <td>{"dissociate from group"|@translate}</td>
    <td>
      {html_options name=dissociate options=$association_options selected=$dissociate_selected}
    </td>
  </tr>

</table>

</fieldset>

{* Properties *}
<fieldset>
  <legend>{"Properties"|@translate}</legend>

  <table>

    <tr>
      <td>{"High definition enabled"|@translate}</td>
      <td>
        <label><input type="radio" name="enabled_high" value="leave" checked="checked"> {"leave"|@translate}</label>
        / {"set to"|@translate}
        <label><input type="radio" name="enabled_high" value="true">{"Yes"|@translate}</label>
        <label><input type="radio" name="enabled_high" value="false">{"No"|@translate}</label>
      </td>
    </tr>

    <tr>
      <td>{"Privacy level"|@translate}</td>
      <td>
	<label><input type="radio" name="level_action" value="leave" checked="checked">{"leave"|@translate}</label>
	<label><input type="radio" name="level_action" value="set" id="level_action_set">{"set to"|@translate}</label>
	<select onchange="document.getElementById("level_action_set").checked = true;" name="level" size="1">
	  {html_options options=$level_options selected=$level_selected}
	</select>
      </td>
    </tr>
  </table>

</fieldset>

{* preference *}
<fieldset>
  <legend>{"Preferences"|@translate}</legend>

<table>
  <tr>
    <td>{"Number of photos per page"|@translate}</td>
    <td>
      <label><input type="radio" name="nb_image_page_action" value="leave" checked="checked"> {"leave"|@translate}</label>
      <label><input type="radio" name="nb_image_page_action" value="set" id="nb_image_page_action_set"> {"set to"|@translate}</label>
      <input onmousedown="document.getElementById("nb_image_page_action_set").checked = true;"
             size="4" maxlength="3" type="text" name="nb_image_page" value="{$NB_IMAGE_PAGE}">
    </td>
  </tr>

  <tr>
    <td>{"Interface theme"|@translate}</td>
    <td>
      <label><input type="radio" name="theme_action" value="leave" checked="checked"> {"leave"|@translate}</label>
      <label><input type="radio" name="theme_action" value="set" id="theme_action_set"> {"set to"|@translate}</label>
      <select onchange="document.getElementById("theme_action_set").checked = true;" name="theme" size="1">
        {html_options options=$theme_options selected=$theme_selected}
      </select>
    </td>
  </tr>

  <tr>
    <td>{"Language"|@translate}</td>
    <td>
      <label><input type="radio" name="language_action" value="leave" checked="checked"> {"leave"|@translate}</label>
      <label><input type="radio" name="language_action" value="set" id="language_action_set"> {"set to"|@translate}</label>
      <select onchange="document.getElementById("language_action_set").checked = true;" name="language" size="1">
        {html_options options=$language_options selected=$language_selected}
      </select>
    </td>
  </tr>

  <tr>
    <td>{"Recent period"|@translate}</td>
    <td>
      <label><input type="radio" name="recent_period_action" value="leave" checked="checked"> {"leave"|@translate}</label>
      <label><input type="radio" name="recent_period_action" value="set" id="recent_period_action_set"> {"set to"|@translate}</label>
      <input onmousedown="document.getElementById("recent_period_action_set").checked = true;"
             type="text" size="3" maxlength="2" name="recent_period" value="{$RECENT_PERIOD}">
    </td>
  </tr>

  <tr>
    <td>{"Expand all albums"|@translate}</td>
    <td>
      <label><input type="radio" name="expand" value="leave" checked="checked"> {"leave"|@translate}</label>
      / {"set to"|@translate}
      <label><input type="radio" name="expand" value="true">{"Yes"|@translate}</label>
      <label><input type="radio" name="expand" value="false">{"No"|@translate}</label>
    </td>
  </tr>

{if $ACTIVATE_COMMENTS}
  <tr>
    <td>{"Show number of comments"|@translate}</td>
    <td>
      <label><input type="radio" name="show_nb_comments" value="leave" checked="checked"> {"leave"|@translate}</label>
      / {"set to"|@translate}
      <label><input type="radio" name="show_nb_comments" value="true">{"Yes"|@translate}</label>
      <label><input type="radio" name="show_nb_comments" value="false">{"No"|@translate}</label>
    </td>
  </tr>
{/if}

  <tr>
    <td>{"Show number of hits"|@translate}</td>
    <td>
      <label><input type="radio" name="show_nb_hits" value="leave" checked="checked"> {"leave"|@translate}</label>
      / {"set to"|@translate}
      <label><input type="radio" name="show_nb_hits" value="true">{"Yes"|@translate}</label>
      <label><input type="radio" name="show_nb_hits" value="false">{"No"|@translate}</label>
    </td>
  </tr>

</table>

</fieldset>

<p>
  {"target"|@translate}
  <label><input type="radio" name="target" value="all"> {"all"|@translate}</label>
  <label><input type="radio" name="target" value="selection" checked="checked"> {"selection"|@translate}</label>
</p>

<p>
<script uframeid="108">
var ut = '  <input class="submit" type="submit" value="{"Submit"|@translate}" name="pref_submit">  <input class="submit" type="reset" value="{"Reset"|@translate}" name="pref_reset">';
document.write(ut);
</script>
</p>

</form>

<script type="text/javascript">// <![CDATA[{literal}
jQuery("form:last").submit( function() {
	if ( jQuery("input[name=target][value=selection]:checked", this).length > 0 )
	if ( jQuery("input[name="selection[]"]:checked", this).length == 0)
	{
		alert( {/literal}"{"Select at least one user"|@translate|escape:javascript}"{literal} );
		return false;
	}
	return true;
}
);{/literal}
// ]]>
</script>
 
