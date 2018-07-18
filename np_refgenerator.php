<?php
/*
 * @author Evanggelos L. Goritsas <vgoritsas@gmail.com> Nextpointer Team.
 * @copyright  2018
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Np_RefGenerator extends Module
{

    protected  $html='';

    public static $executed = false;
	
	
    public function __construct()
    {
        $this->name = 'np_refgenerator';
        $this->version = '1.0.0';
        $this->author = 'Evanggelos L. Goritsas';
        $this->need_instance = 0;

        $this->bootstrap = true;


        $this->displayName = $this->l('Ref generator', array(), 'Modules.NpRefGenerator.Admin');
        $this->description = $this->l('Ref generator', array(), 'Modules.NpRefGenerator.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
        parent::__construct();
    }

    public function getContent(){

        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('ACTIVE', Tools::getValue('ACTIVE'));
            Configuration::updateValue('ADD_PREFIX', Tools::getValue('ADD_PREFIX'));
            Configuration::updateValue('PREFIX', Tools::getValue('PREFIX'));
            Configuration::updateValue('ADD_DASH', Tools::getValue('ADD_DASH'));
            Configuration::updateValue('ADD_PRODUCT_ID', Tools::getValue('ADD_PRODUCT_ID'));


            Configuration::updateValue('LENGTH', Tools::getValue('LENGTH'));


            Configuration::updateValue('ADD_DATE_WHEN_YOU_HAVE_ADD_PRODUCT', Tools::getValue('ADD_DATE_WHEN_YOU_HAVE_ADD_PRODUCT'));



            $this->_html .= $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
        }

        $this->_html .= $this->_adminConfigForm();
        return $this->_html;
    }

    public function _adminConfigForm(){
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable: '),
                        'name' => 'ACTIVE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Add Dash '),
                        'name' => 'ADD_DASH',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Add product id '),
                        'name' => 'ADD_PRODUCT_ID',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Add Prefix: '),
                        'name' => 'ADD_PREFIX',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),



                    array(
                        'type' => 'text',
                        'label' => $this->l('Prefix: '),
                        'name' => 'PREFIX',
                    ),

                    array(
                        'type' => 'text',
                        'label' => $this->l('Length: '),
                        'name' => 'LENGTH',
                        'desc' => $this->l('enter a number')
                    ),



                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array($fields_form));
    }





    public function getConfigFieldsValues(){
        return array(

            'ACTIVE' => Tools::getValue('ACTIVE', Configuration::get('ACTIVE')),
            'PREFIX' => Tools::getValue('PREFIX', Configuration::get('PREFIX')),
            'LENGTH' => Tools::getValue('LENGTH', Configuration::get('LENGTH')),
            'ADD_PREFIX' => Tools::getValue('ADD_PREFIX', Configuration::get('ADD_PREFIX')),
            'ADD_PRODUCT_ID' =>  Tools::getValue('ADD_PRODUCT_ID', Configuration::get('ADD_PRODUCT_ID')),
            'ADD_DASH' => Tools::getValue('ADD_DASH', Configuration::get('ADD_DASH')),
            'ADD_DATE_WHEN_YOU_HAVE_ADD_PRODUCT' => Tools::getValue('ADD_DATE_WHEN_YOU_HAVE_ADD_PRODUCT', Configuration::get('ADD_DATE_WHEN_YOU_HAVE_ADD_PRODUCT')),
        );
    }
  
	public function install(){
        return parent::install() &&
        $this->registerHook('actionProductSave');
	}

	public function uninstall()
	{
		return parent::uninstall();
	}

    public function hookActionProductSave($params){
            if(Configuration::get('ACTIVE') == 1) {
                $executed = self::$executed;
                if($executed == true){
                    return;
                }

                self::$executed = true;
                $product        = $params['product'];


                $output ='';
                if(Configuration::get('ADD_PREFIX')){
                    $output.= Configuration::get('PREFIX');
                }

                if(Configuration::get('ADD_DASH')){
                    $output.= '-';
                }

                if (Configuration::get('ADD_PRODUCT_ID')){
                    $output.= $product->id;
                }

                $uniqid = uniqid();

                $rand_start = rand(1,5);
                $output .= substr($uniqid,$rand_start,Configuration::get('LENGTH'));






                if(empty($product->reference)){
                    $prefix = Configuration::get('PREFIX');
                    $product->reference = $output;
                    $product->save();
                }

            }
    }



   

}