  public function findByMultiple($table, $where, $nr=null, $rev=null, $distinct=null)
  {
    $value=""; /* quiet the PHP Notice */
    $match=null; /* quiet the PHP Notice */
    $query="SELECT";

    if($nr!=null){
       # LIMIT doesn't exist in Oracle, so we encapsulate the query to be
       # able to filter a given number of rows afterwars (after ordering)
       $query.= " * FROM (SELECT";
    }

    if ($distinct!=null) {
      $query.= " DISTINCT " . $distinct;
    } else {
      $query.= " *";
    }
    $query.= " FROM " . $table;
    if ($where!=null){
      foreach ($where as $key=>$value) {
	if ($key!=null) {
	  if ($value!=null) $match.= " ". $key . " = '" . $value . "' and";
	  else $match.= " ". $key . " is NULL and";
	}
      }
      if ($match!=null) $query .= " WHERE" . $match;
      $query=rtrim($query, "and");
      $query=rtrim($query);
    }
    if ($rev==1) $query.= " ORDER BY id DESC";
    if ($nr!=null) {
      $query .= ") WHERE rownum < " . ($nr+1);
    }

    $result = $this->query($query, true);
    if (!$result) return false;

    if ($nr==1) {
      $row = $this->fetchArray($result);
      $this->closeCursor($result);
      return $row;
    }
    else {
      $collection=array();
      while($row = $this->fetchArray($result)){
	$collection[]=$row;
      }
      $this->closeCursor($result);
      return $collection;
    }
  }

  /**
   * main function used to delete rows by multiple key=>value pairs from Db table.
   *
   * @param string $table Database table to delete row in
   * @param array $where Array with column=>values to select rows by
   * @param int $nr Number of rows to collect. NULL=>inifinity. Default=NULL.
   * @param int $rev rev=1 indicates order should be reversed. Default=NULL.
   * @param string distinct Select rows with distinct columns, Default=NULL
   * @return boolean True on success, otherwise false.
   *
   */
  public function deleteByMultiple($table, $where, $nr=null, $rev=null)
  {
    $query="DELETE";
    $query.= " FROM " . $table;
    $query .= " WHERE id IN (SELECT id FROM " . $table;
    if ($where!=null){
      $query.= " WHERE";
      foreach ($where as $key=>$value) {
	$query.= " ". $key . " = '" . $value . "' and";
      }
      $query=rtrim($query, "and");
      $query=rtrim($query);
    }
    if ($rev==1) $query.= " ORDER BY id DESC";

    $query .= ")";
    if ($nr!=null) $query.= " and rownum < " . ($nr+1);
