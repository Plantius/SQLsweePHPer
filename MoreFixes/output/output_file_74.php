		function add_application($app) {	
			if ($app->enabled) // skip inactive modules
				$this->applications[$app->id] = $app;
		}
