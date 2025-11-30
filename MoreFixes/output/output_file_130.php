		function display() {
			global $path_to_root;
			
			include_once($path_to_root . '/themes/'.user_theme().'/renderer.php');

			$this->init();
			$rend = new renderer();
			$rend->wa_header();

			$rend->display_applications($this);

			$rend->wa_footer();
			$this->renderer =& $rend;
		}
