<?php
class FegPreferencesPage extends FegPageExtension {
	private $_TPL_PATH = '';
	
	function __construct($manifest) {
		$this->_TPL_PATH = dirname(dirname(dirname(__FILE__))) . '/templates/';
		parent::__construct($manifest);
	}
	
	function isVisible() {
		// check login
		$session = DevblocksPlatform::getSessionService();
		$visit = $session->getVisit();
		
		if(empty($visit)) {
			return false;
		} else {
			return true;
		}
	}
	
	function render() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = $this->_TPL_PATH;
		$tpl->assign('path', $tpl_path);

		$response = DevblocksPlatform::getHttpResponse();
		$path = $response->path;
		
		array_shift($path); // preferences
		
		$tab_manifests = DevblocksPlatform::getExtensions('feg.preferences.tab', false);
		$tpl->assign('tab_manifests', $tab_manifests);
		
		@$section = array_shift($path); // section
		switch($section) {
		    default:
		    	$tpl->assign('tab', $section);
				$tpl->display('file:' . $tpl_path . 'preferences/index.tpl');
				break;
		}
	}
	
	// Ajax
	function showTabAction() {
		@$ext_id = DevblocksPlatform::importGPC($_REQUEST['ext_id'],'string','');
		
		if(null != ($tab_mft = DevblocksPlatform::getExtension($ext_id)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_PreferenceTab) {
			$inst->showTab();
		}
	}
	
	// Post
	function saveTabAction() {
		@$ext_id = DevblocksPlatform::importGPC($_REQUEST['ext_id'],'string','');
		
		if(null != ($tab_mft = DevblocksPlatform::getExtension($ext_id)) 
			&& null != ($inst = $tab_mft->createInstance()) 
			&& $inst instanceof Extension_PreferenceTab) {
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
			&& $inst instanceof Extension_PreferenceTab) {
				if(method_exists($inst,$action.'Action')) {
					call_user_func(array(&$inst, $action.'Action'));
				}
		}
	}
	
	// Ajax [TODO] This should probably turn into Extension_PreferenceTab
	function showTabGeneralAction() {
		$date_service = DevblocksPlatform::getDateService();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = $this->_TPL_PATH;
		$tpl->assign('path', $tpl_path);
		
		$worker = FegApplication::getActiveWorker();
		$tpl->assign('worker', $worker);
		
		$assist_mode = intval(DAO_WorkerPref::get($worker->id, 'assist_mode', 1));
		$tpl->assign('assist_mode', $assist_mode);

		$keyboard_shortcuts = intval(DAO_WorkerPref::get($worker->id, 'keyboard_shortcuts', 1));
		$tpl->assign('keyboard_shortcuts', $keyboard_shortcuts);

		// Timezones
		$tpl->assign('timezones', $date_service->getTimezones());
		@$server_timezone = date_default_timezone_get();
		$tpl->assign('server_timezone', $server_timezone);
		
		// Languages
		$langs = DAO_Translation::getDefinedLangCodes();
		$tpl->assign('langs', $langs);
		$tpl->assign('selected_language', DAO_WorkerPref::get($worker->id,'locale','en_US')); 
		
		$tpl->display('file:' . $tpl_path . 'preferences/tabs/general.tpl');
	}
	
	// Post [TODO] This should probably turn into Extension_PreferenceTab
	function saveTabGeneralAction() {
		@$timezone = DevblocksPlatform::importGPC($_REQUEST['timezone'],'string');
		@$lang_code = DevblocksPlatform::importGPC($_REQUEST['lang_code'],'string','en_US');
	    
		$worker = FegApplication::getActiveWorker();
		$translate = DevblocksPlatform::getTranslationService();
   		$tpl = DevblocksPlatform::getTemplateService();
   		
   		// Time
   		$_SESSION['timezone'] = $timezone;
   		@date_default_timezone_set($timezone);
   		DAO_WorkerPref::set($worker->id,'timezone',$timezone);
   		
   		// Language
   		$_SESSION['locale'] = $lang_code;
   		DevblocksPlatform::setLocale($lang_code);
   		DAO_WorkerPref::set($worker->id,'locale',$lang_code);
   		
		@$new_password = DevblocksPlatform::importGPC($_REQUEST['change_pass'],'string');
		@$verify_password = DevblocksPlatform::importGPC($_REQUEST['change_pass_verify'],'string');
    	
		//[mdf] if nonempty passwords match, update worker's password
		if($new_password != "" && $new_password===$verify_password) {
			$session = DevblocksPlatform::getSessionService();
			$fields = array(
				DAO_Worker::PASS => md5($new_password)
			);
			DAO_Worker::update($worker->id, $fields);
		}

		@$assist_mode = DevblocksPlatform::importGPC($_REQUEST['assist_mode'],'integer',0);
		DAO_WorkerPref::set($worker->id, 'assist_mode', $assist_mode);

		@$keyboard_shortcuts = DevblocksPlatform::importGPC($_REQUEST['keyboard_shortcuts'],'integer',0);
		DAO_WorkerPref::set($worker->id, 'keyboard_shortcuts', $keyboard_shortcuts);

		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('preferences')));
	}
	
	function showTabNotificationsAction() {
		$visit = FegApplication::getVisit();
		$translate = DevblocksPlatform::getTranslationService();
		$active_worker = FegApplication::getActiveWorker();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->_TPL_PATH);
		
		// Select tab
		$visit->set(FegVisit::KEY_PREFERENCES_SELECTED_TAB, 'notifications');
		
		// My Notifications
		$myNotificationsView = Feg_AbstractViewLoader::getView(self::VIEW_MY_NOTIFICATIONS);
		
		$title = vsprintf($translate->_('preferences.my_notifications.view.title'), $active_worker->getName());
		
		if(null == $myNotificationsView) {
			$myNotificationsView = new View_WorkerEvent();
			$myNotificationsView->id = self::VIEW_MY_NOTIFICATIONS;
			$myNotificationsView->name = $title;
			$myNotificationsView->renderLimit = 25;
			$myNotificationsView->renderPage = 0;
			$myNotificationsView->renderSortBy = SearchFields_WorkerEvent::CREATED_DATE;
			$myNotificationsView->renderSortAsc = 0;
		}
		// Overload criteria
		$myNotificationsView->name = $title;
		$myNotificationsView->params = array(
			SearchFields_WorkerEvent::WORKER_ID => new DevblocksSearchCriteria(SearchFields_WorkerEvent::WORKER_ID,'=',$active_worker->id),
			SearchFields_WorkerEvent::IS_READ => new DevblocksSearchCriteria(SearchFields_WorkerEvent::IS_READ,'=',0),
		);
		/*
		 * [TODO] This doesn't need to save every display, but it was possible to 
		 * lose the params in the saved version of the view in the DB w/o recovery.
		 * This should be moved back into the if(null==...) check in a later build.
		 */
		Feg_AbstractViewLoader::setView($myNotificationsView->id,$myNotificationsView);
		
		$tpl->assign('view', $myNotificationsView);
		$tpl->display('file:' . $this->_TPL_PATH . 'preferences/tabs/my_notifications/index.tpl');
	}	
	
	/**
	 * Open an event, mark it read, and redirect to its URL.
	 * Used by Preferences->Notifications view.
	 *
	 */
	function redirectReadAction() {
		$worker = FegApplication::getActiveWorker();
		
		$request = DevblocksPlatform::getHttpRequest();
		$stack = $request->path;
		
		array_shift($stack); // preferences
		array_shift($stack); // redirectReadAction
		@$id = array_shift($stack); // id
		
		if(null != ($event = DAO_WorkerEvent::get($id))) {
			// Mark as read before we redirect
			DAO_WorkerEvent::update($id, array(
				DAO_WorkerEvent::IS_READ => 1
			));
			
			DAO_WorkerEvent::clearCountCache($worker->id);

			session_write_close();
			header("Location: " . $event->url);
		}
		exit;
	} 
	
	function doNotificationsMarkReadAction() {
		$worker = FegApplication::getActiveWorker();
		
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'], 'string', '');
		@$row_ids = DevblocksPlatform::importGPC($_REQUEST['row_id'],'array',array());

		if(is_array($row_ids) && !empty($row_ids)) {
			DAO_WorkerEvent::updateWhere(
				array(
					DAO_WorkerEvent::IS_READ => 1,
				), 
				sprintf("%s = %d AND %s IN (%s)",
					DAO_WorkerEvent::WORKER_ID,
					$worker->id,
					DAO_WorkerEvent::ID,
					implode(',', $row_ids)
				)
			);
			
			DAO_WorkerEvent::clearCountCache($worker->id);
		}
		
		$myEventsView = Feg_AbstractViewLoader::getView($view_id);
		$myEventsView->render();
	}
	
};
