	static function FromJSON($sJson)
	{
		if (is_array($sJson))
		{
			$aJson = $sJson;
		}
		else
		{
			$aJson = json_decode($sJson, true);
		}

		/** @var \Combodo\iTop\Portal\Form\ObjectFormManager $oFormManager */
		$oFormManager = parent::FromJSON($sJson);

		// Retrieving object to edit
		if (!isset($aJson['formobject_class']))
		{
			throw new Exception('Object class must be defined in order to generate the form');
		}
		$sObjectClass = $aJson['formobject_class'];

		if (!isset($aJson['formobject_id']))
		{
			$oObject = MetaModel::NewObject($sObjectClass);
		}
		else
		{
			// Note : AllowAllData set to true here instead of checking scope's flag because we are displaying a value that has been set and validated
			$oObject = MetaModel::GetObject($sObjectClass, $aJson['formobject_id'], true, true);
		}
		$oFormManager->SetObject($oObject);

		// Retrieving form mode
		if (!isset($aJson['formmode']))
		{
			throw new Exception('Form mode must be defined in order to generate the form');
		}
		$oFormManager->SetMode($aJson['formmode']);

		// Retrieving actions rules
		if (isset($aJson['formactionrulestoken']))
		{
			$oFormManager->SetActionRulesToken($aJson['formactionrulestoken']);
		}

		// Retrieving form properties
		if (isset($aJson['formproperties']))
		{
			// As empty array are no passed through HTTP, this one is not always present and we have to ensure it is.
			if (!isset($aJson['formproperties']['fields']))
			{
				$aJson['formproperties']['fields'] = array();
			}
			$oFormManager->SetFormProperties($aJson['formproperties']);
		}

		// Retrieving callback urls
		if (!isset($aJson['formcallbacks']))
		{
			// TODO
		}

		return $oFormManager;
	}
