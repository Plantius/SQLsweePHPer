function db_uquery($sql) {
    global $LM_DEBUG, $LM_READONLY,$LM_DBENGINE;
    if ($LM_READONLY==1) {
		echo("<b>Read only mode.</b><br>");
		return;
	}
	$my_link=db_connect();
	$i=0;
	$result=array();
	
        try {
            $stmt = $my_link->query($sql);
        } catch(PDOException $ex) {
            loguj(dirname(__FILE__).'/../var/error.txt',"Error in query: $sql MySQL reply: ".$ex->getMessage());
            if ($LM_DEBUG==1) {
                    printerr("Error in query: $sql<br />MySQL reply: ".$ex->getMessage());
            } else {
                    printerr("Database error. Contact your administrator and report the problem.<br/>");
            }
            die();
        }
	
        error_reporting(E_ALL & ~E_NOTICE);
	return($stmt->rowCount());
}
