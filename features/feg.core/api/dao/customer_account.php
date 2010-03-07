<?php
// Custom Field Sources

class FegCustomFieldSource_CustomerAccount extends Extension_CustomFieldSource {
	const ID = 'feg.fields.source.customer_account';
};

class Model_CustomerAccount {
	public $id;
	public $is_disabled;
	public $account_number;
	public $account_name;
	public $notes;
};

class DAO_CustomerAccount extends DevblocksORMHelper {
	const ID = 'id';
	const IS_DISABLED = 'is_disabled';
	const ACCOUNT_NUMBER = 'account_number';
	const ACCOUNT_NAME = 'account_name';
	const NOTES = 'notes';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO customer_account (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'customer_account', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('customer_account', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_CustomerAccount[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, is_disabled, account_number, account_name, notes ".
			"FROM customer_account ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_CustomerAccount	 */
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
	 * @return Model_CustomerAccount[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_CustomerAccount();
			$object->id = $row['id'];
			$object->is_disabled = $row['is_disabled'];
			$object->account_number = $row['account_number'];
			$object->account_name = $row['account_name'];
			$object->notes = $row['notes'];
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
		
		$db->Execute(sprintf("DELETE FROM customer_account WHERE id IN (%s)", $ids_list));
		
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
		$fields = SearchFields_CustomerAccount::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"customer_account.id as %s, ".
			"customer_account.is_disabled as %s, ".
			"customer_account.account_number as %s, ".
			"customer_account.account_name as %s, ".
			"customer_account.notes as %s ",
				SearchFields_CustomerAccount::ID,
				SearchFields_CustomerAccount::IS_DISABLED,
				SearchFields_CustomerAccount::ACCOUNT_NUMBER,
				SearchFields_CustomerAccount::ACCOUNT_NAME,
				SearchFields_CustomerAccount::NOTES
			);
			
		$join_sql = "FROM customer_account ";
		
		// Custom field joins
		//list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
		//	$tables,
		//	$params,
		//	'customer_account.id',
		//	$select_sql,
		//	$join_sql
		//);
				
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";
			
		$sql = 
			$select_sql.
			$join_sql.
			$where_sql.
			($has_multiple_values ? 'GROUP BY customer_account.id ' : '').
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
			$object_id = intval($row[SearchFields_CustomerAccount::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT customer_account.id) " : "SELECT COUNT(customer_account.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};


class SearchFields_CustomerAccount implements IDevblocksSearchFields {
	const ID = 'c_id';
	const IS_DISABLED = 'c_is_disabled';
	const ACCOUNT_NUMBER = 'c_account_number';
	const ACCOUNT_NAME = 'c_account_name';
	const NOTES = 'c_notes';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'customer_account', 'id', $translate->_('id')),
			self::IS_DISABLED => new DevblocksSearchField(self::IS_DISABLED, 'customer_account', 'is_disabled', $translate->_('is_disabled')),
			self::ACCOUNT_NUMBER => new DevblocksSearchField(self::ACCOUNT_NUMBER, 'customer_account', 'account_number', $translate->_('account_number')),
			self::ACCOUNT_NAME => new DevblocksSearchField(self::ACCOUNT_NAME, 'customer_account', 'account_name', $translate->_('account_name')),
			self::NOTES => new DevblocksSearchField(self::NOTES, 'customer_account', 'notes', $translate->_('notes')),
		);
		
		// Custom Fields
		//$fields = DAO_CustomField::getBySource(PsCustomFieldSource_XXX::ID);

		//if(is_array($fields))
		//foreach($fields as $field_id => $field) {
		//	$key = 'cf_'.$field_id;
		//	$columns[$key] = new DevblocksSearchField($key,$key,'field_value',$field->name);
		//}
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};


class View_CustomerAccount extends FEG_AbstractView {
	const DEFAULT_ID = 'customer_account';
	
	function __construct() {
		$this->id = self::DEFAULT_ID;
		$this->name = 'CustomerAccount';
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_CustomerAccount::ACCOUNT_NUMBER;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_CustomerAccount::ID,
			SearchFields_CustomerAccount::IS_DISABLED,
			SearchFields_CustomerAccount::ACCOUNT_NUMBER,
			SearchFields_CustomerAccount::ACCOUNT_NAME,
			SearchFields_CustomerAccount::NOTES
		);
		$this->doResetCriteria();
		
//		$this->params = array(
//			SearchFields_CustomerAccount::FIXME => new DevblocksSearchCriteria(SearchFields_CustomerAccount::FIXME,'=',1),
//		);
	}

	function getData() {
		$objects = DAO_CustomerAccount::search(
			$this->id,
			$this->is_disabled,
			$this->account_number,
			$this->account_name,
			$this->notes
		);
		return $objects;
	}

	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		
		$tpl->assign('view', $this);

		$address_fields = DAO_CustomField::getBySource(FegCustomFieldSource_CustomerAccount::ID);
		$tpl->assign('custom_fields', $address_fields);
		
		$tpl->assign('view_fields', $this->getColumns());
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/customer_account/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		switch($field) {
			case SearchFields_CustomerAccount::ACCOUNT_NUMBER:
			case SearchFields_CustomerAccount::ACCOUNT_NAME:
			case SearchFields_CustomerAccount::NOTES:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_CustomerAccount::ID:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
			case SearchFields_CustomerAccount::IS_DISABLED:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
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
		return SearchFields_CustomerAccount::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		unset($fields[SearchFields_CustomerAccount::ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
//		unset($fields[SearchFields_CustomerAccount::FIXME]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
//			SearchFields_CustomerAccount::ID => new DevblocksSearchCriteria(SearchFields_Address::ID,'=',1),
		);
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_CustomerAccount::ACCOUNT_NUMBER:
			case SearchFields_CustomerAccount::ACCOUNT_NAME:
			case SearchFields_CustomerAccount::NOTES:
				
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
				
			case SearchFields_CustomerAccount::ID:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
			case SearchFields_CustomerAccount::IS_DISABLED:
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
		@set_time_limit(600); // [TODO] Temp!
	  
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
				case 'is_disabled':
					$change_fields[DAO_CustomerAccount::IS_DISABLED] = intval($v);
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
			list($objects,$null) = DAO_CustomerAccount::search(
				array(),
				$this->params,
				100,
				$pg++,
				SearchFields_CustomerAccount::ID,
				true,
				false
			);
			 
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			DAO_CustomerAccount::update($batch_ids, $change_fields);
			
			// Custom Fields
			self::_doBulkSetCustomFields(FegCustomFieldSource_CustomerAccount::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};