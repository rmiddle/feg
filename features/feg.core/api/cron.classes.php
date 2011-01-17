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
		$logger->info("[FEG] Finished Maintenance Task");
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
		$logger->info("[Heartbeat] Finished Heartbeat Task");
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
					$logger->info("[IXO Importer] Importer started");
					self::importCombined($import_source);
					break;
				case 1:
					$logger->info("[ComMon Importer] Importer started");
					self::importCombined($import_source);
					break;
				case 2:
					$logger->info("[PI Importer] Importer started");
					self::importCombined($import_source);
					break;
				default:
					break;
			}
	    }

		self::importAccountReProcess();
		
		self::importInQueueReProcess();
					
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
	
	function importAccountReProcess() {
		$db = DevblocksPlatform::getDatabaseService();
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Message] Reprocessing Messages linking them to New Accounts");

		$sql = sprintf("SELECT m.id ".
			"FROM message m ".
			"WHERE m.import_status in (0,1) ".
			"AND m.account_id = 0 "
		);
		$rs = $db->Execute($sql);
		
		// Loop though pending outbound emails.
		while($row = mysql_fetch_assoc($rs)) {
			$id = $row['id'];
			self::importAccountReProcessMessage($id);
		}
		mysql_free_result($rs);
	}
	
	function importAccountReProcessMessage($id, $set_account_id = 0) {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Message] Reprocessing message ID: ".$id);
			
		$message = DAO_Message::get($id);
			
		@$acc_name = $message->params['account_name'];
		@$import_source = $message->params['import_source'];
		if((isset($acc_name)) && (isset($import_source))) {
			// Check and see if the account exists now
			if ($set_account_id > 0) {
				$account = DAO_CustomerAccount::get($set_account_id);
			} else {
				$account = array_shift(DAO_CustomerAccount::getWhere(sprintf("%s = %d AND %s = %d AND %s = '0'",
					DAO_CustomerAccount::ACCOUNT_NUMBER,
					$acc_name,
					DAO_CustomerAccount::IMPORT_SOURCE,
					$import_source,
					DAO_CustomerAccount::IS_DISABLED
				)));
			}
			if (isset($account)) {
				$account_id = $account->id;
				$logger->info("[Message] Found Account: ". $account->account_name ." well reprocessing message id: " . $id);
			} else {
				$account_id = 0;
				$logger->info("[Message] No Account Found");
			}
			$fields = get_object_vars($message);
			$fields[DAO_Message::ACCOUNT_ID] = $account_id;
		
			$mr_status = DAO_Message::update($id, $fields);
				
			// Give plugins a chance to run export
			$eventMgr = DevblocksPlatform::getEventService();
			$eventMgr->trigger(
				new Model_DevblocksEvent(
					'cron.reprocessing.accounts',
					array(
						'message_id' => $id,
						'account_id'  => $account_id,
						'import_source' => $import_source,
					)
				)
			);
		}
	}
	
	function importInQueueReProcess() {
		$db = DevblocksPlatform::getDatabaseService();
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Message] Reprocessing Messages in-queue status");
		$sql = sprintf("SELECT m.id ".
			"FROM message m ".
			"WHERE m.import_status = 0 ".
			"AND m.account_id > 0 "
		);
		$rs = $db->Execute($sql);
		
		// Loop though pending outbound emails.
		while($row = mysql_fetch_assoc($rs)) {
			$id = $row['id'];
			self::importInQueueReProcessMessage($id);
		}
		mysql_free_result($rs);		
	}
	
	function importInQueueReProcessMessage($id) {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Message] Reprocessing message ID: ".$id);
			
		$message = DAO_Message::get($id);
			
		$status = $this->_createMessageRecipient($message->account_id, $id, $message->message);
		$fields = get_object_vars($message);
		if ($status) {
			$logger->info("[Message Import] Status set to: Complete");
			$fields[DAO_Message::IMPORT_STATUS] = 2; // 0 = In Queus, 1 = Failure, 2 = Complete
		} else {
			$logger->info("[Message Import] Status set to: Failure");
			$fields[DAO_Message::IMPORT_STATUS] = 1; // 0 = In Queus, 1 = Failure, 2 = Complete
		}
		$mr_status = DAO_Message::update($id, $fields);
	}
			
	function importCombined(Model_ImportSource $import_source) {
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
			 
		$logger->info("[Importer] Reading '$dir'");
		foreach($files as $file) {
			// If we can't nuke the file, there's no sense in trying to import it
			if(!is_writeable($file)) {
				$logger->info("[Importer] Can't write to '$file'");
				continue;
			}

			$this->_parseFile($file, $import_source);
			
		}
		return NULL;		
	}

	function _parseFile($full_filename, Model_ImportSource $import_source) {
		$logger = DevblocksPlatform::getConsoleLog();
		$db = DevblocksPlatform::getDatabaseService();
		$fail = false;
		$fail_reason = "";
		$fileparts = pathinfo($full_filename);
		$logger->info("[Parser] Reading ".$fileparts['basename']."...");
		
		$fp = fopen($full_filename, "r");
		$data = fread($fp, filesize($full_filename));
		fclose($fp); 

		// Convert all message to Unix style line ending by stripping any \r
		$data = str_replace("\r\n","\n",$data);
		$data = str_replace("\r","\n",$data);
		$message_arr = explode("\n",$data);

		switch($import_source->type) {
			case 0:
				$first_line = $message_arr[0];
				$last_line_count = count($message_arr);
				do  {
					$last_line = $message_arr[--$last_line_count];
				} while ("" == $last_line);
				
				if(preg_match('/=====\w+=====/i', $first_line, $acc_top_id)) {
					$match = sprintf('/=====%s=====/i',substr($acc_top_id[0],5,-5));
					if(preg_match($match, $last_line, $acc_id)) {
						$account_name = substr($acc_id[0],5,-5);
						$logger->info("[Parser] acc_id = ".$account_name."...");
					} else {
						$account_name = substr($acc_top_id[0],5,-5);
						$logger->info("[Parser] acc_id = ".$account_name."...");
						$fail = true;
						$fail_reason = "Message Not in the correct format";
						$logger->info("[Parser] Message Not in the correct format");
					}
				} else {
					if(preg_match('/=====\w+=====/i', $data, $acc_id)) {
						$account_name = substr($acc_id[0],5,-5);
						$logger->info("[Parser] acc_id = ".$account_name."...");
					}
					$fail = true;
					$fail_reason = "Message Not in the correct format";
					$logger->info("[Parser] Message Not in the correct format");
				}
				break;
			case 1:
				if(preg_match('/FMDS\w+/i', $data, $acc_id)) {
					$account_name = substr($acc_id[0],4);
					$logger->info("[Parser] acc_id = ".$account_name."...");
				} else {
					$fail = true;
					$logger->info("[Parser] Not in the correct format");
					$fail_reason = "Message Not in the correct format";
				}
				break;
			case 2:
				$account_name = substr($fileparts['basename'],0,-4);
				$logger->info("[Parser] account_name = ".$account_name."...");
				break;
			default:
				break;
		}
		// Store the filename and Interperted account Name and source into a Json array incase account doesn't match
		$json = json_encode(array(
			'is_fail' => $fail,
			'fail_reason' => $fail_reason,
			'import_source' => $import_source->id,
			'account_name' => $account_name,
			'file_name' => $fileparts['basename'],
		));
		
		// Now Confirm the account exists and is active
		if($fail == false) { 
			$account = array_shift(DAO_CustomerAccount::getWhere(sprintf("%s = %d AND %s = %d AND %s = '0'",
				DAO_CustomerAccount::ACCOUNT_NUMBER,
				$account_name,
				DAO_CustomerAccount::IMPORT_SOURCE,
				$import_source->id,
				DAO_CustomerAccount::IS_DISABLED
			)));
			if (isset($account))
				$account_id = $account->id;
			else
				$account_id = 0;
		} else {
			$account_id = 0;
		}
		
		if($this->_createMessage($account_id, $db->qstr($data), $json, $fail)) {
			@unlink($full_filename);
		} else {
			$logger->error("[Parser] Failed to create message ".$account_name."...");
			// Move to failed
		}
	}

	function _createMessage($account_id, $message_text, $json, $fail = false) {
		$logger = DevblocksPlatform::getConsoleLog();
		
		$current_time = time();
		$status = TRUE; // Return OK status unless something sets it to false
		//  0 = In Queus, 1 = Failure Account not found, 2 = Complet, 3 = Failure Messages Format
		if($account_id) {
			$import_status = 2; // Complete
		} else {
			if($fail == false) {
				$import_status = 1;  // Failure Account
			} else {
				$import_status = 3; // Failure Message Format
			}
		}
		$fields = array(
			DAO_Message::ACCOUNT_ID => $account_id,
			DAO_Message::IMPORT_STATUS => $import_status, 
			DAO_Message::CREATED_DATE => $current_time,
			DAO_Message::UPDATED_DATE => $current_time,
			DAO_Message::MESSAGE => $message_text,
		);
		if(false !== ($params = json_decode($json, true))) {
			$fields[DAO_Message::PARAMS] = $params;
		}
		
		$message_id = DAO_Message::create($fields);
		
		$logger->info("[Parser] Created message id = " . $message_id . "...");

		// Give plugins a chance to note a message is imported.
	    $eventMgr = DevblocksPlatform::getEventService();
	    $eventMgr->trigger(
	        new Model_DevblocksEvent(
	            'message.create',
                array(
                    'account_id' => $account_id,
                    'message_id' => $message_id,
                    'message_text' => $message_text,
					'json' => $json,
                )
            )
	    );
		
		// Now we grab the Customer Recipient and create Message Recipients
		if($account_id && $status && ($fail == false)) {
			$status = $this->_createMessageRecipient($account_id, $message_id, $message_text);
		}
		// return $status;
		if ($fail == true) {
			return false;
		} else {
			return true;
		}
	}
	
	function _createMessageRecipient($account_id, $message_id, $message_text) {
		$logger = DevblocksPlatform::getConsoleLog();
		
		$current_time = time();
		$status = TRUE; // Return TRUE status unless something sets it to false
		
		// Now we grab the Customer Recipient and create Message Recipients
		if($account_id) { // This isn't really needed but you can never be two safe
			$ids_cr = DAO_CustomerRecipient::getWhere(sprintf("%s = %d",
				DAO_CustomerRecipient::ACCOUNT_ID,
				$account_id
			));
			foreach($ids_cr as $cr_id=>$cr ) {
				if($cr->type == 255) {
					$ids_slave_cr = DAO_CustomerRecipient::getWhere(sprintf("%s = %d",
						DAO_CustomerRecipient::ACCOUNT_ID,
						$cr->address
					));
					foreach($ids_slave_cr as $cr_slave_id=>$cr_slave ) {
						$this->_createIndividualMessageRecipient($cr_slave_id, $account_id, $message_id, $message_text, $current_time);
					}
				} else {
					$this->_createIndividualMessageRecipient($cr_id, $account_id, $message_id, $message_text, $current_time);
				}
			}
		}
		return $status; 
	}
	
	function _createIndividualMessageRecipient($cr_id, $account_id, $message_id, $message_text, $current_time) {
		$logger = DevblocksPlatform::getConsoleLog();
		
		$fields = array(
			DAO_MessageRecipient::RECIPIENT_ID => $cr_id,
			DAO_MessageRecipient::MESSAGE_ID => $message_id,
			DAO_MessageRecipient::ACCOUNT_ID => $account_id,
			DAO_MessageRecipient::SEND_STATUS => 0, // 0 = New
			DAO_MessageRecipient::UPDATED_DATE => $current_time,
			DAO_MessageRecipient::CLOSED_DATE => 0, // 0 = Not Closed
		);
		$message_recipient_id = DAO_MessageRecipient::create($fields);
		$logger->info("[Parser] Message Recipient Id = ".$message_recipient_id."...");
	
		// Give plugins a chance to note a message is imported.
		$eventMgr = DevblocksPlatform::getEventService();
		$eventMgr->trigger(
			new Model_DevblocksEvent(
				'message.recipient.create',
					array(
						'account_id' => $account_id,
						'recipient_id' => $cr_id,
						'message_id' => $message_id,
						'message_recipient_id' => $message_recipient_id,
						'message_text' => $message_text,
					)
				)
			);
	}	
};

