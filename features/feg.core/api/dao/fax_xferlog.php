<?php

class Model_FaxXferlog {
	public $id;
	public $message_recipient_id;
	public $timestamp;
	public $entrytype;
	public $commid;
	public $modem;
	public $jobid;
	public $jobtag;
	public $user;
	public $localnumber;
	public $tsi;
	public $params;
	public $npages;
	public $jobtime;
	public $conntime;
	public $reason;
	public $CIDName;
	public $CIDNumber;
	public $callid;
	public $owner;
	public $dcs;
	public $jobinfo;
};

class DAO_FaxXferlog extends Feg_ORMHelper {
	const ID = 'id';
	const MESSAGE_RECIPIENT_ID = 'message_recipient_id';
	const TIMESTAMP = 'timestamp';
	const ENTRYTYPE = 'entrytype';
	const COMMID = 'commid';
	const MODEM = 'modem';
	const JOBID = 'jobid';
	const JOBTAG = 'jobtag';
	const USER = 'user';
	const LOCALNUMBER = 'localnumber';
	const TSI = 'tsi';
	const PARAMS = 'params';
	const NPAGES = 'npages';
	const JOBTIME = 'jobtime';
	const CONNTIME = 'conntime';
	const REASON = 'reason';
	const CIDNAME = 'CIDName';
	const CIDNUMBER = 'CIDNumber';
	const CALLID = 'callid';
	const OWNER = 'owner';
	const DCS = 'dcs';
	const JOBINFO = 'jobinfo';

	// FIXME Create is broken not likely to be used for anything.
	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		// Use Auto Increment inside mysql instead due to hylafax insert script
		//	$id = $db->GenID('generic_seq');
		
//		$sql = sprintf("INSERT INTO fax_xferlog (send_status) ".
//			"VALUES (%d)",
//			$send_status
//		);
//		$db->Execute($sql);
		
//		self::update($id, $fields);
		
//		return $id;
		return NULL;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'fax_xferlog', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('fax_xferlog', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_FaxXferlog[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, message_recipient_id, timestamp, entrytype, commid, modem, jobid, jobtag, user, localnumber, tsi, params, npages, jobtime, conntime, reason, CIDName, CIDNumber, callid, owner, dcs, jobinfo ".
			"FROM fax_xferlog ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_FaxXferlog	 */
	static function get($id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::ID,
			$id
		));
		
		if(isset($objects[$id]))
			return $objects[$id];
		
		return null;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_FaxXferlog[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_FaxXferlog();
			$object->id = $row['id'];
			$object->message_recipient_id = $row['message_recipient_id'];
			$object->timestamp = $row['timestamp'];
			$object->entrytype = $row['entrytype'];
			$object->commid = $row['commid'];
			$object->modem = $row['modem'];
			$object->jobid = $row['jobid'];
			$object->jobtag = $row['jobtag'];
			$object->user = $row['user'];
			$object->localnumber = $row['localnumber'];
			$object->tsi = $row['tsi'];
			$object->params = $row['params'];
			$object->npages = $row['npages'];
			$object->jobtime = $row['jobtime'];
			$object->conntime = $row['conntime'];
			$object->reason = $row['reason'];
			$object->CIDName = $row['CIDName'];
			$object->CIDNumber = $row['CIDNumber'];
			$object->callid = $row['callid'];
			$object->owner = $row['owner'];
			$object->dcs = $row['dcs'];
			$object->jobinfo = $row['jobinfo'];
			$objects[$object->id] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}
	
	static function delete($ids) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		
		if(empty($ids))
			return;
		
		$ids_list = implode(',', $ids);
		
		$db->Execute(sprintf("DELETE FROM fax_xferlog WHERE id IN (%s)", $ids_list));
		
		return true;
	}
	
