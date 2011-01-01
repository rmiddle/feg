<?php

class MessageAuditLogEventListener extends DevblocksEventListenerExtension {
    function __construct($manifest) {
        parent::__construct($manifest);
    }

    /**
     * @param Model_DevblocksEvent $event
     */
    function handleEvent(Model_DevblocksEvent $event) {
		$translate = DevblocksPlatform::getTranslationService();
		
        switch($event->id) {
            case 'cron.maint':
            	DAO_MessageAuditLog::maint();
            	break;
            	
            case 'cron.import':
            	// Is a worker around to invoke this change?  0 = automatic
				/*
            	@$worker_id = (null != ($active_worker = FegApplication::getActiveWorker()) && !empty($active_worker->id))
            		? $active_worker->id
            		: 0;
	            		
           		$fields = array(
					DAO_MessageAuditLog::WORKER_ID => $worker_id,
					DAO_MessageAuditLog::ACCOUNT_ID => 0,
					DAO_MessageAuditLog::RECIPIENT_ID => 0,
					DAO_MessageAuditLog::MESSAGE_ID => 0,
					DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => 0,
					DAO_MessageAuditLog::CHANGE_DATE => time(),
					DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cron.import',
					DAO_MessageAuditLog::CHANGE_VALUE => 'Cron Importer Ran',
           		);
				$log_id = DAO_MessageAuditLog::create($fields);
				*/
            	break;
            	
            case 'cron.reprocessing.accounts':
            	@$account_id = $event->params['account_id'];
            	@$message_id = $event->params['message_id'];
            	@$import_source = $event->params['import_source'];
				
				if ($account_id == 0)
					break;

            	// Is a worker around to invoke this change?  0 = automatic
            	@$worker_id = (null != ($active_worker = FegApplication::getActiveWorker()) && !empty($active_worker->id))
            		? $active_worker->id
            		: 0;
				
          		$fields = array(
          			DAO_MessageAuditLog::WORKER_ID => $worker_id,
          			DAO_MessageAuditLog::ACCOUNT_ID => $account_id,
          			DAO_MessageAuditLog::RECIPIENT_ID => 0,
          			DAO_MessageAuditLog::MESSAGE_ID => $message_id,
          			DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => 0,
           			DAO_MessageAuditLog::CHANGE_DATE => time(),
           			DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cf.message.status',
           			DAO_MessageAuditLog::CHANGE_VALUE => sprintf("Message ID %d assocated to Account ID %d", $message_id, $account_id),
          		);
            	$log_id = DAO_MessageAuditLog::create($fields);
            	break;
            case 'cron.export':
            	// Is a worker around to invoke this change?  0 = automatic
            	@$worker_id = (null != ($active_worker = FegApplication::getActiveWorker()) && !empty($active_worker->id))
            		? $active_worker->id
            		: 0;
	            		
           		$fields = array(
					DAO_MessageAuditLog::WORKER_ID => $worker_id,
					DAO_MessageAuditLog::ACCOUNT_ID => 0,
					DAO_MessageAuditLog::RECIPIENT_ID => 0,
					DAO_MessageAuditLog::MESSAGE_ID => 0,
					DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => 0,
					DAO_MessageAuditLog::CHANGE_DATE => time(),
					DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cron.export',
					DAO_MessageAuditLog::CHANGE_VALUE => 'Cron Exporter Ran',
           		);
				$log_id = DAO_MessageAuditLog::create($fields);
            	break;
            	
            case 'cron.send.email':
            	@$recipient = $event->params['recipient'];
            	@$message = $event->params['message'];
            	@$message_recipient = $event->params['message_recipient'];
            	@$message_text = $event->params['message_text'];
            	@$send_status = $event->params['send_status'];
				
          		$fields = array(
          			DAO_MessageAuditLog::WORKER_ID => 0,
          			DAO_MessageAuditLog::ACCOUNT_ID => $message_recipient->account_id,
          			DAO_MessageAuditLog::RECIPIENT_ID => $message_recipient->recipient_id,
          			DAO_MessageAuditLog::MESSAGE_ID => $message_recipient->message_id,
          			DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => $message_recipient->id,
           			DAO_MessageAuditLog::CHANGE_DATE => time(),
           			DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cs.message.email.send',
           			DAO_MessageAuditLog::CHANGE_VALUE => $send_status ? "Successful Sent" : "Failed to Send",
          		);
            	$log_id = DAO_MessageAuditLog::create($fields);
            	break;
            	
            case 'cron.queue.fax':
            	@$recipient = $event->params['recipient'];
            	@$message = $event->params['message'];
            	@$message_recipient = $event->params['message_recipient'];
            	@$message_text = $event->params['message_text'];
            	@$queue_status = $event->params['queue_status'];
            	@$fax_id = $event->params['fax_id'];
				
          		$fields = array(
          			DAO_MessageAuditLog::WORKER_ID => 0,
          			DAO_MessageAuditLog::ACCOUNT_ID => $message_recipient->account_id,
          			DAO_MessageAuditLog::RECIPIENT_ID => $message_recipient->recipient_id,
          			DAO_MessageAuditLog::MESSAGE_ID => $message_recipient->message_id,
          			DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => $message_recipient->id,
           			DAO_MessageAuditLog::CHANGE_DATE => time(),
           			DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cs.message.fax.queue',
           			DAO_MessageAuditLog::CHANGE_VALUE => $queue_status ? "Fax successful queue with ID: " . $fax_id : "Failed to Queue",
          		);
            	$log_id = DAO_MessageAuditLog::create($fields);
            	break;
            	
            case 'cron.send.snpp':
            	@$recipient = $event->params['recipient'];
            	@$message = $event->params['message'];
            	@$message_recipient = $event->params['message_recipient'];
            	@$message_text = $event->params['message_text'];
            	@$send_status = $event->params['send_status'];
				
          		$fields = array(
          			DAO_MessageAuditLog::WORKER_ID => 0,
          			DAO_MessageAuditLog::ACCOUNT_ID => $message_recipient->account_id,
          			DAO_MessageAuditLog::RECIPIENT_ID => $message_recipient->recipient_id,
          			DAO_MessageAuditLog::MESSAGE_ID => $message_recipient->message_id,
          			DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => $message_recipient->id,
           			DAO_MessageAuditLog::CHANGE_DATE => time(),
           			DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cs.message.snpp.send',
           			DAO_MessageAuditLog::CHANGE_VALUE => $send_status ? "Successful Sent" : "Failed to Send",
          		);
            	$log_id = DAO_MessageAuditLog::create($fields);
            	break;
				
            case 'dao.customer.account.update':
            	@$objects = $event->params['objects'];
            	
            	foreach($objects as $object_id => $object) {
            		$model = $object['model'];
            		$changes = $object['changes'];
            		
	            	// Filter out any changes we could care less about
					//unset($changes[DAO_CustomerAccount::IS_DISABLED]);
            		
	            	// Is a worker around to invoke this change?  0 = automatic
	            	@$worker_id = (null != ($active_worker = FegApplication::getActiveWorker()) && !empty($active_worker->id))
	            		? $active_worker->id
	            		: 0;
	            		
	            	if(!empty($changes))
	            	foreach($changes as $key => $change) {
	            		$value = $change['to'];
	            		
            			if(is_array($value))
							$value = implode("\r\n", $value);
						
	            		$fields = array(
							DAO_MessageAuditLog::WORKER_ID => $worker_id,
							DAO_MessageAuditLog::ACCOUNT_ID => $model['id'],
							DAO_MessageAuditLog::RECIPIENT_ID => 0,
							DAO_MessageAuditLog::MESSAGE_ID => 0,
							DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => 0,
							DAO_MessageAuditLog::CHANGE_DATE => time(),
							DAO_MessageAuditLog::CHANGE_FIELD => "auditlog.ca.".$key,
							DAO_MessageAuditLog::CHANGE_VALUE => substr($value,0,128),
	            		);
						$log_id = DAO_MessageAuditLog::create($fields);
	            	}
				}
           		break;
				
            case 'dao.customer.recipient.update':
            	@$objects = $event->params['objects'];
            	
            	foreach($objects as $object_id => $object) {
            		$model = $object['model'];
            		$changes = $object['changes'];
            		
	            	// Filter out any changes we could care less about
					//unset($changes[DAO_CustomerAccount::IS_DISABLED]);
            		
	            	// Is a worker around to invoke this change?  0 = automatic
	            	@$worker_id = (null != ($active_worker = FegApplication::getActiveWorker()) && !empty($active_worker->id))
	            		? $active_worker->id
	            		: 0;
	            		
	            	if(!empty($changes))
	            	foreach($changes as $key => $change) {
	            		$value = $change['to'];
	            		
            			if(is_array($value))
							$value = implode("\r\n", $value);
							
						if($key == 'cr.type') {
							$value = $translate->_('auditlog.cr.type_'.$value);
							if ($value == "") $value = $translate->_('auditlog.cr.type_unknown');							
						}
						
	            		$fields = array(
							DAO_MessageAuditLog::WORKER_ID => $worker_id,
							DAO_MessageAuditLog::ACCOUNT_ID => $model['account_id'],
							DAO_MessageAuditLog::RECIPIENT_ID => $model['id'],
							DAO_MessageAuditLog::MESSAGE_ID => 0,
							DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => 0,
							DAO_MessageAuditLog::CHANGE_DATE => time(),
							DAO_MessageAuditLog::CHANGE_FIELD => "auditlog.cr.".$key,
							DAO_MessageAuditLog::CHANGE_VALUE => substr($value,0,128),
	            		);
						$log_id = DAO_MessageAuditLog::create($fields);
	            	}
				}
           		break;
				
            case 'message.create':
            	@$account_id = $event->params['account_id'];
            	@$message_id = $event->params['message_id'];
            	@$message_text = $event->params['message_text'];

          		$fields = array(
          			DAO_MessageAuditLog::WORKER_ID => 0,
          			DAO_MessageAuditLog::ACCOUNT_ID => $account_id,
          			DAO_MessageAuditLog::RECIPIENT_ID => 0,
          			DAO_MessageAuditLog::MESSAGE_ID => $message_id,
          			DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => 0,
           			DAO_MessageAuditLog::CHANGE_DATE => time(),
           			DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cf.message.created',
           			DAO_MessageAuditLog::CHANGE_VALUE => sprintf("Message created for account %d",$account_id),
          		);
            	$log_id = DAO_MessageAuditLog::create($fields);
            	break;
				
            case 'message.recipient.create':
            	@$account_id = $event->params['account_id'];
            	@$recipient_id = $event->params['recipient_id'];
            	@$message_id = $event->params['message_id'];
            	@$message_recipient_id = $event->params['message_recipient_id'];
            	@$message_text = $event->params['message_text'];
				
				$cr_id = array_shift(DAO_CustomerRecipient::getWhere(sprintf("%s = %d",
					DAO_CustomerRecipient::ID,
					$recipient_id
				)));
				switch($cr_id->type) {
					case 0: // Email
						@$change_value = sprintf("Email Scheduled for %s \<%s\>",$address_to, $address);
						break;
					case 1: // Fax
						@$change_value = sprintf("Fax Scheduled for %s \<%s\>",$address_to, $address);
						break;
					case 2: // SNPP
						@$change_value = sprintf("Page Scheduled for %s \<%s\>",$address_to, $address);
						break;
					default:
						@$change_value = sprintf("Unknown Type %d",$cr_id->type);
						break;
				}

          		$fields = array(
          			DAO_MessageAuditLog::WORKER_ID => 0,
          			DAO_MessageAuditLog::ACCOUNT_ID => $account_id,
          			DAO_MessageAuditLog::RECIPIENT_ID => $recipient_id,
          			DAO_MessageAuditLog::MESSAGE_ID => $message_id,
          			DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => $message_recipient_id,
           			DAO_MessageAuditLog::CHANGE_DATE => time(),
           			DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cf.message.recipient.created',
           			DAO_MessageAuditLog::CHANGE_VALUE => $change_value,
          		);
            	$log_id = DAO_MessageAuditLog::create($fields);
            	break;
				
            case 'message.recipient.status':
            	@$message_recipient_id = $event->params['message_recipient_id'];
            	@$account_id = $event->params['account_id'];
            	@$recipient_id = $event->params['recipient_id'];
            	@$message_id = $event->params['message_id'];
            	@$send_status = $event->params['send_status'];
				
            	// Is a worker around to invoke this change?  0 = automatic
            	@$worker_id = (null != ($active_worker = FegApplication::getActiveWorker()) && !empty($active_worker->id))
            		? $active_worker->id
            		: 0;
				
				$status_text = $translate->_('feg.message_recipient.status_'.$send_status);
				if ($status_text == "") $status_text = $translate->_('feg.core.send_status.unknown');
          		$fields = array(
          			DAO_MessageAuditLog::WORKER_ID => $worker_id,
          			DAO_MessageAuditLog::ACCOUNT_ID => $account_id,
          			DAO_MessageAuditLog::RECIPIENT_ID => $recipient_id,
          			DAO_MessageAuditLog::MESSAGE_ID => $message_id,
          			DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => $message_recipient_id,
           			DAO_MessageAuditLog::CHANGE_DATE => time(),
           			DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cf.message.recipient.status',
           			DAO_MessageAuditLog::CHANGE_VALUE => $status_text,
          		);
            	$log_id = DAO_MessageAuditLog::create($fields);
            	break;
            case 'message.status':
            	@$account_id = $event->params['account_id'];
            	@$message_id = $event->params['message_id'];
            	@$import_status = $event->params['import_status'];
				
            	// Is a worker around to invoke this change?  0 = automatic
            	@$worker_id = (null != ($active_worker = FegApplication::getActiveWorker()) && !empty($active_worker->id))
            		? $active_worker->id
            		: 0;
				
				$status_text = $translate->_('feg.message.import_status_'.$import_status);
				if ($status_text == "") $status_text = $translate->_('feg.core.send_status.unknown');
          		$fields = array(
          			DAO_MessageAuditLog::WORKER_ID => $worker_id,
          			DAO_MessageAuditLog::ACCOUNT_ID => $account_id,
          			DAO_MessageAuditLog::RECIPIENT_ID => 0,
          			DAO_MessageAuditLog::MESSAGE_ID => $message_id,
          			DAO_MessageAuditLog::MESSAGE_RECIPIENT_ID => 0,
           			DAO_MessageAuditLog::CHANGE_DATE => time(),
           			DAO_MessageAuditLog::CHANGE_FIELD => 'auditlog.cf.message.status',
           			DAO_MessageAuditLog::CHANGE_VALUE => $status_text,
          		);
            	$log_id = DAO_MessageAuditLog::create($fields);
            	break;
        }
    }
};

