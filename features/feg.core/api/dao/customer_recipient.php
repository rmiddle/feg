<?php

// Custom Field Sources
class FegCustomFieldSource_CustomerRecipient extends Extension_CustomFieldSource {
	const ID = 'feg.fields.source.customer_recipient';
};

class Model_CustomerRecipient {
	public $id;
	public $account_id;
	public $export_filter;
	public $is_disabled;
	public $type;
	public $address;
	public $address_to;
	public $subject;
};

class DAO_CustomerRecipient extends Feg_ORMHelper {
	const ID = 'id';
	const ACCOUNT_ID = 'account_id';
	const EXPORT_FILTER = 'export_filter';
	const IS_DISABLED = 'is_disabled';
	const TYPE = 'type';
	const ADDRESS = 'address';
	const ADDRESS_TO = 'address_to';
	const SUBJECT = 'subject';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('customer_recipient_seq');
		
		$sql = sprintf("INSERT INTO customer_recipient (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'customer_recipient', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('customer_recipient', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_CustomerRecipient[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, account_id, export_filter, is_disabled, type, address, address_to, subject ".
			"FROM customer_recipient cr ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_CustomerRecipient	 */
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
	 * @return Model_CustomerRecipient[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_CustomerRecipient();
			$object->id = $row['id'];
			$object->account_id = $row['account_id'];
			$object->export_filter = $row['export_filter'];
			$object->is_disabled = $row['is_disabled'];
			$object->type = $row['type'];
			$object->address = $row['address'];
			$object->address_to = $row['address_to'];
			$object->subject = $row['subject'];
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
		
		$db->Execute(sprintf("DELETE FROM customer_recipient WHERE id IN (%s)", $ids_list));
		
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
		$fields = SearchFields_CustomerRecipient::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"cr.id as %s, ".
			"cr.account_id as %s, ".
			"cr.export_filter as %s, ".
			"cr.is_disabled as %s, ".
			"cr.type as %s, ".
			"cr.address as %s, ",
			"cr.address_to as %s, ",
			"cr.subject as %s ",
				SearchFields_CustomerRecipient::ID,
				SearchFields_CustomerRecipient::ACCOUNT_ID,
				SearchFields_CustomerRecipient::EXPORT_FILTER,
				SearchFields_CustomerRecipient::IS_DISABLED,
				SearchFields_CustomerRecipient::TYPE,
				SearchFields_CustomerRecipient::ADDRESS,
				SearchFields_CustomerRecipient::ADDRESS_TO,
				SearchFields_CustomerRecipient::SUBJECT
			);
			
		$join_sql = "FROM customer_recipient cr ";
		
		// Custom field joins
		list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
			$tables,
			$params,
			'cr.id',
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
			($has_multiple_values ? 'GROUP BY cr.id ' : '').
			$sort_sql;
			
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
			$object_id = intval($row[SearchFields_CustomerRecipient::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT cr.id) " : "SELECT COUNT(cr.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};


class SearchFields_CustomerRecipient implements IDevblocksSearchFields {
	const ID = 'cr_id';
	const ACCOUNT_ID = 'cr_account_id';
	const EXPORT_FILTER = 'cr_export_filter';
	const IS_DISABLED = 'cr_is_disabled';
	const TYPE = 'cr_type';
	const ADDRESS = 'cr_address';
	const ADDRESS_TO = 'cr_address_to';
	const SUBJECT = 'cr_subject';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'cr', 'id', $translate->_('feg.customer_recipient.id')),
			self::ACCOUNT_ID => new DevblocksSearchField(self::ACCOUNT_ID, 'cr', 'account_id', $translate->_('feg.customer_recipient.account_id')),
			self::EXPORT_FILTER => new DevblocksSearchField(self::EXPORT_FILTER, 'cr', 'export_filter', $translate->_('feg.customer_recipient.export_filter')),
			self::IS_DISABLED => new DevblocksSearchField(self::IS_DISABLED, 'cr', 'is_disabled', $translate->_('common.disabled')),
			self::TYPE => new DevblocksSearchField(self::TYPE, 'cr', 'type', $translate->_('feg.customer_recipient.type')),
			self::ADDRESS => new DevblocksSearchField(self::ADDRESS, 'cr', 'address', $translate->_('feg.customer_recipient.address')),
			self::ADDRESS_TO => new DevblocksSearchField(self::ADDRESS_TO, 'cr', 'address_to', $translate->_('feg.customer_recipient.address_to')),
			self::SUBJECT => new DevblocksSearchField(self::SUBJECT, 'cr', 'subject', $translate->_('feg.customer_recipient.subject')),
		);
		
		// Custom Fields
		$fields = DAO_CustomField::getBySource(FegCustomFieldSource_CustomerRecipient::ID);

		if(is_array($fields))
		foreach($fields as $field_id => $field) {
			$key = 'cf_'.$field_id;
			$columns[$key] = new DevblocksSearchField($key,$key,'field_value',$field->name);
		}
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};


class View_CustomerRecipient extends Feg_AbstractView {
	const DEFAULT_ID = 'customer_recipient';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		$this->name = $translate->_('feg.customer_recipient.title');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_CustomerRecipient::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_CustomerRecipient::ID,
			SearchFields_CustomerRecipient::ACCOUNT_ID,
			SearchFields_CustomerRecipient::EXPORT_FILTER,
			SearchFields_CustomerRecipient::IS_DISABLED,
			SearchFields_CustomerRecipient::TYPE,
			SearchFields_CustomerRecipient::ADDRESS,
			SearchFields_CustomerRecipient::ADDRESS_TO,
			SearchFields_CustomerRecipient::SUBJECT,
		);
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_CustomerRecipient::search(
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

		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_CustomerRecipient::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$tpl->assign('view_fields', $this->getColumns());	
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/customer_recipient/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		switch($field) {
			case SearchFields_CustomerRecipient::EXPORT_FILTER:
			case SearchFields_CustomerRecipient::ADDRESS:
			case SearchFields_CustomerRecipient::ADDRESS_TO:
			case SearchFields_CustomerRecipient::SUBJECT:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_CustomerRecipient::ID:
			case SearchFields_CustomerRecipient::ACCOUNT_ID:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
			case SearchFields_CustomerRecipient::IS_DISABLED:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__is_disable.tpl');
				break;
//			case 'placeholder_date':
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__date.tpl');
//				break;
			case SearchFields_CustomerRecipient::TYPE:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/feg/customer_recipient_type.tpl');
				break;
			default:
				// Custom Fields
				if('cf_' == substr($field,0,3)) {
					$this->_renderCriteriaCustomField($tpl, substr($field,3));
				} else {
					echo ' ';
				}
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
		return SearchFields_CustomerRecipient::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		// [TODO] Filter fields
		unset($fields[SearchFields_CustomerRecipient::ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		// [TODO] Filter fields
		unset($fields[SearchFields_CustomerRecipient::ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
			SearchFields_CustomerRecipient::IS_DISABLED => new DevblocksSearchCriteria(SearchFields_CustomerRecipient::IS_DISABLED,'=',0),
		);
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_CustomerRecipient::EXPORT_FILTER:
			case SearchFields_CustomerRecipient::ADDRESS:
			case SearchFields_CustomerRecipient::ADDRESS_TO:
			case SearchFields_CustomerRecipient::SUBJECT:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
			case SearchFields_CustomerRecipient::ACCOUNT_ID:
			case SearchFields_CustomerRecipient::ID:
			case SearchFields_CustomerRecipient::TYPE:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
//			case 'placeholder_date':
//				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
//				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

//				if(empty($from)) $from = 0;
//				if(empty($to)) $to = 'today';

//				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
//				break;
				
			case SearchFields_CustomerRecipient::IS_DISABLED:
				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
				break;
			default:
				// Custom Fields
				if(substr($field,0,3)=='cf_') {
					$criteria = $this->_doSetCriteriaCustomField($field, substr($field,3));
				}
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
//			$change_fields[DAO_CustomerRecipient::ID] = intval($v);
//			$change_fields[DAO_CustomerRecipient::ACCOUNT_ID] = intval($v);
//			$change_fields[DAO_CustomerRecipient::EXPORT_FILTER] = intval($v);
//			$change_fields[DAO_CustomerRecipient::TYPE] = intval($v);
//			$change_fields[DAO_CustomerRecipient::ADDRESS] = intval($v);
//			$change_fields[DAO_CustomerRecipient::ADDRESS_TO] = intval($v);
//			$change_fields[DAO_CustomerRecipient::SUBJECT] = intval($v);
				// [TODO] Implement actions
				case 'is_disabled':
					$change_fields[DAO_CustomerRecipient::IS_DISABLED] = intval($v);
					break;
				default:
					// Custom fields
					if(substr($k,0,3)=="cf_") {
						$custom_fields[substr($k,3)] = $v;
					}
			}
		}
		
		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_CustomerRecipient::search(
				array(),
				$this->params,
				100,
				$pg++,
				SearchFields_CustomerRecipient::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_CustomerRecipient::update($batch_ids, $change_fields);
			
			// Custom Fields
			self::_doBulkSetCustomFields(FegCustomFieldSource_CustomerRecipient::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};
