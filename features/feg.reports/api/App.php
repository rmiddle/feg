<?php
class FegReportsPage extends FegPageExtension {
	private $tpl_path = null;
	
	function __construct($manifest) {
		parent::__construct($manifest);

		$this->tpl_path = dirname(dirname(__FILE__)).'/templates';
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

	function getActivity() {
		return new Model_Activity('activity.reports');
	}
	
	/**
	 * Proxy page actions from an extension's render() to the extension's scope.
	 *
	 */
	function actionAction() {
		@$extid = DevblocksPlatform::importGPC($_REQUEST['extid']);
		@$extid_a = DevblocksPlatform::strAlphaNumDash($_REQUEST['extid_a']);
		
		$action = $extid_a.'Action';
		
		$reportMft = DevblocksPlatform::getExtension($extid);
		
		// If it's a value report extension, proxy the action
		if(null != ($reportInst = DevblocksPlatform::getExtension($extid, true)) 
			&& $reportInst instanceof Extension_Report) {
				
			// If we asked for a value method on the extension, call it
			if(method_exists($reportInst, $action)) {
				call_user_func(array(&$reportInst, $action));
			}
		}
		
		return;
	}
	
	function render() {
		$active_worker = FegApplication::getActiveWorker();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('path', $this->tpl_path);
		
		$response = DevblocksPlatform::getHttpResponse();
		$stack = $response->path;

		array_shift($stack); // reports
		@$reportId = array_shift($stack);
		$report = null;

		// We're given a specific report to display
		if(!empty($reportId)) {
			if(null != ($reportMft = DevblocksPlatform::getExtension($reportId))) {
				// Make sure we have a report group
				if(null == ($report_group_mft_id = $reportMft->params['report_group']))
					return;
					
				// Make sure the report group exists
				if(null == ($report_group_mft = DevblocksPlatform::getExtension($report_group_mft_id)))
					return;
					
				// Check our permissions on the parent report group before rendering the report
				if(isset($report_group_mft->params['acl']) && !$active_worker->hasPriv($report_group_mft->params['acl']))
					return;
					
				// Render
				if(null != ($report = $reportMft->createInstance()) && $report instanceof Extension_Report) { /* @var $report Extension_Report */
					$report->render();
					return;
				}
			}
		}
		
		// If we don't have a selected report yet
		if(empty($report)) {
			// Organize into report groups
			$report_groups = array();
			$reportGroupMfts = DevblocksPlatform::getExtensions('feg.report.group', false);
			
			// [TODO] Alphabetize groups and nested reports
			
			// Load report groups
			if(!empty($reportGroupMfts))
			foreach($reportGroupMfts as $reportGroupMft) {
				$report_groups[$reportGroupMft->id] = array(
					'manifest' => $reportGroupMft,
					'reports' => array()
				);
			}
			
			$reportMfts = DevblocksPlatform::getExtensions('feg.report', false);
			
			// Load reports and file them under groups according to manifest
			if(!empty($reportMfts))
			foreach($reportMfts as $reportMft) {
				$report_group = $reportMft->params['report_group'];
				if(isset($report_group)) {
					$report_groups[$report_group]['reports'][] = $reportMft;
				}
			}
			
			$tpl->assign('report_groups', $report_groups);
		}

		$tpl->display('file:' . $this->tpl_path . '/reports/index.tpl');
	}
		
};

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

