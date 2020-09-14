![Utrust integrations](https://user-images.githubusercontent.com/1558992/67495646-1e356b00-f673-11e9-8854-1beac877c586.png)

# Utrust for PrestaShop

**Demo Store:** https://prestashop.store.utrust.com/

Accept Bitcoin, Ethereum, Utrust Token and other cryptocurrencies directly on your store with the Utrust payment gateway for WooCommerce.
Utrust is cryptocurrency agnostic and provides fiat settlements.
The Utrust plugin extends WooCommerce allowing you to take cryptocurrency payments directly on your store via the Utrust API.
Find out more about Utrust at [utrust.com](https://utrust.com).

## Requirements

- Utrust Merchant account
- Online store using PrestaShop v1.6 (this module doesn't work on 1.7)

## Install and Update

### Installing

1. Download our latest release zip file on the [releases page](https://github.com/utrustdev/utrust-for-prestashop/releases).
2. Go to your PrestaShop admin dashboard (it should be something like https://<your-store.com>/admin).
3. Navigate to the _Modules and Services_ -> _Modules and Services_.
4. Click _Add a new module_ on top and upload the zip file.
5. After the module being uploaded, click _Install_ and _Proceed with Installation_
6. Once installed, follow the Setup instructions bellow.

### Updating

You can always check our [releases page](https://github.com/utrustdev/utrust-for-prestashop/releases) for a new version. To update just follow steps 2, 3 and 4 on the above install instructions.

## Setup

### On the Utrust side

1. Go to [Utrust merchant dashboard](https://merchants.utrust.com).
2. Log in or sign up if you didn't yet.
3. On the left sidebar choose _Organization_.
4. Click the button _Generate Credentials_.
5. You will see now your `Api Key` and `Webhook Secret`, save them somewhere safe temporarily.

   :warning: You will only be able to see the `Webhook Secret` once, after refreshing or changing page it will be no longer available to copy; if needed, you can always generate new credentials.

   :no_entry_sign: Don't share your credentials with anyone. They can use it to place orders **on your behalf**.

### On the PrestaShop side

1. Go to your PrestaShop admin dashboard.
2. Navigate to the _Modules and Services_ -> _Modules and Services_.
3. Search for _Utrust_ in the list and click _Configure_
4. Add your `Api Key` and `Webhook Secret` and click Save.

## Features

:sparkles: These are the features already implemented and planned for the Utrust for PrestaShop plugin:

- [x] Creates Order and redirects to Utrust payment widget
- [x] Receives and handles webhook payment received
- [x] Receives and handles webhook payment cancelled
- [ ] Starts automatic refund on Utrust when refund initiated in PrestaShop

## Support

Feel free to reach [by opening an issue on GitHub](https://github.com/utrustdev/utrust-for-prestashop/issues/new) if you need any help with the Utrust for PrestaShop plugin.

If you're having specific problems with your account, then please contact support@utrust.com.

In both cases, our team will be happy to help :purple_heart:.

## Contribute

This plugin was initially written by a third-party contractor ([Bleujour](https://www.bleujour.com/)/[Infopolis](https://www.infopolis.fr/)), and is now maintained by the Utrust development team.

We have now opened it to the world so that the community using this plugin may have the chance of shaping its development.

You can contribute by simply letting us know your suggestions or any problems that you find [by opening an issue on GitHub](https://github.com/utrustdev/utrust-for-prestashop/issues/new).

You can also fork the repository on GitHub and open a pull request for the `master` branch with your missing features and/or bug fixes.
Please make sure the new code follows the same style and conventions as already written code.
Our team is eager to welcome new contributors into the mix :blush:.

### Development

If you want to get your hands dirty and make your own changes to the Utrust for PrestaShop plugin, we recommend you to install it in a local PrestaShop store (either directly on your computer or using a virtual host) so you can make the changes in a controlled environment.
Alternatively, you can also do it in a PrestaShop online store that you have for testing/staging.

Once the plugin is installed in your store, the source code should be in `modules/UTRUST`. All the changes there should be reflected live in the store.
When you add a translation string like `l s='This is phrase' mod='UTRUST'` it should automatically appear in "Admin dashboard" -> "Localization" -> "Translations" -> "Modify Translations" (section) and choose the Type of translation "Installed modules translations" and the desired language. Search for Utrust and make the changes. Once you save, it will store the translations in Utrust module code (folder `translations`).
f something goes wrong, logs can be found in `/log`.

## Publishing

For now only members of the Utrust development team can publish new versions of the Utrust for PrestaShop plugin.

To publish a new version, simply follow [these instructions](https://github.com/utrustdev/utrust-for-prestashop/wiki/Publishing).

## License

The Utrust for PrestaShop plugin is maintained with :purple_heart: by the Utrust development team, and is available to the public under the GNU GPLv3 license. Please see [LICENSE](https://github.com/utrustdev/prestashop/blob/master/LICENSE) for further details.

&copy; Utrust 2019
