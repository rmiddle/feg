<?php

class FegAccountTab extends Extension_HomeTab {
	const ID = 'home.tab.account';
	
	function __construct($manifest) {
		$this->_TPL_PATH = dirname(dirname(dirname(__FILE__))) . '/templates/';
		parent::__construct($manifest);
	}
		
	// Ajax
	function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		$translate = DevblocksPlatform::getTranslationService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);
		$tpl->assign('core_tplpath', $core_tplpath);
		
		$tpl->assign('view_id', $view_id);
		
		$title = $translate->_('account.tab.account.title');
		
		$defaults = new Feg_AbstractViewModel();
		$defaults->id = self::ID;
		$defaults->class_name = 'View_CustomerAccount';
		
		$defaults->renderSortBy = SearchFields_CustomerAccount::ID;
		$defaults->renderSortAsc = 0;
		$defaults->name = $title;
		
		$view = Feg_AbstractViewLoader::getView($defaults->id, $defaults);
		$tpl->assign('view', $view);
		$tpl->assign('view_fields', View_CustomerRecipient::getFields());
		$tpl->assign('view_searchable_fields', View_CustomerRecipient::getSearchFields());
				
		$tpl->display('file:' . $this->_TPL_PATH . 'home/tabs/account/index.tpl');		
	}	
		
};