class FegAuditLogPage extends FegPageExtension {
	private $_TPL_PATH = '';
	const ID = 'zz.auditlog.page';

	
	function __construct($manifest) {
		$this->_TPL_PATH = dirname(dirname(__FILE__)).'/templates';
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
		return new Model_Activity('activity.auditlog');
	}
	
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
 		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);
		$tpl->assign('core_tplpath', $core_tplpath);
		
		$tpl->assign('view_id', $view_id);
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->class_name = 'View_MessageAuditLog';
		$defaults->id = '_audit_log';
		$defaults->renderLimit = 25;
		
		$defaults->view_columns = array(
			SearchFields_MessageAuditLog::CHANGE_DATE,
		    SearchFields_MessageAuditLog::ACCOUNT_ID,
		    SearchFields_MessageAuditLog::RECIPIENT_ID,
		    SearchFields_MessageAuditLog::MESSAGE_ID,
		    SearchFields_MessageAuditLog::MESSAGE_RECIPIENT_ID,
			SearchFields_MessageAuditLog::WORKER_ID,
			SearchFields_MessageAuditLog::CHANGE_FIELD,
			SearchFields_MessageAuditLog::CHANGE_VALUE,
		);
		
		$defaults->renderSortBy = SearchFields_MessageAuditLog::CHANGE_DATE;
		$defaults->renderSortAsc = false;
		$defaults->params = array();
		$defaults->renderPage = 0;

		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);

		$view->name = 'Audit Log';
		$view->renderTemplate = 'default';
		$view->params = array(
			//SearchFields_MessageAuditLog::ACCOUNT_ID => new DevblocksSearchCriteria(SearchFields_MessageAuditLog::ACCOUNT_ID,DevblocksSearchCriteria::OPER_EQ,$customer_id)
		);
		$view->renderPage = 0;

		Feg_AbstractViewLoader::setView($view->id,$view);
		
		$tpl->assign('view', $view);
		$tpl->assign('view_fields', View_MessageAuditLog::getFields());
		$tpl->assign('view_searchable_fields', View_MessageAuditLog::getSearchFields());
				
		$tpl->display('file:' . $this->_TPL_PATH . '/display/index.tpl');		
	}
	
};

