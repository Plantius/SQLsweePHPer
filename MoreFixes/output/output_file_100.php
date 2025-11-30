	function __construct() {

		$this->dbhost	= 'localhost';
		$this->dbport 	= '3306';
		$this->dbname	= 'orangehrm_mysql';
		$this->dbuser	= 'root';
		$this->dbpass	= '';
		$this->version = '4.6';

		$this->emailConfiguration = dirname(__FILE__).'mailConf.php';
		$this->errorLog =  realpath(dirname(__FILE__).'/../logs/').'/';
	}
