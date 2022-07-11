{*
 * PrestaPay - A Sample Payment Module for PrestaShop 1.7
 *
 * HTML to be displayed in the order confirmation page
 *
 * @author Andresa Martins <contact@andresa.dev>
 * @license https://opensource.org/licenses/afl-3.0.php
 *}

{if (isset($status) == true) && ($status == 'ok')}
<p class="alert alert-info">{l s='Command complete, waiting for UTRUST payment' mod='utrustpayments'}</p>
<div class="box">
	- {l s='Amount' mod='utrustpayments'} : <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
	<br />- {l s='Reference' mod='utrustpayments'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='A confirmation e-mail will be send to you when UTRUST will validate the payment.' mod='utrustpayments'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='utrustpayments'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='utrustpayments'}</a>
</div>
{else if ($status == 'cancel')}
<p class="alert alert-info">{l s='Your order on %s has been cancelled.' sprintf=[$shop.name] mod='utrustpayments'}</p>
<div class="box">- {l s='Reference' mod='utrustpayments'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='Your order will be cancelled shortly.' mod='utrustpayments'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='utrustpayments'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='utrustpayments'}</a>
</div>
{else}
<h3>{l s='Your order on %s has not been accepted.' sprintf=[$shop.name] mod='utrustpayments'}</h3>
<p>
	<br />- {l s='Reference' mod='utrustpayments'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='Please, try to order again.' mod='utrustpayments'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='utrustpayments'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='utrustpayments'}</a>
</p>
{/if}
<hr />