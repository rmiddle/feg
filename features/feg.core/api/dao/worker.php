<?php

class FegCustomFieldSource_Worker extends Extension_CustomFieldSource {
	const ID = 'feg.fields.source.worker';
};

class Model_Worker {
	public $id;
	public $first_name;
	public $last_name;
	public $title;
	public $email;
	public $pass;
	public $is_superuser;
	public $last_activity_date;
	public $last_activity;
	public $is_disabled;
	
	function hasPriv($priv_id) {
		// We don't need to do much work if we're a superuser
		if($this->is_superuser)
			return true;
		
		$settings = DevblocksPlatform::getPluginSettingsService();
		$acl_enabled = $settings->get('feg.core',FegSettings::ACL_ENABLED);
			
		// ACL is a paid feature (please respect the licensing and support the project!)
		$license = FegLicense::getInstance();
		if(!$acl_enabled || !isset($license['serial']) || isset($license['a']))
			return ("core.setup"==substr($priv_id,0,11)) ? false : true;
			
		// Check the aggregated worker privs from roles
		$acl = DAO_WorkerRole::getACL();
		$privs_by_worker = $acl[DAO_WorkerRole::CACHE_KEY_PRIVS_BY_WORKER];
		
		if(!empty($priv_id) && isset($privs_by_worker[$this->id][$priv_id]))
			return true;
			
		return false;
	}
	
	function getName($reverse=false) {
		if(!$reverse) {
			$name = sprintf("%s%s%s",
				$this->first_name,
				(!empty($this->first_name) && !empty($this->last_name)) ? " " : "",
				$this->last_name
			);
		} else {
			$name = sprintf("%s%s%s",
				$this->last_name,
				(!empty($this->first_name) && !empty($this->last_name)) ? ", " : "",
				$this->first_name
			);
		}
		
		return $name;
	}
};

class Model_WorkerRole {
	public $id;
	public $name;
};

class DAO_Worker extends Feg_ORMHelper {
	const CACHE_ALL = 'ps_workers';
	
	const ID = 'id';
	const FIRST_NAME = 'first_name';
	const LAST_NAME = 'last_name';
	const TITLE = 'title';
	const EMAIL = 'email';
	const PASS = 'pass';
	const IS_SUPERUSER = 'is_superuser';
	const LAST_ACTIVITY_DATE = 'last_activity_date';
	const LAST_ACTIVITY = 'last_activity';
	const IS_DISABLED = 'is_disabled';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('worker_seq');
		
		$sql = sprintf("INSERT INTO worker (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields, $flush_cache=true) {
		parent::_update($ids, 'worker', $fields);
		
		if($flush_cache) {
			self::clearCache();
		}
	}
	
	static function clearCache() {
		$cache = DevblocksPlatform::getCacheService();
		$cache->remove(self::CACHE_ALL);
	}
	
	static function getAllActive() {
		return self::getAll(false, false);
	}
	
	static function getAllWithDisabled() {
		return self::getAll(false, true);
	}
	
	static function getAllOnline() {
		list($whos_online_workers, $null) = self::search(
			array(),
		    array(
		        new DevblocksSearchCriteria(SearchFields_Worker::LAST_ACTIVITY_DATE,DevblocksSearchCriteria::OPER_GT,(time()-60*15)), // idle < 15 mins
		        new DevblocksSearchCriteria(SearchFields_Worker::LAST_ACTIVITY,DevblocksSearchCriteria::OPER_NOT_LIKE,'%translation_code";N;%'), // translation code not null (not just logged out)
		    ),
		    -1,
		    0,
		    SearchFields_Worker::LAST_ACTIVITY_DATE,
		    false,
		    false
		);
		
		if(!empty($whos_online_workers))
			return self::getWhere(
				sprintf("%s IN (%s)",
					DAO_Worker::ID,
					implode(',',array_keys($whos_online_workers))
				));
			
		return array();
	}
	
	static function getAll($nocache=false, $with_disabled=true) {
	    $cache = DevblocksPlatform::getCacheService();
	    if($nocache || null === ($workers = $cache->load(self::CACHE_ALL))) {
    	    $workers = self::getWhere();
    	    $cache->save($workers, self::CACHE_ALL);
	    }
	    
	    /*
	     * If the caller doesn't want disabled workers then remove them from the results,
	     * but don't bother caching two different versions (always cache all)
	     */
	    if(!$with_disabled) {
	    	foreach($workers as $worker_id => $worker) { /* @var $worker CerberusWorker */
	    		if($worker->is_disabled)
	    			unset($workers[$worker_id]);
	    	}
	    }
	    
	    return $workers;
	}	
	
