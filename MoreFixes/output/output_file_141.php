function filter_description($description) {
    return preg_replace('/[\x7F-\xFF]/','',$description);
}
