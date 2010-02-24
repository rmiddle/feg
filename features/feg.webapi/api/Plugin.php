<?php
class PsRestPlugin {
	const PLUGIN_ID = 'feg.controller.rest';
};

if (class_exists('DevblocksTranslationsExtension',true)):
	class UmWebApiTranslations extends DevblocksTranslationsExtension {
		function __construct($manifest) {
			parent::__construct($manifest);	
		}
		
		function getTmxFile() {
			return dirname(dirname(__FILE__)) . '/strings.xml';
		}
	};
endif;