class CustomerAuditLogTab extends Extension_CustomerTab {
	private $tpl_path = null; 
	
    function __construct($manifest) {
        parent::__construct($manifest);
        $this->tpl_path = dirname(dirname(__FILE__)).'/templates';
    }
	
	function showTab() {
		$visit = FegApplication::getVisit(); /* @var $visit CerberusVisit */
		$translate = DevblocksPlatform::getTranslationService();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->tpl_path);
		
		@$customer_id = DevblocksPlatform::importGPC($_REQUEST['customer_id'],'integer',0);
		$tpl->assign('customer_id', $customer_id);

		$defaults = new Feg_AbstractViewModel();
		$defaults->class_name = 'View_MessageAuditLog';
		$defaults->id = 'customer_audit_log';
		$defaults->renderLimit = 15;
		
		$defaults->view_columns = array(
			SearchFields_MessageAuditLog::CHANGE_DATE,
		    //SearchFields_MessageAuditLog::ACCOUNT_ID,
		    SearchFields_MessageAuditLog::RECIPIENT_ID,
		    SearchFields_MessageAuditLog::MESSAGE_ID,
		    SearchFields_MessageAuditLog::MESSAGE_RECIPIENT_ID,
			SearchFields_MessageAuditLog::WORKER_ID,
			SearchFields_MessageAuditLog::CHANGE_FIELD,
			SearchFields_MessageAuditLog::CHANGE_VALUE,
		);
		
