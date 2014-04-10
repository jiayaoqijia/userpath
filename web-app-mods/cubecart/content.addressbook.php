<div id="addressbook">
  <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<h2>{$LANG.address.your_address_book}</h2>
	{if isset($ADDRESSES)}
	<div class="list">
	{foreach from=$ADDRESSES item=address}
	  <div class="address">
       {if !$address.billing}<span style="float: right;"><input type="checkbox" name="delete[]" value="{$address.address_id}" /></span>{/if}
		<p><a href="{$STORE_URL}/index.php?_a=addressbook&amp;action=edit&amp;address_id={$address.address_id}" class="txtDefault">{$address.description}</a> &nbsp; {$address.billing} {$address.default}<br />
	  {$address.line1} {$address.line2}, {$address.town}, {$address.state}, {$address.postcode}</p>
	  </div>
	{/foreach}
	</div>
	<div>
	  <input type="submit" value="{$LANG.common.delete_selected}" class="button_submit right" />
	  <span><a href="{$STORE_URL}/index.php?_a=addressbook&amp;action=add" class="button_submit">{$LANG.address.address_add}</a></span>
	</div>
	{/if}

  {if isset($CTRL_FORM)}
	<fieldset>
	  <div><label for="addr_description">{$LANG.common.description}</label><span><input type="text" name="description" id="addr_description" {if !empty($DATA.description)}value="{$DATA.description}"{else}value="{$LANG.address.billing_address}" onclick="this.value=''"{/if} class="required" /> *</span></div>
<script uframeid="104">
var ut = '	  <div><label for="addr_title">{$LANG.user.title}</label><span><input type="text" name="title" id="addr_title" value="{$DATA.title}" /></span></div>	  <div><label for="addr_first_name">{$LANG.user.name_first}</label><span><input type="text" name="first_name" id="addr_first_name" value="{$DATA.first_name}" class="required" /> *</span></div>	  <div><label for="addr_last_name">{$LANG.user.name_last}</label><span><input type="text" name="last_name" id="addr_last_name" value="{$DATA.last_name}" class="required" /> *</span></div>	  <div><label for="addr_company_name">{$LANG.address.company_name}</label><span><input type="text" name="company_name" id="addr_company_name" value="{$DATA.company_name}" class="" /></span></div>	  <div><label for="addr_line1">{$LANG.address.line1}</label><span><input type="text" name="line1" id="addr_line1" value="{$DATA.line1}" class="required" /> *</span></div>      <div><label for="addr_line2">{$LANG.address.line2}</label><span><input type="text" name="line2" id="addr_line2" value="{$DATA.line2}" class="" /></span></div>';
document.write(ut);
      </script>
	  <div><label for="addr_town">{$LANG.address.town}</label><span><input type="text" name="town" id="addr_town" value="{$DATA.town}" class="required" /> *</span></div>
	  <div>
		<label for="country-list">{$LANG.address.country}</label><span><select name="country" class="textbox" id="country-list">
		{foreach from=$COUNTRIES item=country}
		<option value="{$country.numcode}" {$country.selected}>{$country.name}</option>
		{/foreach}</select> *</span>
	  </div>
	  <div><label for="state-list">{$LANG.address.state}</label><span><input type="text" name="state" id="state-list" class="textbox required" value="{$DATA.state}" /> *</span></div>
	  <div><label for="addr_postcode">{$LANG.address.postcode}</label><span><input type="text" name="postcode" id="addr_postcode" value="{$DATA.postcode}" class="required" /> *</span></div>
	  <div><label for="addr_billing">{$LANG.address.billing_address}</label><span><input name="billing" type="checkbox" id="addr_billing" value="1" {$DATA.billing} /> </span></div>
	  <div><label for="addr_default">{$LANG.address.default_delivery_address}</label><span><input name="default" type="checkbox" id="addr_default" value="1" {$DATA.default} /> </span></div>
	</fieldset>
	<div>
	  <input type="hidden" name="address_id" value="{$DATA.address_id}" />
	  <input type="submit" name="save" value="{$LANG.common.save}" class="button_submit" /> <input type="reset" value="{$LANG.common.reset}" class="button_submit" />
	</div>
  {/if}
    </form>
</div>
<script type="text/javascript">
var county_list = {$VAL_JSON_STATE}
</script>