/**
 * Plugins can implement an event listener on the import action being done 
 * every 1 minutes.
 */

class StatsCron extends FegCronExtension {
	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Stats] Running Stats cleanup");
		
		//	System wide default should be fine will revisit if needed	
		//	@ini_set('memory_limit','128M');

    	$current_fields = DAO_Stats::get(0);
		$current_hour = date("G");
		if($current_fields->current_hour != $current_hour) {
			$fields = array(
				DAO_Stats::CURRENT_HOUR => $current_hour,
				DAO_Stats::FAX_CURRENT_HOUR => 0,
				DAO_Stats::FAX_LAST_HOUR => $current_fields->fax_current_hour,
				DAO_Stats::EMAIL_CURRENT_HOUR => 0,
				DAO_Stats::EMAIL_LAST_HOUR => $current_fields->email_current_hour,
				DAO_Stats::SNPP_CURRENT_HOUR => 0,
				DAO_Stats::SNPP_LAST_HOUR => $current_fields->snpp_current_hour,
			);
			$current_day = date("j");
			if($current_fields->current_day != $current_day) {
				$fields_day = array(
					DAO_Stats::CURRENT_DAY => $current_day,
					DAO_Stats::FAX_SENT_TODAY => 0,
					DAO_Stats::FAX_SENT_YESTERDAY => $current_fields->fax_sent_today,
					DAO_Stats::EMAIL_SENT_TODAY => 0,
					DAO_Stats::EMAIL_SENT_YESTERDAY => $current_fields->email_sent_today,
					DAO_Stats::SNPP_SENT_TODAY => 0,
					DAO_Stats::SNPP_SENT_YESTERDAY => $current_fields->snpp_sent_today,
				);
				$fields = array_merge($fields, $fields_day);
			}
			DAO_Stats::update(0, $fields);
		}

		$logger->info("[Stats] Running Stats Finished");
	}

	function configure($instance) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