    /**
     * Enter description here...
     *
     * @param array $columns
     * @param DevblocksSearchCriteria[] $params
     * @param integer $limit
     * @param integer $page
     * @param string $sortBy
     * @param boolean $sortAsc
     * @param boolean $withCounts
     * @return array
     */
    static function search($columns, $params, $limit=10, $page=0, $sortBy=null, $sortAsc=null, $withCounts=true) {
		$db = DevblocksPlatform::getDatabaseService();
		$fields = SearchFields_FaxXferlog::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"fx.id as %s, ".
			"fx.message_recipient_id as %s, ".
			"fx.timestamp as %s, ".
			"fx.entrytype as %s, ".
			"fx.commid as %s, ".
			"fx.modem as %s, ".
			"fx.jobid as %s, ".
			"fx.jobtag as %s, ".
			"fx.user as %s, ".
			"fx.localnumber as %s, ".
			"fx.tsi as %s, ".
			"fx.params as %s, ".
			"fx.npages as %s, ".
			"fx.jobtime as %s, ".
			"fx.conntime as %s, ".
			"fx.reason as %s, ".
			"fx.CIDName as %s, ".
			"fx.CIDNumber as %s, ".
			"fx.callid as %s, ".
			"fx.owner as %s, ".
			"fx.dcs as %s, ".
			"fx.jobinfo as %s ",
				SearchFields_FaxXferlog::ID,
				SearchFields_FaxXferlog::MESSAGE_RECIPIENT_ID,
				SearchFields_FaxXferlog::TIMESTAMP,
				SearchFields_FaxXferlog::ENTRYTYPE,
				SearchFields_FaxXferlog::COMMID,
				SearchFields_FaxXferlog::MODEM,
				SearchFields_FaxXferlog::JOBID,
				SearchFields_FaxXferlog::JOBTAG,
				SearchFields_FaxXferlog::USER,
				SearchFields_FaxXferlog::LOCALNUMBER,
				SearchFields_FaxXferlog::TSI,
				SearchFields_FaxXferlog::PARAMS,
				SearchFields_FaxXferlog::NPAGES,
				SearchFields_FaxXferlog::JOBTIME,
				SearchFields_FaxXferlog::CONNTIME,
				SearchFields_FaxXferlog::REASON,
				SearchFields_FaxXferlog::CIDNAME,
				SearchFields_FaxXferlog::CIDNUMBER,
				SearchFields_FaxXferlog::CALLID,
				SearchFields_FaxXferlog::OWNER,
				SearchFields_FaxXferlog::DCS,
				SearchFields_FaxXferlog::JOBINFO
			);
			
		$join_sql = "FROM fax_xferlog fx";
		
