		function init() {
			global $SysPrefs;

			$this->menu = new menu(_('Main  Menu'));
			$this->menu->add_item(_('Main  Menu'), 'index.php');
			$this->menu->add_item(_('Logout'), '/account/access/logout.php');
			$this->applications = array();
			$this->add_application(new customers_app());
			$this->add_application(new suppliers_app());
			$this->add_application(new inventory_app());
			if (get_company_pref('use_manufacturing'))
				$this->add_application(new manufacturing_app());
			if (get_company_pref('use_fixed_assets'))
				$this->add_application(new assets_app());
			$this->add_application(new dimensions_app());
			$this->add_application(new general_ledger_app());

			hook_invoke_all('install_tabs', $this);

			$this->add_application(new setup_app());
		}