	/**
	 * @param string $where
	 * @return Model_Worker[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, first_name, last_name, title, email, pass, is_superuser, last_activity_date, last_activity, is_disabled ".
			"FROM worker ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_Worker	 */
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
	 * @return Model_Worker[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_Worker();
			$object->id = $row['id'];
			$object->first_name = $row['first_name'];
			$object->last_name = $row['last_name'];
			$object->title = $row['title'];
			$object->email = $row['email'];
			$object->pass = $row['pass'];
			$object->is_superuser = $row['is_superuser'];
			$object->last_activity_date = $row['last_activity_date'];
			$object->is_disabled = $row['is_disabled'];

			if(!empty($row['last_activity']))
			    $object->last_activity = unserialize($row['last_activity']);
			
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
		
		$db->Execute(sprintf("DELETE FROM worker WHERE id IN (%s)", $ids_list));
		
		self::clearCache();
		
		return true;
	}
	
	static function login($email, $password) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$where = sprintf("%s = %s AND %s = %s",
				self::EMAIL,
				$db->qstr($email),
				self::PASS,
				$db->qstr(md5($password))
			);
		
		$results = self::getWhere($where);
		
		if(!empty($results))
			return array_shift($results);
			
		return NULL;
	}
	
	/**
	 * Store the workers last activity (provided by the page extension).
	 * 
	 * @param integer $worker_id
	 * @param Model_Activity $activity
	 */
	static function logActivity($worker_id, Model_Activity $activity) {
	    DAO_Worker::update($worker_id,array(
	        DAO_Worker::LAST_ACTIVITY_DATE => time(),
	        DAO_Worker::LAST_ACTIVITY => serialize($activity)
	    ),false);
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
    static function search($columns, $params, $limit=10, $page=0, $sortBy=null, $sortAsc=null, $withCounts=true) {
		$db = DevblocksPlatform::getDatabaseService();
		$fields = SearchFields_Worker::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		$start = ($page * $limit); // [JAS]: 1-based [TODO] clean up + document
		$total = -1;
		
		$select_sql = sprintf("SELECT ".
			"w.id as %s, ".
			"w.first_name as %s, ".
			"w.last_name as %s, ".
			"w.title as %s, ".
			"w.email as %s, ".
			"w.is_superuser as %s, ".
			"w.last_activity_date as %s, ".
			"w.is_disabled as %s ",
			    SearchFields_Worker::ID,
			    SearchFields_Worker::FIRST_NAME,
			    SearchFields_Worker::LAST_NAME,
			    SearchFields_Worker::TITLE,
			    SearchFields_Worker::EMAIL,
			    SearchFields_Worker::IS_SUPERUSER,
			    SearchFields_Worker::LAST_ACTIVITY_DATE,
			    SearchFields_Worker::IS_DISABLED
			);
			
		$join_sql = "FROM worker w ";
		
		// Custom field joins
		list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
			$tables,
			$params,
			'w.id',
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
			($has_multiple_values ? 'GROUP BY w.id ' : '').
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
			$object_id = intval($row[SearchFields_Worker::ID]);
			$results[$object_id] = $result;
		}
		
		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT w.id) " : "SELECT COUNT(w.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
    }

};

