function printerr($text) {
	echo("<br><table class=\"error\"><tr><td>$text</td></tr></table>");
	echo('<input type="button" value="&lt; Back" onclick="history.back();">');
}
