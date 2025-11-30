function check_template($template)
{
	// Check to see if our database password is in the template
	if(preg_match('#\$config\[(([\'|"]database[\'|"])|([^\'"].*?))\]\[(([\'|"](database|hostname|password|table_prefix|username)[\'|"])|([^\'"].*?))\]#i', $template)) 
	{
		return true;
	}

	// System calls via backtick
	if(preg_match('#\$\s*\{#', $template))
	{
		return true;
	}

	// Any other malicious acts?
	// Courtesy of ZiNgA BuRgA
	if(preg_match("~\\{\\$.+?\\}~s", preg_replace('~\\{\\$+[a-zA-Z_][a-zA-Z_0-9]*((?:-\\>|\\:\\:)\\$*[a-zA-Z_][a-zA-Z_0-9]*|\\[\s*\\$*([\'"]?)[a-zA-Z_ 0-9 ]+\\2\\]\s*)*\\}~', '', $template)))
	{
		return true;
	}

	return false;
}
