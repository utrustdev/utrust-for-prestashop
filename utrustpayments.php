<?php
/**
 * UtrustPayments - A Sample Payment Module for PrestaShop 1.7
 *
 * This file is the declaration of the module.
 *
 * @author Andresa Martins <contact@andresa.dev>
 * @license https://opensource.org/licenses/afl-3.0.php
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class UtrustPayments extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();

    public $address;
    static $api_sandbox = 'https://merchants.api.sandbox-utrust.com/api/';
    static $api_prod = 'https://merchants.api.utrust.com/api/';

    /**
     * UtrustPayments constructor.
     *
     * Set the information about this module
     */
    public function __construct()
    {
        $this->name                   = 'utrustpayments';
        $this->tab                    = 'payments_gateways';
        $this->version                = '1.0.0';
        $this->author                 = 'TBI';
        $this->controllers            = array('payment', 'validation');
        $this->currencies             = true;
        $this->currencies_mode        = 'checkbox';
        $this->bootstrap              = true;
        $this->displayName            = 'Utrust Payments';
        $this->displayNamePayment     = 'Utrust - Pay with Cryptocurrencies';
        $this->description            = 'Sample Payment module developed for learning purposes.';
        $this->confirmUninstall       = 'Are you sure you want to uninstall this module?';
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        parent::__construct();

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
    }

    /**
     * Install this module and register the following Hooks:
     *
     * @return bool
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        include dirname(__FILE__) . '/sql/install.php';

        // create new order status Utrust
        if (!Configuration::get('PS_OS_UTRUST')) {
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

            if (!Db::getInstance()->insert('order_state', $values_to_insert)) {
                return false;
            }

            $id_order_state = (int) Db::getInstance()->Insert_ID();

            $languages = Language::getLanguages(false);

            foreach ($languages as $language) {
                Db::getInstance()->insert('order_state_lang', array('id_order_state' => $id_order_state, 'id_lang' => $language['id_lang'], 'name' => $this->l('Waiting for Utrust payment'), 'template' => ''));
            }

            if (!@copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.png', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'os' . DIRECTORY_SEPARATOR . $id_order_state . '.gif')) {
                return false;
            }

            Configuration::updateValue('PS_OS_UTRUST', $id_order_state);

            unset($id_order_state);
        }

        Configuration::updateValue('UTRUST_MODE_API', false);
        
        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader') 
            && $this->registerHook('payment')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn')
            && $this->registerHook('displayPayment')
            && $this->registerHook('displayPaymentReturn');
    }

    /**
     * Uninstall this module and remove it from all hooks
     *
     * @return bool
     */
    public function uninstall()
    {
        Configuration::deleteByName('UTRUST_API_KEY');
        Configuration::deleteByName('UTRUST_WEBHOOK_SECRET');
        Configuration::deleteByName('UTRUST_MODE_API');

        include dirname(__FILE__) . '/sql/uninstall.php';
        return parent::uninstall();
    }

    /**
     * Returns a string containing the HTML necessary to
     * generate a configuration screen on the admin
     *
     * @return string
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

        $this->_html .= $output. $this->renderForm();
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
                        'label' => $this->l('Webhook Secret')
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
            Tools::getValue($key);
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
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * Display this module as a payment option during the checkout
     *
     * @param array $params
     * @return array|void
     */
    public function hookPaymentOptions($params)
    {
        /*
         * Verify if this module is active
         */
        if (!$this->active) {
            return;
        }

        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int) $currency_id);

        if ($currency->iso_code != null && in_array($currency->iso_code, $this->limited_currencies) == false) {
            return false;
        }

        /**
         * Form action URL. The form data will be sent to the
         * validation controller when the user finishes
         * the order process.
         */
        $formAction = $this->context->link->getModuleLink($this->name, 'redirect', array(), true);
        
        /**
         * Assign the url form action to the template var $action
         */
        $this->smarty->assign(['action' => $formAction]);
 
        /**
         *  Load form template to be displayed in the checkout step
         */
        $paymentForm = $this->fetch('module:utrustpayments/views/templates/hook/payment.tpl');
 
        /**
         * Create a PaymentOption object containing the necessary data
         * to display this module in the checkout
         */
        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption->setModuleName($this->displayName)
            ->setCallToActionText($this->displayNamePayment)
            ->setAction($formAction)
            ->setForm($paymentForm);
 
        $payment_options = array(
            $newOption
        );
 
        return $payment_options;
    }

    /**
     * Display a message in the paymentReturn hook
     * 
     * @param array $params
     * @return string
     */
    public function hookPaymentReturn($params)
    {
        /**
         * Verify if this module is enabled
         */
        if (!$this->active) {
            return;
        }

        $order = $params['order'];       
        
        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        if (Tools::isSubmit('cancel') == true && Tools::getValue('cancel') == 1) {
            $this->smarty->assign('status', 'cancel');

        }

        $currency = new Currency($params['order']->id_currency);
        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($order->getOrdersTotalPaid(), $currency, false),
        ));

        return $this->fetch('module:utrustpayments/views/templates/hook/confirmation.tpl');
    }


    public function hookDisplayPaymentReturn()
    {
        /* Place your code here. */
    }

}