		$defaults->renderSortBy = SearchFields_MessageAuditLog::CHANGE_DATE;
		$defaults->renderSortAsc = false;
		$defaults->params = array();
		$defaults->renderPage = 0;

		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);

		$view->name = 'Customer Audit Log';
		$view->renderTemplate = 'default';
		$view->params = array(
			SearchFields_MessageAuditLog::ACCOUNT_ID => new DevblocksSearchCriteria(SearchFields_MessageAuditLog::ACCOUNT_ID,DevblocksSearchCriteria::OPER_EQ,$customer_id)
		);
		$view->renderPage = 0;

		Feg_AbstractViewLoader::setView($view->id,$view);
		
		$tpl->assign('view', $view);
		$tpl->display('file:' . $this->tpl_path . '/display/log/index.tpl');
	}
	
	function saveTab() {
		
	}
};

class DAO_MessageAuditLog extends DevblocksORMHelper {
	const ID = 'id';
	const WORKER_ID = 'worker_id';
	const ACCOUNT_ID = 'account_id';
	const RECIPIENT_ID = 'recipient_id';
	const MESSAGE_ID = 'message_id';
	const MESSAGE_RECIPIENT_ID = 'message_recipient_id';
	const CHANGE_DATE = 'change_date';
	const CHANGE_FIELD = 'change_field';
	const CHANGE_VALUE = 'change_value';
	
