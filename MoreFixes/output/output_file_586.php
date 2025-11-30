	public static function connect($dbhost = 'localhost', $dbname, $dbuser, $dbpass, $dbport = '', $reportErrors = true) {
		try {
			\ze::ignoreErrors();
				if ($dbport) {
					$con = @mysqli_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport);
				} else {
					$con = @mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
				}
			\ze::noteErrors();
		
			if ($con) {
				if (mysqli_query($con,'SET NAMES "UTF8"')
				 && mysqli_query($con,"SET collation_connection='utf8mb4_general_ci'")
				 && mysqli_query($con,"SET collation_server='utf8mb4_general_ci'")
				 && mysqli_query($con,"SET character_set_client='utf8mb4'")
				 && mysqli_query($con,"SET character_set_connection='utf8mb4'")
				 && mysqli_query($con,"SET character_set_results='utf8mb4'")
				 && mysqli_query($con,"SET character_set_server='utf8mb4'")) {
					
					if (defined('DEBUG_USE_STRICT_MODE') && DEBUG_USE_STRICT_MODE) {
						mysqli_query($con,"SET @@SESSION.sql_mode = 'STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_DATE,NO_ZERO_IN_DATE'");
					} else {
						mysqli_query($con,"SET @@SESSION.sql_mode = ''");
					}
					//N.b. we don't support the new ONLY_FULL_GROUP_BY option in 5.7, as some of our queries rely on this being disabled.
					
					return $con;
				}
			}
		} catch (\Exception $e) {
		}
	
		if ($reportErrors) {
			$errorText = 'Database connection failure, could not connect to '. $dbname. ' at '. $dbhost;
			
			if ($con) {
				\ze\db::reportError('Database error at', $errorText, @mysqli_errno($con), @mysqli_error($con));
			} else {
				\ze\db::reportError('Database error at', $errorText);
			}
		}
	
		return false;
	}
