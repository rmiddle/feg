<?php
class FegStatsPage extends FegPageExtension {
	private $_TPL_PATH = '';

	const VIEW_STATICS_PAGE = 'statics_page';
	
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
		return new Model_Activity('activity.stats');
	}
	
	function render() {
		$active_worker = FegApplication::getActiveWorker();
		$visit = FegApplication::getVisit();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);

		$response = DevblocksPlatform::getHttpResponse();
		$tpl->assign('request_path', implode('/',$response->path));
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->id = '_failed_messages';
		$defaults->class_name = 'View_Message';
		$defaults->renderLimit = 10;
		
		$defaults->renderSortBy = SearchFields_Message::ID;
		$defaults->renderSortAsc = 0;

		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$view->name = 'Failed Account Messages List';
		$view->renderLimit = 10;
		$view->renderTemplate = 'failed';
		$view->params = array(
			SearchFields_Message::ACCOUNT_ID => new DevblocksSearchCriteria(SearchFields_Message::ACCOUNT_ID,'=',0),
		);
		$view->renderPage = 0;
		Feg_AbstractViewLoader::setView($view->id,$view);
		
		if(!empty($view))
			$views[] = $view;

		$defaults = new Feg_AbstractViewModel();
		$defaults->id = '_failed_recipient';
		$defaults->class_name = 'View_MessageRecipient';
		$defaults->renderLimit = 10;
		
		$defaults->renderSortBy = SearchFields_MessageRecipient::ID;
		$defaults->renderSortAsc = 0;

		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$view->name = 'Failed Messages Recipient List';
		$view->renderLimit = 10;
		$view->renderTemplate = 'failed';
		$view->params = array(
			SearchFields_MessageRecipient::SEND_STATUS => new DevblocksSearchCriteria(SearchFields_MessageRecipient::SEND_STATUS,'=',1),
		);
		$view->renderPage = 0;
		Feg_AbstractViewLoader::setView($view->id,$view);
		
		if(!empty($view))
			$views[] = $view;

		// ====== Who's Online
		$whos_online = DAO_Worker::getAllOnline();
		if(!empty($whos_online)) {
			$tpl->assign('whos_online', $whos_online);
			$tpl->assign('whos_online_count', count($whos_online));
		}
		
		$tpl->assign('views', $views);
		$tpl->display('file:' . $this->_TPL_PATH . 'stats/index.tpl');
	}
	
	function showAccountFailurePeekAction() {
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$tpl->assign('id', $id);
		$tpl->assign('view_id', $view_id);
		
		$message = DAO_Message::get($id);
		$tpl->assign('message', $message);

		$message_lines = explode('\r\n',substr($message->message,1,-1));
		$tpl->assign('message_lines', $message_lines);
		
		// Custom Fields
		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_Message::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$custom_field_values = DAO_CustomFieldValue::getValuesBySourceIds(FegCustomFieldSource_Message::ID, $id);
		if(isset($custom_field_values[$id]))
			$tpl->assign('custom_field_values', $custom_field_values[$id]);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'stats/message/select_account.tpl');
	}
	
	function saveAccountFailurePeekAction() {
		$translate = DevblocksPlatform::getTranslationService();
		
		@$id = DevblocksPlatform::importGPC($_POST['id'],'integer');
		@$view_id = DevblocksPlatform::importGPC($_POST['view_id'],'string');

		@$message_account_id = DevblocksPlatform::importGPC($_POST['message_account_id'],'integer',0);
		
		$fields = array(
			DAO_Message::ACCOUNT_ID => $message_account_id,
		);
		
		$status = DAO_CustomerRecipient::update($id, $fields);
		
		if(!empty($view_id)) {
			$view = Feg_AbstractViewLoader::getView($view_id);
			$view->render();
		}
		
		//DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','workers')));		
	}
	
	function showMailQueueStatsAction() {
		echo "Email Queue: <b>";
		echo date("n:i:s A");
		echo "</b>";
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}

	function showMailStatsAction() {
		echo "Email Sent Last Hour: <b>";
		echo "FIXME";
		echo "</b><br>";
		echo "Email Sent Last Day: <b>";
		echo "FIXME";
		echo "</b><br>";
		echo "Email Sent Last Week: <b>";
		echo "FIXME";
		echo "</b><br>";
		echo "Email Sent Last Month: <b>";
		echo "FIXME";
		echo "</b><br>";
		echo "Email Sent Last Year: <b>";
		echo "FIXME";
		echo "</b><br>";
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}

	function showFaxQueAction() {
		echo "FaxQue: ";
		//echo "FIXME";
		exec("/usr/bin/faxstat -s", $results); 
		echo implode("<br>", $results);
		echo "<br>";
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}

	function showFaxStatsAction() {
		echo "Fax Sent Last Hour: <b>";
		echo "FIXME";
		echo "</b><br>";
		echo "Fax Sent Last Day: <b>";
		echo "FIXME";
		echo "</b><br>";
		echo "Fax Sent Last Week: <b>";
		echo "FIXME";
		echo "</b><br>";
		echo "Fax Sent Last Month: <b>";
		echo "FIXME";
		echo "</b><br>";
		echo "Fax Sent Last Year: <b>";
		echo "FIXME";
		echo "</b><br>";
		echo date("n:i:s A");
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}
	 
};