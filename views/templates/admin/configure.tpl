{*
* 2007-2022 PrestaShop
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
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="alert alert-info">
    <img src="../modules/utrustpayments/views/img/logo.png" style="float:left; margin-right:15px;" width="60" height="60">
    <p><strong>{l s="This module allows cryptocurrencies payment on your shop with Utrust" mod='utrustpayments'}</strong></p>
    <p>{l s="If you don't have one already, create an account on " mod='utrustpayments'} <a href="https://merchants.utrust.com">merchants.utrust.com</a>.</p>
    <p>{l s="You can find the API Key and Webhook Secret code on your Utrust account." mod='utrustpayments'}</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('UTRUST_WEBHOOK_SECRET').type = 'password';
    }, false);
</script>
