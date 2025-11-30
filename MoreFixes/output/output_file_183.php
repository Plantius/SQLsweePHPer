function jo_get_masks($id){
    $condition = "";
    $masks = [];
    if($id != "all"){
        $condition = " WHERE id='".$id."' ";
    }else{
        $condition = " WHERE type='mask' ";
    }
    $code;
    $handle = jocms_db_link();
    $result = $handle->query("SELECT * FROM masks ".$condition." ORDER BY name");
    while($output = $result->fetchArray()){
        $masks[] = $output;
    }
    return $masks;
}
