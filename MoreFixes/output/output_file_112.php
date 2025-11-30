function db_count($sql) {
	global $LM_DEBUG,$LM_DBENGINE;
	$my_link=db_connect();
	$i=0;
	$result=array();
        try {
            $stmt = $my_link->query($sql); 
            $rows = count($stmt->fetchAll(PDO::FETCH_NUM));
        } catch(PDOException $ex) {
            loguj(dirname(__FILE__).'/../var/error.txt',"Error in query: $sql MySQL reply: ".$ex->getMessage());
            if ($LM_DEBUG==1) {
                    printerr("Error in query: $sql<br />MySQL reply: ".$ex->getMessage());
            } else {
                    printerr("Database error. Contact your administrator and report the problem.<br/>");
            }
            die();
        }
        //echo("<pre>db_count($sql): "); var_dump($rows); echo('</pre>');
	return($rows);
}
