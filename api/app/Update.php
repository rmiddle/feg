<?php
class FegUpdateController extends DevblocksControllerExtension {
	function __construct($manifest) {
		parent::__construct($manifest);
	}
	
	/*
	 * Request Overload
	 */
	function handleRequest(DevblocksHttpRequest $request) {
	    @set_time_limit(0); // no timelimit (when possible)

	    $translate = DevblocksPlatform::getTranslationService();
	    
	    $stack = $request->path;
	    array_shift($stack); // update

	    $cache = DevblocksPlatform::getCacheService(); /* @var $cache _DevblocksCacheManager */
	    
	    switch(array_shift($stack)) {
	    	case 'locked':
	    		if(!DevblocksPlatform::versionConsistencyCheck()) {
	    			$url = DevblocksPlatform::getUrlService();
	    			echo "<h1>Feg - Fax Email Gateway 1.x</h1>";
	    			echo "The application is currently waiting for an administrator to finish upgrading. ".
	    				"Please wait a few minutes and then ". 
		    			sprintf("<a href='%s'>try again</a>.<br><br>",
							$url->write('c=update&a=locked')
		    			);
	    			echo sprintf("If you're an admin you may <a href='%s'>finish the upgrade</a>.",
	    				$url->write('c=update')
	    			);
	    		} else {
	    			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('login')));
	    		}
	    		break;
	    		
	    	default:
			    $path = APP_TEMP_PATH . DIRECTORY_SEPARATOR;
				$file = $path . 'feg_update_lock';	    		
				
				$settings = DevblocksPlatform::getPluginSettingsService();
				
			    $authorized_ips_str = $settings->get('feg.core',FegSettings::AUTHORIZED_IPS);
			    $authorized_ips = DevblocksPlatform::parseCrlfString($authorized_ips_str);
			    
		   	    $authorized_ip_defaults = DevblocksPlatform::parseCsvString(AUTHORIZED_IPS_DEFAULTS);
			    $authorized_ips = array_merge($authorized_ips, $authorized_ip_defaults);
				
			    // Is this IP authorized?
			    $pass = false;
				foreach ($authorized_ips as $ip)
				{
					if(substr($ip,0,strlen($ip)) == substr($_SERVER['REMOTE_ADDR'],0,strlen($ip)))
				 	{ $pass=true; break; }
				}
			    if(!$pass) {
				    echo vsprintf($translate->_('update.ip_unauthorized'), $_SERVER['REMOTE_ADDR']);
				    return;
			    }
				
			    // Check requirements
			    $errors = FegApplication::checkRequirements();
			    
			    if(!empty($errors)) {
			    	echo $translate->_('update.correct_errors');
			    	echo "<ul style='color:red;'>";
			    	foreach($errors as $error) {
			    		echo "<li>".$error."</li>";
			    	}
			    	echo "</ul>";
			    	exit;
			    }
			    
			    try {
				    // If authorized, lock and attempt update
					if(!file_exists($file) || @filectime($file)+600 < time()) { // 10 min lock
						// Log everybody out since we're touching the database
						$session = DevblocksPlatform::getSessionService();
						$session->clearAll();

						// Lock file
						touch($file);
						
						// Recursive patch
						CerberusApplication::update();
						
						// Clean up
						@unlink($file);

						$cache = DevblocksPlatform::getCacheService();
						$cache->save(APP_BUILD, "devblocks_app_build");

						// Clear all caches
						$cache->clean();
						DevblocksPlatform::getClassLoaderService()->destroy();
						
						// Clear compiled templates
						$tpl = DevblocksPlatform::getTemplateService();
						$tpl->clear_compiled_tpl();

						// Reload plugin translations
						DAO_Translation::reloadPluginStrings();

						// Redirect
				    	DevblocksPlatform::redirect(new DevblocksHttpResponse(array('login')));
	
					} else {
						echo $translate->_('update.locked_another');
					}
					
	    	} catch(Exception $e) {
	    		unlink($file);
	    		die($e->getMessage());
	    	}
	    }
		
		exit;
	}
}
