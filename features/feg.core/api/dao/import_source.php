<?php

class Model_ImportSource {
	public $id;
	public $name;
	public $path;
	public $type;
	public $is_disabled;
};

class DAO_ImportSource extends Feg_ORMHelper {
	const CACHE_ALL = 'feg_import_source';
	
	const ID = 'id';
	const NAME = 'name';
	const PATH = 'path';
	const TYPE = 'type';
	const IS_DISABLED = 'is_disabled';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO import_source (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'import_source', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('import_source', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_ImportSource[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, name, path, type, is_disabled ".
			"FROM import_source ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_ImportSource	 */
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
	 * @return Model_ImportSource[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_ImportSource();
			$object->id = $row['id'];
			$object->name = $row['name'];
			$object->path = $row['path'];
			$object->type = $row['type'];
			$object->is_disabled = $row['is_disabled'];
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
		
		$db->Execute(sprintf("DELETE FROM import_source WHERE id IN (%s)", $ids_list));
		
		return true;
	}
	
	static function getAll($nocache=false, $with_disabled=false) {
	    $cache = DevblocksPlatform::getCacheService();
	    if($nocache || null === ($import_sources = $cache->load(self::CACHE_ALL))) {
    	    $import_sources = self::getWhere();
    	    $cache->save($import_sources, self::CACHE_ALL);
	    }
	    
	    if(!$with_disabled) {
	    	foreach($import_sources as $import_source_id => $import_source) { /* @var $worker CerberusWorker */
	    		if($import_source->is_disabled)
	    			unset($import_sources[$import_source_id]);
	    	}
	    }
	    
	    return $import_sources;
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
		$fields = SearchFields_ImportSource::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"import_source.id as %s, ".
			"import_source.name as %s, ".
			"import_source.path as %s, ".
			"import_source.type as %s, ".
			"import_source.is_disabled as %s ",
				SearchFields_ImportSource::ID,
				SearchFields_ImportSource::NAME,
				SearchFields_ImportSource::PATH,
				SearchFields_ImportSource::TYPE,
				SearchFields_ImportSource::IS_DISABLED
			);
			
		$join_sql = "FROM import_source ";
		
		// Custom field joins
		list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
			$tables,
			$params,
			'import_source.id',
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
			($has_multiple_values ? 'GROUP BY import_source.id ' : '').
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
			$object_id = intval($row[SearchFields_ImportSource::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT import_source.id) " : "SELECT COUNT(import_source.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};


class SearchFields_ImportSource implements IDevblocksSearchFields {
	const ID = 'i_id';
	const NAME = 'i_name';
	const PATH = 'i_path';
	const TYPE = 'i_type';
	const IS_DISABLED = 'i_is_disabled';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'import_source', 'id', $translate->_('feg.import_source.id')),
			self::NAME => new DevblocksSearchField(self::NAME, 'import_source', 'name', $translate->_('feg.import_source.name')),
			self::PATH => new DevblocksSearchField(self::PATH, 'import_source', 'path', $translate->_('feg.import_source.path')),
			self::TYPE => new DevblocksSearchField(self::TYPE, 'import_source', 'type', $translate->_('feg.import_source.type')),
			self::IS_DISABLED => new DevblocksSearchField(self::IS_DISABLED, 'import_source', 'is_disabled', $translate->_('feg.import_source.is_disabled')),
		);
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};


class View_ImportSource extends FEG_AbstractView {
	const DEFAULT_ID = 'import_source';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		$this->name = $translate->_('feg.import_source.default_name');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_ImportSource::NAME;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_ImportSource::ID,
			SearchFields_ImportSource::NAME,
			SearchFields_ImportSource::PATH,
			SearchFields_ImportSource::TYPE,
			SearchFields_ImportSource::IS_DISABLED,
		);
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_ImportSource::search(
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
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/tabs/import_source/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		switch($field) {
			case SearchFields_ImportSource::NAME:
			case SearchFields_ImportSource::PATH:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_ImportSource::ID:
			case SearchFields_ImportSource::TYPE:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
			case SearchFields_ImportSource::IS_DISABLED:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
				break;
//			case 'placeholder_date':
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__date.tpl');
//				break;
			default:
				// Custom Fields
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
		return SearchFields_ImportSource::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		// [TODO] Filter fields
		// unset($fields[SearchFields_ImportSource::ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		// [TODO] Filter fields
		//	unset($fields[SearchFields_ImportSource::ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
		//SearchFields_ImportSource::ID => new DevblocksSearchCriteria(SearchFields_ImportSource::ID,'!=',0),
		);
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_ImportSource::NAME:
			case SearchFields_ImportSource::PATH:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
			case SearchFields_ImportSource::ID:
			case SearchFields_ImportSource::TYPE:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
//			case 'placeholder_date':
//				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
//				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

//				if(empty($from)) $from = 0;
//				if(empty($to)) $to = 'today';

//				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
//				break;
				
			case SearchFields_ImportSource::IS_DISABLED:
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
				//$change_fields[DAO_ImportSource::ID] = intval($v);
				//$change_fields[DAO_ImportSource::NAME] = intval($v);
				//$change_fields[DAO_ImportSource::PATH] = intval($v);
				//$change_fields[DAO_ImportSource::TYPE] = intval($v);
				//$change_fields[DAO_ImportSource::IS_DISABLED] = intval($v);
				// [TODO] Implement actions
				case 'example':
					//$change_fields[DAO_ImportSource::EXAMPLE] = 'some value';
					break;
				default:
					break;
			}
		}
		
		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_ImportSource::search(
				$this->params,
				100,
				$pg++,
				SearchFields_ImportSource::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_ImportSource::update($batch_ids, $change_fields);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};
