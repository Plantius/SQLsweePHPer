function getTypeID($typeName) {
    global $LM_EVEDB;
    $data = db_asocquery("SELECT * FROM `$LM_EVEDB`.`invTypes` WHERE `typeName`='$typeName';");
    if (count($data) > 0) {
        return($data[0]['typeID']);
    } else return FALSE;
}
