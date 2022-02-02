<?php
/**
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
 */

ini_set("xdebug.var_display_max_children", -1);
ini_set("xdebug.var_display_max_data", -1);
ini_set("xdebug.var_display_max_depth", -1);

if (!defined('_PS_VERSION_')) {
    exit;
}

class UTRUST extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();
    protected $config_form = false;
    static $api_sandbox = 'https://merchants.api.sandbox-utrust.com/api/';
    static $api_prod = 'https://merchants.api.utrust.com/api/';

    public function __construct()
    {
        $this->name = 'UTRUST';
        $this->tab = 'payments_gateways';
        $this->version = '1.3.0';
        $this->author = 'Utrust Team';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Utrust');
        $this->description = $this->l('Pay with major cryptocurrencies');

        $this->confirmUninstall = $this->l('');

        $this->limited_currencies = array(
          "USD",
          "EUR",
          "GBP",
          "ARS",
          "AUD",
          "BRL",
          "CAD",
          "CLP",
          "CNY",
          "CZK",
          "DKK",
          "DOP",
          "HKD",
          "HUF",
          "INR",
          "IDR",
          "ILS",
          "JPY",
          "KRW",
          "MYR",
          "MXN",
          "NZD",
          "NOK",
          "PKR",
          "PHP",
          "PLN",
          "RON",
          "RUB",
          "SGD",
          "ZAR",
          "SEK",
          "CHF",
          "TWD",
          "THB",
          "AED"
        );

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        // $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        // if (in_array($iso_code, $this->limited_countries) == false)
        // {
        //     $this->_errors[] = $this->l('This module is not available in your country');
        //     return false;
        // }

        include dirname(__FILE__) . '/sql/install.php';

        // create new order status Utrust
        if (!Configuration::hasKey('PS_OS_UTRUST')) {
            $values_to_insert = array(
                'invoice' => 0,
                'send_email' => 0,
                'module_name' => $this->name,
                'color' => '#4169E1',
                'unremovable' => 0,
                'hidden' => 0,
                'logable' => 0,
                'delivery' => 0,
                'shipped' => 0,
                'paid' => 0,
                'deleted' => 0);

            if (!Db::getInstance()->autoExecute(_DB_PREFIX_ . 'order_state', $values_to_insert, 'INSERT')) {
                return false;
            }

            $id_order_state = (int) Db::getInstance()->Insert_ID();

            $languages = Language::getLanguages(false);

            foreach ($languages as $language) {
                Db::getInstance()->autoExecute(_DB_PREFIX_ . 'order_state_lang', array('id_order_state' => $id_order_state, 'id_lang' => $language['id_lang'], 'name' => $this->l('Waiting for Utrust payment'), 'template' => ''), 'INSERT');
            }

            if (!@copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.png', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'os' . DIRECTORY_SEPARATOR . $id_order_state . '.gif')) {
                return false;
            }

            Configuration::updateValue('PS_OS_UTRUST', $id_order_state);

            unset($id_order_state);
        }

        Configuration::updateValue('UTRUST_MODE_API', false);

        return parent::install() &&
        $this->registerHook('header') &&
        $this->registerHook('backOfficeHeader') &&
        $this->registerHook('payment') &&
        $this->registerHook('paymentReturn') &&
        $this->registerHook('actionPaymentCCAdd') &&
        $this->registerHook('actionPaymentConfirmation') &&
        $this->registerHook('displayPayment') &&
        $this->registerHook('displayPaymentReturn');
    }

    public function uninstall()
    {
        Configuration::deleteByName('UTRUST_API_KEY');
        Configuration::deleteByName('UTRUST_WEBHOOK_SECRET');
        Configuration::deleteByName('UTRUST_MODE_API');

        include dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitUTRUSTModule')) == true) {
            $this->postValidation();
            if (!count($this->_postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }

        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        $this->_html .= $output . $this->renderForm();
        return $this->_html;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUTRUSTModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'UTRUST_MODE_API',
                        'is_bool' => true,
                        'desc' => $this->l('Disable if you want to use the Sandbox Utrust API to test the module. Enable to make it live mode.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-user"></i>',
                        'desc' => $this->l('Enter your Utrust Api Key'),
                        'name' => 'UTRUST_API_KEY',
                        'required' => true,
                        'label' => $this->l('Api Key'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter your Utrust Webhook Secret'),
                        'name' => 'UTRUST_WEBHOOK_SECRET',
                        'required' => true,
                        'label' => $this->l('Webhook Secret'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'UTRUST_MODE_API' => Configuration::get('UTRUST_MODE_API'),
            'UTRUST_API_KEY' => Configuration::get('UTRUST_API_KEY'),
            'UTRUST_WEBHOOK_SECRET' => Configuration::get('UTRUST_WEBHOOK_SECRET'),
        );
    }

    protected function postValidation()
    {
        if (!Tools::getValue('UTRUST_API_KEY')) {
            $this->_postErrors[] = $this->l('Please, enter your Utrust Api Key.');
        } elseif (!Tools::getValue('UTRUST_WEBHOOK_SECRET')) {
            $this->_postErrors[] = $this->l('Please, enter your Utrust Webhook Secret.');
        }

    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            echo Tools::getValue($key);
            Configuration::updateValue($key, Tools::getValue($key));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated.'));
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {

        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {

        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int) $currency_id);

        if ($currency->iso_code != null && in_array($currency->iso_code, $this->limited_currencies) == false) {
            return false;
        }

        $this->smarty->assign('module_dir', $this->_path);

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        if (Tools::isSubmit('cancel') == true && Tools::getValue('cancel') == 1) {
            $this->smarty->assign('status', 'cancel');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    public function hookActionPaymentCCAdd()
    {
        /* Place your code here. */
    }

    public function hookActionPaymentConfirmation()
    {
        /* Place your code here. */
    }

    public function hookDisplayPayment($params)
    {
        return $this->hookPayment($params);
    }

    public function hookDisplayPaymentReturn($params)
    {
        return $this->hookPaymentReturn($params);
    }
}