//		$tpl->assign('import_folder_path', $this->getParam('import_folder_path', APP_STORAGE_PATH . '/import/new'));

		$tpl->display($tpl_path . 'cron/stats/config.tpl');
	}

	function saveConfigurationAction() {
//		@$import_folder_path = DevblocksPlatform::importGPC($_POST['import_folder_path'],'string');
//		$this->setParam('import_folder_path', $import_folder_path);
	}
};

class ExportEmailCron extends FegCronExtension {
	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Email Exporting] Starting Email Export Task");
		
		//	System wide default should be fine will revisit if needed	
		//	@ini_set('memory_limit','128M');

		$export_type_email = DAO_ExportType::getByType(0);
    	foreach($export_type_email as $export_type_id => $export_type) { 
			$logger->info('[Email Exporting] Now processing export number: ' . $export_type->id . " export name:  " . $export_type->name);
			self::ExportEmail($export_type);
	    }
		$logger->info('[Email Exporting] finished.');
	}

	function configure($instance) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

//		$tpl->assign('import_folder_path', $this->getParam('import_folder_path', APP_STORAGE_PATH . '/import/new'));

		$tpl->display($tpl_path . 'cron/export_email/config.tpl');
	}

	function saveConfigurationAction() {
//		@$import_folder_path = DevblocksPlatform::importGPC($_POST['import_folder_path'],'string');
//		$this->setParam('import_folder_path', $import_folder_path);
	}
	
	function ExportEmail(Model_ExportType $export_type) {
		$logger = DevblocksPlatform::getConsoleLog();
		$db = DevblocksPlatform::getDatabaseService();
		@$email_current_hour = 0;
		@$email_sent_today = 0;
		
		$memory_limit = ini_get('memory_limit');
		if(substr($memory_limit, 0, -1)  < 128) {
			@ini_set('memory_limit','128M');
		}
		
		@set_time_limit(0); // Unlimited (if possible)
		 
		$logger->info("[Email Exporter] Overloaded memory_limit to: " . ini_get('memory_limit'));
		$logger->info("[Email Exporter] Overloaded max_execution_time to: " . ini_get('max_execution_time'));
		
		$sql = sprintf("SELECT mr.id ".
			"FROM message_recipient mr ".
			"inner join customer_recipient cr on mr.recipient_id = cr.id ".
			"WHERE mr.send_status in (0,3,4) ".
			"AND cr.is_disabled = 0 ".
			"AND cr.export_type = %d ".
			"AND cr.type = 0 ",
			$export_type->id
		);
		$rs = $db->Execute($sql);
		
		// Loop though pending outbound emails.
		while($row = mysql_fetch_assoc($rs)) {
			$id = $row['id'];
			$logger->info("[Email Exporter] Procing MR ID: ".$id);
			
			$message_recipient = DAO_MessageRecipient::get($id);
			$message = DAO_Message::get($message_recipient->message_id);
			$message_lines = explode('\n',substr($message->message,1,-1));
			$recipient = DAO_CustomerRecipient::get($message_recipient->recipient_id);
			
			$to	= !empty($recipient->address_to) ? (array($recipient->address => $recipient->address_to)) : (array($recipient->address));
			$subject = $recipient->subject;
			$from_addy = !empty($export_type->params['7']) ? $export_type->params['7'] : null;
			$from_personal = !empty($export_type->params['11']) ? $export_type->params['11'] : null;
//echo "<pre>";
//print_r($export_type);
//echo "</pre>";
			// FIXME - Need to add in filter for now everything is unfiltered.
			$send_status = FegMail::sendMail($to, $subject, implode("\r\n", $message_lines), $from_addy, $from_personal);
			
			$logger->info("[Email Exporter] Send Status: " . ($send_status ? "Successful" : "Failure"));
			
			// Give plugins a chance to run export
			$eventMgr = DevblocksPlatform::getEventService();
			$eventMgr->trigger(
				new Model_DevblocksEvent(
					'cron.send.email',
					array(
						'recipient' => $recipient,
						'message' => $message,
						'message_lines' => $message_lines,
						'message_recipient' => $message_recipient,
						'send_status'  => $send_status,
					)
				)
			);
			if($send_status) {
				$email_current_hour++;
				$email_sent_today++;
			} 
			$fields = array(
           		DAO_MessageRecipient::SEND_STATUS => $send_status ? 2 : 1, // 2 = Successful // 1 = Fail
				DAO_MessageRecipient::CLOSED_DATE => $send_status ? time() : 0,
          	);
            DAO_MessageRecipient::update($id, $fields);
		}
		
		mysql_free_result($rs);

		if($email_current_hour) {
			$current_fields = DAO_Stats::get(0);
			$fields = array(
				DAO_Stats::EMAIL_CURRENT_HOUR => $current_fields->email_current_hour + $email_current_hour,
				DAO_Stats::EMAIL_SENT_TODAY => $current_fields->email_sent_today + $email_sent_today,
			);
			DAO_Stats::update(0, $fields);
		}
		
		$timeout = ini_get('max_execution_time');
		$runtime = microtime(true);
		
		return NULL;		
	}
};