	public static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('audit_log_message_seq');
		
		$sql = sprintf("INSERT INTO audit_log_message (id, worker_id, account_id, recipient_id, message_id, message_recipient_id, change_date, change_field, change_value) ".
			"VALUES (%d,0,0,0,0,0,%d,'','')",
			$id,
			time()
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	/**
	 * @return Model_messageAuditLog[]
	 */
	public static function getWhere($where) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, worker_id, account_id, recipient_id, message_id, message_recipient_id, change_date, change_field, change_value ".
			"FROM audit_log_message ".
			(!empty($where)?sprintf("WHERE %s ",$where):" ").
			"ORDER BY id "
			;
		$rs = $db->Execute($sql);
		
		return self::_createObjectsFromResultSet($rs);
	}
	
	static private function _createObjectsFromResultSet($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
		    $object = new Model_MessageAuditLog();
		    $object->id = intval($row['id']);
		    $object->account_id = intval($row['account_id']);
		    $object->recipient_id = intval($row['recipient_id']);
		    $object->message_id = intval($row['message_id']);
		    $object->message_recipient_id = intval($row['message_recipient_id']);
		    $object->worker_id = intval($row['worker_id']);
		    $object->change_date = intval($row['change_date']);
		    $object->change_field = $row['change_field'];
		    $object->change_value = $row['change_value'];
		    $objects[$object->id] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}
			
	public static function update($ids, $fields) {
		parent::_update($ids, 'audit_log_message', $fields);
	}
	
	public static function updateWhere($fields, $where) {
		parent::_updateWhere('audit_log_message', $fields, $where);
	}
	
