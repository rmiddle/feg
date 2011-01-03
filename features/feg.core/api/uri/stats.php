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
	
	function showFailedMessageAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);

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

		$tpl->assign('view', $view);
		$tpl->display('file:' . $this->_TPL_PATH . 'stats/view_failed_messages.tpl');
	}
	
	function showFailedRecipientAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);

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
		
		$tpl->assign('view', $view);
		$tpl->display('file:' . $this->_TPL_PATH . 'stats/view_failed_recipient.tpl');
	}
	
	function showAccountFailurePeekAction() {
		$active_worker = FegApplication::getActiveWorker();
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');

		$tpl->assign('id', $id);
		$tpl->assign('view_id', $view_id);
		
		$message = DAO_Message::get($id);
		$tpl->assign('message', $message);
		
		$message_lines = explode('\r\n',substr($message->message,1,-1));
		$tpl->assign('message_lines', $message_lines);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'stats/message/failed_account.tpl');
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
		
	function showMessageRecipientFailurePeekAction() {
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$tpl->assign('id', $id);
		$tpl->assign('view_id', $view_id);
		
		$message_recipient = DAO_MessageRecipient::get($id);
		$tpl->assign('message_recipient', $message_recipient);

		$message = DAO_Message::get($message_recipient->message_id);
		$tpl->assign('message', $message);

		$message_lines = explode('\r\n',substr($message->message,1,-1));
		$tpl->assign('message_lines', $message_lines);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'stats/message_recipient/failed_recipient.tpl');
	}
	
	function saveMessageRecipientFailurePeekAction() {
		@$id = DevblocksPlatform::importGPC($_POST['id'],'integer');
		@$view_id = DevblocksPlatform::importGPC($_POST['view_id'],'string');

		@$retry = DevblocksPlatform::importGPC($_POST['retry'],'integer',0);
		
		$fields = array(
			DAO_MessageRecipient::SEND_STATUS => $retry,
		);
		
		$status = DAO_MessageRecipient::update($id, $fields);
		
		if(!empty($view_id)) {
			$view = Feg_AbstractViewLoader::getView($view_id);
			$view->render();
		}
		
		//DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','workers')));		
	}
		
	function showMailQueueStatsAction() {
		$db = DevblocksPlatform::getDatabaseService();
		echo "Email(s) In Queue: <b>";
		$sql = sprintf("SELECT count(*) as total ".
				"FROM message_recipient mr ".
				"inner join customer_recipient cr on mr.recipient_id = cr.id ".
				"WHERE mr.send_status in (0,3,4,5) ".
				"AND cr.is_disabled = 0 ".
				"AND cr.type = 0 "
				);
		$rs = $db->Execute($sql);
		$row = mysql_fetch_assoc($rs);
		echo $row['total'];
		echo "</b><br>";
		mysql_free_result($rs);
	}

	function showMailStatsAction() {
		$db = DevblocksPlatform::getDatabaseService();
		$sql = sprintf("SELECT email_current_hour, email_last_hour, email_sent_today, email_sent_yesterday ".
				"FROM stats ".
				"WHERE stats.id = 0 "
				);
		$rs = $db->Execute($sql);
		$row = mysql_fetch_assoc($rs);
		echo "Email Sent This Hour: <b>";
		echo $row['email_current_hour'];
		echo "</b><br>";
		echo "Email Sent Last Hour: <b>";
		echo $row['email_last_hour'];
		echo "</b><br>";
		echo "Email Sent Today: <b>";
		echo $row['email_sent_today'];
		echo "</b><br>";
		echo "Email Sent Yesterday: <b>";
		echo $row['email_sent_yesterday'];
		echo "</b><br>";
	}


