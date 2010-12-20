<?php

class FegMail {
	private function __construct() {}
	
	static function getMailerDefaults() {
		$settings = DevblocksPlatform::getPluginSettingsService();

		return array(
			'host' => $settings->get('feg.core',FegSettings::SMTP_HOST,''),
			'port' => $settings->get('feg.core',FegSettings::SMTP_PORT,25),
			'auth_enabled' => $settings->get('feg.core',FegSettings::SMTP_AUTH_ENABLED,false),
			'auth_user' => $settings->get('feg.core',FegSettings::SMTP_AUTH_USER,''),
			'auth_pass' => $settings->get('feg.core',FegSettings::SMTP_AUTH_PASS,''),
			'enc' => $settings->get('feg.core',FegSettings::SMTP_ENCRYPTION_TYPE,'None'),
			'max_sends' => $settings->get('feg.core',FegSettings::SMTP_MAX_SENDS,'20'),
			'timeout' => $settings->get('feg.core',FegSettings::SMTP_TIMEOUT,'30'),
			'sender_address' => $settings->get('feg.core','default_reply_from',''),
			'sender_personal' => $settings->get('feg.core','default_reply_personal',''),
		);
	}
		
	static function send_mail($properties) {
		$status = true;
		@$toStr = $properties['to'];
		@$cc = $properties['cc'];
		@$bcc = $properties['bcc'];
		@$subject = $properties['subject'];
		@$content = $properties['content'];
		@$files = $properties['files'];

		$mail_settings = self::getMailerDefaults();
		$from = $mail_settings['sender_address'];
		$personal = $mail_settings['sender_personal'];
		
		if(empty($subject)) $subject = '(no subject)';
		
		// [JAS]: Replace any semi-colons with commas (people like using either)
		$toList = DevblocksPlatform::parseCsvString(str_replace(';', ',', $toStr));
		
		$mail_headers = array();
		$mail_headers['X-FegCompose'] = '1';
		
		// Headers needed for the ticket message
		$log_headers = new Swift_Message_Headers();
		$log_headers->setCharset(LANG_CHARSET_CODE);
		$log_headers->set('To', $toStr);
		$log_headers->set('From', !empty($personal) ? (sprintf("%s <%s>",$personal,$from)) : (sprintf('%s',$from)));
		$log_headers->set('Subject', $subject);
		$log_headers->set('Date', date('r'));
			
		foreach($log_headers->getList() as $hdr => $v) {
			if(null != ($hdr_val = $log_headers->getEncoded($hdr))) {
				if(!empty($hdr_val))
					$mail_headers[$hdr] = $hdr_val;
			}
		}
			
		try {
			$mail_service = DevblocksPlatform::getMailService();
			$mailer = $mail_service->getMailer(FegMail::getMailerDefaults());
		
			$email = $mail_service->createMessage();
		
			$email->setTo($toList);
				
			// cc
			$ccs = array();
			if(!empty($cc) && null != ($ccList = DevblocksPlatform::parseCsvString(str_replace(';',',',$cc)))) {
				$email->setCc($ccList);
			}
				
			// bcc
			if(!empty($bcc) && null != ($bccList = DevblocksPlatform::parseCsvString(str_replace(';',',',$bcc)))) {
				$email->setBcc($bccList);
			}
				
			$email->setFrom(array($from => $personal));
			$email->setSubject($subject);
			$email->generateId();
				
			$headers = $email->getHeaders();
				
			$headers->addTextHeader('X-Mailer','Fax Email Gateway (FEG) ' . APP_VERSION . ' (Build '.APP_BUILD.')');
				
			$email->setBody($content);
				
			// Mime Attachments
			if (is_array($files) && !empty($files)) {
				foreach ($files['tmp_name'] as $idx => $file) {
					if(empty($file) || empty($files['name'][$idx]))
						continue;
		
					$email->attach(Swift_Attachment::fromPath($file)->setFilename($files['name'][$idx]));
				}
			}
		
			// Headers
			foreach($email->getHeaders()->getAll() as $hdr) {
				if(null != ($hdr_val = $hdr->getFieldBody())) {
					if(!empty($hdr_val))
						$mail_headers[$hdr->getFieldName()] = $hdr_val;
				}
			}
				
			// [TODO] Allow separated addresses (parseRfcAddress)
			//		$mailer->log->enable();
			if(!@$mailer->send($email)) {
				throw new Exception('Mail failed to send: unknown reason');
			}
			//		$mailer->log->dump();
		} catch (Exception $e) {
			// Do Something
			$status = false;
		}

		// Give plugins a chance to note a message is imported.
		$eventMgr = DevblocksPlatform::getEventService();
	    $eventMgr->trigger(
	        new Model_DevblocksEvent(
	            'email.send',
                array(
                    'properties' => $properties,
					'send_status' => $status ? 2 : 1, // 2 = Successful // 1 = Fail
                )
            )
	    );

		return $status;
	}

};
