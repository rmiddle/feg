<?php

// Custom Field Sources
class FegCustomFieldSource_Message extends Extension_CustomFieldSource {
	const ID = 'feg.fields.source.message';
};

class Model_Message {
	public $id;
	public $account_id;
	public $created_date;
	public $updated_date;
	public $params_json;
	public $params;
	public $message;
};

class DAO_Message extends Feg_ORMHelper {
	const ID = 'id';
	const ACCOUNT_ID = 'account_id';
	const CREATED_DATE = 'created_date';
	const UPDATED_DATE = 'updated_date';
	const PARAMS_JSON = 'params_json';
	const MESSAGE = 'message';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('message_seq');
		
		$sql = sprintf("INSERT INTO message (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		if( !empty($fields['params'])) {
			$fields['params_json'] = json_encode($fields['params']);
			unset($fields['params']);
		}
		parent::_update($ids, 'message', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('message', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_Message[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, account_id, created_date, updated_date, params_json, message ".
			"FROM message ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_Message	 */
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
	 * @return Model_Message[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_Message();
			$object->id = $row['id'];
			$object->account_id = $row['account_id'];
			$object->created_date = $row['created_date'];
			$object->updated_date = $row['updated_date'];
			$object->params_json = $row['params_json'];
			
			if(false !== ($params = json_decode($object->params_json, true))) {
				$object->params = $params;
			} else {
				$object->params = array();
			}
			
			$object->message = $row['message'];
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
		
		$db->Execute(sprintf("DELETE FROM message WHERE id IN (%s)", $ids_list));
		
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
		$fields = SearchFields_Message::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"message.id as %s, ".
			"message.account_id as %s, ".
			"message.created_date as %s, ".
			"message.updated_date as %s, ".
			"message.params_json as %s, ".
			"message.message as %s ",
				SearchFields_Message::ID,
				SearchFields_Message::ACCOUNT_ID,
				SearchFields_Message::CREATED_DATE,
				SearchFields_Message::UPDATED_DATE,
				SearchFields_Message::PARAMS_JSON,
				SearchFields_Message::MESSAGE
			);
			
		$join_sql = "FROM message ";
		
		// Custom field joins
		list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
			$tables,
			$params,
			'message.id',
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
			($has_multiple_values ? 'GROUP BY message.id ' : '').
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
			$object_id = intval($row[SearchFields_Message::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT message.id) " : "SELECT COUNT(message.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};


class SearchFields_Message implements IDevblocksSearchFields {
	const ID = 'm_id';
	const ACCOUNT_ID = 'm_account_id';
	const CREATED_DATE = 'm_created_date';
	const UPDATED_DATE = 'm_updated_date';
	const PARAMS_JSON = 'm_params_json';
	const PARAMS = 'm_params';
	const MESSAGE = 'm_message';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'message', 'id', $translate->_('feg.message.id')),
			self::ACCOUNT_ID => new DevblocksSearchField(self::ACCOUNT_ID, 'message', 'account_id', $translate->_('feg.message.account_id')),
			self::CREATED_DATE => new DevblocksSearchField(self::CREATED_DATE, 'message', 'created_date', $translate->_('feg.message.created_date')),
			self::UPDATED_DATE => new DevblocksSearchField(self::UPDATED_DATE, 'message', 'updated_date', $translate->_('feg.message.updated_date')),
			self::PARAMS_JSON => new DevblocksSearchField(self::PARAMS_JSON, 'message', 'params_json', $translate->_('feg.message.params_json')),
			self::PARAMS => new DevblocksSearchField(self::PARAMS, 'message', 'params', $translate->_('feg.message.params')),
			self::MESSAGE => new DevblocksSearchField(self::MESSAGE, 'message', 'message', $translate->_('feg.message.message')),
		);
		
		// Custom Fields
		$fields = DAO_CustomField::getBySource(FegCustomFieldSource_Message::ID);

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


class View_Message extends FEG_AbstractView {
	const DEFAULT_ID = 'message';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		$this->name = $translate->_('feg.message.default_name');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_Message::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_Message::ID,
			SearchFields_Message::ACCOUNT_ID,
			SearchFields_Message::CREATED_DATE,
			SearchFields_Message::UPDATED_DATE,
			// SearchFields_Message::PARAMS_JSON,
			// SearchFields_Message::PARAMS,
			SearchFields_Message::MESSAGE,
		);
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_Message::search(
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

		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_Message::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$tpl->assign('view_fields', $this->getColumns());
		switch($this->renderTemplate) {
			case 'failed':
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/message/view_failed.tpl');
				break;
			default:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/message/view.tpl');
				break;
		}
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		// [TODO] Move the fields into the proper data type
		switch($field) {
			case SearchFields_Message::MESSAGE:
			case SearchFields_Message::PARAMS_JSON:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_Message::ID:
			case SearchFields_Message::ACCOUNT_ID:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
//			case SearchFields_Message::IS_CLOSED:
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
//				break;
			case SearchFields_Message::CREATED_DATE:
			case SearchFields_Message::UPDATED_DATE:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__date.tpl');
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
		return SearchFields_Message::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		// [TODO] Filter fields
		unset($fields[SearchFields_Message::PARAMS_JSON]);
		unset($fields[SearchFields_Message::PARAMS]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		// [TODO] Filter fields
		//	unset($fields[SearchFields_Message::ID]);
		unset($fields[SearchFields_Message::PARAMS_JSON]);
		unset($fields[SearchFields_Message::PARAMS]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
		//SearchFields_Message::ID => new DevblocksSearchCriteria(SearchFields_Message::ID,'!=',0),
		);
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_Message::MESSAGE:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
			case SearchFields_Message::ID:
			case SearchFields_Message::ACCOUNT_ID:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
			case SearchFields_Message::CREATED_DATE:
			case SearchFields_Message::UPDATED_DATE:
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from)) $from = 0;
				if(empty($to)) $to = 'today';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;
			case SearchFields_Message::PARAMS_JSON:
			case SearchFields_Message::PARAMS:
				break;
				
//			case SearchFields_Message::IS_CLOSED:
//				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
//				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
//				break;
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
//			$change_fields[DAO_Message::ID] = intval($v);
//			$change_fields[DAO_Message::ACCOUNT_ID] = intval($v);
//			$change_fields[DAO_Message::CREATED_DATE] = intval($v);
//			$change_fields[DAO_Message::UPDATED_DATE] = intval($v);
//			$change_fields[DAO_Message::MESSAGE] = intval($v);
//			$change_fields[DAO_Message::PARAMS_JSON] = intval($v);
//			$change_fields[DAO_Message::PARAMS] = intval($v);
				// [TODO] Implement actions
				case 'example':
					//$change_fields[DAO_Message::EXAMPLE] = 'some value';
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
			list($objects,$null) = DAO_Message::search(
				$this->params,
				100,
				$pg++,
				SearchFields_Message::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_Message::update($batch_ids, $change_fields);
			
			// Custom Fields
			self::_doBulkSetCustomFields(FegCustomFieldSource_Message::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};
