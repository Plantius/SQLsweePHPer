				foreach ($actions as $action) {
					// set sql failure if status set to zero on any action
					if ($action['status'] == 0) {
						$failure = 'WARNING: One or more database actions failed';
					}
				}
