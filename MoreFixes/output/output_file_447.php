	public function getValidator()
	{
		$validator = [];
		$fieldName = $this->getName();
		switch ($fieldName) {
			case 'birthday':
				$funcName = ['name' => 'lessThanToday'];
				$validator[] = $funcName;
				break;
			case 'targetenddate':
			case 'actualenddate':
			case 'enddate':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['startdate'], ];
				$validator[] = $funcName;
				break;
			case 'startdate':
				if ('Project' === $this->getModule()->get('name')) {
					$params = ['targetenddate'];
				} else {
					//for project task
					$params = ['enddate'];
				}
				$funcName = ['name' => 'lessThanDependentField',
					'params' => $params, ];
				$validator[] = $funcName;
				break;
			case 'expiry_date':
			case 'due_date':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['start_date'], ];
				$validator[] = $funcName;
				break;
			case 'sales_end_date':
				$funcName = ['name' => 'greaterThanDependentField',
					'params' => ['sales_start_date'], ];
				$validator[] = $funcName;
				break;
			case 'sales_start_date':
				$funcName = ['name' => 'lessThanDependentField',
					'params' => ['sales_end_date'], ];
				$validator[] = $funcName;
				break;
			case 'qty_per_unit':
			case 'qtyindemand':
			case 'hours':
			case 'days':
				$funcName = ['name' => 'PositiveNumber'];
				$validator[] = $funcName;
				break;
			case 'employees':
				$funcName = ['name' => 'WholeNumber'];
				$validator[] = $funcName;
				break;
			case 'related_to':
				$funcName = ['name' => 'ReferenceField'];
				$validator[] = $funcName;
				break;
			//SRecurringOrders field sepecial validators
			case 'end_period':
				$funcName1 = ['name' => 'greaterThanDependentField',
					'params' => ['start_period'], ];
				$validator[] = $funcName1;
				$funcName2 = ['name' => 'lessThanDependentField',
					'params' => ['duedate'], ];
				$validator[] = $funcName2;

			// no break
			case 'start_period':
				$funcName = ['name' => 'lessThanDependentField',
					'params' => ['end_period'], ];
				$validator[] = $funcName;
				break;
			default:
				break;
		}
		return $validator;
	}