class ExportFaxCron extends FegCronExtension {
	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[Fax Exporting] Starting Fax Export Task");
		
		//	System wide default should be fine will revisit if needed	
		//	@ini_set('memory_limit','128M');

		$export_type_email = DAO_ExportType::getByType(1);
    	foreach($export_type_email as $export_type_id => $export_type) { 
			$logger->info('[Fax Exporter] Now processing export number: ' . $export_type->id . " export name:  " . $export_type->name);
			self::ExportFax($export_type);
	    }
		$logger->info('[Fax Exporting] finished.');
	}

	function configure($instance) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

//		$tpl->assign('import_folder_path', $this->getParam('import_folder_path', APP_STORAGE_PATH . '/import/new'));

		$tpl->display($tpl_path . 'cron/export_fax/config.tpl');
	}

	function saveConfigurationAction() {
//		@$import_folder_path = DevblocksPlatform::importGPC($_POST['import_folder_path'],'string');
//		$this->setParam('import_folder_path', $import_folder_path);
	}
	
	function ExportFax(Model_ExportType $export_type) {
		$logger = DevblocksPlatform::getConsoleLog();
		$db = DevblocksPlatform::getDatabaseService();
		@$fax_current_hour = 0;
		@$fax_sent_today = 0;
	
		$memory_limit = ini_get('memory_limit');
		if(substr($memory_limit, 0, -1)  < 128) {
			@ini_set('memory_limit','128M');
		}
		
		@set_time_limit(0); // Unlimited (if possible)
		 
		$logger->info("[Fax Exporter] Overloaded memory_limit to: " . ini_get('memory_limit'));
		$logger->info("[Fax Exporter] Overloaded max_execution_time to: " . ini_get('max_execution_time'));
		
		$timeout = ini_get('max_execution_time');
		$runtime = microtime(true);
		
		$sql = sprintf("SELECT mr.id ".
			"FROM message_recipient mr ".
			"inner join customer_recipient cr on mr.recipient_id = cr.id ".
			"WHERE mr.send_status in (0,3,4) ".
			"AND cr.is_disabled = 0 ".
			"AND cr.export_type = %d ".
			"AND cr.type = 1 ",
			$export_type->id
		);
		$rs = $db->Execute($sql);
		
		// Loop though pending outbound emails.
		while($row = mysql_fetch_assoc($rs)) {
			$id = $row['id'];
			$logger->info("[Fax Exporter] Procing MR ID: ".$id);
			
			$message_recipient = DAO_MessageRecipient::get($id);
			$message = DAO_Message::get($message_recipient->message_id);
			$message_lines = explode('\n',substr($message->message,1,-1));
			$recipient = DAO_CustomerRecipient::get($message_recipient->recipient_id);
			$account = DAO_CustomerAccount::get($message_recipient->account_id);
			
			$message_str = implode("\r\n", $message_lines);
			
			// FIXME - Need to add in filter for now everything is unfiltered.
			// sendFax($phone_number, $message, $subject, $to, $account_name, $from=null, )
			$fax_info = FegFax::sendFax($recipient->address, $message_str, $recipient->subject, $recipient->address_to, $account->account_number);

			if($fax_info['status']) {
				$fax_current_hour++;
				$fax_sent_today++;
				$fields = array(
					DAO_MessageRecipient::SEND_STATUS => 5,
					DAO_MessageRecipient::FAX_ID => $fax_info['jobid'],
				);
				$logger->info("[FAX Exporter] Fax added to queue");
			} else {
				$fields = array(
					DAO_MessageRecipient::SEND_STATUS => 1,
					DAO_MessageRecipient::FAX_ID => 0,
				);
				$logger->info("[FAX Exporter] Failed to add fax to queue");
			}
			DAO_MessageRecipient::update($id, $fields);				
			
			// Give plugins a chance to run export
			$eventMgr = DevblocksPlatform::getEventService();
			$eventMgr->trigger(
				new Model_DevblocksEvent(
					'cron.queue.fax',
					array(
						'account' => $account,
						'recipient' => $recipient,
						'message' => $message,
						'message_lines' => $message_lines,
						'message_recipient' => $message_recipient,
						'queue_status'  => $fax_info['status'],
						'fax_id' => $fax_info['status'] ? $fax_info['jobid'] : 0,
					)
				)
			);
		}
		
		mysql_free_result($rs);

		if($fax_current_hour) {
			$current_fields = DAO_Stats::get(0);
			$fields = array(
				DAO_Stats::FAX_CURRENT_HOUR => $current_fields->fax_current_hour + $fax_current_hour,
				DAO_Stats::FAX_SENT_TODAY => $current_fields->fax_sent_today + $fax_sent_today,
			);
			DAO_Stats::update(0, $fields);
		}
		return NULL;		
	}
};

