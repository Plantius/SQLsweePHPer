function generate_title($subtitle = null) {
    global $LM_APP_NAME, $lmver;
    $main_title = "$LM_APP_NAME $lmver";
    
    if (is_null($subtitle)) $title="$main_title"; else $title="$main_title - $subtitle";
    return $title;
}
