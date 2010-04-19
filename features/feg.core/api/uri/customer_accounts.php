<?php

class FegAccountPage extends FegPageExtension {
	private $_TPL_PATH = '';
	const ID = 'customer.page.account';

	
	function __construct($manifest) {
		$this->_TPL_PATH = dirname(dirname(dirname(__FILE__))) . '/templates/';
		parent::__construct($manifest);
	}
		
	function isVisible() {
		// check login
		$visit = FegApplication::getVisit();
		
		if(empty($visit)) {
			return false;
		} else {
			return true;
		}
	}

	function getActivity() {
		return new Model_Activity('activity.account');
	}
	
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$translate = DevblocksPlatform::getTranslationService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);
		$tpl->assign('core_tplpath', $core_tplpath);
		
		$tpl->assign('view_id', $view_id);
		
		$title = $translate->_('account.tab.account.title');
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->id = "customer_view_account";
		$defaults->class_name = 'View_CustomerAccount';
		
		$defaults->renderSortBy = SearchFields_CustomerAccount::ID;
		$defaults->renderSortAsc = 1;
		$defaults->name = $title;
		
		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$tpl->assign('view', $view);
		$tpl->assign('view_fields', View_CustomerAccount::getFields());
		$tpl->assign('view_searchable_fields', View_CustomerAccount::getSearchFields());
				
		$tpl->display('file:' . $this->_TPL_PATH . 'accounts/index.tpl');		
	}
	
	function createNewCustomerAction() {
		$active_worker = FegApplication::getActiveWorker();

		$fields = array(
			DAO_CustomerAccount::IMPORT_FILTER => 0,
			DAO_CustomerAccount::ACCOUNT_NAME => "",
			DAO_CustomerAccount::ACCOUNT_NUMBER => "",
			DAO_CustomerAccount::IS_DISABLED => 1,
		);
		// Create a new Customer Recipients 
		$customer_id = DAO_CustomerAccount::create($fields);

//		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('customer', $customer_id,'property')));
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('customer', $customer_id,'property')));
	}
		
};