/*
 *		JobFmt: "%-3j %3i %1a %15o %40M %-12.12e %5P %5D %7z %.25s"
 */
	function showHylfaxQueAction() {
		$db = DevblocksPlatform::getDatabaseService();
		echo "FaxQue - Running: ";
		$sql = sprintf("SELECT sc.counter ".
				"FROM  stats_counters sc ".
				"WHERE sc.id = 0 "
				);
		$rs = $db->Execute($sql);
		$row = mysql_fetch_assoc($rs);
		$counter = $row['counter'];
		echo $counter;
		$counter++;
		if ($counter > 9) 
			$counter=0;
		$sql = sprintf("UPDATE stats_counters ".
				"SET counter = %u ".
				"WHERE id = 0 ",
				$counter
				);
		$db->Execute($sql);
		echo "<br>";
		exec(HYLAFAX_FAXSTATS, $output_current);
		array_shift($output_current); 		// HylaFAX scheduler on ...
		foreach ($output_current as $line) {
			if (preg_match("/^Modem /", $line)) {	// match "/^Modem/
				$arr = split(" ", $line);
				array_shift($arr);
				echo $arr[0]." ";
				//array_shift($arr);
				array_shift($arr);
				while($arr) {
					echo $arr[0]. " ";
					array_shift($arr);
				}
				echo "<br>";
				//echo implode("<br>", 	$arr[3]);
				array_shift($output_current);				// remove entry from array
			} else {
				break;
			}
		}
		echo "<br>";
		mysql_free_result($rs);
	}

	function showFaxQueAction() {
		$db = DevblocksPlatform::getDatabaseService();
		
		echo "Fax(s) Waiting to process: <b>";
		$sql = sprintf("SELECT count(*) as total ".
				"FROM message_recipient mr ".
				"inner join customer_recipient cr on mr.recipient_id = cr.id ".
				"WHERE mr.send_status in (0,3,4) ".
				"AND cr.is_disabled = 0 ".
				"AND cr.type = 1 "
				);
		$rs = $db->Execute($sql);
		$row = mysql_fetch_assoc($rs);
		echo $row['total'];
		echo "</b><br>";
		mysql_free_result($rs);
		
		echo "Fax(s) In Hylafax Queue: <b>";
		$sql = sprintf("SELECT count(*) as total ".
				"FROM message_recipient mr ".
				"inner join customer_recipient cr on mr.recipient_id = cr.id ".
				"WHERE cr.is_disabled = 0 ".
				"AND cr.is_disabled = 0 ".
				"AND cr.type = 1 ".
				"AND ((mr.send_status = 5) or (mr.send_status BETWEEN 100 AND 140))"
				);
		$rs = $db->Execute($sql);
		$row = mysql_fetch_assoc($rs);
		echo $row['total'];
		echo "</b><br>";
		mysql_free_result($rs);
	}

	function showFaxStatsAction() {
		$db = DevblocksPlatform::getDatabaseService();
		$sql = sprintf("SELECT fax_current_hour, fax_last_hour, fax_sent_today, fax_sent_yesterday ".
				"FROM stats ".
				"WHERE stats.id = 0 "
				);
		$rs = $db->Execute($sql);
		$row = mysql_fetch_assoc($rs);
		echo "Fax Sent This Hour: <b>";
		echo $row['fax_current_hour'];
		echo "</b><br>";
		echo "Fax Sent Last Hour: <b>";
		echo $row['fax_last_hour'];
		echo "</b><br>";
		echo "Fax Sent Today: <b>";
		echo $row['fax_sent_today'];
		echo "</b><br>";
		echo "Fax Sent Yesterday: <b>";
		echo $row['fax_sent_yesterday'];
		echo "</b><br>";
		//echo date("n:i:s A");
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}
	
	function showSNPPQueueStatsAction() {
		$db = DevblocksPlatform::getDatabaseService();
		echo "Page(s) In Queue: <b>";
		$sql = sprintf("SELECT count(*) as total ".
				"FROM message_recipient mr ".
				"inner join customer_recipient cr on mr.recipient_id = cr.id ".
				"WHERE mr.send_status in (0,3,4,5) ".
				"AND cr.is_disabled = 0 ".
				"AND cr.type = 2 "
				);
		$rs = $db->Execute($sql);
		$row = mysql_fetch_assoc($rs);
		echo $row['total'];
		echo "</b><br>";
		mysql_free_result($rs);
	}
	
	function showSNPPStatsAction() {
		$db = DevblocksPlatform::getDatabaseService();
		$sql = sprintf("SELECT snpp_current_hour, snpp_last_hour, snpp_sent_today, snpp_sent_yesterday ".
				"FROM stats ".
				"WHERE stats.id = 0 "
				);
		$rs = $db->Execute($sql);
		$row = mysql_fetch_assoc($rs);
		echo "SNPP Sent This Hour: <b>";
		echo $row['snpp_current_hour'];
		echo "</b><br>";
		echo "SNPP Sent Last Hour: <b>";
		echo $row['snpp_last_hour'];
		echo "</b><br>";
		echo "SNPP Sent Today: <b>";
		echo $row['snpp_sent_today'];
		echo "</b><br>";
		echo "SNPP Sent Yesterday: <b>";
		echo $row['snpp_sent_yesterday'];
		echo "</b><br>";
		//echo date("n:i:s A");
		//$tpl->display('file:' . $this->_TPL_PATH . 'stats/postfix.tpl');
	}
	
};