	/*
	 * Maint task to prevent the audit log from getting to large.  
	 */
	public static function maint() {
		$db = DevblocksPlatform::getDatabaseService();
		
		// 15 552 000 Seconds = 180 Days
		$sql = sprintf("DELETE QUICK audit_log_message FROM audit_log_message WHERE change_date < %d", (time()-15552000));
		$db->Execute($sql);
	}
	
	public static function delete($ids) {
		if(!is_array($ids)) $ids = array($ids);
		
		$db = DevblocksPlatform::getDatabaseService();
		$ids_list = implode(',', $ids);
		
		$db->Execute(sprintf("DELETE QUICK FROM audit_log_message WHERE id IN (%s)", $ids_list));
	}
	
    /**
     * Enter description here...
     *
     * @param DevblocksSearchCriteria[] $params
     * @param integer $limit
     * @param integer $page
     * @param string $sortBy
     * @param boolean $sortAsc
     * @param boolean $withCounts
     * @return array
     */
    static function search($params, $limit=10, $page=0, $sortBy=null, $sortAsc=null, $withCounts=true) {
		$db = DevblocksPlatform::getDatabaseService();
		$fields = SearchFields_messageAuditLog::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, array(), $fields,$sortBy);
		$start = ($page * $limit); // [JAS]: 1-based [TODO] clean up + document
		$total = -1;
		
		$sql = sprintf("SELECT ".
			"l.id as %s, ".
			"l.account_id as %s, ".
			"l.recipient_id as %s, ".
			"l.message_id as %s, ".
			"l.message_recipient_id as %s, ".
			"l.worker_id as %s, ".
			"l.change_date as %s, ".
			"l.change_field as %s, ".
			"l.change_value as %s ".
			"FROM audit_log_message l ",
			    SearchFields_MessageAuditLog::ID,
			    SearchFields_MessageAuditLog::ACCOUNT_ID,
			    SearchFields_MessageAuditLog::RECIPIENT_ID,
			    SearchFields_MessageAuditLog::MESSAGE_ID,
			    SearchFields_MessageAuditLog::MESSAGE_RECIPIENT_ID,
			    SearchFields_MessageAuditLog::WORKER_ID,
			    SearchFields_MessageAuditLog::CHANGE_DATE,
			    SearchFields_MessageAuditLog::CHANGE_FIELD,
			    SearchFields_MessageAuditLog::CHANGE_VALUE
			).
			
			// [JAS]: Dynamic table joins
//			(isset($tables['ra']) ? "INNER JOIN requester r ON (r.message_id=t.id)" : " ").
			
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "").
			(!empty($sortBy) ? sprintf("ORDER BY %s %s",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : "")
		;
		// [TODO] Could push the select logic down a level too
		if($limit > 0) {
    		$rs = $db->SelectLimit($sql,$limit,$start) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); 
		} else {
		    $rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); 
            $total = mysql_num_rows($rs);
		}
		
		$results = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$result = array();
			foreach($row as $f => $v) {
				$result[$f] = $v;
			}
			$id = intval($row[SearchFields_MessageAuditLog::ID]);
			$results[$id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
		    $rs = $db->Execute($sql);
		    $total = mysql_num_rows($rs);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
    }
	
};

class SearchFields_MessageAuditLog implements IDevblocksSearchFields {
	// Audit Log
	const ID = 'l_id';
	const ACCOUNT_ID = 'l_account_id';
	const RECIPIENT_ID = 'l_recipient_id';
	const MESSAGE_ID = 'l_message_id';
	const MESSAGE_RECIPIENT_ID = 'l_message_recipient_id';
	const WORKER_ID = 'l_worker_id';
	const CHANGE_DATE = 'l_change_date';
	const CHANGE_FIELD = 'l_change_field';
	const CHANGE_VALUE = 'l_change_value';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'l', 'id'),
			self::ACCOUNT_ID=> new DevblocksSearchField(self::ACCOUNT_ID, 'l', 'account_id',$translate->_('auditlog_entry.account_id')),
			self::RECIPIENT_ID => new DevblocksSearchField(self::RECIPIENT_ID, 'l', 'recipient_id',$translate->_('auditlog_entry.recipient_id')),
			self::MESSAGE_ID => new DevblocksSearchField(self::MESSAGE_ID, 'l', 'message_id',$translate->_('auditlog_entry.message_id')),
			self::MESSAGE_RECIPIENT_ID => new DevblocksSearchField(self::MESSAGE_RECIPIENT_ID, 'l', 'message_recipient_id',$translate->_('auditlog_entry.message_recipient_id')),
			self::WORKER_ID => new DevblocksSearchField(self::WORKER_ID, 'l', 'worker_id',$translate->_('auditlog_entry.worker_id')),
			self::CHANGE_DATE => new DevblocksSearchField(self::CHANGE_DATE, 'l', 'change_date',$translate->_('auditlog_entry.change_date')),
			self::CHANGE_FIELD => new DevblocksSearchField(self::CHANGE_FIELD, 'l', 'change_field',$translate->_('auditlog_entry.change_field')),
			self::CHANGE_VALUE => new DevblocksSearchField(self::CHANGE_VALUE, 'l', 'change_value',$translate->_('auditlog_entry.change_value')),
		);
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));
		
		return $columns;
	}
};

