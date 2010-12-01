<?php

class Model_Stats {
	public $id;
	public $fax_current_hour;
	public $fax_last_hour;
	public $fax_sent_today;
	public $fax_sent_yesterday;
	public $email_current_hour;
	public $email_last_hour;
	public $email_sent_today;
	public $email_sent_yesterday;
	public $snpp_current_hour;
	public $snpp_last_hour;
	public $snpp_sent_today;
	public $snpp_sent_yesterday;
};

class DAO_Stats extends Feg_ORMHelper {
	const ID = 'id';
	const FAX_CURRENT_HOUR = 'fax_current_hour';
	const FAX_LAST_HOUR = 'fax_last_hour';
	const FAX_SENT_TODAY = 'fax_sent_today';
	const FAX_SENT_YESTERDAY = 'fax_sent_yesterday';
	const EMAIL_CURRENT_HOUR = 'email_current_hour';
	const EMAIL_LAST_HOUR = 'email_last_hour';
	const EMAIL_SENT_TODAY = 'email_sent_today';
	const EMAIL_SENT_YESTERDAY = 'email_sent_yesterday';
	const SNPP_CURRENT_HOUR = 'snpp_current_hour';
	const SNPP_LAST_HOUR = 'snpp_last_hour';
	const SNPP_SENT_TODAY = 'snpp_sent_today';
	const SNPP_SENT_YESTERDAY = 'snpp_sent_yesterday';

/*
	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO stats (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
*/	
	static function update($ids, $fields) {
		parent::_update($ids, 'stats', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('stats', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_Stats[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, fax_current_hour, fax_last_hour, fax_sent_today, fax_sent_yesterday, email_current_hour, email_last_hour, email_sent_today, email_sent_yesterday, snpp_current_hour, snpp_last_hour, snpp_sent_today, snpp_sent_yesterday ".
			"FROM stats ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_Stats	 */
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
	 * @return Model_Stats[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_Stats();
			$object->id = $row['id'];
			$object->fax_current_hour = $row['fax_current_hour'];
			$object->fax_last_hour = $row['fax_last_hour'];
			$object->fax_sent_today = $row['fax_sent_today'];
			$object->fax_sent_yesterday = $row['fax_sent_yesterday'];
			$object->email_current_hour = $row['email_current_hour'];
			$object->email_last_hour = $row['email_last_hour'];
			$object->email_sent_today = $row['email_sent_today'];
			$object->email_sent_yesterday = $row['email_sent_yesterday'];
			$object->snpp_current_hour = $row['snpp_current_hour'];
			$object->snpp_last_hour = $row['snpp_last_hour'];
			$object->snpp_sent_today = $row['snpp_sent_today'];
			$object->snpp_sent_yesterday = $row['snpp_sent_yesterday'];
			$objects[$object->id] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}

/*
	static function delete($ids) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		
		if(empty($ids))
			return;
		
		$ids_list = implode(',', $ids);
		
		$db->Execute(sprintf("DELETE FROM stats WHERE id IN (%s)", $ids_list));
		
		return true;
	}
*/

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
		$fields = SearchFields_Stats::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"stats.id as %s, ".
			"stats.fax_current_hour as %s, ".
			"stats.fax_last_hour as %s, ".
			"stats.fax_sent_today as %s, ".
			"stats.fax_sent_yesterday as %s, ".
			"stats.email_current_hour as %s, ".
			"stats.email_last_hour as %s, ".
			"stats.email_sent_today as %s, ".
			"stats.email_sent_yesterday as %s, ".
			"stats.snpp_current_hour as %s, ".
			"stats.snpp_last_hour as %s, ".
			"stats.snpp_sent_today as %s, ".
			"stats.snpp_sent_yesterday as %s ",
				SearchFields_Stats::ID,
				SearchFields_Stats::FAX_CURRENT_HOUR,
				SearchFields_Stats::FAX_LAST_HOUR,
				SearchFields_Stats::FAX_SENT_TODAY,
				SearchFields_Stats::FAX_SENT_YESTERDAY,
				SearchFields_Stats::EMAIL_CURRENT_HOUR,
				SearchFields_Stats::EMAIL_LAST_HOUR,
				SearchFields_Stats::EMAIL_SENT_TODAY,
				SearchFields_Stats::EMAIL_SENT_YESTERDAY,
				SearchFields_Stats::SNPP_CURRENT_HOUR,
				SearchFields_Stats::SNPP_LAST_HOUR,
				SearchFields_Stats::SNPP_SENT_TODAY,
				SearchFields_Stats::SNPP_SENT_YESTERDAY
			);
			
		$join_sql = "FROM stats ";
		
		// Custom field joins
		list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
			$tables,
			$params,
			'stats.id',
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
			($has_multiple_values ? 'GROUP BY stats.id ' : '').
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
			$object_id = intval($row[SearchFields_Stats::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT stats.id) " : "SELECT COUNT(stats.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};


class SearchFields_Stats implements IDevblocksSearchFields {
	const ID = 'stats_id';
	const FAX_CURRENT_HOUR = 'stats_fax_current_hour';
	const FAX_LAST_HOUR = 'stats_fax_last_hour';
	const FAX_SENT_TODAY = 'stats_fax_sent_today';
	const FAX_SENT_YESTERDAY = 'stats_fax_sent_yesterday';
	const EMAIL_CURRENT_HOUR = 'stats_email_current_hour';
	const EMAIL_LAST_HOUR = 'stats_email_last_hour';
	const EMAIL_SENT_TODAY = 'stats_email_sent_today';
	const EMAIL_SENT_YESTERDAY = 'stats_email_sent_yesterday';
	const SNPP_CURRENT_HOUR = 'stats_snpp_current_hour';
	const SNPP_LAST_HOUR = 'stats_snpp_last_hour';
	const SNPP_SENT_TODAY = 'stats_snpp_sent_today';
	const SNPP_SENT_YESTERDAY = 'stats_snpp_sent_yesterday';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'stats', 'id', $translate->_('feg.stats.id')),
			self::FAX_CURRENT_HOUR => new DevblocksSearchField(self::FAX_CURRENT_HOUR, 'stats', 'fax_current_hour', $translate->_('feg.stats.fax_current_hour')),
			self::FAX_LAST_HOUR => new DevblocksSearchField(self::FAX_LAST_HOUR, 'stats', 'fax_last_hour', $translate->_('feg.stats.fax_last_hour')),
			self::FAX_SENT_TODAY => new DevblocksSearchField(self::FAX_SENT_TODAY, 'stats', 'fax_sent_today', $translate->_('feg.stats.fax_sent_today')),
			self::FAX_SENT_YESTERDAY => new DevblocksSearchField(self::FAX_SENT_YESTERDAY, 'stats', 'fax_sent_yesterday', $translate->_('feg.stats.fax_sent_yesterday')),
			self::EMAIL_CURRENT_HOUR => new DevblocksSearchField(self::EMAIL_CURRENT_HOUR, 'stats', 'email_current_hour', $translate->_('feg.stats.email_current_hour')),
			self::EMAIL_LAST_HOUR => new DevblocksSearchField(self::EMAIL_LAST_HOUR, 'stats', 'email_last_hour', $translate->_('feg.stats.email_last_hour')),
			self::EMAIL_SENT_TODAY => new DevblocksSearchField(self::EMAIL_SENT_TODAY, 'stats', 'email_sent_today', $translate->_('feg.stats.email_sent_today')),
			self::EMAIL_SENT_YESTERDAY => new DevblocksSearchField(self::EMAIL_SENT_YESTERDAY, 'stats', 'email_sent_yesterday', $translate->_('feg.stats.email_sent_yesterday')),
			self::SNPP_CURRENT_HOUR => new DevblocksSearchField(self::SNPP_CURRENT_HOUR, 'stats', 'snpp_current_hour', $translate->_('feg.stats.snpp_current_hour')),
			self::SNPP_LAST_HOUR => new DevblocksSearchField(self::SNPP_LAST_HOUR, 'stats', 'snpp_last_hour', $translate->_('feg.stats.snpp_last_hour')),
			self::SNPP_SENT_TODAY => new DevblocksSearchField(self::SNPP_SENT_TODAY, 'stats', 'snpp_sent_today', $translate->_('feg.stats.snpp_sent_today')),
			self::SNPP_SENT_YESTERDAY => new DevblocksSearchField(self::SNPP_SENT_YESTERDAY, 'stats', 'snpp_sent_yesterday', $translate->_('feg.stats.snpp_sent_yesterday')),
		);
		
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


class View_Stats extends FEG_AbstractView {
	const DEFAULT_ID = 'stats';
	
	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('feg.stats.default_name');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_Stats::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_Stats::ID,
			SearchFields_Stats::FAX_CURRENT_HOUR,
			SearchFields_Stats::FAX_LAST_HOUR,
			SearchFields_Stats::FAX_SENT_TODAY,
			SearchFields_Stats::FAX_SENT_YESTERDAY,
			SearchFields_Stats::EMAIL_CURRENT_HOUR,
			SearchFields_Stats::EMAIL_LAST_HOUR,
			SearchFields_Stats::EMAIL_SENT_TODAY,
			SearchFields_Stats::EMAIL_SENT_YESTERDAY,
			SearchFields_Stats::SNPP_CURRENT_HOUR,
			SearchFields_Stats::SNPP_LAST_HOUR,
			SearchFields_Stats::SNPP_SENT_TODAY,
			SearchFields_Stats::SNPP_SENT_YESTERDAY,
		);
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_Stats::search(
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

		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_Stats::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$tpl->assign('view_fields', $this->getColumns());
		// [TODO] Set your template path
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/stats/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		$tpl_path = APP_PATH . '/features/feg.core/templates/';
		
		// [TODO] Move the fields into the proper data type
		switch($field) {
//			case 'placeholder_string':
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
//				break;
			case SearchFields_Stats::ID:
			case SearchFields_Stats::FAX_CURRENT_HOUR:
			case SearchFields_Stats::FAX_LAST_HOUR:
			case SearchFields_Stats::FAX_SENT_TODAY:
			case SearchFields_Stats::FAX_SENT_YESTERDAY:
			case SearchFields_Stats::EMAIL_CURRENT_HOUR:
			case SearchFields_Stats::EMAIL_LAST_HOUR:
			case SearchFields_Stats::EMAIL_SENT_TODAY:
			case SearchFields_Stats::EMAIL_SENT_YESTERDAY:
			case SearchFields_Stats::SNPP_CURRENT_HOUR:
			case SearchFields_Stats::SNPP_LAST_HOUR:
			case SearchFields_Stats::SNPP_SENT_TODAY:
			case SearchFields_Stats::SNPP_SENT_YESTERDAY:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
				break;
//			case 'placeholder_bool':
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
				break;
//			case 'placeholder_date':
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__date.tpl');
				break;
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
		return SearchFields_Stats::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		// [TODO] Filter fields
		unset($fields[SearchFields_Stats::ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		// [TODO] Filter fields
		unset($fields[SearchFields_Stats::ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
		//SearchFields_Stats::ID => new DevblocksSearchCriteria(SearchFields_Stats::ID,'!=',0),
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
			case SearchFields_Stats::ID:
			case SearchFields_Stats::FAX_CURRENT_HOUR:
			case SearchFields_Stats::FAX_LAST_HOUR:
			case SearchFields_Stats::FAX_SENT_TODAY:
			case SearchFields_Stats::FAX_SENT_YESTERDAY:
			case SearchFields_Stats::EMAIL_CURRENT_HOUR:
			case SearchFields_Stats::EMAIL_LAST_HOUR:
			case SearchFields_Stats::EMAIL_SENT_TODAY:
			case SearchFields_Stats::EMAIL_SENT_YESTERDAY:
			case SearchFields_Stats::SNPP_CURRENT_HOUR:
			case SearchFields_Stats::SNPP_LAST_HOUR:
			case SearchFields_Stats::SNPP_SENT_TODAY:
			case SearchFields_Stats::SNPP_SENT_YESTERDAY:
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
				// Custom Fields
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
				// [TODO] Implement actions
				case 'example':
				// [TODO] Used for bulk update
			$change_fields[DAO_Stats::ID] = intval($v);
			$change_fields[DAO_Stats::FAX_CURRENT_HOUR] = intval($v);
			$change_fields[DAO_Stats::FAX_LAST_HOUR] = intval($v);
			$change_fields[DAO_Stats::FAX_SENT_TODAY] = intval($v);
			$change_fields[DAO_Stats::FAX_SENT_YESTERDAY] = intval($v);
			$change_fields[DAO_Stats::EMAIL_CURRENT_HOUR] = intval($v);
			$change_fields[DAO_Stats::EMAIL_LAST_HOUR] = intval($v);
			$change_fields[DAO_Stats::EMAIL_SENT_TODAY] = intval($v);
			$change_fields[DAO_Stats::EMAIL_SENT_YESTERDAY] = intval($v);
			$change_fields[DAO_Stats::SNPP_CURRENT_HOUR] = intval($v);
			$change_fields[DAO_Stats::SNPP_LAST_HOUR] = intval($v);
			$change_fields[DAO_Stats::SNPP_SENT_TODAY] = intval($v);
			$change_fields[DAO_Stats::SNPP_SENT_YESTERDAY] = intval($v);
					//$change_fields[DAO_Stats::EXAMPLE] = 'some value';
					break;
				default:
			}
		}
		
		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_Stats::search(
				$this->params,
				100,
				$pg++,
				SearchFields_Stats::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_Stats::update($batch_ids, $change_fields);
			
			// Custom Fields
			//self::_doBulkSetCustomFields(FegCustomFieldSource_Stats::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}	
};