class SearchFields_Worker implements IDevblocksSearchFields {
	// Worker
	const ID = 'w_id';
	const FIRST_NAME = 'w_first_name';
	const LAST_NAME = 'w_last_name';
	const TITLE = 'w_title';
	const EMAIL = 'w_email';
	const IS_SUPERUSER = 'w_is_superuser';
	const LAST_ACTIVITY = 'w_last_activity';
	const LAST_ACTIVITY_DATE = 'w_last_activity_date';
	const IS_DISABLED = 'w_is_disabled';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'w', 'id', $translate->_('common.id')),
			self::FIRST_NAME => new DevblocksSearchField(self::FIRST_NAME, 'w', 'first_name', $translate->_('worker.first_name')),
			self::LAST_NAME => new DevblocksSearchField(self::LAST_NAME, 'w', 'last_name', $translate->_('worker.last_name')),
			self::TITLE => new DevblocksSearchField(self::TITLE, 'w', 'title', $translate->_('worker.title')),
			self::EMAIL => new DevblocksSearchField(self::EMAIL, 'w', 'email', null, ucwords($translate->_('common.email'))),
			self::IS_SUPERUSER => new DevblocksSearchField(self::IS_SUPERUSER, 'w', 'is_superuser', $translate->_('worker.is_superuser')),
			self::LAST_ACTIVITY => new DevblocksSearchField(self::LAST_ACTIVITY, 'w', 'last_activity', $translate->_('worker.last_activity')),
			self::LAST_ACTIVITY_DATE => new DevblocksSearchField(self::LAST_ACTIVITY_DATE, 'w', 'last_activity_date', $translate->_('worker.last_activity_date')),
			self::IS_DISABLED => new DevblocksSearchField(self::IS_DISABLED, 'w', 'is_disabled', ucwords($translate->_('common.disabled'))),
		);
		
		// Custom Fields
		$fields = DAO_CustomField::getBySource(FegCustomFieldSource_Worker::ID);

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

class DAO_WorkerPref extends DevblocksORMHelper {
    const CACHE_PREFIX = 'ps_workerpref_';
    
	static function set($worker_id, $key, $value) {
		// Persist long-term
		$db = DevblocksPlatform::getDatabaseService();
		
		$db->Execute(sprintf("REPLACE INTO worker_pref (worker_id, setting, value) VALUES (%d, %s, %s)",
			$worker_id,
			$db->qstr($key),
			$db->qstr($value)
		));
		
		// Invalidate cache
		$cache = DevblocksPlatform::getCacheService();
		$cache->remove(self::CACHE_PREFIX.$worker_id);
	}
	
	static function get($worker_id, $key, $default=null) {
		$value = null;
		
		if(null !== ($worker_prefs = self::getByWorker($worker_id))) {
			if(isset($worker_prefs[$key])) {
				$value = $worker_prefs[$key];
			}
		}
		
		if(null === $value && !is_null($default)) {
		    return $default;
		}
		
		return $value;
	}

	static function getByWorker($worker_id) {
		$cache = DevblocksPlatform::getCacheService();
		
		if(null === ($objects = $cache->load(self::CACHE_PREFIX.$worker_id))) {
			$db = DevblocksPlatform::getDatabaseService();
			$sql = sprintf("SELECT setting, value FROM worker_pref WHERE worker_id = %d", $worker_id);
			$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); 
			
			$objects = array();
			
			while($row = mysql_fetch_assoc($rs)) {
			    $objects[$row['setting']] = $row['value'];
			}
			
			mysql_free_result($rs);
			
			$cache->save($objects, self::CACHE_PREFIX.$worker_id);
		}
		
		return $objects;
	}
};

class DAO_WorkerRole extends DevblocksORMHelper {
	const _CACHE_ALL = 'ps_acl';
	
	const CACHE_KEY_ROLES = 'roles';
	const CACHE_KEY_PRIVS_BY_ROLE = 'privs_by_role';
	const CACHE_KEY_WORKERS_BY_ROLE = 'workers_by_role';
	const CACHE_KEY_PRIVS_BY_WORKER = 'privs_by_worker';
	
