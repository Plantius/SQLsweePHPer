	public function validate($value, $isUserFormat = false)
	{
		if (empty($value)) {
			return;
		}
		if (\is_string($value)) {
			$value = \App\Json::decode($value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$currencies = \App\Fields\Currency::getAll(true);
		foreach ($value['currencies'] ?? [] as $id => $currency) {
			if (!isset($currencies[$id])) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $id, 406);
			}
			$price = $currency['price'];
			if ($isUserFormat) {
				$price = App\Fields\Double::formatToDb($price);
			}
			if (!is_numeric($price)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $price, 406);
			}
		}
	}
