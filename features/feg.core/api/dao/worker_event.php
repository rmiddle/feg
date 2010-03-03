<?php

class Model_WorkerEvent {
	public $id;
	public $created_date;
	public $worker_id;
	public $title;
	public $content;
	public $is_read;
	public $url;
};

class DAO_WorkerEvent extends DevblocksORMHelper {
	const CACHE_COUNT_PREFIX = 'workerevent_count_';
	
	const ID = 'id';
	const CREATED_DATE = 'created_date';
	const WORKER_ID = 'worker_id';
	const TITLE = 'title';
	const CONTENT = 'content';
	const IS_READ = 'is_read';
	const URL = 'url';

	public static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		return array(
			'id' => $translate->_('worker_event.id'),
			'created_date' => $translate->_('worker_event.created_date'),
			'worker_id' => $translate->_('worker_event.worker_id'),
			'title' => $translate->_('worker_event.title'),
			'content' => $translate->_('worker_event.content'),
			'is_read' => $translate->_('worker_event.is_read'),
			'url' => $translate->_('worker_event.url'),
		);
	}
	
	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('worker_event_seq');
		
		$sql = sprintf("INSERT INTO worker_event (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		// Invalidate the worker notification count cache
		if(isset($fields[self::WORKER_ID])) {
			$cache = DevblocksPlatform::getCacheService();
			self::clearCountCache($fields[self::WORKER_ID]);
		}
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'worker_event', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('worker_event', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @return Model_WorkerEvent[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, created_date, worker_id, title, content, is_read, url ".
			"FROM worker_event ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_WorkerEvent	 */
	static function get($id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::ID,
			$id
		));
		
		if(isset($objects[$id]))
			return $objects[$id];
		
		return null;
	}
	
	static function getUnreadCountByWorker($worker_id) {
		$db = DevblocksPlatform::getDatabaseService();
		$cache = DevblocksPlatform::getCacheService();
		
	    if(null === ($count = $cache->load(self::CACHE_COUNT_PREFIX.$worker_id))) {
			$sql = sprintf("SELECT count(*) ".
				"FROM worker_event ".
				"WHERE worker_id = %d ".
				"AND is_read = 0",
				$worker_id
			);
			
			$count = $db->GetOne($sql);
			$cache->save($count, self::CACHE_COUNT_PREFIX.$worker_id);
	    }
		
		return intval($count);
	}
	
	/**
	 * @param resource $rs
	 * @return Model_WorkerEvent[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_WorkerEvent();
			$object->id = $row['id'];
			$object->created_date = $row['created_date'];
			$object->worker_id = $row['worker_id'];
			$object->title = $row['title'];
			$object->url = $row['url'];
			$object->content = $row['content'];
			$object->is_read = $row['is_read'];
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
		
		$db->Execute(sprintf("DELETE FROM worker_event WHERE id IN (%s)", $ids_list));
		
		return true;
	}

	static function clearCountCache($worker_id) {
		$cache = DevblocksPlatform::getCacheService();
		$cache->remove(self::CACHE_COUNT_PREFIX.$worker_id);
	}

    /**
     * Enter description here...
     *
     * @param DevblocksSearchCriteria[] $params
     * @param integer $limit
     * @param integer $page
     * @param string $sortBy
     * @param boolean $sortAsc
     * @param boolean $withCounts
     * @return array
     */
    static function search($params, $limit=10, $page=0, $sortBy=null, $sortAsc=null, $withCounts=true) {
		$db = DevblocksPlatform::getDatabaseService();
		$fields = SearchFields_WorkerEvent::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, array(),$fields,$sortBy);
		$start = ($page * $limit); // [JAS]: 1-based [TODO] clean up + document
		$total = -1;
		
		$sql = sprintf("SELECT ".
			"we.id as %s, ".
			"we.created_date as %s, ".
			"we.worker_id as %s, ".
			"we.title as %s, ".
			"we.content as %s, ".
			"we.is_read as %s, ".
			"we.url as %s ".
			"FROM worker_event we ",
//			"INNER JOIN team tm ON (tm.id = t.team_id) ".
			    SearchFields_WorkerEvent::ID,
			    SearchFields_WorkerEvent::CREATED_DATE,
			    SearchFields_WorkerEvent::WORKER_ID,
			    SearchFields_WorkerEvent::TITLE,
			    SearchFields_WorkerEvent::CONTENT,
			    SearchFields_WorkerEvent::IS_READ,
			    SearchFields_WorkerEvent::URL
			).
			
			// [JAS]: Dynamic table joins
//			(isset($tables['ra']) ? "INNER JOIN requester r ON (r.ticket_id=t.id)" : " ").
			
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "").
			(!empty($sortBy) ? sprintf("ORDER BY %s %s",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : "")
		;
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
			$ticket_id = intval($row[SearchFields_WorkerEvent::ID]);
			$results[$ticket_id] = $result;
		}
		
		// [JAS]: Count all
		if($withCounts) {
		    $rs = $db->Execute($sql);
		    $total = mysql_num_rows($rs);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
    }
	
};

