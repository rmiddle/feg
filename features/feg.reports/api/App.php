<?php
abstract class Extension_Report extends DevblocksExtension {
	function __construct($manifest) {
		parent::DevblocksExtension($manifest);
	}
	
	function render() {
		// Overload 
	}
};

abstract class Extension_ReportGroup extends DevblocksExtension {
	function __construct($manifest) {
		parent::DevblocksExtension($manifest);
	}
};

class FegReportGroupFax extends Extension_ReportGroup {
	function __construct($manifest) {
		parent::__construct($manifest);
	}
};

class FegReportFaxDailyUsage extends Extension_Report {
	private $tpl_path = null;
	
	function __construct($manifest) {
		parent::__construct($manifest);
		$this->tpl_path = dirname(dirname(__FILE__)).'/templates';
	}
	
	function render() {
		$db = DevblocksPlatform::getDatabaseService();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->tpl_path);
		
		$tpl->display('file:' . $this->tpl_path . '/reports/fax/usage/index.tpl');
	}
	
};

