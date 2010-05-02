<?php

class FegCustomerPage extends FegPageExtension {
	private $_TPL_PATH = '';
	const ID = 'core.page.customer';

	
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
		return new Model_Activity('activity.customer');
	}
	
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);

		$active_worker = FegApplication::getActiveWorker();
		$visit = FegApplication::getVisit();
		$response = DevblocksPlatform::getHttpResponse();
		$translate = DevblocksPlatform::getTranslationService();
		$url = DevblocksPlatform::getUrlService();

		$stack = $response->path;
		@array_shift($stack); // customer
		
		@$customer_id = array_shift($stack);
		@$customer = DAO_CustomerAccount::get($customer_id);
		if(empty($customer)) {
			echo "<H1>".$translate->_('customer.display.invalid_customer')."</H1>";
			return;
		}
		$tpl->assign('customer_id', $customer_id);
		
		// Tabs
		$tab_manifests = DevblocksPlatform::getExtensions('feg.customer.tab', false);
		$tpl->assign('tab_manifests', $tab_manifests);

		@$tab_selected = array_shift($stack);
		if(empty($tab_selected)) $tab_selected = 'property';
		$tpl->assign('tab_selected', $tab_selected);
		
		switch($tab_selected) {
			case 'property':
				@$tab_option = array_shift($stack);				
				break;
		}
		
		// ====== Who's Online
		$whos_online = DAO_Worker::getAllOnline();
		if(!empty($whos_online)) {
			$tpl->assign('whos_online', $whos_online);
			$tpl->assign('whos_online_count', count($whos_online));
		}
		
		$tpl->display('file:' . $this->_TPL_PATH . 'customer/index.tpl');
	}
	
	// Ajax
	function showTabAction() {
		@$ext_id = DevblocksPlatform::importGPC($_REQUEST['ext_id'],'string','');
		
		if(null != ($tab_mft = DevblocksPlatform::getExtension($ext_id)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_CustomerTab) {
			$inst->showTab();
		}
	}
	
	// Post
	function saveTabAction() {
		@$ext_id = DevblocksPlatform::importGPC($_REQUEST['ext_id'],'string','');
		
		if(null != ($tab_mft = DevblocksPlatform::getExtension($ext_id)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_CustomerTab) {
			$inst->saveTab();
		}
	}
	
	/*
	 * [TODO] Proxy any func requests to be handled by the tab directly, 
	 * instead of forcing tabs to implement controllers.  This should check 
	 * for the *Action() functions just as a handleRequest would
	 */
	function handleTabActionAction() {
		@$tab = DevblocksPlatform::importGPC($_REQUEST['tab'],'string','');
		@$action = DevblocksPlatform::importGPC($_REQUEST['action'],'string','');

		if(null != ($tab_mft = DevblocksPlatform::getExtension($tab)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_CustomerTab) {
				if(method_exists($inst,$action.'Action')) {
					call_user_func(array(&$inst, $action.'Action'));
				}
		}
	}
	
};

class FegCustomerTabProperty extends Extension_CustomerTab {
	private $_TPL_PATH = '';

	function __construct($manifest) {
		$this->_TPL_PATH = dirname(dirname(dirname(__FILE__))) . '/templates/';
		$this->DevblocksExtension($manifest,1);
	}
 
	function showTab() {
		$translate = DevblocksPlatform::getTranslationService();
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		
		@$customer_id = DevblocksPlatform::importGPC($_REQUEST['customer_id'],'integer',0);
		$tpl->assign('customer_id', $customer_id);
		
		$core_tpl = $this->_TPL_PATH;
		$tpl->assign('core_tpl', $core_tpl);

		@$customer = DAO_CustomerAccount::get($customer_id);
		if(empty($customer)) {
			echo "<H1>".$translate->_('customer.display.invalid_customer')."</H1>";
			return;
		}
		$tpl->assign('customer', $customer);
		
		@$import_source = DAO_ImportSource::getAll();
		$tpl->assign('import_source', $import_source);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'customer/tabs/property/index.tpl');
	}

	function saveCustomerAccountAction() {
		@$customer_id = DevblocksPlatform::importGPC($_REQUEST['customer_id'],'integer',0);
		@$and_close = DevblocksPlatform::importGPC($_POST['and_close'],'integer',0);
		
		@$id = DevblocksPlatform::importGPC($_POST['id'],'integer');
		@$disabled = DevblocksPlatform::importGPC($_POST['account_is_disabled'],'integer',0);
		@$import_source = DevblocksPlatform::importGPC($_POST['customer_account_import_source'],'integer',0);
		
		@$account_number = DevblocksPlatform::importGPC($_REQUEST['customer_account_number'],'string','');
		@$account_name = DevblocksPlatform::importGPC($_REQUEST['customer_account_name'],'string','');
		
		$fields = array(
			DAO_CustomerAccount::IMPORT_SOURCE => $import_source,
			DAO_CustomerAccount::ACCOUNT_NAME => $account_name,
			DAO_CustomerAccount::ACCOUNT_NUMBER => $account_number,
			DAO_CustomerAccount::IS_DISABLED => $disabled,
		);
		// Update Customer Recipients 
		$status = DAO_CustomerAccount::update($customer_id, $fields);
		
		if($and_close) {
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('account')));
		} else {
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('customer', $customer_id,'property')));
		}
	}
};

