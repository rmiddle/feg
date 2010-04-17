<?php
class FegFailurePage extends FegPageExtension {
	private $_TPL_PATH = '';

	const VIEW_STATICS_PAGE = 'failure_page';
	
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
		return new Model_Activity('activity.failure');
	}
	
	function render() {
		$active_worker = FegApplication::getActiveWorker();
		$visit = FegApplication::getVisit();
		
//  FIXME setup ACL check.
//		$worker = FegApplication::getActiveWorker();
//		if(!$worker || !$worker->is_superuser) {
//			echo $translate->_('common.access_denied');
//			return;
//		}
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		$tpl->assign('path', $this->_TPL_PATH);

		$response = DevblocksPlatform::getHttpResponse();
		$tpl->assign('request_path', implode('/',$response->path));
		
		// ====== Who's Online
		$whos_online = DAO_Worker::getAllOnline();
		if(!empty($whos_online)) {
			$tpl->assign('whos_online', $whos_online);
			$tpl->assign('whos_online_count', count($whos_online));
		}
		
		$tpl->assign('customer_id', $customer_id);
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->name = 'Failed Messages List';
		$defaults->id = '_failed_messages';
		$defaults->class_name = 'View_Message';
		$defaults->renderLimit = 15;
		
		$defaults->renderSortBy = SearchFields_Message::ID;
		$defaults->renderSortAsc = 0;

		$viewMes = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$viewMes->renderTemplate = 'failed';
		$viewMes->params = array(
			SearchFields_Message::ACCOUNT_ID => new DevblocksSearchCriteria(SearchFields_Message::ACCOUNT_ID,'=',0),
		);
		$viewMes->renderPage = 0;
		Feg_AbstractViewLoader::setView($viewMes->id,$viewMes);
		
		$tpl->assign('viewMes', $viewMes);
		$tpl->display('file:' . $this->_TPL_PATH . 'failure/index.tpl');
	}
		 
};