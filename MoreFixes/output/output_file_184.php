function jo_set_mask($id, $name, $type, $code){
    $handle = jocms_db_link();
    if($id != 0){
        $return = $handle->exec("UPDATE masks SET name='".$name."', code='".$code."' WHERE id='".$id."'");
        $return = $id;
    }else{
        $return = $handle->exec("INSERT INTO masks(name,type,code) VALUES ('".$handle->escapeString($name)."','".$handle->escapeString($type)."','".$handle->escapeString($code)."')");
        $return = $handle->lastInsertRowid();
    }
    return $return;
}
