			foreach ($oBlock->getSelectors() as $oSelector) {
				if ($sSpecificitySearch === null) {
					$aResult[] = $oSelector;
				} else {
					$sComparison = "\$bRes = {$oSelector->getSpecificity()} $sSpecificitySearch;";
					eval($sComparison);
					if ($bRes) {
						$aResult[] = $oSelector;
					}
				}
			}
		}
