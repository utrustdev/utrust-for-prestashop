{*
 * UtrustPayments - A Sample Payment Module for PrestaShop 1.7
 *
 * Form to be displayed in the payment step
 *
 * @author Andresa Martins <contact@andresa.dev>
 * @license https://opensource.org/licenses/afl-3.0.php
 *}
<form method="post" action="{$action}">
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module">
                <span class="text-primary">{l s='You will be redirected to the Utrust payment widget compatible with any major crypto wallets. It will allow you to pay for your purchase in a safe and seamless way using Bitcoin, Ethereum, Tether or a number of other currencies.' mod='utrustpayments'}</span>
                <u><a class="utrust" href="https://utrust.com/" target="_blank">
                {l s='What is Utrust?' mod='utrustpayments'}
                </a></u>
            </p>
        </div>
    </div>
</form>