		// Custom field joins
		list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
			$tables,
			$params,
			'fx.id',
			$select_sql,
			$join_sql
		);
				
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";
			
		$sql = 
			$select_sql.
			$join_sql.
			$where_sql.
			($has_multiple_values ? 'GROUP BY fx.id ' : '').
			$sort_sql;
			
		// [TODO] Could push the select logic down a level too
		if($limit > 0) {
    		$rs = $db->SelectLimit($sql,$limit,$start) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs */
		} else {
		    $rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs */
            $total = mysql_num_rows($rs);
		}
		
		$results = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$result = array();
			foreach($row as $f => $v) {
				$result[$f] = $v;
			}
			$object_id = intval($row[SearchFields_FaxXferlog::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT fx.id) " : "SELECT COUNT(fx.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};


class SearchFields_FaxXferlog implements IDevblocksSearchFields {
	const ID = 'fx_id';
	const MESSAGE_RECIPIENT_ID = 'fx_message_recipient_id';
	const TIMESTAMP = 'fx_timestamp';
	const ENTRYTYPE = 'fx_entrytype';
	const COMMID = 'fx_commid';
	const MODEM = 'fx_modem';
	const JOBID = 'fx_jobid';
	const JOBTAG = 'fx_jobtag';
	const USER = 'fx_user';
	const LOCALNUMBER = 'fx_localnumber';
	const TSI = 'fx_tsi';
	const PARAMS = 'fx_params';
	const NPAGES = 'fx_npages';
	const JOBTIME = 'fx_jobtime';
	const CONNTIME = 'fx_conntime';
	const REASON = 'fx_reason';
	const CIDNAME = 'fx_CIDName';
	const CIDNUMBER = 'fx_CIDNumber';
	const CALLID = 'fx_callid';
	const OWNER = 'fx_owner';
	const DCS = 'fx_dcs';
	const JOBINFO = 'fx_jobinfo';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'fx', 'id', $translate->_('feg.fax_xferlog.id')),
			self::MESSAGE_RECIPIENT_ID => new DevblocksSearchField(self::MESSAGE_RECIPIENT_ID, 'fx', 'message_recipient_id', $translate->_('feg.fax_xferlog.message_recipient_id')),
			self::TIMESTAMP => new DevblocksSearchField(self::TIMESTAMP, 'fx', 'timestamp', $translate->_('feg.fax_xferlog.timestamp')),
			self::ENTRYTYPE => new DevblocksSearchField(self::ENTRYTYPE, 'fx', 'entrytype', $translate->_('feg.fax_xferlog.entrytype')),
			self::COMMID => new DevblocksSearchField(self::COMMID, 'fx', 'commid', $translate->_('feg.fax_xferlog.commid')),
			self::MODEM => new DevblocksSearchField(self::MODEM, 'fx', 'modem', $translate->_('feg.fax_xferlog.modem')),
			self::JOBID => new DevblocksSearchField(self::JOBID, 'fx', 'jobid', $translate->_('feg.fax_xferlog.jobid')),
			self::JOBTAG => new DevblocksSearchField(self::JOBTAG, 'fx', 'jobtag', $translate->_('feg.fax_xferlog.jobtag')),
			self::USER => new DevblocksSearchField(self::USER, 'fx', 'user', $translate->_('feg.fax_xferlog.user')),
			self::LOCALNUMBER => new DevblocksSearchField(self::LOCALNUMBER, 'fx', 'localnumber', $translate->_('feg.fax_xferlog.localnumber')),
			self::TSI => new DevblocksSearchField(self::TSI, 'fx', 'tsi', $translate->_('feg.fax_xferlog.tsi')),
			self::PARAMS => new DevblocksSearchField(self::PARAMS, 'fx', 'params', $translate->_('feg.fax_xferlog.params')),
			self::NPAGES => new DevblocksSearchField(self::NPAGES, 'fx', 'npages', $translate->_('feg.fax_xferlog.npages')),
			self::JOBTIME => new DevblocksSearchField(self::JOBTIME, 'fx', 'jobtime', $translate->_('feg.fax_xferlog.jobtime')),
			self::CONNTIME => new DevblocksSearchField(self::CONNTIME, 'fx', 'conntime', $translate->_('feg.fax_xferlog.conntime')),
			self::REASON => new DevblocksSearchField(self::REASON, 'fx', 'reason', $translate->_('feg.fax_xferlog.reason')),
			self::CIDNAME => new DevblocksSearchField(self::CIDNAME, 'fx', 'CIDName', $translate->_('feg.fax_xferlog.CIDName')),
			self::CIDNUMBER => new DevblocksSearchField(self::CIDNUMBER, 'fx', 'CIDNumber', $translate->_('feg.fax_xferlog.CIDNumber')),
			self::CALLID => new DevblocksSearchField(self::CALLID, 'fx', 'callid', $translate->_('feg.fax_xferlog.callid')),
			self::OWNER => new DevblocksSearchField(self::OWNER, 'fx', 'owner', $translate->_('feg.fax_xferlog.owner')),
			self::DCS => new DevblocksSearchField(self::DCS, 'fx', 'dcs', $translate->_('feg.fax_xferlog.dcs')),
			self::JOBINFO => new DevblocksSearchField(self::JOBINFO, 'fx', 'jobinfo', $translate->_('feg.fax_xferlog.jobinfo')),
		);
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};


