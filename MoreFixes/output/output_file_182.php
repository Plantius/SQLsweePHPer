function jo_delete_mask($id){
    $handle = jocms_db_link();
    $result = $handle->query("DELETE FROM masks WHERE id='".$id."'");
    return $result;
}
