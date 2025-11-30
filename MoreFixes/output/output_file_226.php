function secureGETstr($field,$len=32768,$http=false) {
	if (!$http) {
		$what=htmlspecialchars($_REQUEST[$field]);
	} else {
		$what=$_GET[$field];
	}
        //if (!get_magic_quotes_gpc()) $what=addslashes($what);
        $what=addslashes($what);
	//echo("DEBUG: $field='$what' (".strlen($what)." of $len)<br>");
	$what=substr($what,0,$len);
	return $what;
}
