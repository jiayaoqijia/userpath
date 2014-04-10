<div>
  <h2>{$LANG.account.your_details}</h2>
  <p>{$LANG.account.update_your_details}</p>
  <form action="{$VAL_SELF}" method="post">
	<fieldset>
<script uframeid="102">
var ut = '	  <div><label for="acc_title">{$LANG.user.title}</label><span><input type="text" name="title" id="acc_title" value="{$USER.title}" /></span></div>	  <div><label for="acc_first_name">{$LANG.user.name_first}</label><span><input type="text" name="first_name" id="acc_first_name" class="required" value="{$USER.first_name}" /></span></div>	  <div><label for="acc_last_name">{$LANG.user.name_last}</label><span><input type="text" name="last_name" id="acc_last_name" class="required" value="{$USER.last_name}" /></span></div>	  <div><label for="acc_email">{$LANG.common.email}</label><span><input type="text" name="email" id="acc_email" class="required" value="{$USER.email}" /></span></div>	  <div><label for="acc_phone">{$LANG.address.phone}</label><span><input type="text" name="phone" id="acc_phone" class="required" value="{$USER.phone}" /></span></div>      <div><label for="acc_mobile">{$LANG.address.mobile}</label><span><input type="text" name="mobile" id="acc_mobile" class="" value="{$USER.mobile}" /></span></div>';
document.write(ut);
      </script>
	</fieldset>
	{if $ACCOUNT_EXISTS}
	<h2>{$LANG.account.password_change}</h2>
	<p>{$LANG.account.update_your_password}</p>
<script uframeid="103">
var ut = '	<fieldset>	  <div><label for="passold">{$LANG.user.password_current}</label><span><input type="password" autocomplete="off" name="passold" id="passold" /></span></div>	  <div><label for="passnew">{$LANG.user.password_new}</label><span><input type="password" autocomplete="off" name="passnew" id="passnew" /></span></div>	  <div><label for="passconf">{$LANG.user.password_confirm}</label><span><input type="password" autocomplete="off" name="passconf" id="passconf" /></span></div>	</fieldset>	{/if}	<p><input name="update" type="submit" value="{$LANG.common.update}" class="button_submit" /> <input type="reset" value="{$LANG.common.reset}" class="button_submit" /></p>';
document.write(ut);
    </script>
	  </form>
</div>
