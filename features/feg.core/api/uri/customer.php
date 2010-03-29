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
		if($customer_id == 0) {
			$c_id = array_shift(DAO_CustomerAccount::getWhere("account_number = '' and is_disabled = 1 "));
			
			$customer_id = $c_id->id; 
			$fields = array(
				DAO_CustomerAccount::IMPORT_FILTER => 0,
				DAO_CustomerAccount::ACCOUNT_NAME => "",
				DAO_CustomerAccount::ACCOUNT_NUMBER => "",
				DAO_CustomerAccount::IS_DISABLED => 1,
			);
			if(NULL == $customer_id) {
				// Create a new Customer Recipients 
				$customer_id = DAO_CustomerAccount::create($fields);
			} else {
				// Update Customer Recipients 
				DAO_CustomerRecipient::update($customer_id, $fields);
			}
		} else {
			@$customer = DAO_CustomerAccount::get($customer_id);
			if(empty($customer)) {
				echo "<H1>".$translate->_('customer.display.invalid_customer')."</H1>";
				return;
			}
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

		$tpl->assign('customer', $customer);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'customer/tabs/property.tpl');
	}

	function saveCustomerAccountAction() {
		@$customer_id = DevblocksPlatform::importGPC($_REQUEST['customer_id'],'integer',0);
		@$delete = DevblocksPlatform::importGPC($_POST['do_delete'],'integer',0);
		
		@$id = DevblocksPlatform::importGPC($_POST['id'],'integer');
		@$disabled = DevblocksPlatform::importGPC($_POST['account_is_disabled'],'integer',0);
		@$import_filter = DevblocksPlatform::importGPC($_POST['customer_account_import_filter'],'integer',0);
		
		@$account_number = DevblocksPlatform::importGPC($_REQUEST['customer_account_number'],'string','');
		@$account_name = DevblocksPlatform::importGPC($_REQUEST['customer_account_name'],'string','');
		
		if($delete) {
			return;
		}
		
		$fields = array(
			DAO_CustomerAccount::IMPORT_FILTER => 0,
			DAO_CustomerAccount::ACCOUNT_NAME => "Test Name",
			DAO_CustomerAccount::ACCOUNT_NUMBER => "Test Number",
			DAO_CustomerAccount::IS_DISABLED => 0,
		);
		// Update Customer Recipients 
		DAO_CustomerRecipient::update($customer_id, $fields);
		
        DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('stats')));
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
		$defaults->id = 'ticket_view_recipient';
		$defaults->class_name = 'View_CustomerRecipient';
		$defaults->renderLimit = 15;
		
		$defaults->renderSortBy = SearchFields_CustomerRecipient::ID;
		$defaults->renderSortAsc = 0;

		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$view->params = array(
			SearchFields_CustomerRecipient::ACCOUNT_ID => new DevblocksSearchCriteria(SearchFields_CustomerRecipient::ACCOUNT_ID,DevblocksSearchCriteria::OPER_EQ,$customer_id)
		);
		$view->renderPage = 0;
		Feg_AbstractViewLoader::setView($view->id,$view);
		
		$tpl->assign('view', $view);
		$tpl->display('file:' . $this->_TPL_PATH . 'customer/tabs/recipient/index.tpl');
	}

	function showRecipientPeekAction() {
		@$customer_id = DevblocksPlatform::importGPC($_REQUEST['customer_id'],'integer',0);
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$tpl->assign('view_id', $view_id);
		
		$worker = DAO_Worker::get($id);
		$tpl->assign('worker', $worker);
		
		// Custom Fields
		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_Worker::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$custom_field_values = DAO_CustomFieldValue::getValuesBySourceIds(FegCustomFieldSource_CustomerRecipient::ID, $id);
		if(isset($custom_field_values[$id]))
			$tpl->assign('custom_field_values', $custom_field_values[$id]);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'customer/tabs/recipient/peek.tpl');		
	}

	function saveRecipientPeekAction() {
		$translate = DevblocksPlatform::getTranslationService();
		$active_worker = FegApplication::getActiveWorker();
		
		// TODO add ACL to edit / create recipient.
		//if(!$active_worker || !$active_worker->is_superuser) {
		//	return;
		//}
		
		@$view_id = DevblocksPlatform::importGPC($_POST['view_id'],'string');
		@$delete = DevblocksPlatform::importGPC($_POST['do_delete'],'integer',0);
		
		@$id = DevblocksPlatform::importGPC($_POST['id'],'integer');
		@$customer_id = DevblocksPlatform::importGPC($_POST['customer_id'],'integer');
		@$disabled = DevblocksPlatform::importGPC($_POST['is_disabled'],'integer',0);
		@$customer_recipient_type = DevblocksPlatform::importGPC($_POST['customer_recipient_type'],'integer');
		@$customer_recipient_address = DevblocksPlatform::importGPC($_POST['customer_recipient_address'],'integer');
		@$customer_recipient_export_filter= DevblocksPlatform::importGPC($_POST['customer_recipient_export_filter'],'integer');
		
		if(!empty($id) && !empty($delete)) {
			//Delete reciepent.
			return;
		} 
		
		if(empty($id)) {
			// Update fields array
			$fields = array(
				DAO_CustomerRecipient::ID => $id,
				DAO_CustomerRecipient::ACCOUNT_ID => $customer_id,
				DAO_CustomerRecipient::EXPORT_FILTER => $customer_recipient_export_filter,
				DAO_CustomerRecipient::TYPE => $customer_recipient_type,
				DAO_CustomerRecipient::ADDRESS => $customer_recipient_address,
				DAO_CustomerRecipient::IS_DISABLED => $disabled,
			);
			// Update Customer Recipients 
			DAO_CustomerRecipient::update($id, $fields);
		} else {
			// Update fields array
			$fields = array(
				DAO_CustomerRecipient::EXPORT_FILTER => $customer_recipient_export_filter,
				DAO_CustomerRecipient::TYPE => $customer_recipient_type,
				DAO_CustomerRecipient::ADDRESS => $customer_recipient_address,
				DAO_CustomerRecipient::IS_DISABLED => $disabled,
			);
			// Create a new Customer Recipients 
			DAO_CustomerRecipient::create($fields);
		}
		// Custom field saves
		@$field_ids = DevblocksPlatform::importGPC($_POST['field_ids'], 'array', array());
		DAO_CustomFieldValue::handleFormPost(FegCustomFieldSource_Worker::ID, $id, $field_ids);
		if(!empty($view_id)) {
			$view = Feg_AbstractViewLoader::getView($view_id);
			$view->render();
		}
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

		$tpl->display('file:' . $this->_TPL_PATH . 'customer/tabs/recent/messages.tpl');
	}

	function saveTab() {
	}
};

