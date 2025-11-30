function db_connect() {
    global $LM_DEBUG,$LM_DBENGINE,$LM_dbhost,$LM_dbname,$LM_dbuser,$LM_dbpass,$PDO_CONNECTION;
    
    if (!is_null($PDO_CONNECTION)) return($PDO_CONNECTION);
    
    if ($LM_DBENGINE=="MYSQL") {
        $dsn='mysql';
    } else if ($LM_DBENGINE=="PGSQL") {
        $dsn='pgsql';
    } else {
        die('Error: $LM_DBENGINE setting is missing in config.php');
    }
		
    try {
        $ret = new PDO("$dsn:host=$LM_dbhost;dbname=$LM_dbname;charset=utf8", $LM_dbuser, $LM_dbpass, array(PDO::ATTR_EMULATE_PREPARES => false, 
                                                                                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $ret->exec("SET CHARACTER SET utf8");
        
        //for MySQL 5.7.5 and newer - workaround for ONLY_FULL_GROUP_BY
        $ret->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        
    } catch(PDOException $ex) {
        if ($LM_DEBUG==1) {
                    printerr("No connection to the database.<br />MySQL reply: ".$ex->getMessage());
            } else {
                    printerr("No connection to the database. Contact your administrator and report the problem.<br/>");
        }
        loguj(dirname(__FILE__).'/../var/error.txt',"Error connecting to the database. MySQL reply: ".$ex->getMessage());
        die();
    }
    $PDO_CONNECTION=$ret;
    return($ret);
    
}
