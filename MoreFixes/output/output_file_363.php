	public function createDatabase($dbname = null)
	{
		Database::query("CREATE DATABASE `" . $dbname . "`");
	}
