<?php
class CustomerRecipientConfigTab extends Extension_SetupTab {
	const ID = 'feg.recipient.config.tab';

  function __construct($manifest) {
      parent::__construct($manifest);
  }

	function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(dirname(__FILE__))) . '/templates/';
		$core_tplpath = dirname(dirname(dirname(dirname(__FILE__)))) . '/feg.core/templates/';

		$worker = FegApplication::getActiveWorker();
		if(!$worker || !$worker->is_superuser) {
			echo $translate->_('common.access_denied');
			return;
		}
		
		$tpl->assign('core_tplpath', $core_tplpath);
		$tpl->assign('view_id', $view_id);
		
//		$recipient = DAO_Recipient::getAll();
//		$tpl->assign('recipient', $recipient);
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->id = 'Recipients';
		$defaults->class_name = 'Feg_RecipientView';
		
		$defaults->renderSortBy = SearchFields_Recipient::ID;
		$defaults->renderSortAsc = 0;
		
		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$tpl->assign('view', $view);
		$tpl->assign('view_fields', Feg_RecipientView::getFields());
		$tpl->assign('view_searchable_fields', Feg_RecipientView::getSearchFields());
				
		$tpl->display('file:' . $tpl_path . 'setup/tabs/recipient/index.tpl');
	}

//	function saveAnswernetAction() {
//    $tpl = DevblocksPlatform::getTemplateService();
//    $tpl_path = dirname(dirname(__FILE__)) . '/templates/';
//    $tpl->cache_lifetime = "0";

//    $tpl->display('file:' . $tpl_path . 'config_success.tpl');
//	}
};

