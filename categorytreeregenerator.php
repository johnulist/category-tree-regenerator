<?php
/**
* The MIT License (MIT)
*
* Copyright (c) 2014 @fusillicode
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* @author    @fusillicode
* @copyright Copyright (c) 2014 @fusillicode
* @license   http://opensource.org/licenses/MIT
*/

if (!defined('_PS_VERSION_')) {
	exit();
}

class CategoryTreeRegenerator extends Module
{
	public function __construct()
	{
		$this->name = 'categorytreeregenerator';
		$this->displayName = $this->l('Category Tree Regenerator');
		$this->meta_title = $this->l('Category Tree Regenerator');
		$this->description = $this->l('Simply regenerate the category tree.');
		$this->tab = 'administration';
		$this->version = '1.0';
		$this->author = 'Gianluca Randazzo';
		$this->need_instance = 0;
		$this->bootstrap = true;
    $this->display = 'view';
		parent::__construct();
	}

	public function install()
	{
		return parent::install();
	}

	public function uninstall()
	{
		return parent::uninstall();
	}

	public function getContent()
  {
    return $this->postProcess().$this->renderForm();
  }

  public function postProcess()
  {
    if (Tools::isSubmit('regenerate')) {
      $this->regenerateCategoryTree();
      return $this->displayNotifications(array('error', 'confirmation'));
    }
    return '';
  }

  public function renderForm()
  {
    $current_back_office_language = $this->context->language->id;
    $fields_form = array_merge(
      $this->regenerationCategoryTreeForm($current_back_office_language)
    );
    $helper = new HelperForm();
    $helper->module = $this;
    $helper->table =  $this->table;
    $helper->identifier = $this->identifier;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    $helper->languages = Language::getLanguages();
    $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;
    $helper->toolbar_scroll = true;
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
      'save' => array(
        'desc' => $this->l('Save'),
        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
        '&token='.Tools::getAdminTokenLite('AdminModules'),
      ),
      'back' => array(
        'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
        'desc' => $this->l('Back to list')
      )
    );
    $helper->tpl_vars = array(
      'uri' => $this->getPathUri(),
      'languages' => $this->context->controller->getLanguages(),
      'id_language' => $current_back_office_language
    );
    return $helper->generateForm($fields_form);
  }

  private function regenerationCategoryTreeForm()
  {
    $fields_form = array();
    $fields_form[0]['form'] = array(
      'legend' => array(
        'title' => $this->l('Regenerate category tree'),
        'icon' => 'icon-cogs'
      ),
      'input' => array(
        array(
          'type' => 'label',
          'id' => 'description',
          'name' => 'description',
          'desc' => $this->l('Just click the button below to trigger the regeneration of the category tree.')
        )
      ),
      'submit' => array(
        'title' => $this->l('Regenerate'),
        'name' => 'regenerate'
      )
    );
    return $fields_form;
  }

  private function regenerateCategoryTree()
  {
    Category::regenerateEntireNtree();
    $this->_confirmations[] = $this->l('Category tree regenerated');
    return true;
  }

  private function displayNotifications($notification_types = array())
  {
    $output = '';
    foreach ($notification_types as $notification_type) {
      $notifications = "_{$notification_type}s";
      foreach ($this->$notifications as $notification) {
        $output .= call_user_func_array(
          array($this, 'display'.Tools::ucfirst($notification_type)),
          array($notification)
        );
      }
    }
    return $output;
  }

}
