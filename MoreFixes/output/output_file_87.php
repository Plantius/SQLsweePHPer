function applycss($css) {
	printf('<link type="text/css" href="%s" rel="stylesheet">',getUrl().$css);
}