	const ID = 'id';
	const NAME = 'name';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO worker_role (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'worker_role', $fields);
	}
	
	static function getACL($nocache=false) {
	    $cache = DevblocksPlatform::getCacheService();
	    if($nocache || null === ($acl = $cache->load(self::_CACHE_ALL))) {
	    	$db = DevblocksPlatform::getDatabaseService();
	    	
	    	// All roles
	    	$all_roles = self::getWhere();
	    	$all_worker_ids = array();

	    	// All privileges by role
	    	$all_privs = array();
	    	$rs = $db->Execute("SELECT role_id, priv_id FROM worker_role_acl WHERE has_priv = 1 ORDER BY role_id, priv_id");
	    	while($row = mysql_fetch_assoc($rs)) {
	    		$role_id = intval($row['role_id']);
	    		$priv_id = $row['priv_id'];
	    		if(!isset($all_privs[$role_id]))
	    			$all_privs[$role_id] = array();
	    		
	    		$all_privs[$role_id][$priv_id] = $priv_id;
	    	}
	    	
	    	mysql_free_result($rs);
	    	
	    	// All workers by role
	    	$all_rosters = array();
	    	$rs = $db->Execute("SELECT role_id, worker_id FROM worker_to_role");
	    	while($row = mysql_fetch_assoc($rs)) {
	    		$role_id = intval($row['role_id']);
	    		$worker_id = intval($row['worker_id']);
	    		if(!isset($all_rosters[$role_id]))
	    			$all_rosters[$role_id] = array();

	    		$all_rosters[$role_id][$worker_id] = $worker_id;
	    		$all_worker_ids[$worker_id] = $worker_id;
	    	}
	    	
	    	mysql_free_result($rs);
	    	
	    	// Aggregate privs by workers' roles (if set anywhere, keep)
	    	$privs_by_worker = array();
	    	if(is_array($all_worker_ids))
	    	foreach($all_worker_ids as $worker_id) {
	    		if(!isset($privs_by_worker[$worker_id]))
	    			$privs_by_worker[$worker_id] = array();
	    		
	    		foreach($all_rosters as $role_id => $role_roster) {
	    			if(isset($role_roster[$worker_id]) && isset($all_privs[$role_id])) {
	    				// If we have privs from other groups, merge on the keys
	    				$current_privs = (is_array($privs_by_worker[$worker_id])) ? $privs_by_worker[$worker_id] : array();
    					$privs_by_worker[$worker_id] = array_merge($current_privs,$all_privs[$role_id]);
	    			}
	    		}
	    	}
	    	
	    	$acl = array(
	    		self::CACHE_KEY_ROLES => $all_roles,
	    		self::CACHE_KEY_PRIVS_BY_ROLE => $all_privs,
	    		self::CACHE_KEY_WORKERS_BY_ROLE => $all_rosters,
	    		self::CACHE_KEY_PRIVS_BY_WORKER => $privs_by_worker,
	    	);
	    	
    	    $cache->save($acl, self::_CACHE_ALL);
	    }
	    
	    return $acl;
	    
	}
	
	/**
	 * @param string $where
	 * @return Model_WorkerRole[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, name ".
			"FROM worker_role ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY name asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_WorkerRole	 */
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
	 * @return Model_WorkerRole[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_WorkerRole();
			$object->id = $row['id'];
			$object->name = $row['name'];
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
		
		$db->Execute(sprintf("DELETE FROM worker_role WHERE id IN (%s)", $ids_list));
		$db->Execute(sprintf("DELETE FROM worker_to_role WHERE role_id IN (%s)", $ids_list));
		$db->Execute(sprintf("DELETE FROM worker_role_acl WHERE role_id IN (%s)", $ids_list));
		
		return true;
	}
	
	static function getRolePrivileges($role_id) {
		$acl = self::getACL();
		
		if(empty($role_id) || !isset($acl[self::CACHE_KEY_PRIVS_BY_ROLE][$role_id]))
			return array();
		
		return $acl[self::CACHE_KEY_PRIVS_BY_ROLE][$role_id];
	}
	
	/**
	 * @param integer $role_id
	 * @param array $privileges
	 * @param boolean $replace
	 */
	static function setRolePrivileges($role_id, $privileges) {
		if(!is_array($privileges)) $privileges = array($privileges);
		$db = DevblocksPlatform::getDatabaseService();
		
		if(empty($role_id))
			return;
		
		// Wipe all privileges on blank replace
		$sql = sprintf("DELETE FROM worker_role_acl WHERE role_id = %d", $role_id);
		$db->Execute($sql);

		// Load entire ACL list
		$acl = DevblocksPlatform::getAclRegistry();
		
		// Set ACLs according to the new master list
		if(!empty($privileges)) { // && !empty($acl)
			foreach($privileges as $priv) { /* @var $priv DevblocksAclPrivilege */
				$sql = sprintf("INSERT INTO worker_role_acl (role_id, priv_id, has_priv) ".
					"VALUES (%d, %s, %d)",
					$role_id,
					$db->qstr($priv),
					1
				);
				$db->Execute($sql);
			}
		}
		
		unset($privileges);
		
		self::clearCache();
	}
	
	static function getRoleWorkers($role_id) {
		$acl = self::getACL();
		
		if(empty($role_id) || !isset($acl[self::CACHE_KEY_WORKERS_BY_ROLE][$role_id]))
			return array();
		
		return $acl[self::CACHE_KEY_WORKERS_BY_ROLE][$role_id];
	}
	
	static function setRoleWorkers($role_id, $worker_ids) {
		if(!is_array($worker_ids)) $worker_ids = array($worker_ids);
		$db = DevblocksPlatform::getDatabaseService();
		
		if(empty($role_id))
			return;
			
		// Wipe roster
		$sql = sprintf("DELETE FROM worker_to_role WHERE role_id = %d", $role_id);
		$db->Execute($sql);
		
		// Add desired workers to role's roster		
		if(is_array($worker_ids))
		foreach($worker_ids as $worker_id) {
			$sql = sprintf("INSERT INTO worker_to_role (worker_id, role_id) ".
				"VALUES (%d, %d)",
				$worker_id,
				$role_id
			);
			$db->Execute($sql);
		}
		
		self::clearCache();
	}
	
	static function clearCache() {
		$cache = DevblocksPlatform::getCacheService();
		$cache->remove(self::_CACHE_ALL);
	}
};
class View_Worker extends Feg_AbstractView {
	const DEFAULT_ID = 'workers';

