function message_sent($opcje='') {
	global $USERSTABLE;
	$sql="SELECT m.*, a1.login AS od, a2.login AS do FROM `message_sent` AS m LEFT JOIN `$USERSTABLE` AS a1 ON m.msgfrom = a1.`userID` LEFT JOIN `$USERSTABLE` AS a2 ON m.msgto = a2.`userID` $opcje ORDER BY m.id DESC";
	$result=db_asocquery($sql);
	return($result);
}
