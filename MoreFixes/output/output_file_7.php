		$extension_translations = $ext->get_translations(); // load all translations of the extension

		// remove extension strings that do not match the search query
		if(!empty($qsearch))
		{
			for($trs=0; $trs < count($extension_translations); $trs++)
			{
				$tt_text = mb_strtolower($extension_translations[$trs]['text']);
				$tt_extension = mb_strtolower($extension_translations[$trs]['extension']);
				$tt_id = mb_strtolower($extension_translations[$trs]['node_id']);
				$tt_lang = $extension_translations[$trs]['lang'];

				if(	strpos($tt_text, $qsearch) === false	&&
					strpos($tt_extension, $qsearch) === false	&&
					strpos($tt_id, $qsearch) === false	&&
					strpos($tt_lang, $qsearch) === false	)
				{
					$extension_translations[$trs] = NULL;
				}
			}

			$extension_translations = array_filter($extension_translations);
		}

		if(!empty($extension_translations))
		{
			$extensions_translations = array_merge(
				$extensions_translations,
				$extension_translations
			);
		}
	}


	$DB->query('
		SELECT id, theme, node_id, node_type, lang, `text`, CONCAT_WS(".", node_type, "" , subtype) AS source
		  FROM nv_webdictionary
		 WHERE '.$where.'
		   AND node_type = "global"
		
		UNION 
		
		SELECT id, theme, subtype AS node_id, node_type, lang, `text`, CONCAT_WS(".", node_type, theme, subtype) AS source
		  FROM nv_webdictionary
		 WHERE '.$where.'
		   AND node_type = "theme"',
		 'array'
	);
	$resultset = $DB->result();

	// remove from theme_translations the strings already present (customized) in database
	for($dbrs=0; $dbrs < count($resultset); $dbrs++)
	{
		for($trs=0; $trs < count($theme_translations); $trs++)
		{				
			if(	$resultset[$dbrs]['node_type'] == "theme"	&&
				$resultset[$dbrs]['node_id'] == $theme_translations[$trs]['node_id']	&&
				$resultset[$dbrs]['lang'] == $theme_translations[$trs]['lang']
			)
			{
				unset($theme_translations[$trs]);
				break;
			}
		}
	}

	$dataset = array_merge($resultset, $theme_translations);

	$DB->query('
		SELECT id, extension, node_type, subtype AS node_id, lang, `text`, CONCAT_WS(".", node_type, extension, subtype) AS source
		  FROM nv_webdictionary
		 WHERE '.$where.'
		   AND node_type = "extension"',
		 'array'
	);
	$resultset = $DB->result();

	// remove from extension translations the strings already present (customized) in database
	for($dbrs=0; $dbrs < count($resultset); $dbrs++)
	{
		for($trs=0; $trs < count($extensions_translations); $trs++)
		{
			if(	$resultset[$dbrs]['node_type'] == "extension"	&&
				$resultset[$dbrs]['extension'] == $extensions_translations[$trs]['extension']	&&
				$resultset[$dbrs]['node_id'] == $extensions_translations[$trs]['node_id']	&&
				$resultset[$dbrs]['lang'] == $extensions_translations[$trs]['lang']
			)
			{
				unset($extensions_translations[$trs]);
				break;
			}
		}
	}

	$dataset = array_merge($dataset, $resultset, $extensions_translations);
	$total = count($dataset);

	// reorder dataset
	$orderby = explode(' ', $orderby);
	// [0] -> column, [1] -> asc | desc

	$dataset = array_orderby($dataset, $orderby[0], ($orderby[1]=='desc'? SORT_DESC : SORT_ASC));

	$dataset = array_slice($dataset, $offset, $max);

	return array($dataset, $total);
}
