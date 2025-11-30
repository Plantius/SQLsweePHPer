  public function deleteByMultiple($table, $where, $nr=null, $rev=null)
  {
    $query="DELETE";
    $query.= " FROM " . $table;
    if ($where!=null){
      $query.= " WHERE";
      foreach ($where as $key=>$value) {
	$query.= " ". $key . " = '" . $value . "' and";
      }
      $query=rtrim($query, "and");
      $query=rtrim($query);
    }
    if ($rev==1) $query.= " ORDER BY id DESC";
    if ($nr!=null) $query.= " LIMIT " . $nr;
    return $this->query($query, false);
  }
