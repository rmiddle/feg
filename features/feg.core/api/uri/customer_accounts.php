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
				
		$tpl->display('file:' . $this->_TPL_PATH . 'account/accounts.tpl');		
	}
	
	function searchCustomerJsonAction() {
		$db = DevblocksPlatform::getDatabaseService();

		@$term = DevblocksPlatform::importGPC($_REQUEST['term'],'string','');
		
		$sql = sprintf("SELECT account_number ".
			"FROM customer_account ".
			"WHERE account_number like '%%%s%%' ".
			"AND is_disabled = 0 ".
			"LIMIT 0, 10 ",
			$term
		);
print_r($sql);
		
		$rs = $db->Execute($sql);
		
		// Loop though pending outbound emails.
		while($row = mysql_fetch_assoc($rs)) {
			$ret[] = $row['account_number'];
		}
		echo json_encode($ret);
	}
	
	function createNewCustomerAction() {
		$active_worker = FegApplication::getActiveWorker();
		@$account_number = DevblocksPlatform::importGPC($_REQUEST['account_name'],'string','');
		@$message_id = DevblocksPlatform::importGPC($_REQUEST['message_id'],'integer',0);

		if (!$active_worker->hasPriv('core.access.customer.create')) {
			return;
		}

		if(empty($account_number)) {
			$fields = array(
				DAO_CustomerAccount::IMPORT_SOURCE => 0,
				DAO_CustomerAccount::ACCOUNT_NAME => "",
				DAO_CustomerAccount::ACCOUNT_NUMBER => "",
				DAO_CustomerAccount::IS_DISABLED => 1,
			);
		} else {
			$fields = array(
				DAO_CustomerAccount::IMPORT_SOURCE => 0,
				DAO_CustomerAccount::ACCOUNT_NAME => "Customer # " . $account_number,
				DAO_CustomerAccount::ACCOUNT_NUMBER => $account_number,
				DAO_CustomerAccount::IS_DISABLED => 1,
			);
		}
		// Create a new Customer Recipients 
		$account_id = DAO_CustomerAccount::create($fields);
		if($message_id > 0) {
			ImportCron::importAccountReProcessMessage($message_id, $account_id);
		}

//		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('customer', $customer_id,'property')));
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('customer', $account_id,'property')));
	}
		
};