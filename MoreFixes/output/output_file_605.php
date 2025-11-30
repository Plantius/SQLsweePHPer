	public static function runSQL($prefix = false, $file, &$error, $patterns = false, $replacements = false) {
		
		\ze\dbAdm::getTableEngine();
		$error = false;
	
		//Attempt to work out the location of the installer scripts, if not provided
		if (!$prefix) {
			$prefix = CMS_ROOT. 'zenario/admin/db_install/';
		}
	
		if (!file_exists($prefix. $file)) {
			$error = 'SQL Template File '. $file. ' does not exist.';
			return false;
		}
	
		//Build up a list of pattern replacements
		if (!$patterns) {
			//If no patterns have been set, go in with a few default patterns. Note I am assuming that the CMS
			//is running here...
			$from = ["\r", '[[DB_PREFIX]]',	'[[LATEST_REVISION_NO]]',	'[[INSTALLER_REVISION_NO]]',	'[[ZENARIO_TABLE_ENGINE]]',		'[[THEME]]'];
			$to =	['',	DB_PREFIX,		LATEST_REVISION_NO,			INSTALLER_REVISION_NO,			ZENARIO_TABLE_ENGINE,			INSTALLER_DEFAULT_THEME];
		} else {
			$from = ["\r"];
			$to = [''];
			foreach($patterns as $pattern => $replacement) {
			
				//Accept $patterns and $replacements in two different arrays with numeric keys
				if (is_array($replacements)) {
					$pattern = $replacement;
					$replacement = $replacements[$pattern];
				}
			
				$from[] = '[['. $pattern. ']]';
				if ($pattern == 'DB_PREFIX') {
					$to[] = $replacement;
				} else {
					$to[] = \ze\escape::sql($replacement);
				}
			}
		}
	
		//Get the contents of the script, do the replacements, and split up into statements
		$sqls = explode(";\n", str_replace($from, $to, file_get_contents($prefix. $file)));


		//Get the number of sql statements, check if the last one is empty and exclude it if so
		$count = count($sqls);
		trim($sqls[$count-1])? null: --$count;
	
		//Loop through and execute each statement
		for ($i = 0; $i < $count; ++$i) {
			$query = $sqls[$i];
		
			if (!$result = \ze\sql::cacheFriendlyUpdate($query)) {
				$errno = \ze\sql::errno();
				$error = \ze\sql::error();
				$error = '(Error '. $errno. ': '. $error. "). \n\n". $query. "\nFile: ". $file;
				return false;
			}
		}
	
		return true;
	}
