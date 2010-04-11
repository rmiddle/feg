<?php

class Model_ImportFilter {
	public $id;
	public $filter_name;
	public $filter_folder;
	public $is_disabled;
	public $filter;
};

class DAO_ImportFilter extends DevblocksORMHelper  {
	const CACHE_ALL = 'feg_ifilter';
	
	const ID = 'id';
	const FILTER_NAME = 'filter_name';
	const FILTER_FOLDER = 'filter_folder';
	const IS_DISABLED = 'is_disabled';
	const FILTER = 'filter';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('filter_seq');
		
		$sql = sprintf("INSERT INTO import_filter (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'import_filter', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('import_filter', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_ImportFilter[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, filter_name, is_disabled, filter ".
			"FROM import_filter ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_ImportFilter	 */
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
	 * @return Model_ImportFilter[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_ImportFilter();
			$object->id = $row['id'];
			$object->filter_name = $row['filter_name'];
			$object->filter_folder = $row['filter_folder'];
			$object->is_disabled = $row['is_disabled'];
			$object->filter = $row['filter'];
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
		
		$db->Execute(sprintf("DELETE FROM import_filter WHERE id IN (%s)", $ids_list));
		
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
		$fields = SearchFields_ImportFilter::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"import_filter.id as %s, ".
			"import_filter.filter_name as %s, ".
			"import_filter.filter_folder as %s, ".
			"import_filter.is_disabled as %s, ".
			"import_filter.filter as %s ",
				SearchFields_ImportFilter::ID,
				SearchFields_ImportFilter::FILTER_NAME,
				SearchFields_ImportFilter::FILTER_FOLDER,
				SearchFields_ImportFilter::IS_DISABLED,
				SearchFields_ImportFilter::FILTER
			);
			
		$join_sql = "FROM import_filter ";
		
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";
			
		$sql = 
			$select_sql.
			$join_sql.
			$where_sql.
			($has_multiple_values ? 'GROUP BY import_filter.id ' : '').
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
			$object_id = intval($row[SearchFields_ImportFilter::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT import_filter.id) " : "SELECT COUNT(import_filter.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

	static function getAll($nocache=false, $with_disabled=false) {
	    $cache = DevblocksPlatform::getCacheService();
	    if($nocache || null === ($import_filters = $cache->load(self::CACHE_ALL))) {
    	    $import_filters = self::getWhere();
    	    $cache->save($import_filters, self::CACHE_ALL);
	    }
	    
	    /*
	     * Generally we don't want disabled filters so ignore those.
	     * but don't bother caching two different versions (always cache all)
	     */
	    if(!$with_disabled) {
	    	foreach($import_filters as $import_filter_id => $import_filter) { 
	    		if($import_filter->is_disabled)
	    			unset($import_filters[$import_filter_id]);
	    	}
	    }
	    
	    return $import_filters;
	}	
	
};


class SearchFields_ImportFilter implements IDevblocksSearchFields {
	const ID = 'i_id';
	const FILTER_NAME = 'i_filter_name';
	const FILTER_NAME = 'i_filter_folder';
	const IS_DISABLED = 'i_is_disabled';
	const FILTER = 'i_filter';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'import_filter', 'id', $translate->_('id')),
			self::FILTER_NAME => new DevblocksSearchField(self::FILTER_NAME, 'import_filter', 'filter_name', $translate->_('filter_name')),
			self::FILTER_FOLDER => new DevblocksSearchField(self::FILTER_FOLDER, 'import_filter', 'filter_folder', $translate->_('filter_folder')),
			self::IS_DISABLED => new DevblocksSearchField(self::IS_DISABLED, 'import_filter', 'is_disabled', $translate->_('is_disabled')),
			self::FILTER => new DevblocksSearchField(self::FILTER, 'import_filter', 'filter', $translate->_('filter')),
		);
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};


class View_ImportFilter extends Feg_AbstractView {
	const DEFAULT_ID = 'importfilter';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('import_filter.name');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_ImportFilter::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_ImportFilter::ID,
			SearchFields_ImportFilter::FILTER_NAME,
			SearchFields_ImportFilter::FILTER_FOLDER,
			SearchFields_ImportFilter::IS_DISABLED,
			SearchFields_ImportFilter::FILTER,
		);
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_ImportFilter::search(
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
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/import_filter/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		switch($field) {
			case SearchFields_ImportFilter::FILTER_NAME:
			case SearchFields_ImportFilter::FILTER_FOLDER:
			case SearchFields_ImportFilter::FILTER:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_ImportFilter::ID:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
			case SearchFields_ImportFilter::IS_DISABLED:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
				break;
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
		return SearchFields_ImportFilter::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		// [TODO] Filter fields
//		unset($fields[SearchFields_ImportFilter::ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		// [TODO] Filter fields
		//	unset($fields[SearchFields_ImportFilter::ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
		//SearchFields_ImportFilter::ID => new DevblocksSearchCriteria(SearchFields_ImportFilter::ID,'!=',0),
		);
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_ImportFilter::FILTER_NAME:
			case SearchFields_ImportFilter::FILTER_FOLDER:
			case SearchFields_ImportFilter::FILTER:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
			case SearchFields_ImportFilter::ID:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
//			case 'placeholder_date':
//				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
//				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

//				if(empty($from)) $from = 0;
//				if(empty($to)) $to = 'today';

//				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
//				break;
				
			case SearchFields_ImportFilter::IS_DISABLED:
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
//			$change_fields[DAO_ImportFilter::ID] = intval($v);
//			$change_fields[DAO_ImportFilter::FILTER_NAME] = intval($v);
//			$change_fields[DAO_ImportFilter::FILTER_FOLDER] = intval($v);
//			$change_fields[DAO_ImportFilter::IS_DISABLED] = intval($v);
//			$change_fields[DAO_ImportFilter::FILTER] = intval($v);
				// [TODO] Implement actions
				case 'example':
					//$change_fields[DAO_ImportFilter::EXAMPLE] = 'some value';
					break;
				default:
					break;
			}
		}
		
		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_ImportFilter::search(
				$this->params,
				100,
				$pg++,
				SearchFields_ImportFilter::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_ImportFilter::update($batch_ids, $change_fields);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};
