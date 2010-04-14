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
			
			switch($import_source->type) {
				case 0:
					self::importIXO($import_source);
					break;
				case 1:
					self::importComMon($import_source);
					break;
				case 2:
					self::importPI($import_source);
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
	
	function importIXO(Model_ImportSource $import_source) {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[IXO Importer] Importer started");

		return self::importCombinedComMonIXO($import_source);
	}
	
	function importComMon(Model_ImportSource $import_source) {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[ComMon Importer] Importer started");

		return self::importCombinedComMonIXO($import_source);
	}

	function importCombinedComMonIXO(Model_ImportSource $import_source) {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[ComMon / IXO Importer] Importer started");
		
		$memory_limit = ini_get('memory_limit');
		if(substr($memory_limit, 0, -1)  < 128) {
			@ini_set('memory_limit','128M');
		}
		
		@set_time_limit(0); // Unlimited (if possible)
		 
		$logger->info("[Importer] Overloaded memory_limit to: " . ini_get('memory_limit'));
		$logger->info("[Importer] Overloaded max_execution_time to: " . ini_get('max_execution_time'));
		
		$timeout = ini_get('max_execution_time');
		$runtime = microtime(true);
		
		$dir = $import_source->path;
		if(!is_writable($dir)) {
			$logger->error("[Importer] Unable to write in '$dir'.  Please check permissions.");
			return;
		}

		if(substr($dir,-1,1) != DIRECTORY_SEPARATOR) $dir .= DIRECTORY_SEPARATOR;
		$files = glob($dir . '*.txt');
		if ($files === false) $files = array();
			 
		foreach($files as $file) {
			// If we can't nuke the file, there's no sense in trying to import it
			if(!is_writeable($file))
				continue;

			$this->_parseFile($file);
			
		//	unset($files);
		}
		return NULL;		
	}

	function importPI(Model_ImportSource $import_source) {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[PI Importer] Importer started");

		return NULL;		
	}

	function _parseFile($full_filename) {
		$logger = DevblocksPlatform::getConsoleLog();
		
		$fileparts = pathinfo($full_filename);
		$logger->info("[Parser] Reading ".$fileparts['basename']."...");
		
		

		//@unlink($full_filename);
	}

};

