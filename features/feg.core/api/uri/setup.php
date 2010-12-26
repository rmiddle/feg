<?php
class FegSetupPage extends FegPageExtension  {
	private $_TPL_PATH = '';
	
	function __construct($manifest) {
		$this->_TPL_PATH = dirname(dirname(dirname(__FILE__))) . '/templates/';
		parent::__construct($manifest);
	}
	
	// [TODO] Refactor to isAuthorized
	function isVisible() {
		$worker = FegApplication::getActiveWorker();
		
		if(empty($worker)) {
			return false;
		} elseif($worker->is_superuser) {
			return true;
		}
	}
	
	function getActivity() {
	    return new Model_Activity('activity.setup');
	}
	
	function render() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);

		$worker = FegApplication::getActiveWorker();
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}

		if(file_exists(APP_PATH . '/install/')) {
			$tpl->assign('install_dir_warning', true);
		}
		
		$tab_manifests = DevblocksPlatform::getExtensions('feg.setup.tab', false);
		uasort($tab_manifests, create_function('$a, $b', "return strcasecmp(\$a->name,\$b->name);\n"));
		$tpl->assign('tab_manifests', $tab_manifests);
		
		// Selected tab
		$response = DevblocksPlatform::getHttpResponse();
		$stack = $response->path;
		array_shift($stack); // setup
		$tab_selected = array_shift($stack);
		$tpl->assign('tab_selected', $tab_selected);
		
		// [TODO] check showTab* hooks for active_worker->is_superuser (no ajax bypass)
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/index.tpl');
	}
	
	// Ajax
	function showTabAction() {
		@$ext_id = DevblocksPlatform::importGPC($_REQUEST['ext_id'],'string','');
		
		if(null != ($tab_mft = DevblocksPlatform::getExtension($ext_id)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_SetupTab) {
			$inst->showTab();
		}
	}
	
	// Post
	function saveTabAction() {
		@$ext_id = DevblocksPlatform::importGPC($_REQUEST['ext_id'],'string','');
		
		if(null != ($tab_mft = DevblocksPlatform::getExtension($ext_id)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_SetupTab) {
			$inst->saveTab();
		}
	}
	
	/*
	 * [TODO] Proxy any func requests to be handled by the tab directly, 
	 * instead of forcing tabs to implement controllers.  This should check 
	 * for the *Action() functions just as a handleRequest would
	 */
	function handleTabActionAction() {
		@$tab = DevblocksPlatform::importGPC($_REQUEST['tab'],'string','');
		@$action = DevblocksPlatform::importGPC($_REQUEST['action'],'string','');

		if(null != ($tab_mft = DevblocksPlatform::getExtension($tab)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_SetupTab) {
				if(method_exists($inst,$action.'Action')) {
					call_user_func(array(&$inst, $action.'Action'));
				}
		}
	}
	
	// Ajax
	function showTabSettingsAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$license = FegLicense::getInstance();
		$tpl->assign('license', $license);
		
		$db = DevblocksPlatform::getDatabaseService();
		$rs = $db->Execute("SHOW TABLE STATUS");

		$total_db_size = 0;
		$total_db_data = 0;
		$total_db_indexes = 0;
		$total_db_slack = 0;
		$total_file_size = 0;
		
		// [TODO] This would likely be helpful to the /debug controller
		
		if(null != ($row = mysql_fetch_assoc($rs))) {
			$table_name = $row['Name'];
			$table_size_data = intval($row['Data_length']);
			$table_size_indexes = intval($row['Index_length']);
			$table_size_slack = intval($row['Data_free']);
			
			$total_db_size += $table_size_data + $table_size_indexes;
			$total_db_data += $table_size_data;
			$total_db_indexes += $table_size_indexes;
			$total_db_slack += $table_size_slack;
		}
		
		mysql_free_result($rs);
		
//		$sql = "SELECT SUM(file_size) FROM attachment";
//		$total_file_size = intval($db->GetOne($sql));

		$tpl->assign('total_db_size', number_format($total_db_size/1048576,2));
		$tpl->assign('total_db_data', number_format($total_db_data/1048576,2));
		$tpl->assign('total_db_indexes', number_format($total_db_indexes/1048576,2));
		$tpl->assign('total_db_slack', number_format($total_db_slack/1048576,2));
//		$tpl->assign('total_file_size', number_format($total_file_size/1048576,2));
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/settings/index.tpl');
	}
	
	// Post
	function saveTabSettingsAction() {
		$translate = DevblocksPlatform::getTranslationService();
		$worker = FegApplication::getActiveWorker();
		
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
	    @$title = DevblocksPlatform::importGPC($_POST['title'],'string','');
	    @$logo = DevblocksPlatform::importGPC($_POST['logo'],'string');
	    @$authorized_ips_str = DevblocksPlatform::importGPC($_POST['authorized_ips'],'string','');

	    if(empty($title))
	    	$title = 'Feg - Fax Email Gateway';
	    
	    $settings = DevblocksPlatform::getPluginSettingsService();
	    $settings->set('feg.core',FegSettings::APP_TITLE, $title);
	    $settings->set('feg.core',FegSettings::APP_LOGO_URL, $logo); // [TODO] Enforce some kind of max resolution?
	    $settings->set('feg.core',FegSettings::AUTHORIZED_IPS, $authorized_ips_str);
	    
	    DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','settings')));
	}
	
	// Ajax
	function showTabPluginsAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		// Auto synchronize when viewing Config->Extensions
        DevblocksPlatform::readPlugins();
		
		if(DEVELOPMENT_MODE)		
			DAO_Platform::cleanupPluginTables();
			
		$plugins = DevblocksPlatform::getPluginRegistry();
		unset($plugins['devblocks.core']);
		unset($plugins['feg.core']);
		unset($plugins['feg.auditlog']);
		$tpl->assign('plugins', $plugins);
		
		$license = FegLicense::getInstance();
		$tpl->assign('license', $license);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/plugins/index.tpl');
	}
	
	function saveTabPluginsAction() {
		$translate = DevblocksPlatform::getTranslationService();
		$worker = FegApplication::getActiveWorker();
		
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		$pluginStack = DevblocksPlatform::getPluginRegistry();
		@$plugins_enabled = DevblocksPlatform::importGPC($_REQUEST['plugins_enabled']);

		if(is_array($pluginStack))
		foreach($pluginStack as $plugin) { /* @var $plugin DevblocksPluginManifest */
			switch($plugin->id) {
				case 'devblocks.core':
				case 'feg.core':
				case 'feg.auditlog':
					$plugin->setEnabled(true);
					break;
					
				default:
					if(null !== $plugins_enabled && false !== array_search($plugin->id, $plugins_enabled)) {
						$plugin->setEnabled(true);
					} else {
						$plugin->setEnabled(false);
					}
					break;
			}
		}
		
		try {
			FegApplication::update();	
		} catch (Exception $e) {
			// [TODO] ...
		}

		DevblocksPlatform::clearCache();
		
        // Reload plugin translations
		DAO_Translation::reloadPluginStrings();
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('setup','plugins')));
	}	
	
	// Ajax
	function showTabWorkersAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$workers = DAO_Worker::getAllWithDisabled();
		$tpl->assign('workers', $workers);

		$tpl->assign('response_uri', 'setup/workers');
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->id = 'workers_cfg';
		$defaults->class_name = 'View_Worker';
		
		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$tpl->assign('view', $view);
		$tpl->assign('view_fields', View_Worker::getFields());
		$tpl->assign('view_searchable_fields', View_Worker::getSearchFields());
		
		$tpl->assign('license', FegLicense::getInstance());
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/workers/index.tpl');
	}
	
	function showWorkerPeekAction() {
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$tpl->assign('view_id', $view_id);
		
		$worker = DAO_Worker::get($id);
		$tpl->assign('worker', $worker);
		
		// Custom Fields
		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_Worker::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$custom_field_values = DAO_CustomFieldValue::getValuesBySourceIds(FegCustomFieldSource_Worker::ID, $id);
		if(isset($custom_field_values[$id]))
			$tpl->assign('custom_field_values', $custom_field_values[$id]);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/workers/peek.tpl');		
	}
	
	function saveWorkerPeekAction() {
		$translate = DevblocksPlatform::getTranslationService();
		$active_worker = FegApplication::getActiveWorker();
		
		if(!$active_worker || !$active_worker->is_superuser) {
			return;
		}
		
		@$id = DevblocksPlatform::importGPC($_POST['id'],'integer');
		@$view_id = DevblocksPlatform::importGPC($_POST['view_id'],'string');
		@$first_name = DevblocksPlatform::importGPC($_POST['first_name'],'string');
		@$last_name = DevblocksPlatform::importGPC($_POST['last_name'],'string');
		@$title = DevblocksPlatform::importGPC($_POST['title'],'string');
		@$email = DevblocksPlatform::importGPC($_POST['email'],'string');
		@$password = DevblocksPlatform::importGPC($_POST['password'],'string');
		@$is_superuser = DevblocksPlatform::importGPC($_POST['is_superuser'],'integer', 0);
		@$disabled = DevblocksPlatform::importGPC($_POST['is_disabled'],'integer',0);
//		@$group_ids = DevblocksPlatform::importGPC($_POST['group_ids'],'array');
//		@$group_roles = DevblocksPlatform::importGPC($_POST['group_roles'],'array');
		@$delete = DevblocksPlatform::importGPC($_POST['do_delete'],'integer',0);

		// [TODO] The superuser set bit here needs to be protected by ACL
		
		if(empty($first_name)) $first_name = "Anonymous";
		
		if(!empty($id) && !empty($delete)) {
			// Can't delete or disable self
			if($active_worker->id != $id)
				DAO_Worker::delete($id);
			
		} else {
			if(empty($id) && null == DAO_Worker::getWhere(sprintf("%s=%s",DAO_Worker::EMAIL,Feg_ORMHelper::qstr($email)))) {
				$workers = DAO_Worker::getAll();
				$license = FegLicense::getInstance();
				if ((!empty($license) && !empty($license['serial'])) || count($workers) < 3) {
					// Creating new worker.  If password is empty, email it to them
				    if(empty($password)) {
				    	$settings = DevblocksPlatform::getPluginSettingsService();
						$replyFrom = $settings->get('feg.core',FegSettings::DEFAULT_REPLY_FROM);
						$replyPersonal = $settings->get('feg.core',FegSettings::DEFAULT_REPLY_PERSONAL, '');
						$url = DevblocksPlatform::getUrlService();
				    	
						$password = FegApplication::generatePassword(8);
				    }
					
				    $fields = array(
				    	DAO_Worker::EMAIL => $email,
				    	DAO_Worker::PASS => $password
				    );
				    
					$id = DAO_Worker::create($fields);
				}
			} // end create worker
		    
		    // Update
			$fields = array(
				DAO_Worker::FIRST_NAME => $first_name,
				DAO_Worker::LAST_NAME => $last_name,
				DAO_Worker::TITLE => $title,
				DAO_Worker::EMAIL => $email,
				DAO_Worker::IS_SUPERUSER => $is_superuser,
				DAO_Worker::IS_DISABLED => $disabled,
			);
			
			// if we're resetting the password
			if(!empty($password)) {
				$fields[DAO_Worker::PASS] = md5($password);
			}
			
			// Update worker
			DAO_Worker::update($id, $fields);
			
			// Custom field saves
			@$field_ids = DevblocksPlatform::importGPC($_POST['field_ids'], 'array', array());
			DAO_CustomFieldValue::handleFormPost(FegCustomFieldSource_Worker::ID, $id, $field_ids);
		}
		
		if(!empty($view_id)) {
			$view = Feg_AbstractViewLoader::getView($view_id);
			$view->render();
		}
	}
	
	function showWorkersBulkPanelAction() {
		@$id_csv = DevblocksPlatform::importGPC($_REQUEST['ids']);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id']);

		$tpl = DevblocksPlatform::getTemplateService();
		$path = $this->_TPL_PATH;
		$tpl->assign('path', $path);
		$tpl->assign('view_id', $view_id);

	    if(!empty($id_csv)) {
	        $ids = DevblocksPlatform::parseCsvString($id_csv);
	        $tpl->assign('ids', implode(',', $ids));
	    }
		
		// Custom Fields
		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_Worker::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$tpl->display('file:' . $path . 'setup/tabs/workers/bulk.tpl');
	}
	
	function doWorkersBulkUpdateAction() {
		// Checked rows
	    @$ids_str = DevblocksPlatform::importGPC($_REQUEST['ids'],'string');
		$ids = DevblocksPlatform::parseCsvString($ids_str);

		// Filter: whole list or check
	    @$filter = DevblocksPlatform::importGPC($_REQUEST['filter'],'string','');
	    
	    // View
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string');
		$view = Feg_AbstractViewLoader::getView($view_id);
		
		// Worker fields
		@$is_disabled = trim(DevblocksPlatform::importGPC($_POST['is_disabled'],'string',''));

		$do = array();
		
		// Do: Disabled
		if(0 != strlen($is_disabled))
			$do['is_disabled'] = $is_disabled;
			
		// Do: Custom fields
		$do = DAO_CustomFieldValue::handleBulkPost($do);
		
		$view->doBulkUpdate($filter, $do, $ids);
		
		$view->render();
		return;
	}
	
	function showTabImportAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('$path', $this->_TPL_PATH);

		$worker = FegApplication::getActiveWorker();
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		$tpl->assign('response_uri', 'setup/import');
		
		//$tpl->assign('core_tplpath', $core_tplpath);
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->name = 'Import Source List';
		$defaults->id = 'import_source_list';
		$defaults->class_name = 'View_ImportSource';
		$defaults->renderLimit = 15;
		
		$defaults->renderSortBy = SearchFields_ImportSource::ID;
		$defaults->renderSortAsc = 0;
		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$view->name = 'Import Source List';
		$view->renderPage = 0;
		Feg_AbstractViewLoader::setView($view->id,$view);
		
		$tpl->assign('view', $view);
		$tpl->assign('view_fields', View_ImportSource::getFields());
		$tpl->assign('view_searchable_fields', View_ImportSource::getSearchFields());
				
		$tpl->display('file:' . $this->_TPL_PATH . 'internal/tabs/import_source/index.tpl');		
	}
	
	function showImportPeekAction() {
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$tpl->assign('id', $id);
		$tpl->assign('view_id', $view_id);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'internal/tabs/import_source/peek.tpl');		
	}
	
	function saveImportPeekAction() {
		$translate = DevblocksPlatform::getTranslationService();
		
		@$id = DevblocksPlatform::importGPC($_POST['id'],'integer');
		@$view_id = DevblocksPlatform::importGPC($_POST['view_id'],'string');
		@$delete = DevblocksPlatform::importGPC($_POST['do_delete'],'integer',0);

		@$disabled = DevblocksPlatform::importGPC($_POST['imports_is_disabled'],'integer',0);
		@$import_name = DevblocksPlatform::importGPC($_POST['import_name'],'string',"");
		@$import_type = DevblocksPlatform::importGPC($_POST['import_type'],'integer',0);
		@$import_path = DevblocksPlatform::importGPC($_POST['import_path'],'string', "");
		
		$fields = array(
			DAO_ImportSource::NAME => $import_name,
			DAO_ImportSource::PATH => $import_path,
			DAO_ImportSource::TYPE => $import_type,
			DAO_ImportSource::IS_DISABLED => $disabled,
		);
		
		if($id == 0) {
			// Create New Import 
			$id = $status = DAO_ImportSource::create($fields);
		} else {
			// Update Existing Import 
			$status = DAO_ImportSource::update($id, $fields);
		}
		
		if(!empty($view_id)) {
			$view = Feg_AbstractViewLoader::getView($view_id);
			$view->render();
		}
		
		//DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','workers')));		
	}
	
	function showImportBulkPanelAction() {
		@$id_csv = DevblocksPlatform::importGPC($_REQUEST['ids']);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id']);

		$tpl = DevblocksPlatform::getTemplateService();
		$path = $this->_TPL_PATH;
		$tpl->assign('path', $path);
		$tpl->assign('view_id', $view_id);

	    if(!empty($id_csv)) {
	        $ids = DevblocksPlatform::parseCsvString($id_csv);
	        $tpl->assign('ids', implode(',', $ids));
	    }
		
		$tpl->display('file:' . $this->_TPL_PATH . 'internal/tabs/import_source/bulk.tpl');		
	}
	
	function doImportBulkUpdateAction() {
		// Checked rows
	    @$ids_str = DevblocksPlatform::importGPC($_REQUEST['ids'],'string');
		$ids = DevblocksPlatform::parseCsvString($ids_str);

		// Filter: whole list or check
	    @$filter = DevblocksPlatform::importGPC($_REQUEST['filter'],'string','');
	    
	    // View
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string');
		$view = Feg_AbstractViewLoader::getView($view_id);
		
		// Customer Recpiept Fields.
		@$is_disabled = trim(DevblocksPlatform::importGPC($_POST['import_is_disabled'],'string',''));

		$do = array();
		
		// Do: Disabled
		if(0 != strlen($is_disabled))
			$do['is_disabled'] = $is_disabled;
			
		$view->doBulkUpdate($filter, $do, $ids);
		
		$view->render();
		return;
	}
	
	function showTabExportAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('$path', $this->_TPL_PATH);

		$worker = FegApplication::getActiveWorker();
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		$tpl->assign('response_uri', 'setup/export_type');
		
		//$tpl->assign('core_tplpath', $core_tplpath);
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->name = 'Export Type List';
		$defaults->id = '_full_export_type_list';
		$defaults->class_name = 'View_ExportType';
		$defaults->renderLimit = 15;
		
		$defaults->renderSortBy = SearchFields_ExportType::ID;
		$defaults->renderSortAsc = true;
		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$view->name = 'Export Type Management List';
		$view->renderPage = 0;
		Feg_AbstractViewLoader::setView($view->id,$view);
		
		$tpl->assign('view', $view);
		$tpl->assign('view_fields', View_ExportType::getFields());
		$tpl->assign('view_searchable_fields', View_ExportType::getSearchFields());
				
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/export_type/index.tpl');		
	}
	
	function showExportPeekAction() {
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$tpl->assign('id', $id);
		$tpl->assign('view_id', $view_id);
		
		$export_type = DAO_ExportType::get($id);
		$tpl->assign('export_type', $export_type);
		
		$export_type_params = DAO_ExportTypeParams::getAll();
		$tpl->assign('export_type_params', $export_type_params);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/export_type/peek.tpl');		
	}
	
	function showExportPeekTypeParmTypeAction() {
		@$type = DevblocksPlatform::importGPC($_REQUEST['type'],'integer',0);
		
		$export_type_by_type = DAO_ExportTypeParams::getByType($type);
		
		echo json_encode($export_type_by_type);
	}
	
	function showExportPeekTypeParmAction() {
		@$type = DevblocksPlatform::importGPC($_REQUEST['type'],'integer',0);
		
		$export_type_by_type = DAO_ExportTypeParams::getByType($type);
		
		echo json_encode($export_type_by_type);
	}
	
	function saveExportPeekTypeParmAddAction() {
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$add_id = DevblocksPlatform::importGPC($_REQUEST['add_id'],'integer',0);

		$export_type = DAO_ExportType::get($id);
		$export_type_params = DAO_ExportTypeParams::getAll();
		
echo "<pre>";
echo "<br>id: ";
print_r($id);
echo "<br>add_id: ";
print_r($add_id);
echo "<br>export_type: ";
print_r($export_type);
echo "<br>export_type_params: ";
print_r($export_type_params);
/*
		if (!exists($export_type_params[$add_id])) {
			// Bad add_id
			return;
		}
*/		
		if ($export_type_params[$add_id]->options['default']) {
			$export_type->params[$add_id] = $export_type_params[$add_id]->options['default'];
			$export_param_add['id'] = $add_id;
			$export_param_add['default'] = $export_type_params[$add_id]->options['default'];
		} else {
			$export_type->params[$add_id] = NULL;
			$export_param_add['id'] = $add_id;
			$export_param_add['default'] = NULL;
		}
		
		$fields = array(
			DAO_ExportType::PARAMS => $export_type->params,
		);
echo "<br>export_param_addexport_param_add";
print_r($export_param_add);
echo "<br>fields: ";
print_r($fields);
echo "<br>Post export_type: ";
print_r($export_type);
echo "</pre>";
		//$status = DAO_ExportType::update($id, $fields);
		
		echo json_encode($export_param_add);		
	}
	
	function saveExportPeekAction() {
		$translate = DevblocksPlatform::getTranslationService();
		
		@$id = DevblocksPlatform::importGPC($_POST['id'],'integer');
		@$view_id = DevblocksPlatform::importGPC($_POST['view_id'],'string');
		@$delete = DevblocksPlatform::importGPC($_POST['do_delete'],'integer',0);

		@$disabled = DevblocksPlatform::importGPC($_POST['export_type_is_disabled'],'integer',0);
		@$export_type_name = DevblocksPlatform::importGPC($_POST['export_type_name'],'string',"");
		@$export_type_recipient_type = DevblocksPlatform::importGPC($_POST['export_type_recipient_type'],'integer',0);
		
		@$params_ids = DevblocksPlatform::importGPC($_POST['export_type_recipient_type'],'array',array());

		$export_type_params = DAO_ExportTypeParams::getAll();
/*		
		foreach($params_ids as $params_id) { // 1 = Yes/No, 2 = 255 Char input
			switch ($export_type_params[$params_id]->type) {
				case 1:
					@parms[$params_id] = DevblocksPlatform::importGPC($_REQUEST['export_type_params_'.$params_id],'integer',0);
					break;
				case 2:
					@parms[$params_id] = DevblocksPlatform::importGPC($_REQUEST['export_type_params_'.$params_id],'string','');
					break;
				default: 
					@parms[$params_id] = DevblocksPlatform::importGPC($_REQUEST['export_type_params_'.$params_id],'string','');
					break;
			}
*/		
		$fields = array(
			DAO_ExportType::NAME => $export_type_name,
			DAO_ExportType::RECIPIENT_TYPE => $export_type_recipient_type,
			DAO_ExportType::IS_DISABLED => $disabled,
		);
		
		$status = DAO_ExportType::update($id, $fields);
		
		if(!empty($view_id)) {
			$view = Feg_AbstractViewLoader::getView($view_id);
			$view->render();
		}
		
		//DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','workers')));		
	}
	
	function showExportBulkPanelAction() {
		@$id_csv = DevblocksPlatform::importGPC($_REQUEST['ids']);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id']);

		$tpl = DevblocksPlatform::getTemplateService();
		$path = $this->_TPL_PATH;
		$tpl->assign('path', $path);
		$tpl->assign('view_id', $view_id);

	    if(!empty($id_csv)) {
	        $ids = DevblocksPlatform::parseCsvString($id_csv);
	        $tpl->assign('ids', implode(',', $ids));
	    }
		
		// Custom Fields
		$custom_fields = DAO_CustomField::getBySource(SearchFields_ExportType::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/export_type/bulk.tpl');		
	}
	
	function doExportBulkUpdateAction() {
		// Checked rows
	    @$ids_str = DevblocksPlatform::importGPC($_REQUEST['ids'],'string');
		$ids = DevblocksPlatform::parseCsvString($ids_str);

		// Filter: whole list or check
	    @$filter = DevblocksPlatform::importGPC($_REQUEST['filter'],'string','');
	    
	    // View
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string');
		$view = Feg_AbstractViewLoader::getView($view_id);
		
		// Customer Recpiept Fields.
		@$is_disabled = trim(DevblocksPlatform::importGPC($_POST['recipient_is_disabled'],'string',''));

		$do = array();
		
		// Do: Disabled
		if(0 != strlen($is_disabled))
			$do['is_disabled'] = $is_disabled;
			
		$view->render();
		return;
	}
	
	function showTabRecipientAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('$path', $this->_TPL_PATH);

		$worker = FegApplication::getActiveWorker();
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		$tpl->assign('response_uri', 'setup/recipient');
		
		// $tpl->assign('core_tplpath', $core_tplpath);
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->name = 'Full Customer Recipient List';
		$defaults->id = 'full_view_recipient';
		$defaults->class_name = 'View_CustomerRecipient';
		$defaults->renderLimit = 15;
		
		$defaults->renderSortBy = SearchFields_CustomerRecipient::ID;
		$defaults->renderSortAsc = 0;
		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$view->name = 'Full Customer Recipient List';
		$view->renderPage = 0;
		Feg_AbstractViewLoader::setView($view->id,$view);
		
		$tpl->assign('view', $view);
		$tpl->assign('view_fields', View_CustomerRecipient::getFields());
		$tpl->assign('view_searchable_fields', View_CustomerRecipient::getSearchFields());
				
		$tpl->display('file:' . $this->_TPL_PATH . 'internal/tabs/customer_recipient/index.tpl');		
	}
	
	function showRecipientBulkPanelAction() {
		@$id_csv = DevblocksPlatform::importGPC($_REQUEST['ids']);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id']);

		$tpl = DevblocksPlatform::getTemplateService();
		$path = $this->_TPL_PATH;
		$tpl->assign('path', $path);
		$tpl->assign('view_id', $view_id);

	    if(!empty($id_csv)) {
	        $ids = DevblocksPlatform::parseCsvString($id_csv);
	        $tpl->assign('ids', implode(',', $ids));
	    }
		
		// Custom Fields
		$custom_fields = DAO_CustomField::getBySource(SearchFields_CustomerRecipient::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'internal/tabs/customer_recipient/bulk.tpl');		
	}
	
	function doRecipientBulkUpdateAction() {
		// Checked rows
	    @$ids_str = DevblocksPlatform::importGPC($_REQUEST['ids'],'string');
		$ids = DevblocksPlatform::parseCsvString($ids_str);

		// Filter: whole list or check
	    @$filter = DevblocksPlatform::importGPC($_REQUEST['filter'],'string','');
	    
	    // View
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string');
		$view = Feg_AbstractViewLoader::getView($view_id);
		
		// Customer Recpiept Fields.
		@$is_disabled = trim(DevblocksPlatform::importGPC($_POST['recipient_is_disabled'],'string',''));

		$do = array();
		
		// Do: Disabled
		if(0 != strlen($is_disabled))
			$do['is_disabled'] = $is_disabled;
			
		// Do: Custom fields
		$do = DAO_CustomFieldValue::handleBulkPost($do);
		
		$view->doBulkUpdate($filter, $do, $ids);
		
		$view->render();
		return;
	}
	
	function showAccountBulkPanelAction() {
		@$id_csv = DevblocksPlatform::importGPC($_REQUEST['ids']);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id']);

		$tpl = DevblocksPlatform::getTemplateService();
		$path = $this->_TPL_PATH;
		$tpl->assign('path', $path);
		$tpl->assign('view_id', $view_id);

	    if(!empty($id_csv)) {
	        $ids = DevblocksPlatform::parseCsvString($id_csv);
	        $tpl->assign('ids', implode(',', $ids));
	    }
		
		// Custom Fields
		$custom_fields = DAO_CustomField::getBySource(FegCustomFieldSource_CustomerAccount::ID);
		$tpl->assign('custom_fields', $custom_fields);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'internal/tabs/customer_account/bulk.tpl');		
	}
	
	function doAccountBulkUpdateAction() {
		// Checked rows
	    @$ids_str = DevblocksPlatform::importGPC($_REQUEST['ids'],'string');
		$ids = DevblocksPlatform::parseCsvString($ids_str);

		// Filter: whole list or check
	    @$filter = DevblocksPlatform::importGPC($_REQUEST['filter'],'string','');
	    
	    // View
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string');
		$view = Feg_AbstractViewLoader::getView($view_id);
		
		// Customer Recpiept Fields.
		@$is_disabled = trim(DevblocksPlatform::importGPC($_POST['account_is_disabled'],'string',''));

		$do = array();
		
		// Do: Disabled
		if(0 != strlen($is_disabled))
			$do['is_disabled'] = $is_disabled;
			
		// Do: Custom fields
		$do = DAO_CustomFieldValue::handleBulkPost($do);
		
		$view->doBulkUpdate($filter, $do, $ids);
		
		$view->render();
		return;
	}
	
	// Ajax
	function showTabMailSetupAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$settings = DevblocksPlatform::getPluginSettingsService();
		$mail_service = DevblocksPlatform::getMailService();
		
		$smtp_host = $settings->get('feg.core',FegSettings::SMTP_HOST,'');
		$smtp_port = $settings->get('feg.core',FegSettings::SMTP_PORT,25);
		$smtp_auth_enabled = $settings->get('feg.core',FegSettings::SMTP_AUTH_ENABLED,false);
		if ($smtp_auth_enabled) {
			$smtp_auth_user = $settings->get('feg.core',FegSettings::SMTP_AUTH_USER,'');
			$smtp_auth_pass = $settings->get('feg.core',FegSettings::SMTP_AUTH_PASS,''); 
		} else {
			$smtp_auth_user = '';
			$smtp_auth_pass = ''; 
		}
		$smtp_enc = $settings->get('feg.core',FegSettings::SMTP_ENCRYPTION_TYPE,'None');
		$smtp_max_sends = $settings->get('feg.core',FegSettings::SMTP_MAX_SENDS,'20');
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/mail/index.tpl');
	}
	
	// Form Submit
	function saveTabMailSetupAction() {
		$translate = DevblocksPlatform::getTranslationService();
		$worker = FegApplication::getActiveWorker();
		
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
	    @$default_reply_address = DevblocksPlatform::importGPC($_REQUEST['sender_address'],'string');
	    @$default_reply_personal = DevblocksPlatform::importGPC($_REQUEST['sender_personal'],'string');
//	    @$default_signature = DevblocksPlatform::importGPC($_POST['default_signature'],'string');
//	    @$default_signature_pos = DevblocksPlatform::importGPC($_POST['default_signature_pos'],'integer',0);
	    @$smtp_host = DevblocksPlatform::importGPC($_REQUEST['smtp_host'],'string','localhost');
	    @$smtp_port = DevblocksPlatform::importGPC($_REQUEST['smtp_port'],'integer',25);
	    @$smtp_enc = DevblocksPlatform::importGPC($_REQUEST['smtp_enc'],'string','None');
	    @$smtp_timeout = DevblocksPlatform::importGPC($_REQUEST['smtp_timeout'],'integer',30);
	    @$smtp_max_sends = DevblocksPlatform::importGPC($_REQUEST['smtp_max_sends'],'integer',20);

	    @$smtp_auth_enabled = DevblocksPlatform::importGPC($_REQUEST['smtp_auth_enabled'],'integer', 0);
	    if($smtp_auth_enabled) {
		    @$smtp_auth_user = DevblocksPlatform::importGPC($_REQUEST['smtp_auth_user'],'string');
		    @$smtp_auth_pass = DevblocksPlatform::importGPC($_REQUEST['smtp_auth_pass'],'string');
	    	
	    } else { // need to clear auth info when smtp auth is disabled
		    @$smtp_auth_user = '';
		    @$smtp_auth_pass = '';
	    }
	    
	    $settings = DevblocksPlatform::getPluginSettingsService();
	    $settings->set('feg.core',FegSettings::DEFAULT_REPLY_FROM, $default_reply_address);
	    $settings->set('feg.core',FegSettings::DEFAULT_REPLY_PERSONAL, $default_reply_personal);
//	    $settings->set('feg.core',FegSettings::DEFAULT_SIGNATURE, $default_signature);
//	    $settings->set('feg.core',FegSettings::DEFAULT_SIGNATURE_POS, $default_signature_pos);
	    $settings->set('feg.core',FegSettings::SMTP_HOST, $smtp_host);
	    $settings->set('feg.core',FegSettings::SMTP_PORT, $smtp_port);
	    $settings->set('feg.core',FegSettings::SMTP_AUTH_ENABLED, $smtp_auth_enabled);
	    $settings->set('feg.core',FegSettings::SMTP_AUTH_USER, $smtp_auth_user);
	    $settings->set('feg.core',FegSettings::SMTP_AUTH_PASS, $smtp_auth_pass);
	    $settings->set('feg.core',FegSettings::SMTP_ENCRYPTION_TYPE, $smtp_enc);
	    $settings->set('feg.core',FegSettings::SMTP_TIMEOUT, !empty($smtp_timeout) ? $smtp_timeout : 30);
	    $settings->set('feg.core',FegSettings::SMTP_MAX_SENDS, !empty($smtp_max_sends) ? $smtp_max_sends : 20);
	    
	    DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','mail','outgoing','test')));
	}	
	
	function getSmtpTestAction() {
		$translate = DevblocksPlatform::getTranslationService();
		
		@$host = DevblocksPlatform::importGPC($_REQUEST['host'],'string','');
		@$port = DevblocksPlatform::importGPC($_REQUEST['port'],'integer',25);
		@$smtp_enc = DevblocksPlatform::importGPC($_REQUEST['enc'],'string','');
		@$smtp_auth = DevblocksPlatform::importGPC($_REQUEST['smtp_auth'],'integer',0);
		@$smtp_user = DevblocksPlatform::importGPC($_REQUEST['smtp_user'],'string','');
		@$smtp_pass = DevblocksPlatform::importGPC($_REQUEST['smtp_pass'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		// [JAS]: Test the provided SMTP settings and give form feedback
		if(!empty($host)) {
			try {
				$mail_service = DevblocksPlatform::getMailService();
				$mailer = $mail_service->getMailer(array(
					'host' => $host,
					'port' => $port,
					'auth_user' => $smtp_user,
					'auth_pass' => $smtp_pass,
					'enc' => $smtp_enc,
				));
				
				$transport = $mailer->getTransport();
				$transport->start();
				$transport->stop();
				$tpl->assign('smtp_test', true);
				
			} catch(Exception $e) {
				$tpl->assign('smtp_test', false);
				$tpl->assign('smtp_test_output', $translate->_('setup.mail.smtp.failed') . ' ' . $e->getMessage());
			}
			
			$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/mail/test_smtp.tpl');			
		}
		
		return;
	}

	// Ajax
	function showTabACLAction() {
		$settings = DevblocksPlatform::getPluginSettingsService();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		$license = FegLicense::getInstance();
		$tpl->assign('license', $license);	
		
		$plugins = DevblocksPlatform::getPluginRegistry();
		$tpl->assign('plugins', $plugins);
		
		$acl = DevblocksPlatform::getAclRegistry();
		$tpl->assign('acl', $acl);
		
		$roles = DAO_WorkerRole::getWhere();
		$tpl->assign('roles', $roles);
		
		$workers = DAO_Worker::getAllActive();
		$tpl->assign('workers', $workers);
		
		// Permissions enabled
		$acl_enabled = $settings->get('feg.core',FegSettings::ACL_ENABLED);
		$tpl->assign('acl_enabled', $acl_enabled);
		
		if(empty($license) || (!empty($license)&&isset($license['a'])))
			$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/acl/trial.tpl');
		else
			$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/acl/index.tpl');
	}
	
	function toggleACLAction() {
		$worker = FegApplication::getActiveWorker();
		$settings = DevblocksPlatform::getPluginSettingsService();
		
		if(!$worker || !$worker->is_superuser) {
			return;
		}
		
		@$enabled = DevblocksPlatform::importGPC($_REQUEST['enabled'],'integer',0);
		
		$settings->set('feg.core',FegSettings::ACL_ENABLED, $enabled);
	}
	
	function getRoleAction() {
		$translate = DevblocksPlatform::getTranslationService();
		$worker = FegApplication::getActiveWorker();
		
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		@$id = DevblocksPlatform::importGPC($_REQUEST['id']);

		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);

		$plugins = DevblocksPlatform::getPluginRegistry();
		$tpl->assign('plugins', $plugins);
		
		$acl = DevblocksPlatform::getAclRegistry();
		$tpl->assign('acl', $acl);

		$workers = DAO_Worker::getAllActive();
		$tpl->assign('workers', $workers);
		
		$role = DAO_WorkerRole::get($id);
		$tpl->assign('role', $role);
		
		$role_privs = DAO_WorkerRole::getRolePrivileges($id);
		$tpl->assign('role_privs', $role_privs);
		
		$role_roster = DAO_WorkerRole::getRoleWorkers($id);
		$tpl->assign('role_workers', $role_roster);
		
		$tpl->assign('license', FegLicense::getInstance());
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/acl/edit_role.tpl');
	}
	
	// Post
	function saveRoleAction() {
		$translate = DevblocksPlatform::getTranslationService();
		$worker = FegApplication::getActiveWorker();
		
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$name = DevblocksPlatform::importGPC($_REQUEST['name'],'string','');
		@$worker_ids = DevblocksPlatform::importGPC($_REQUEST['worker_ids'],'array',array());
		@$acl_privs = DevblocksPlatform::importGPC($_REQUEST['acl_privs'],'array',array());
		@$do_delete = DevblocksPlatform::importGPC($_REQUEST['do_delete'],'integer',0);
		
		// Sanity checks
		if(empty($name))
			$name = 'New Role';
		
		// Delete
		if(!empty($do_delete) && !empty($id)) {
			DAO_WorkerRole::delete($id);
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','acl')));
		}

		$fields = array(
			DAO_WorkerRole::NAME => $name,
		);
			
		if(empty($id)) { // create
			$id = DAO_WorkerRole::create($fields);
					
		} else { // edit
			DAO_WorkerRole::update($id, $fields);
		}

		// Update role roster
		DAO_WorkerRole::setRoleWorkers($id, $worker_ids);
		
		// Update role privs
		DAO_WorkerRole::setRolePrivileges($id, $acl_privs, true);
		
		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','acl')));
	}
	
	// Ajax
	function showTabSchedulerAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);

	    $jobs = DevblocksPlatform::getExtensions('feg.cron', true);
		$tpl->assign('jobs', $jobs);

		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/scheduler/index.tpl');
	}
	
	// Post
	function saveTabSchedulerAction() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$worker = FegApplication::getActiveWorker();
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
	    // [TODO] Save the job changes
	    @$id = DevblocksPlatform::importGPC($_REQUEST['id'],'string','');
	    @$enabled = DevblocksPlatform::importGPC($_REQUEST['enabled'],'integer',0);
	    @$locked = DevblocksPlatform::importGPC($_REQUEST['locked'],'integer',0);
	    @$duration = DevblocksPlatform::importGPC($_REQUEST['duration'],'integer',5);
	    @$term = DevblocksPlatform::importGPC($_REQUEST['term'],'string','m');
	    @$starting = DevblocksPlatform::importGPC($_REQUEST['starting'],'string','');
	    	    
	    $manifest = DevblocksPlatform::getExtension($id);
	    $job = $manifest->createInstance(); /* @var $job FegCronExtension */

	    if(!empty($starting)) {
		    $starting_time = strtotime($starting);
		    if(false === $starting_time) $starting_time = time();
		    $starting_time -= FegCronExtension::getIntervalAsSeconds($duration, $term);
    	    $job->setParam(FegCronExtension::PARAM_LASTRUN, $starting_time);
	    }
	    
	    if(!$job instanceof FegCronExtension)
	        die($translate->_('common.access_denied'));
	    
	    // [TODO] This is really kludgey
	    $job->setParam(FegCronExtension::PARAM_ENABLED, $enabled);
	    $job->setParam(FegCronExtension::PARAM_LOCKED, $locked);
	    $job->setParam(FegCronExtension::PARAM_DURATION, $duration);
	    $job->setParam(FegCronExtension::PARAM_TERM, $term);
	    
	    $job->saveConfigurationAction();
	    	    
	    DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','scheduler')));
	}	
	
	// Ajax
	function showTabFieldsAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		// Alphabetize
		$source_manifests = DevblocksPlatform::getExtensions('feg.fields.source', false);
		uasort($source_manifests, create_function('$a, $b', "return strcasecmp(\$a->name,\$b->name);\n"));
		$tpl->assign('source_manifests', $source_manifests);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/fields/index.tpl');
	}
	
	private function _getFieldSource($ext_id) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);

		$tpl->assign('ext_id', $ext_id);

		// [TODO] Make sure the extension exists before continuing
		$source_manifest = DevblocksPlatform::getExtension($ext_id, false);
		$tpl->assign('source_manifest', $source_manifest);
		
		$types = Model_CustomField::getTypes();
		$tpl->assign('types', $types);

		// Look up the defined global fields by the given extension
		$fields = DAO_CustomField::getBySource($ext_id);
		$tpl->assign('fields', $fields);
		
		$tpl->display('file:' . $this->_TPL_PATH . 'setup/tabs/fields/edit_source.tpl');
	}
	
	// Ajax
	function getFieldSourceAction() {
		$translate = DevblocksPlatform::getTranslationService();
		$worker = FegApplication::getActiveWorker();
		
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		@$ext_id = DevblocksPlatform::importGPC($_REQUEST['ext_id']);
		$this->_getFieldSource($ext_id);
	}
		
	// Post
	function saveFieldsAction() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$worker = FegApplication::getActiveWorker();
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		// Type of custom fields
		@$ext_id = DevblocksPlatform::importGPC($_POST['ext_id'],'string','');
		
		// Properties
		@$ids = DevblocksPlatform::importGPC($_POST['ids'],'array',array());
		@$names = DevblocksPlatform::importGPC($_POST['names'],'array',array());
		@$orders = DevblocksPlatform::importGPC($_POST['orders'],'array',array());
		@$options = DevblocksPlatform::importGPC($_POST['options'],'array',array());
		@$deletes = DevblocksPlatform::importGPC($_POST['deletes'],'array',array());
		
		if(!empty($ids) && !empty($ext_id))
		foreach($ids as $idx => $id) {
			@$name = $names[$idx];
			@$order = intval($orders[$idx]);
			@$option = $options[$idx];
			@$delete = (false !== array_search($id,$deletes) ? 1 : 0);
			
			if($delete) {
				DAO_CustomField::delete($id);
				
			} else {
				$fields = array(
					DAO_CustomField::NAME => $name, 
					DAO_CustomField::POS => $order, 
					DAO_CustomField::OPTIONS => !is_null($option) ? $option : '', 
				);
				DAO_CustomField::update($id, $fields);
			}
		}
		
		// Adding
		@$add_name = DevblocksPlatform::importGPC($_POST['add_name'],'string','');
		@$add_type = DevblocksPlatform::importGPC($_POST['add_type'],'string','');
		@$add_options = DevblocksPlatform::importGPC($_POST['add_options'],'string','');
		
		if(!empty($add_name) && !empty($add_type)) {
			$fields = array(
				DAO_CustomField::NAME => $add_name,
				DAO_CustomField::TYPE => $add_type,
				DAO_CustomField::SOURCE_EXTENSION => $ext_id,
				DAO_CustomField::OPTIONS => $add_options,
			);
			$id = DAO_CustomField::create($fields);
		}

		// Redraw the form
		$this->_getFieldSource($ext_id);
	}
	
	// Post
	function saveLicensesAction() {
		$translate = DevblocksPlatform::getTranslationService();
		$settings = DevblocksPlatform::getPluginSettingsService();
		$worker = FegApplication::getActiveWorker();
		
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		@$name = DevblocksPlatform::importGPC($_POST['company_name'],'string','');
		@$serial = DevblocksPlatform::importGPC($_POST['company_serial'],'string','');
		@$email = DevblocksPlatform::importGPC($_POST['email'],'string','');
		@$do_delete = DevblocksPlatform::importGPC($_POST['do_delete'],'integer',0);

		if(!empty($do_delete)) {
			$settings->set('feg.core',FegSettings::LICENSE, '');
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','settings')));
			return;
		}
		
		if(empty($name) || empty($serial) || empty($email)) {
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','settings','empty')));
			return;
		}
		
		if(null==($valid = FegLicense::validate($name, $serial, $email)) || 5 != count($valid)) {
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','settings','invalid')));
			return;
		}
		
		/*
		 * [IMPORTANT -- Yes, this is simply a line in the sand.]
		 * You're welcome to modify the code to meet your needs, but please respect 
		 * our licensing.  Buy a legitimate copy to help support the project!
		 * http://feg.answernet.com/
		 */
		$license = $valid;
		
		$settings->set('feg.core',FegSettings::LICENSE, serialize($license));
		
		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('setup','settings')));
	}
	
};