class Model_MessageAuditLog {
	public $id = 0;
	public $account_id = 0;
	public $recipient_id = 0;
	public $message_id = 0;
	public $message_recipient_id = 0;
	public $worker_id = 0;
	public $change_date = 0;
	public $change_field = '';
	public $change_value = '';
};

class View_MessageAuditLog extends Feg_AbstractView {
	const DEFAULT_ID = 'audit_log_message';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$this->id = self::DEFAULT_ID;
		$this->name = $translate->_('auditlog.audit_log');
		$this->renderLimit = 15;
		$this->renderSortBy = 'l_change_date';
		$this->renderSortAsc = false;
		
		$this->view_columns = array(
			SearchFields_MessageAuditLog::CHANGE_DATE,
			SearchFields_MessageAuditLog::ACCOUNT_ID,
			SearchFields_MessageAuditLog::RECIPIENT_ID,
			SearchFields_MessageAuditLog::MESSAGE_ID,
			SearchFields_MessageAuditLog::MESSAGE_RECIPIENT_ID,
			SearchFields_MessageAuditLog::WORKER_ID,
			SearchFields_MessageAuditLog::CHANGE_FIELD,
			SearchFields_MessageAuditLog::CHANGE_VALUE,
		);
	}
	
	function getData() {
		$objects = DAO_MessageAuditLog::search(
			$this->params,
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc,
			$this->renderTotal
		);
		return $objects;	
	}
	
	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);
		$tpl->assign('view_fields', $this->getColumns());
		
		$workers = DAO_Worker::getAll();
		$tpl->assign('workers', $workers);

		switch($this->renderTemplate) {
			case 'peek_tab':
				$tpl->display('file:' . APP_PATH . '/features/feg.auditlog/templates/display/log/peek_tab_view.tpl');
				break;
			case 'example2':
				$tpl->display('file:' . APP_PATH . '/features/feg.auditlog/templates/display/log/example2_view.tpl');
				break;
			default:
				$tpl->display('file:' . APP_PATH . '/features/feg.auditlog/templates/display/log/log_view.tpl');
				break;
		}
	}
	
	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		
		switch($field) {
			case SearchFields_MessageAuditLog::CHANGE_FIELD:
			case SearchFields_MessageAuditLog::CHANGE_VALUE:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			default:
				echo '';
				break;
		}
	}

	static function getFields() {
		return SearchFields_MessageAuditLog::getFields();
	}
	
	static function getSearchFields() {
		$fields = self::getFields();
		unset($fields[SearchFields_MessageAuditLog::ID]);
		return $fields;
	}
	
	static function getColumns() {
		$fields = self::getFields();
		return $fields;
	}
	
	function doSetCriteria($field, $oper, $value) {
		$criteria = null;
		
		switch($field) {
			case SearchFields_MessageAuditLog::ID:
			case SearchFields_MessageAuditLog::WORKER_ID:
			case SearchFields_MessageAuditLog::ACCOUNT_ID:
			case SearchFields_MessageAuditLog::RECIPIENT_ID:
			case SearchFields_MessageAuditLog::MESSAGE_ID:
			case SearchFields_MessageAuditLog::MESSAGE_RECIPIENT_ID:
			case SearchFields_MessageAuditLog::CHANGE_DATE:
			case SearchFields_MessageAuditLog::CHANGE_FIELD:
			case SearchFields_MessageAuditLog::CHANGE_VALUE:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE) 
					&& false === (strpos($value,'*'))) {
						$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
		}
		
		if(!empty($criteria)) {
			$this->params[$field] = $criteria;
			$this->renderPage = 0;
		}
	}	
};

?>