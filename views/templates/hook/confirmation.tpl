{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if (isset($status) == true) && ($status == 'ok')}
<p class="alert alert-info">{l s='Command complete, waiting for UTRUST payment' mod='UTRUST'}</p>
<div class="box">
	- {l s='Amount' mod='UTRUST'} : <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
	<br />- {l s='Reference' mod='UTRUST'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='A confirmation e-mail will be send to you when UTRUST will validate the payment.' mod='UTRUST'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='UTRUST'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='UTRUST'}</a>
</div>
{else if ($status == 'cancel')}
<p class="alert alert-info">{l s='Your order on %s has been canceled.' sprintf=$shop_name mod='UTRUST'}</p>
<div class="box">- {l s='Reference' mod='UTRUST'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='Your order will be canceled shortly.' mod='UTRUST'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='UTRUST'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='UTRUST'}</a>
</div>
{else}
<h3>{l s='Your order on %s has not been accepted.' sprintf=$shop_name mod='UTRUST'}</h3>
<p>
	<br />- {l s='Reference' mod='UTRUST'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='Please, try to order again.' mod='UTRUST'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='UTRUST'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='UTRUST'}</a>
</p>
{/if}
<hr />