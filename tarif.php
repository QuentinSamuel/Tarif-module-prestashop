<?php
/**
* 2007-2023 PrestaShop
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
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Tarif extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'tarif';
        $this->tab = 'pricing_promotion';
        $this->version = '1.0.0';
        $this->author = 'Quentin Samuel';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Affichage des prix TTC et HT');
        $this->description = $this->l('Permet d\'afficher le tarif en ht ou en ttc ');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     Don't forget to create update methods if needed:
     http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    

     public function install()
{
return parent::install() && $this->registerHook('displayProductPriceBlock');
}

public function uninstall()
{
return parent::uninstall();
}

/**

    Load the configuration form
    /
    public function getContent()
    {
    /*
        If values have been submitted in the form, process.
        */

        if (((bool)Tools::isSubmit('submitTarifModule')) == true) {
        $this->postProcess();
        }

    $this->context->smarty->assign('module_dir', $this->_path);

    $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

    return $output.$this->renderForm();
    }

/**

    Create the form that will be displayed in the configuration of your module.
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
    $helper->submit_action = 'submitTarifModule';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
    .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');

    $options = array(
    array(
    'id_option' => '0',
    'name' => $this->l('Afficher le prix HT')
    ),
    array(
    'id_option' => '1',
    'name' => $this->l('Afficher le prix TTC')
    ),
    array(
    'id_option' => '2',
    'name' => $this->l('Afficher les deux tarifs')
    )
    );

    $helper->tpl_vars = array(
    'fields_value' => array(
    'TARIF_DISPLAY_TYPE' => Configuration::get('TARIF_DISPLAY_TYPE')
    ), /* Add values for your inputs */
    'languages' => $this->context->controller->getLanguages(),
    'id_language' => $this->context->language->id,
    );

    return $helper->generateForm(array(
    array(
    'form' => array(
    'legend' => array(
    'title' => $this->l('Settings'),
    'icon' => 'icon-cogs',
    ),
    'input' => array(
    array(
    'type' => 'select',
    'label' => $this->l('Type d\affichage des tarifs'),
    'name' => 'TARIF_DISPLAY_TYPE',
    'options' => array(
    'query' => $options,
    'id' => 'id_option',
    'name' => 'name'
    )
    )
    ),
    'submit' => array(
    'title' => $this->l('Save'),
    )
    )
    )));
    }
    
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
'type' => 'select',
'label' => $this->l('Affichage des tarifs'),
'name' => 'TARIF_DISPLAY',
'options' => array(
'query' => $options,
'id' => 'id_option',
'name' => 'name'
)
)
),
'submit' => array(
'title' => $this->l('Save'),
),
),
);
}

/**

    Set values for the inputs.
    */
    protected function getConfigFormValues()
    {
    return array(
    'TARIF_DISPLAY' => Configuration::get('TARIF_DISPLAY', '0'),
    );
    }

/**

    Save form data.
    */
    protected function postProcess()
    {
    $form_values = $this->getConfigFormValues();

    foreach (array_keys($form_values) as $key) {
    Configuration::updateValue($key, Tools::getValue($key));
    }
    }

/**

    Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
    if (Tools::getValue('module_name') == $this->name) {
    $this->context->controller->addJS($this->_path.'views/js/back.js');
    $this->context->controller->addCSS($this->_path.'views/css/back.css');
    }
    }

/**

    Add the CSS & JavaScript files you want to be added on the FO.
    */
    public function hookHeader()
    {
    $this->context->controller->addJS($this->_path.'/views/js/front.js');
    $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

/**

    This method is used to render the price block for a product

    @param array $params Hook parameters

    @return string HTML output
    */
    public function hookDisplayProductPriceBlock($params)
    {
        $product = $params['product'];
        $tax_enabled = Product::getTaxCalculationMethod((int)$this->context->customer->id) != PS_TAX_EXC;
    
        if (Configuration::get('TARIF_TYPE') == '0') {
            $price = $product['price'];
        } elseif (Configuration::get('TARIF_TYPE') == '1') {
            $price = $tax_enabled ? $product['price_tax_exc'] : $product['price'];
        } else {
            $price = $product['price'];
            $price_tax_exc = $product['price_tax_exc'];
        }
    
        $this->context->smarty->assign(array(
            'price' => $price,
            'price_tax_exc' => isset($price_tax_exc) ? $price_tax_exc : null,
            'tax_enabled' => $tax_enabled,
        ));
    
        return $this->display(__FILE__, 'productpriceblock.tpl');
    }
    
        
            Set values for the inputs.
            */
            protected function getConfigFormValues()
            {
            return array(
            'TARIF_TYPE' => Configuration::get('TARIF_TYPE', '2'),
            );
            }
        
        /**
        
            Save form data.
            */
            protected function postProcess()
            {
            $form_values = $this->getConfigFormValues();
        
            foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
            }
            }
        
            
            