class ExportSNPPCron extends FegCronExtension {
	function run() {
		$logger = DevblocksPlatform::getConsoleLog();
		$logger->info("[SNPP Exporting] Starting SNPP Export Task");
		
		$export_type_snpp = DAO_ExportType::getByType(2);
    	foreach($export_type_snpp as $export_type_id => $export_type) { 
			$logger->info('[Fax Exporter] Now processing export number: ' . $export_type->id . " export name:  " . $export_type->name);
			self::ExportSnpp($export_type);
	    }
		$logger->info('[SNPP Exporting] finished.');
	}

	function configure($instance) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

//		$tpl->assign('import_folder_path', $this->getParam('import_folder_path', APP_STORAGE_PATH . '/import/new'));

		$tpl->display($tpl_path . 'cron/export_snpp/config.tpl');
	}

	function saveConfigurationAction() {
//		@$import_folder_path = DevblocksPlatform::importGPC($_POST['import_folder_path'],'string');
//		$this->setParam('import_folder_path', $import_folder_path);
	}
	
	function ExportSnpp(Model_ExportType $export_type) {
		$logger = DevblocksPlatform::getConsoleLog();
		$db = DevblocksPlatform::getDatabaseService();
		@$snpp_current_hour = 0;
		@$snpp_sent_today = 0;
		
		$memory_limit = ini_get('memory_limit');
		if(substr($memory_limit, 0, -1)  < 128) {
			@ini_set('memory_limit','128M');
		}
		
		@set_time_limit(0); // Unlimited (if possible)
		 
		$logger->info("[SNPP Exporter] Overloaded memory_limit to: " . ini_get('memory_limit'));
		$logger->info("[SNPP Exporter] Overloaded max_execution_time to: " . ini_get('max_execution_time'));
		
		$timeout = ini_get('max_execution_time');
		$runtime = microtime(true);
		
		$sql = sprintf("SELECT mr.id ".
			"FROM message_recipient mr ".
			"inner join customer_recipient cr on mr.recipient_id = cr.id ".
			"WHERE mr.send_status in (0,3,4) ".
			"AND cr.is_disabled = 0 ".
			"AND cr.export_type = %d ".
			"AND cr.type = 2 ",
			$export_type->id
		);
		$rs = $db->Execute($sql);
		
		// Loop though pending outbound emails.
		while($row = mysql_fetch_assoc($rs)) {
			$id = $row['id'];
			$logger->info("[SNPP Exporter] Procing MR ID: ".$id);

			$message_recipient = DAO_MessageRecipient::get($id);
			$message = DAO_Message::get($message_recipient->message_id);
			$message_lines = explode('\n',substr($message->message,1,-1));
			$recipient = DAO_CustomerRecipient::get($message_recipient->recipient_id);
			
			$message_str = substr(implode("", $message_lines),0,160);
			
			// FIXME - Need to add in filter for now everything is unfiltered.
			// sendSnpp($phone_number, $message, $snpp_server="ann100sms01.answernet.com", $port=444)
			$send_status = FegSnpp::sendSnpp($recipient->address, 	$message_str);
			
			$logger->info("[SNPP Exporter] Send Status: " . ($send_status ? "Successful" : "Failure"));
			
			// Give plugins a chance to run export
			$eventMgr = DevblocksPlatform::getEventService();
			$eventMgr->trigger(
				new Model_DevblocksEvent(
					'cron.send.snpp',
					array(
						'recipient' => $recipient,
						'message' => $message,
						'message_lines' => $message_lines,
						'message_recipient' => $message_recipient,
						'send_status'  => $send_status,
					)
				)
			);
			if($send_status) {
				$snpp_current_hour++;
				$snpp_sent_today++;
			} 
			$fields = array(
           		DAO_MessageRecipient::SEND_STATUS => $send_status ? 2 : 1, // 2 = Successful // 1 = Fail
				DAO_MessageRecipient::CLOSED_DATE => $send_status ? time() : 0,
          	);
            DAO_MessageRecipient::update($id, $fields);
		}
		
		mysql_free_result($rs);

		if($snpp_current_hour) {
			$current_fields = DAO_Stats::get(0);
			$fields = array(
				DAO_Stats::SNPP_CURRENT_HOUR => $current_fields->snpp_current_hour + $snpp_current_hour,
				DAO_Stats::SNPP_SENT_TODAY => $current_fields->snpp_sent_today + $snpp_sent_today,
			);
			DAO_Stats::update(0, $fields);
		}
		return NULL;		
	}
};
