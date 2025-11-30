    public function getSelectorsBySpecificity($sSpecificitySearch = null) {
        if (is_numeric($sSpecificitySearch) || is_numeric($sSpecificitySearch[0])) {
            $sSpecificitySearch = "== $sSpecificitySearch";
        }
        $aResult = array();
        $this->allSelectors($aResult, $sSpecificitySearch);
        return $aResult;
    }
