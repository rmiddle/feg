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
		@array_shift($stack); // display
		
		@$id = array_shift($stack);
		
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