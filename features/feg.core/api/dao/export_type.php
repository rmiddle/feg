<?php

class Model_ExportType {
	public $id;
	public $name;
	public $recipient_type;
	public $is_disabled;
	public $params_json;
};

class DAO_ExportType extends Feg_ORMHelper {
	const CACHE_ALL = 'feg_export_type';

	const ID = 'id';
	const NAME = 'name';
	const RECIPIENT_TYPE = 'recipient_type';
	const IS_DISABLED = 'is_disabled';
	const PARAMS_JSON = 'params_json';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO export_type (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'export_type', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('export_type', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_ExportType[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, name, recipient_type, is_disabled, params_json ".
			"FROM export_type ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_ExportType	 */
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
	 * @return Model_ExportType[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_ExportType();
			$object->id = $row['id'];
			$object->name = $row['name'];
			$object->recipient_type = $row['recipient_type'];
			$object->is_disabled = $row['is_disabled'];
			$object->params_json = $row['params_json'];
			if(false !== ($params = json_decode($object->params_json, true))) {
				$object->params = $params;
			} else {
				$object->params = array();
			}
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
		
		$db->Execute(sprintf("DELETE FROM export_type WHERE id IN (%s)", $ids_list));
		
		return true;
	}
	
	static function getAll($nocache=false, $with_disabled=false) {
	    $cache = DevblocksPlatform::getCacheService();
	    if($nocache || null === ($export_types = $cache->load(self::CACHE_ALL))) {
    	    $export_types = self::getWhere();
    	    $cache->save($export_types, self::CACHE_ALL);
	    }
	    
	    if(!$with_disabled) {
	    	foreach($export_types as $export_type_id => $export_type) { 
	    		if($export_type->is_disabled)
	    			unset($export_types[$export_type_id]);
	    	}
	    }
	    
	    return $export_types;
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
		$fields = SearchFields_ExportType::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"export_type.id as %s, ".
			"export_type.name as %s, ".
			"export_type.recipient_type as %s, ".
			"export_type.is_disabled as %s ",
			"export_type.params_json as %s, ".
				SearchFields_ExportType::ID,
				SearchFields_ExportType::NAME,
				SearchFields_ExportType::RECIPIENT_TYPE,
				SearchFields_ExportType::IS_DISABLED,
				SearchFields_ExportType::PARAMS_JSON
			);
			
		$join_sql = "FROM export_type ";
		
		// Custom field joins
		list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
			$tables,
			$params,
			'export_type.id',
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
			($has_multiple_values ? 'GROUP BY export_type.id ' : '').
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
			$object_id = intval($row[SearchFields_ExportType::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT export_type.id) " : "SELECT COUNT(export_type.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};


class SearchFields_ExportType implements IDevblocksSearchFields {
	const ID = 'export_type_id';
	const NAME = 'export_type_name';
	const RECIPIENT_TYPE = 'export_type_recipient_type';
	const IS_DISABLED = 'export_type_is_disabled';
	const PARAMS_JSON = 'export_type_params_json';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'export_type', 'id', $translate->_('feg.export_type.id')),
			self::NAME => new DevblocksSearchField(self::NAME, 'export_type', 'name', $translate->_('feg.export_type.name')),
			self::RECIPIENT_TYPE => new DevblocksSearchField(self::RECIPIENT_TYPE, 'export_type', 'recipient_type', $translate->_('feg.export_type.recipient_type')),
			self::IS_DISABLED => new DevblocksSearchField(self::IS_DISABLED, 'export_type', 'is_disabled', $translate->_('feg.export_type.is_disabled')),
			self::PARAMS_JSON => new DevblocksSearchField(self::PARAMS_JSON, 'export_type', 'params_json', $translate->_('feg.export_type.params_json')),
		);
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};


class View_ExportType extends FEG_AbstractView {
	const DEFAULT_ID = 'exporttype';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('feg.export_type.default_name');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_ExportType::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_ExportType::ID,
			SearchFields_ExportType::NAME,
			SearchFields_ExportType::RECIPIENT_TYPE,
			SearchFields_ExportType::IS_DISABLED,
			SearchFields_ExportType::PARAMS_JSON,
		);
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_ExportType::search(
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

		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_ExportType::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$tpl->assign('view_fields', $this->getColumns());
		// [TODO] Set your template path
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/tabs/export_type/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		// [TODO] Move the fields into the proper data type
		switch($field) {
			case SearchFields_ExportType::NAME:
//			case SearchFields_ExportType::PARAMS_JSON:
//			case 'placeholder_string':
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_ExportType::ID:
//			case 'placeholder_number':
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
			case SearchFields_ExportType::IS_DISABLED:
//			case 'placeholder_bool':
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
				break;
//			case 'placeholder_date':
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__date.tpl');
//				break;
			// FIXME Need to create Customer criteria filter file
			case SearchFields_ExportType::RECIPIENT_TYPE:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__recipient_type.tpl');
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
		return SearchFields_ExportType::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		// [TODO] Filter fields
		unset($fields[SearchFields_ExportType::PARAMS_JSON]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		// [TODO] Filter fields
		//	unset($fields[SearchFields_ExportType::ID]);
		unset($fields[SearchFields_ExportType::PARAMS_JSON]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
		//SearchFields_ExportType::ID => new DevblocksSearchCriteria(SearchFields_ExportType::ID,'!=',0),
		);
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_ExportType::NAME:
//			case SearchFields_ExportType::PARAMS_JSON:
//			case SearchFields_ExportType::RECIPIENT_TYPE:
//			case 'placeholder_string':
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
//			case 'placeholder_number':
			case SearchFields_ExportType::ID:
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
			case SearchFields_ExportType::IS_DISABLED:
				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
				break;
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
//			$change_fields[DAO_ExportType::ID] = intval($v);
//			$change_fields[DAO_ExportType::NAME] = intval($v);
//			$change_fields[DAO_ExportType::RECIPIENT_TYPE] = intval($v);
//			$change_fields[DAO_ExportType::IS_DISABLED] = intval($v);
//			$change_fields[DAO_ExportType::PARAMS_JSON] = intval($v);
				// [TODO] Implement actions
//				case 'example':
					//$change_fields[DAO_ExportType::EXAMPLE] = 'some value';
//					break;
				default:
					break;
			}
		}
		
		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_ExportType::search(
				$this->params,
				100,
				$pg++,
				SearchFields_ExportType::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_ExportType::update($batch_ids, $change_fields);
			
			// Custom Fields
			self::_doBulkSetCustomFields(FegCustomFieldSource_ExportType::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};
