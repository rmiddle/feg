<?php

class Model_MessageRecipient {
	public $id;
	public $recipient_id;
	public $message_id;
	public $account_id;
	public $send_status;
	public $updated_date;
	public $closed_date;
};

class DAO_MessageRecipient extends Feg_ORMHelper {
	const ID = 'id';
	const RECIPIENT_ID = 'recipient_id';
	const MESSAGE_ID = 'message_id';
	const ACCOUNT_ID = 'account_id';
	const SEND_STATUS = 'send_status';
	const UPDATED_DATE = 'updated_date';
	const CLOSED_DATE = 'closed_date';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('message_recipient_seq');
		
		$sql = sprintf("INSERT INTO message_recipient (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'message_recipient', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('message_recipient', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_MessageRecipient[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, recipient_id, message_id, account_id, send_status, updated_date, closed_date ".
			"FROM message_recipient ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_MessageRecipient	 */
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
	 * @return Model_MessageRecipient[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_MessageRecipient();
			$object->id = $row['id'];
			$object->recipient_id = $row['recipient_id'];
			$object->message_id = $row['message_id'];
			$object->account_id = $row['account_id'];
			$object->send_status = $row['send_status'];
			$object->updated_date = $row['updated_date'];
			$object->closed_date = $row['closed_date'];
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
		
		$db->Execute(sprintf("DELETE FROM message_recipient WHERE id IN (%s)", $ids_list));
		
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
		$fields = SearchFields_MessageRecipient::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"mr.id as %s, ".
			"mr.recipient_id as %s, ".
			"mr.message_id as %s, ".
			"mr.account_id as %s, ".
			"mr.send_status as %s, ".
			"mr.updated_date as %s, ".
			"mr.closed_date as %s ",
				SearchFields_MessageRecipient::ID,
				SearchFields_MessageRecipient::RECIPIENT_ID,
				SearchFields_MessageRecipient::MESSAGE_ID,
				SearchFields_MessageRecipient::ACCOUNT_ID,
				SearchFields_MessageRecipient::SEND_STATUS,
				SearchFields_MessageRecipient::UPDATED_DATE,
				SearchFields_MessageRecipient::CLOSED_DATE
			);
			
		$join_sql = "FROM message_recipient mr ";
		
		// Custom field joins
		list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
			$tables,
			$params,
			'mr.id',
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
			($has_multiple_values ? 'GROUP BY mr.id ' : '').
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
			$object_id = intval($row[SearchFields_MessageRecipient::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT mr.id) " : "SELECT COUNT(mr.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};


class SearchFields_MessageRecipient implements IDevblocksSearchFields {
	const ID = 'mr_id';
	const RECIPIENT_ID = 'mr_recipient_id';
	const MESSAGE_ID = 'mr_message_id';
	const ACCOUNT_ID = 'mr_account_id';
	const SEND_STATUS = 'mr_send_status';
	const UPDATED_DATE = 'mr_updated_date';
	const CLOSED_DATE = 'mr_closed_date';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'mr', 'id', $translate->_('feg.message_recipient.id')),
			self::RECIPIENT_ID => new DevblocksSearchField(self::RECIPIENT_ID, 'mr', 'recipient_id', $translate->_('feg.message_recipient.recipient_id')),
			self::MESSAGE_ID => new DevblocksSearchField(self::MESSAGE_ID, 'mr', 'message_id', $translate->_('feg.message_recipient.message_id')),
			self::ACCOUNT_ID => new DevblocksSearchField(self::ACCOUNT_ID, 'mr', 'account_id', $translate->_('feg.message_recipient.account_id')),
			self::SEND_STATUS => new DevblocksSearchField(self::SEND_STATUS, 'mr', 'send_status', $translate->_('feg.message_recipient.send_status')),
			self::UPDATED_DATE => new DevblocksSearchField(self::UPDATED_DATE, 'mr', 'updated_date', $translate->_('feg.message_recipient.updated_date')),
			self::CLOSED_DATE => new DevblocksSearchField(self::CLOSED_DATE, 'mr', 'closed_date', $translate->_('feg.message_recipient.closed_date')),
		);
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};


class View_MessageRecipient extends FEG_AbstractView {
	const DEFAULT_ID = 'message_recipient';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('feg.message_recipient.name');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_MessageRecipient::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_MessageRecipient::ID,
			SearchFields_MessageRecipient::RECIPIENT_ID,
			SearchFields_MessageRecipient::MESSAGE_ID,
			SearchFields_MessageRecipient::ACCOUNT_ID,
			SearchFields_MessageRecipient::SEND_STATUS,
			SearchFields_MessageRecipient::UPDATED_DATE,
			SearchFields_MessageRecipient::CLOSED_DATE,
		);
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_MessageRecipient::search(
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
		
		switch($this->renderTemplate) {
			case 'limited':
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/message_recipient/limited.tpl');
				break;
			default:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/message_recipient/view.tpl');
				break;
		}
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		// [TODO] Move the fields into the proper data type
		switch($field) {
			case SearchFields_MessageRecipient::SEND_STATUS:
				// [TODO] Create Template file
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/feg/message_recipient_send_status.tpl');
				break;
//			case 'placeholder_string':
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
//				break;
			case SearchFields_MessageRecipient::ID:
			case SearchFields_MessageRecipient::RECIPIENT_ID:
			case SearchFields_MessageRecipient::MESSAGE_ID:
			case SearchFields_MessageRecipient::ACCOUNT_ID:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
//			case 'placeholder_bool':
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
//				break;
			case SearchFields_MessageRecipient::UPDATED_DATE:
			case SearchFields_MessageRecipient::CLOSED_DATE:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__date.tpl');
				break;
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
		return SearchFields_MessageRecipient::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		// [TODO] Filter fields
		// unset($fields[SearchFields_MessageRecipient::ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		// [TODO] Filter fields
		//	unset($fields[SearchFields_MessageRecipient::ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
		//SearchFields_MessageRecipient::ID => new DevblocksSearchCriteria(SearchFields_MessageRecipient::ID,'!=',0),
		);
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
//			case 'placeholder_string':
//				// force wildcards if none used on a LIKE
//				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
//				&& false === (strpos($value,'*'))) {
//					$value = '*'.$value.'*';
//				}
//				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
//				break;
			case SearchFields_MessageRecipient::ID:
			case SearchFields_MessageRecipient::RECIPIENT_ID:
			case SearchFields_MessageRecipient::MESSAGE_ID:
			case SearchFields_MessageRecipient::ACCOUNT_ID:
			case SearchFields_MessageRecipient::SEND_STATUS:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
			case SearchFields_MessageRecipient::UPDATED_DATE:
			case SearchFields_MessageRecipient::CLOSED_DATE:
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from)) $from = 0;
				if(empty($to)) $to = 'today';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;
				
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
//			$change_fields[DAO_MessageRecipient::ID] = intval($v);
//			$change_fields[DAO_MessageRecipient::RECIPIENT_ID] = intval($v);
//			$change_fields[DAO_MessageRecipient::MESSAGE_ID] = intval($v);
//			$change_fields[DAO_MessageRecipient::ACCOUNT_ID] = intval($v);
//			$change_fields[DAO_MessageRecipient::SEND_STATUS] = intval($v);
//			$change_fields[DAO_MessageRecipient::UPDATED_DATE] = intval($v);
//			$change_fields[DAO_MessageRecipient::CLOSED_DATE] = intval($v);
				// [TODO] Implement actions
				case 'example':
					//$change_fields[DAO_MessageRecipient::EXAMPLE] = 'some value';
					break;
				default:
			}
		}
		
		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_MessageRecipient::search(
				$this->params,
				100,
				$pg++,
				SearchFields_MessageRecipient::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_MessageRecipient::update($batch_ids, $change_fields);
			
			// Custom Fields
			self::_doBulkSetCustomFields(FegCustomFieldSource_MessageRecipient::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};
