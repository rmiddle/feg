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
		
		// ====== Who's Online
		$whos_online = DAO_Worker::getAllOnline();
		if(!empty($whos_online)) {
			$tpl->assign('whos_online', $whos_online);
			$tpl->assign('whos_online_count', count($whos_online));
		}
		
		$tpl->display('file:' . $this->_TPL_PATH . 'stats/index.tpl');
	}
	
	function showPostfixMailqStatsAction() {
		echo "Postfix Queue: ";
		system("bash_mailq.sh");
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}

	function showPostfixStatsAction() {
		echo "Email Sent Last Hour: ";
		echo "FIXME";
		echo "<br>";
		echo "Email Sent Last Day: ";
		echo "FIXME";
		echo "<br>";
		echo "Email Sent Last Week: ";
		echo "FIXME";
		echo "<br>";
		echo "Email Sent Last Month: ";
		echo "FIXME";
		echo "<br>";
		echo "Email Sent Last Year: ";
		echo "FIXME";
		echo "<br>";
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}

	function showFaxQueStatsAction() {
		echo "FaxQue: ";
		echo "FIXME";
		echo "<br>";
		echo date("n:i:s A");
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}

	function showFaxStatsAction() {
		echo "Fax Sent Last Hour: ";
		echo "FIXME";
		echo "<br>";
		echo "Fax Sent Last Day: ";
		echo "FIXME";
		echo "<br>";
		echo "Fax Sent Last Week: ";
		echo "FIXME";
		echo "<br>";
		echo "Fax Sent Last Month: ";
		echo "FIXME";
		echo "<br>";
		echo "Fax Sent Last Year: ";
		echo "FIXME";
		echo "<br>";
		echo date("n:i:s A");
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}
};