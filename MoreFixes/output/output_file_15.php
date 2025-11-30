				$interval = db_fetch_cell_prepared('SELECT step
					FROM data_source_profiles
					WHERE id = ?',
					array($rra['data_source_profile_id']));

				$timespan = $rra['steps'] * $interval * $rra['rows'];

				$timespan = get_nearest_timespan($timespan);

				db_execute_prepared('UPDATE data_source_profiles_rra
					SET timespan = ?
					WHERE id = ?',
					array($timespan, $rra['id']));
			}
		}
	}
