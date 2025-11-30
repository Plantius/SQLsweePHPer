function secureGETnum($field) {
	$what=$_REQUEST[$field];
	if (!empty($what) && !preg_match('/^(\-){0,1}([\d]+)(\.\d+){0,1}$/',$what)) {
		printerr("Niepoprawny parametr $field.");
		die('');
	}
	return $what;
}
