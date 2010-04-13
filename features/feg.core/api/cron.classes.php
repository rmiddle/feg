<?php
class MaintCron extends FegCronExtension {
	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[FEG] Starting Maintenance Task");
		
		@ini_set('memory_limit','64M');

		$db = DevblocksPlatform::getDatabaseService();

		// Give plugins a chance to run maintenance (nuke NULL rows, etc.)
	    $eventMgr = DevblocksPlatform::getEventService();
	    $eventMgr->trigger(
	        new Model_DevblocksEvent(
	            'cron.maint',
                array()
            )
	    );
	  
//		// [JAS] Remove any empty directories inside storage/import/new
//		$importNewDir = APP_STORAGE_PATH . '/import/new' . DIRECTORY_SEPARATOR;
//		$subdirs = glob($importNewDir . '*', GLOB_ONLYDIR);
//		if ($subdirs !== false) {
//			foreach($subdirs as $subdir) {
//				$directory_empty = count(glob($subdir. DIRECTORY_SEPARATOR . '*')) === 0;
//				if($directory_empty && is_writeable($subdir)) {
//					rmdir($subdir);
//				}
//			}
//		}
//		
//		$logger->info('[Maint] Cleaned up import directories.');
	}

	function configure($instance) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

//		$tpl->assign('purge_waitdays', $this->getParam('purge_waitdays', 7));

		$tpl->display($tpl_path . 'cron/maint/config.tpl');
	}

	function saveConfigurationAction() {
//		@$purge_waitdays = DevblocksPlatform::importGPC($_POST['purge_waitdays'],'integer');
//		$this->setParam('purge_waitdays', $purge_waitdays);
	}
};

/**
 * Plugins can implement an event listener on the heartbeat to do any kind of
 * time-dependent or interval-based events.  For example, doing a workflow
 * action every 5 minutes.
 */
class HeartbeatCron extends FegCronExtension {
	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Heartbeat] Starting Heartbeat Task");
		
		// Heartbeat Event
		$eventMgr = DevblocksPlatform::getEventService();
		$eventMgr->trigger(
			new Model_DevblocksEvent(
	            'cron.heartbeat',
				array(
				)
			)
		);
	}

	function configure($instance) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

		$tpl->display($tpl_path . 'cron/heartbeat/config.tpl');
	}
};

/**
 * Plugins can implement an event listener on the import action being done 
 * every 1 minutes.
 */
class ImportCron extends FegCronExtension {
	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Message Import] Starting Import Task");
		
		//	System wide default should be fine will revisit if needed	
		//	@ini_set('memory_limit','128M');

		$db = DevblocksPlatform::getDatabaseService();

		// Give plugins a chance to run import
	    $eventMgr = DevblocksPlatform::getEventService();
	    $eventMgr->trigger(
	        new Model_DevblocksEvent(
	            'cron.import',
                array()
            )
	    );
		$import_sources = DAO_ImportSource::getAll();
    	foreach($import_sources as $import_source_id => $import_source) { 
			$logger->info('[Message Import] Now Processing ' . $import_source->name . ' Importer Number: ' . $import_source->id);
			
			print_r($import_source);
			
			switch($import_source->type) {
				case 0:
					self::importIXO();
					break;
				case 1:
					self::importComMon();
					break;
				case 2:
					self::importPI();
					break;
				default:
					break;
			}
	    }

		$logger->info('[Message Import] finished.');
	}

	function configure($instance) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

//		$tpl->assign('import_folder_path', $this->getParam('import_folder_path', APP_STORAGE_PATH . '/import/new'));

		$tpl->display($tpl_path . 'cron/import/config.tpl');
	}

	function saveConfigurationAction() {
//		@$import_folder_path = DevblocksPlatform::importGPC($_POST['import_folder_path'],'string');
//		$this->setParam('import_folder_path', $import_folder_path);
	}
	
	function importIXO($import_source) {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[IXO Importer] Importer started");

		return self::importCombinedComMonIXO();
	}
	
	function importComMon() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[ComMon Importer] Importer started");

		return self::importCombinedComMonIXO();
	}

	function importCombinedComMonIXO() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[ComMon / IXO Importer] Importer started");

		return NULL;		
	}

	function importPI() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[PI Importer] Importer started");

		return NULL;		
	}
	
};

