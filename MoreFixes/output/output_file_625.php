  static function getParentGroup($group_id) {
    global $user;

    $mdb2 = getConnection();

    $sql = "select parent_id from tt_groups where id = $group_id and org_id = $user->org_id and status = 1";
    $res = $mdb2->query($sql);

    if (!is_a($res, 'PEAR_Error')) {
      $val = $res->fetchRow();
      return $val['parent_id'];
    }
    return false;
  }
