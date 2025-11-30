	public static function CanTrustFormLayoutContent($sPostedFormManagerData, $aOriginalFormProperties)
	{
		$aPostedFormManagerData = json_decode($sPostedFormManagerData, true);
		$sPostedFormLayoutType = (isset($aPostedFormManagerData['formproperties']['layout']['type'])) ? $aPostedFormManagerData['formproperties']['layout']['type'] : '';

		if ($sPostedFormLayoutType === 'xhtml') {
			return true;
		}

		// we need to parse the content so that autoclose tags are returned correctly (`<div />` => `<div></div>`)
		$oHtmlDocument = new \DOMDocument();

		$sPostedFormLayoutContent = (isset($aPostedFormManagerData['formproperties']['layout']['content'])) ? $aPostedFormManagerData['formproperties']['layout']['content'] : '';
		$oHtmlDocument->loadXML('<root>'.$sPostedFormLayoutContent.'</root>');
		$sPostedFormLayoutRendered = $oHtmlDocument->saveHTML();

		$sOriginalFormLayoutContent = (isset($aOriginalFormProperties['layout']['content'])) ? $aOriginalFormProperties['layout']['content'] : '';
		$oHtmlDocument->loadXML('<root>'.$sOriginalFormLayoutContent.'</root>');
		$sOriginalFormLayoutContentRendered = $oHtmlDocument->saveHTML();

		return ($sPostedFormLayoutRendered === $sOriginalFormLayoutContentRendered);
	}
