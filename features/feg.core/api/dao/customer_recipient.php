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
};

class DAO_CustomerRecipient extends DevblocksORMHelper {
	const ID = 'id';
	const ACCOUNT_ID = 'account_id';
	const EXPORT_FILTER = 'export_filter';
	const IS_DISABLED = 'is_disabled';
	const TYPE = 'type';
	const ADDRESS = 'address';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
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
		
		$sql = "SELECT id, account_id, export_filter, is_disabled, type, address ".
			"FROM customer_recipient ".
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
			"customer_recipient.id as %s, ".
			"customer_recipient.account_id as %s, ".
			"customer_recipient.export_filter as %s, ".
			"customer_recipient.is_disabled as %s, ".
			"customer_recipient.type as %s, ".
			"customer_recipient.address as %s ",
				SearchFields_CustomerRecipient::ID,
				SearchFields_CustomerRecipient::ACCOUNT_ID,
				SearchFields_CustomerRecipient::EXPORT_FILTER,
				SearchFields_CustomerRecipient::IS_DISABLED,
				SearchFields_CustomerRecipient::TYPE,
				SearchFields_CustomerRecipient::ADDRESS
			);
			
		$join_sql = "FROM customer_recipient ";
		
		// Custom field joins
		list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
			$tables,
			$params,
			'customer_recipient.id',
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
			($has_multiple_values ? 'GROUP BY customer_recipient.id ' : '').
			$sort_sql;
			
		// [TODO] Could push the select logic down a level too
		if($limit > 0) {
    		$rs = $db->SelectLimit($sql,$limit,$start) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		} else {
		    $rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
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
				($has_multiple_values ? "SELECT COUNT(DISTINCT customer_recipient.id) " : "SELECT COUNT(customer_recipient.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};


class SearchFields_CustomerRecipient implements IDevblocksSearchFields {
	const ID = 'c_id';
	const ACCOUNT_ID = 'c_account_id';
	const EXPORT_FILTER = 'c_export_filter';
	const IS_DISABLED = 'c_is_disabled';
	const TYPE = 'c_type';
	const ADDRESS = 'c_address';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'customer_recipient', 'id', $translate->_('id')),
			self::ACCOUNT_ID => new DevblocksSearchField(self::ACCOUNT_ID, 'customer_recipient', 'account_id', $translate->_('account_id')),
			self::EXPORT_FILTER => new DevblocksSearchField(self::EXPORT_FILTER, 'customer_recipient', 'export_filter', $translate->_('export_filter')),
			self::IS_DISABLED => new DevblocksSearchField(self::IS_DISABLED, 'customer_recipient', 'is_disabled', $translate->_('is_disabled')),
			self::TYPE => new DevblocksSearchField(self::TYPE, 'customer_recipient', 'type', $translate->_('type')),
			self::ADDRESS => new DevblocksSearchField(self::ADDRESS, 'customer_recipient', 'address', $translate->_('address')),
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


class View_CustomerRecipient extends FEG_AbstractView {
	const DEFAULT_ID = 'customerrecipient';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('CustomerRecipient');
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
		);
		$this->doResetCriteria();
	}

	function getData() {
		$objects = CustomerRecipient::search(
			$this->id,
			$this->account_id,
			$this->export_filter,
			$this->is_disabled,
			$this->type,
			$this->address,
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
		// [TODO] Set your template path
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/customer_recipient/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		// [TODO] Move the fields into the proper data type
		switch($field) {
			case SearchFields_CustomerRecipient::EXPORT_FILTER:
			case SearchFields_CustomerRecipient::ADDRESS:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_CustomerRecipient::ID:
			case SearchFields_CustomerRecipient::ACCOUNT_ID:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
			case SearchFields_CustomerRecipient::IS_DISABLED:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
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
		//	unset($fields[SearchFields_CustomerRecipient::ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
		//SearchFields_CustomerRecipient::ID => new DevblocksSearchCriteria(SearchFields_CustomerRecipient::ID,'!=',0),
		);
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_CustomerRecipient::ACCOUNT_ID:
			case SearchFields_CustomerRecipient::EXPORT_FILTER:
			case SearchFields_CustomerRecipient::ADDRESS:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
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
//			$change_fields[DAO_CustomerRecipient::IS_DISABLED] = intval($v);
//			$change_fields[DAO_CustomerRecipient::TYPE] = intval($v);
//			$change_fields[DAO_CustomerRecipient::ADDRESS] = intval($v);
				// [TODO] Implement actions
				case 'example':
					//$change_fields[DAO_CustomerRecipient::EXAMPLE] = 'some value';
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
