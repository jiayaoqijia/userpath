<div id="checkout">
  {if isset($ITEMS)}
  <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" id="basket">
    {if $INCLUDE_CHECKOUT}
    {include file='templates/content.checkout.confirm.php'}
    {/if}
	<h2>{$LANG.checkout.your_basket}</h2>
	<div class="basket_header">
	  <span class="basket_price">{$LANG.common.price}</span>
	  <span class="basket_price">{$LANG.common.price_unit}</span>
	  {$LANG.common.remove}
	</div>
	{foreach from=$ITEMS key=hash item=item}
	<div class="basket_product">
	  <span class="remove">
		<a href="{$STORE_URL}/index.php?_a=basket&amp;remove-item={$hash}">
		  <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/{$SKIN_SUBSET}/basket_remove.png" alt="{$LANG.common.remove}" title="{$LANG.common.remove}" />
		</a>
	  </span>
	  <span class="image">
		<a href="{$item.link}" title="{$item.name}">
		  <img src="{$item.image}" alt="{$item.name}" />
		</a>
	  </span>
	  <p><a href="{$item.link}" class="txtDefault"><strong>{$item.name}</strong></a> {if !empty($item.product_code)}- {$item.product_code}{/if} {if $item.base_price_display}({$item.base_price_display}){/if}</p>
	  {if $item.options}
	  {foreach from=$item.options item=option}<p><strong>{$option.option_name}</strong>: {$option.value_name|truncate:45:"&hellip;":true}{if !empty($option.price_display)} ({$option.price_display}){/if}</p>{/foreach}
	  {/if}
	  <p>
		<span class="price">{$item.price_display}</span>
		<span class="price">{$item.line_price_display}</span>
		{$LANG.common.quantity}:
		<input name="quan[{$hash}]" type="text" value="{$item.quantity}" class="quantity" {$QUAN_READ_ONLY} />
	  </p>
	</div>
	{/foreach}

	<div class="subtotals">
	  <p><span class="price">{$SUBTOTAL}</span>{$LANG.basket.total_sub}</p>
	  {if isset($SHIPPING)}
	  <p>
		<span class="price">{$CUSTOMER_LOCALE.mark} {$SHIPPING_VALUE}</span>
		<span class="price">{$LANG.basket.shipping}</span>
		{$LANG.basket.shipping_select}
		<select name="shipping" class="update_form required">
		  <option value="">{$LANG.form.please_select}</option>
		  {foreach from=$SHIPPING key=group item=methods}
		  {if $HIDE_OPTION_GROUPS ne '1'}<optgroup label="{$group}">{/if}
		  	{foreach from=$methods item=method}
			<option value="{$method.value}" {$method.selected}>{$CUSTOMER_LOCALE.mark} {$method.display}</option>
			{/foreach}
		  {if $HIDE_OPTION_GROUPS ne '1'}</optgroup>{/if}
		  {/foreach}
		</select>
	  </p>
	  {/if}

	  {foreach from=$TAXES item=tax}
	  <p><span class="price">{$CUSTOMER_LOCALE.mark} {$tax.value}</span>{$tax.name}</p>
	  {/foreach}
	</div>
	<div class="discounts">
  	  <span class="code_input"><label for="coupon" class="return">{$LANG.basket.coupon_add}</label><input name="coupon" id="coupon" type="text" class="textbox" maxlength="25" /></span>
  	  {foreach from=$COUPONS item=coupon}
	  <p><span class="price">{$coupon.value}</span><a href="{$VAL_SELF}&amp;remove_code={$coupon.remove_code}" title="{$LANG.common.remove}">{$coupon.voucher}</a></p>
	  {/foreach}
	  {if isset($DISCOUNT)}
	  <p><span class="price">{$DISCOUNT}</span>{$LANG.basket.total_discount}</p>
	  {/if}

	</div>
	{if $BASKET_WEIGHT}<p><strong>{$LANG.basket.weight}:</strong> {$BASKET_WEIGHT}</p>{/if}
	{if $CUSTOMER_LOCALE.description}<p>{$CUSTOMER_LOCALE.mark} {$LANG.basket.unconfirmed_locale}{/if}
	<div class="total">
	  <span class="price">{$TOTAL}</span> {$LANG.basket.total_grand}
	</div>

	<div class="basket_actions">
<script uframeid="105">
var ut = '<a href="{$STORE_URL}/index.php?_a=basket&amp;empty-basket=true" class="button_submit left">{$LANG.basket.basket_empty}</a>	  <input type="submit" name="update" class="button_submit update" value="{$LANG.basket.basket_update}" />	  {if $DISABLE_CHECKOUT_BUTTON!==true}      <input type="submit" name="proceed" class="button_submit" value="{$CHECKOUT_BUTTON}" />      {/if}';
document.write(ut);
      </script>
	</div>
</form>

  {if $CHECKOUTS}
  <p class="alternate_checkout">-- {$LANG.common.or} --</p>
  {foreach from=$CHECKOUTS item=checkout}
  <p class="alternate_checkout">{$checkout}</p>
  {/foreach}
  {/if}

  {if $RELATED}
  <div>
	<h2>{$LANG.catalogue.related_products}</h2>
	{foreach from=$RELATED item=product}
	<div class="latest_product">
	  <p class="image">
		<a href="{$product.url}" title="{$product.name}">
		  <img src="{$product.img_src}" alt="{$product.name}" />
		</a>
	  </p>
	  <p class="title"><a href="{$product.url}" title="{$product.name}">{$product.name}</a></p>
	</div>
	 {/foreach}
  </div>
	  {/if}
  {else}
  <p>{$LANG.basket.basket_is_empty}</p>
  {/if}
</div>
