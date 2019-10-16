# Utrust for PrestaShop

**Demo Store:** https://prestashop.store.utrust.com/

Accept Bitcoin, Ethereum, Utrust Token and other cryptocurrencies directly on your store with the Utrust payment gateway for WooCommerce.
Utrust is cryptocurrency agnostic and provides fiat settlements.
The Utrust plugin extends WooCommerce allowing you to take cryptocurrency payments directly on your store via Utrust’s API.
Find out more about Utrust in [utrust.com](https://utrust.com).

## Requirements

- Utrust Merchant account
- Online store using PrestaShop v1.6 (this module doesn't work on 1.7)

## Install and Update

### Installing

https://github.com/utrustdev/utrust-for-prestashop/releases

1. Download our latest release zip file on the [releases page](https://github.com/utrustdev/utrust-for-prestashop/releases).
2. Go to your PrestaShop admin dashboard (it should be something like https://<your-store.com>/admin).
3. Navigate to the "Modules and Services" -> "Modules and Services".
4. Click "Add a new module" on top and upload the zip file.
5. After the module being uploaded, click "Install" and "Proceed with Installation"
6. Once installed, follow the Setup instructions bellow.

### Updating

You can always check our [releases page](https://github.com/utrustdev/utrust-for-prestashop/releases) for a new version. To update just follow steps 2, 3 and 4 on the above install instructions.

## Setup

### On Utrust side

1. Go to [Utrust merchant dashboard](https://merchants.utrust.com).
2. Log in or sign up if you didn't yet.
3. On the left sidebar choose "Organization".
4. Click the button "Generate Credentials".
5. You will see now your `Client Id` and `Client Secret`, copy them – you will only be able to see the `Client Secret` once, after refreshing or changing page it will be no longer available to copy; if needed, you can always generate new credentials.

Note: It's important that you don't send your credentials to anyone otherwise they can use it to place orders _on your behalf_.

### On PrestaShop side

1. Go to your PrestaShop admin dashboard.
2. Navigate to the "Modules and Services" -> "Modules and Services".
3. Search for "Utrust" in the list and click "Configure"
4. Add your `Client Id` and `Client Secret` and click Save.

## Features

### Current

- Creates Order and redirects to Utrust payment widget
- Receives and handles webhook payment received
- Receives and handles webhook payment cancelled

## Support

You can create [issues](https://github.com/utrustdev/utrust-for-prestashop/issues) on our repository. In case of specific problems with your account, please contact support@utrust.com.

## Contributing

We commit all our new features directly into our GitHub repository. But you can also request or suggest new features or code changes yourself!

### Developing

If you want to change the code on our plugin, it's recommended to install it in a local PrestaShop store (using a virtual host) so you can make changes in a controlled environment. Alternatively, you can also do it in a PrestaShop online store that you have for testing/staging.
The source code is in `modules/UTRUST`. All the changes there should be reflected live in the store.
When you add a translation string like `l s='This is phrase' mod='UTRUST'` it should automatically appear in "Admin dashboard" -> "Localization" -> "Translations" -> "Modify Translations" (section) and choose the Type of translation "Installed modules translations" and the desired language. Search for Utrust and make the changes. Once you save, it will store the translations in Utrust module code (folder `translations`).
Check the system error logs on `/log`.

### Adding code to the repo

If you have a fix or a feature, submit a pull-request through GitHub against `master` branch. Please make sure the new code follows the same style and conventions as already written code.

# Publishing

If you are member of Utrust Devteam and want to publish a new version of the plugin follow [these instructions](https://github.com/utrustdev/utrust-for-prestashop/wiki/Publishing).

# License

MIT, check the LICENSE.md file for more info.