class SearchFields_WorkerEvent implements IDevblocksSearchFields {
	// Worker Event
	const ID = 'we_id';
	const CREATED_DATE = 'we_created_date';
	const WORKER_ID = 'we_worker_id';
	const TITLE = 'we_title';
	const CONTENT = 'we_content';
	const IS_READ = 'we_is_read';
	const URL = 'we_url';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'we', 'id', $translate->_('worker_event.id')),
			self::CREATED_DATE => new DevblocksSearchField(self::CREATED_DATE, 'we', 'created_date', $translate->_('worker_event.created_date')),
			self::WORKER_ID => new DevblocksSearchField(self::WORKER_ID, 'we', 'worker_id', $translate->_('worker_event.worker_id')),
			self::TITLE => new DevblocksSearchField(self::TITLE, 'we', 'title', $translate->_('worker_event.title')),
			self::CONTENT => new DevblocksSearchField(self::CONTENT, 'we', 'content', $translate->_('worker_event.content')),
			self::IS_READ => new DevblocksSearchField(self::IS_READ, 'we', 'is_read', $translate->_('worker_event.is_read')),
			self::URL => new DevblocksSearchField(self::URL, 'we', 'url', $translate->_('common.url')),
		);
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};

class View_WorkerEvent extends Feg_AbstractView {
	const DEFAULT_ID = 'worker_events';

	function __construct() {
		$this->id = self::DEFAULT_ID;
		$this->name = 'Worker Events';
		$this->renderLimit = 100;
		$this->renderSortBy = SearchFields_WorkerEvent::CREATED_DATE;
		$this->renderSortAsc = false;

		$this->view_columns = array(
			SearchFields_WorkerEvent::CONTENT,
			SearchFields_WorkerEvent::CREATED_DATE,
		);
		
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_WorkerEvent::search(
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

		$workers = DAO_Worker::getAll();
		$tpl->assign('workers', $workers);
		
		$tpl->assign('view_fields', $this->getColumns());
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/home/tabs/my_notifications/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		switch($field) {
			case SearchFields_WorkerEvent::TITLE:
			case SearchFields_WorkerEvent::CONTENT:
			case SearchFields_WorkerEvent::URL:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
//			case SearchFields_WorkerEvent::ID:
//			case SearchFields_WorkerEvent::MESSAGE_ID:
//			case SearchFields_WorkerEvent::TICKET_ID:
//			case SearchFields_WorkerEvent::FILE_SIZE:
//				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__number.tpl');
//				break;
			case SearchFields_WorkerEvent::IS_READ:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
				break;
			case SearchFields_WorkerEvent::CREATED_DATE:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__date.tpl');
				break;
			case SearchFields_WorkerEvent::WORKER_ID:
				$workers = DAO_Worker::getAllActive();
				$tpl->assign('workers', $workers);
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__worker.tpl');
				break;
			default:
				echo '';
				break;
		}
	}

	function renderCriteriaParam($param) {
		$field = $param->field;
		$values = !is_array($param->value) ? array($param->value) : $param->value;

		switch($field) {
			case SearchFields_WorkerEvent::WORKER_ID:
				$workers = DAO_Worker::getAll();
				$strings = array();

				foreach($values as $val) {
					if(empty($val))
					$strings[] = "Nobody";
					elseif(!isset($workers[$val]))
					continue;
					else
					$strings[] = $workers[$val]->getName();
				}
				echo implode(", ", $strings);
				break;
			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	static function getFields() {
		return SearchFields_WorkerEvent::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		unset($fields[SearchFields_WorkerEvent::ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
//		$this->params = array(
//			SearchFields_WorkerEvent::NUM_NONSPAM => new DevblocksSearchCriteria(SearchFields_WorkerEvent::NUM_NONSPAM,'>',0),
//		);
	}
	
	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_WorkerEvent::TITLE:
			case SearchFields_WorkerEvent::CONTENT:
			case SearchFields_WorkerEvent::URL:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
			case SearchFields_WorkerEvent::WORKER_ID:
				@$worker_ids = DevblocksPlatform::importGPC($_REQUEST['worker_id'],'array',array());
				$criteria = new DevblocksSearchCriteria($field,$oper,$worker_ids);
				break;
				
			case SearchFields_WorkerEvent::CREATED_DATE:
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from)) $from = 0;
				if(empty($to)) $to = 'today';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;
				
			case SearchFields_WorkerEvent::IS_READ:
				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
				break;
		}

		if(!empty($criteria)) {
			$this->params[$field] = $criteria;
			$this->renderPage = 0;
		}
	}

//	function doBulkUpdate($filter, $do, $ids=array()) {
//		@set_time_limit(600); // [TODO] Temp!
//	  
//		$change_fields = array();
//
//		if(empty($do))
//		return;
//
//		if(is_array($do))
//		foreach($do as $k => $v) {
//			switch($k) {
//				case 'banned':
//					$change_fields[DAO_Address::IS_BANNED] = intval($v);
//					break;
//			}
//		}
//
//		$pg = 0;
//
//		if(empty($ids))
//		do {
//			list($objects,$null) = DAO_Address::search(
//			$this->params,
//			100,
//			$pg++,
//			SearchFields_Address::ID,
//			true,
//			false
//			);
//			 
//			$ids = array_merge($ids, array_keys($objects));
//			 
//		} while(!empty($objects));
//
//		$batch_total = count($ids);
//		for($x=0;$x<=$batch_total;$x+=100) {
//			$batch_ids = array_slice($ids,$x,100);
//			DAO_Address::update($batch_ids, $change_fields);
//			unset($batch_ids);
//		}
//
//		unset($ids);
//	}

};