class FegCustomerTabRecipient extends Extension_CustomerTab {
	private $_TPL_PATH = '';

	function __construct($manifest) {
		$this->_TPL_PATH = dirname(dirname(dirname(__FILE__))) . '/templates/';
		$this->DevblocksExtension($manifest,1);
	}
 
	function showTab() {
		@$customer_id = DevblocksPlatform::importGPC($_REQUEST['customer_id'],'integer',0);
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		
//  FIXME setup ACL check.
//		$worker = FegApplication::getActiveWorker();
//		if(!$worker || !$worker->is_superuser) {
//			echo $translate->_('common.access_denied');
//			return;
//		}
		
		$tpl->assign('customer_id', $customer_id);
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->name = 'Recipient List';
		$defaults->id = '_customer_view_recipient';
		$defaults->class_name = 'View_CustomerRecipient';
		$defaults->renderLimit = 15;
		
		$defaults->renderSortBy = SearchFields_CustomerRecipient::ID;
		$defaults->renderSortAsc = 1;

		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$view->params = array(
			SearchFields_CustomerRecipient::ACCOUNT_ID => new DevblocksSearchCriteria(SearchFields_CustomerRecipient::ACCOUNT_ID,'=',$customer_id),
		);
		$view->renderPage = 0;
		Feg_AbstractViewLoader::setView($view->id,$view);
		
		$tpl->assign('view', $view);
		$tpl->display('file:' . $this->_TPL_PATH . 'customer/tabs/recipient/index.tpl');
	}
	
};
class FegCustomerTabRecentMessages extends Extension_CustomerTab {
	private $_TPL_PATH = '';

	function __construct($manifest) {
		$this->_TPL_PATH = dirname(dirname(dirname(__FILE__))) . '/templates/';
		$this->DevblocksExtension($manifest,1);
	}
 
	function showTab() {
		@$customer_id = DevblocksPlatform::importGPC($_REQUEST['customer_id'],'integer',0);
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		
//  FIXME setup ACL check.
//		$worker = FegApplication::getActiveWorker();
//		if(!$worker || !$worker->is_superuser) {
//			echo $translate->_('common.access_denied');
//			return;
//		}
		
		$tpl->assign('customer_id', $customer_id);
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->name = 'Message Status List';
		$defaults->id = '_customer_view_messages';
		$defaults->class_name = 'View_MessageRecipient';
		$defaults->renderLimit = 15;
		
		$defaults->renderSortBy = SearchFields_MessageRecipient::ID;
		$defaults->renderSortAsc = 1;

		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$view->name = 'Message Status List';
		$view->params = array(
			SearchFields_MessageRecipient::ACCOUNT_ID => new DevblocksSearchCriteria(SearchFields_MessageRecipient::ACCOUNT_ID,'=',$customer_id),
		);
		$view->renderPage = 0;
		Feg_AbstractViewLoader::setView($view->id,$view);
		
		$tpl->assign('view', $view);
		$tpl->display('file:' . $this->_TPL_PATH . 'customer/tabs/recent/messages.tpl');
	}

	function saveTab() {
	}
};

