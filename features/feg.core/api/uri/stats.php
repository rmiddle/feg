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
		echo "Postfix Queue: <b>";
		system("bash_mailq.sh");
		echo "</b>";
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}

	function showPostfixStatsAction() {
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

	function showFaxQueStatsAction() {
		echo "FaxQue: <b>";
		echo "FIXME";
		echo "</b><br>";
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