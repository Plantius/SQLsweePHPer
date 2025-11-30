				$fieldPickListValues[$value] = \App\Language::translate($value, $this->getModuleName(),false,false);
			}
			// Protection against deleting a value that does not exist on the list
			if ('picklist' === $fieldDataType) {
				$fieldValue = $this->get('fieldvalue');
				if (!empty($fieldValue) && !isset($fieldPickListValues[$fieldValue])) {
					$fieldPickListValues[$fieldValue] = \App\Language::translate($fieldValue, $this->getModuleName(),false,false);
					$this->set('isEditableReadOnly', true);
				}
			}
		} elseif (method_exists($this->getUITypeModel(), 'getPicklistValues')) {