	function __construct() {
		$this->id = self::DEFAULT_ID;
		$this->name = 'Workers';
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_Worker::FIRST_NAME;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_Worker::FIRST_NAME,
			SearchFields_Worker::LAST_NAME,
			SearchFields_Worker::TITLE,
			SearchFields_Worker::EMAIL,
			SearchFields_Worker::LAST_ACTIVITY_DATE,
			SearchFields_Worker::IS_SUPERUSER,
		);
		
		$this->doResetCriteria();
	}

	function getData() {
		return DAO_Worker::search(
			$this->view_columns,
			$this->params,
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc
		);
	}

	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_Worker::ID);
		$tpl->assign('custom_fields', $custom_fields);

		$tpl->assign('view_fields', $this->getColumns());
		$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/setup/tabs/workers/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		switch($field) {
			case SearchFields_Worker::EMAIL:
			case SearchFields_Worker::FIRST_NAME:
			case SearchFields_Worker::LAST_NAME:
			case SearchFields_Worker::TITLE:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__string.tpl');
				break;
			case SearchFields_Worker::IS_DISABLED:
			case SearchFields_Worker::IS_SUPERUSER:
				$tpl->display('file:' . APP_PATH . '/features/feg.core/templates/internal/views/criteria/__bool.tpl');
				break;
			case SearchFields_Worker::LAST_ACTIVITY_DATE:
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
//			case SearchFields_WorkerEvent::WORKER_ID:
//				$workers = DAO_Worker::getAll();
//				$strings = array();
//
//				foreach($values as $val) {
//					if(empty($val))
//					$strings[] = "Nobody";
//					elseif(!isset($workers[$val]))
//					continue;
//					else
//					$strings[] = $workers[$val]->getName();
//				}
//				echo implode(", ", $strings);
//				break;
			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	static function getFields() {
		return SearchFields_Worker::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		unset($fields[SearchFields_Worker::ID]);
		unset($fields[SearchFields_Worker::LAST_ACTIVITY]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		unset($fields[SearchFields_Worker::LAST_ACTIVITY]);
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
			case SearchFields_Worker::EMAIL:
			case SearchFields_Worker::FIRST_NAME:
			case SearchFields_Worker::LAST_NAME:
			case SearchFields_Worker::TITLE:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
				
			case SearchFields_Worker::LAST_ACTIVITY_DATE:
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from)) $from = 0;
				if(empty($to)) $to = 'today';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;
				
			case SearchFields_Worker::IS_DISABLED:
			case SearchFields_Worker::IS_SUPERUSER:
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

		if(empty($do))
			return;

		if(is_array($do))
		foreach($do as $k => $v) {
			switch($k) {
				case 'is_disabled':
					$change_fields[DAO_Worker::IS_DISABLED] = intval($v);
					break;
				default:
					// Custom fields
					if(substr($k,0,3)=="cf_") {
						$custom_fields[substr($k,3)] = $v;
					}
					break;

			}
		}

		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_Worker::search(
			array(),
			$this->params,
			100,
			$pg++,
			SearchFields_Worker::ID,
			true,
			false
			);
			 
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			DAO_Worker::update($batch_ids, $change_fields);
			
			// Custom Fields
			self::_doBulkSetCustomFields(FegCustomFieldSource_Worker::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}

};