class View_FaxXferlog extends FEG_AbstractView {
	const DEFAULT_ID = 'fax_xferlog';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('feg.fax_xferlog.name');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_FaxXferlog::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_FaxXferlog::ID,
			SearchFields_FaxXferlog::MESSAGE_RECIPIENT_ID,
			SearchFields_FaxXferlog::TIMESTAMP,
			SearchFields_FaxXferlog::ENTRYTYPE,
			SearchFields_FaxXferlog::COMMID,
			SearchFields_FaxXferlog::MODEM,
			SearchFields_FaxXferlog::JOBID,
			SearchFields_FaxXferlog::JOBTAG,
			SearchFields_FaxXferlog::USER,
			SearchFields_FaxXferlog::LOCALNUMBER,
			SearchFields_FaxXferlog::TSI,
			SearchFields_FaxXferlog::PARAMS,
			SearchFields_FaxXferlog::NPAGES,
			SearchFields_FaxXferlog::JOBTIME,
			SearchFields_FaxXferlog::CONNTIME,
			SearchFields_FaxXferlog::REASON,
			SearchFields_FaxXferlog::CIDNAME,
			SearchFields_FaxXferlog::CIDNUMBER,
			SearchFields_FaxXferlog::CALLID,
			SearchFields_FaxXferlog::OWNER,
			SearchFields_FaxXferlog::DCS,
			SearchFields_FaxXferlog::JOBINFO,
		);
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_FaxXferlog::search(
			$this->view_columns,
			$this->params,
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc
		);
		return $objects;
	}

	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

		$tpl->assign('view_fields', $this->getColumns());
		// [TODO] Set your template path
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/fax_xferlog/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		switch($field) {
			case SearchFields_FaxXferlog::TIMESTAMP:
				// [TODO] Create Template file
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/feg/mysqldatetime.tpl');
				break;
			case SearchFields_FaxXferlog::JOBTIME:
			case SearchFields_FaxXferlog::CONNTIME:
				// [TODO] Create Template file
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/feg/mysqltime.tpl');
				break;
			case SearchFields_FaxXferlog::ENTRYTYPE:
			case SearchFields_FaxXferlog::COMMID:
			case SearchFields_FaxXferlog::MODEM:
			case SearchFields_FaxXferlog::JOBID:
			case SearchFields_FaxXferlog::JOBTAG:
			case SearchFields_FaxXferlog::USER:
			case SearchFields_FaxXferlog::TSI:
			case SearchFields_FaxXferlog::PARAMS:
			case SearchFields_FaxXferlog::REASON:
			case SearchFields_FaxXferlog::CIDNAME:
			case SearchFields_FaxXferlog::CIDNUMBER:
			case SearchFields_FaxXferlog::CALLID:
			case SearchFields_FaxXferlog::OWNER:
			case SearchFields_FaxXferlog::DCS:
			case SearchFields_FaxXferlog::JOBINFO:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_FaxXferlog::ID:
			case SearchFields_FaxXferlog::MESSAGE_RECIPIENT_ID:
			case SearchFields_FaxXferlog::LOCALNUMBER:
			case SearchFields_FaxXferlog::NPAGES:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
//			case 'placeholder_bool':
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
//				break;
//			case 'placeholder_date':
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__date.tpl');
//				break;
			default:
				echo ' ';
				break;
		}
	}

	function renderCriteriaParam($param) {
		$field = $param->field;
		$values = !is_array($param->value) ? array($param->value) : $param->value;

		switch($field) {
			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	static function getFields() {
		return SearchFields_FaxXferlog::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		// [TODO] Filter fields
		// unset($fields[SearchFields_FaxXferlog::ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		// [TODO] Filter fields
		//	unset($fields[SearchFields_FaxXferlog::ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
		//SearchFields_FaxXferlog::ID => new DevblocksSearchCriteria(SearchFields_FaxXferlog::ID,'!=',0),
		);
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_FaxXferlog::TIMESTAMP:
				// FIXME add datatime support
				break;
			case SearchFields_FaxXferlog::JOBTIME:
			case SearchFields_FaxXferlog::CONNTIME:
				// FIXME add time support
				break;
			case SearchFields_FaxXferlog::ENTRYTYPE:
			case SearchFields_FaxXferlog::COMMID:
			case SearchFields_FaxXferlog::MODEM:
			case SearchFields_FaxXferlog::JOBID:
			case SearchFields_FaxXferlog::JOBTAG:
			case SearchFields_FaxXferlog::USER:
			case SearchFields_FaxXferlog::LOCALNUMBER:
			case SearchFields_FaxXferlog::TSI:
			case SearchFields_FaxXferlog::PARAMS:
			case SearchFields_FaxXferlog::REASON:
			case SearchFields_FaxXferlog::CIDNAME:
			case SearchFields_FaxXferlog::CIDNUMBER:
			case SearchFields_FaxXferlog::CALLID:
			case SearchFields_FaxXferlog::OWNER:
			case SearchFields_FaxXferlog::DCS:
			case SearchFields_FaxXferlog::JOBINFO:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
			case SearchFields_FaxXferlog::ID:
			case SearchFields_FaxXferlog::MESSAGE_RECIPIENT_ID:
			case SearchFields_FaxXferlog::NPAGES:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
//			case 'placeholder_date':
//				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
//				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

//				if(empty($from)) $from = 0;
//				if(empty($to)) $to = 'today';

//				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
//				break;
				
//			case 'placeholder_bool':
//				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
//				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
//				break;
			default:
				break;
		}
		if(!empty($criteria)) {
			$this->params[$field] = $criteria;
			$this->renderPage = 0;
		}
	}
	
	function doBulkUpdate($filter, $do, $ids=array()) {
		@set_time_limit(0); 
	  
		$change_fields = array();
		$custom_fields = array();

		// Make sure we have actions
		if(empty($do))
			return;

		// Make sure we have checked items if we want a checked list
		if(0 == strcasecmp($filter,"checks") && empty($ids))
			return;
			
		if(is_array($do))
		foreach($do as $k => $v) {
			switch($k) {
				// [TODO] Used for bulk update
//			$change_fields[DAO_FaxXferlog::ID] = intval($v);
//			$change_fields[DAO_FaxXferlog::MESSAGE_RECIPIENT_ID] = intval($v);
//			$change_fields[DAO_FaxXferlog::TIMESTAMP] = intval($v);
//			$change_fields[DAO_FaxXferlog::ENTRYTYPE] = intval($v);
//			$change_fields[DAO_FaxXferlog::COMMID] = intval($v);
//			$change_fields[DAO_FaxXferlog::MODEM] = intval($v);
//			$change_fields[DAO_FaxXferlog::JOBID] = intval($v);
//			$change_fields[DAO_FaxXferlog::JOBTAG] = intval($v);
//			$change_fields[DAO_FaxXferlog::USER] = intval($v);
//			$change_fields[DAO_FaxXferlog::LOCALNUMBER] = intval($v);
//			$change_fields[DAO_FaxXferlog::TSI] = intval($v);
//			$change_fields[DAO_FaxXferlog::PARAMS] = intval($v);
//			$change_fields[DAO_FaxXferlog::NPAGES] = intval($v);
//			$change_fields[DAO_FaxXferlog::JOBTIME] = intval($v);
//			$change_fields[DAO_FaxXferlog::CONNTIME] = intval($v);
//			$change_fields[DAO_FaxXferlog::REASON] = intval($v);
//			$change_fields[DAO_FaxXferlog::CIDNAME] = intval($v);
//			$change_fields[DAO_FaxXferlog::CIDNUMBER] = intval($v);
//			$change_fields[DAO_FaxXferlog::CALLID] = intval($v);
//			$change_fields[DAO_FaxXferlog::OWNER] = intval($v);
//			$change_fields[DAO_FaxXferlog::DCS] = intval($v);
//			$change_fields[DAO_FaxXferlog::JOBINFO] = intval($v);
				// [TODO] Implement actions
				case 'example':
					//$change_fields[DAO_FaxXferlog::EXAMPLE] = 'some value';
					break;
				default:
			}
		}
		
		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_FaxXferlog::search(
				$this->params,
				100,
				$pg++,
				SearchFields_FaxXferlog::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_FaxXferlog::update($batch_ids, $change_fields);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};
