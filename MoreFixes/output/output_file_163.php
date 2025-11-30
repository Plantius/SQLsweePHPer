function getTypeName($typeID) {
    global $LM_EVEDB;
    if (!is_numeric($typeID)) return FALSE;
    
    $data = db_asocquery("SELECT * FROM `$LM_EVEDB`.`invTypes` WHERE `typeID`=$typeID;");
    if (count($data) > 0) {
        return($data[0]['typeName']);
    } else return FALSE;
}
