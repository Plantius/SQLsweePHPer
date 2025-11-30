  public function setTestUrgency($testplan_id, $tc_id, $urgency)
  {
    $sql = " UPDATE {$this->tables['testplan_tcversions']} SET urgency={$urgency} " .
           " WHERE testplan_id=" . $this->db->prepare_int($testplan_id) .
           " AND tcversion_id=" . $this->db->prepare_int($tc_id);
    $result = $this->db->exec_query($sql);

    return $result ? tl::OK : tl::ERROR;
  }
