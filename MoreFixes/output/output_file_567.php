  public function updateBy($table, $k, $v, $values)
  {
    $query = "";

    foreach ($values as $key=>$value){
      if (!is_null($value)) $query .= ' ' . $key . "='" . $value . "',";
      else $query .= ' ' . $key . '=NULL,';
    }
    if (! $query) {
      $this->myLog->log(LOG_DEBUG, "no values to set in query. Not updating DB");
      return true;
    }

    $query = rtrim($query, ",") . " WHERE " . $k . " = '" . $v . "'";
    // Insert UPDATE statement at beginning
    $query = "UPDATE " . $table . " SET " . $query;

    return $this->query($query, false);
  }
