function project_get_all_user_rows( $p_project_id = ALL_PROJECTS, $p_access_level = ANYBODY, $p_include_global_users = true ) {
	$c_project_id = (int)$p_project_id;

	# Optimization when access_level is NOBODY
	if( NOBODY == $p_access_level ) {
		return array();
	}

	$t_on = ON;
	$t_users = array();

	$t_global_access_level = $p_access_level;
	if( $c_project_id != ALL_PROJECTS && $p_include_global_users ) {

		# looking for specific project
		if( VS_PRIVATE == project_get_field( $p_project_id, 'view_state' ) ) {
			# @todo (thraxisp) this is probably more complex than it needs to be
			# When a new project is created, those who meet 'private_project_threshold' are added
			# automatically, but don't have an entry in project_user_list_table.
			#  if they did, you would not have to add global levels.
			$t_private_project_threshold = config_get( 'private_project_threshold' );
			if( is_array( $t_private_project_threshold ) ) {
				if( is_array( $p_access_level ) ) {
					# both private threshold and request are arrays, use intersection
					$t_global_access_level = array_intersect( $p_access_level, $t_private_project_threshold );
				} else {
					# private threshold is an array, but request is a number, use values in threshold higher than request
					$t_global_access_level = array();
					foreach( $t_private_project_threshold as $t_threshold ) {
						if( $p_access_level <= $t_threshold ) {
							$t_global_access_level[] = $t_threshold;
						}
					}
				}
			} else {
				if( is_array( $p_access_level ) ) {
					# private threshold is a number, but request is an array, use values in request higher than threshold
					$t_global_access_level = array();
					foreach( $p_access_level as $t_threshold ) {
						if( $t_threshold >= $t_private_project_threshold ) {
							$t_global_access_level[] = $t_threshold;
						}
					}
				} else {
					# both private threshold and request are numbers, use maximum
					$t_global_access_level = max( $p_access_level, $t_private_project_threshold );
				}
			}
		}
	}

	if( is_array( $t_global_access_level ) ) {
		if( 0 == count( $t_global_access_level ) ) {
			$t_global_access_clause = '>= ' . NOBODY . ' ';
		} else if( 1 == count( $t_global_access_level ) ) {
			$t_global_access_clause = '= ' . array_shift( $t_global_access_level ) . ' ';
		} else {
			$t_global_access_clause = 'IN (' . implode( ',', $t_global_access_level ) . ')';
		}
	} else {
		$t_global_access_clause = '>= ' . $t_global_access_level . ' ';
	}

	if( $p_include_global_users ) {
		db_param_push();
		$t_query = 'SELECT id, username, realname, access_level
				FROM {user}
				WHERE enabled = ' . db_param() . '
					AND access_level ' . $t_global_access_clause;
		$t_result = db_query( $t_query, array( $t_on ) );

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_users[(int)$t_row['id']] = $t_row;
		}
	}

	if( $c_project_id != ALL_PROJECTS ) {
		# Get the project overrides
		db_param_push();
		$t_query = 'SELECT u.id, u.username, u.realname, l.access_level
				FROM {project_user_list} l, {user} u
				WHERE l.user_id = u.id
				AND u.enabled = ' . db_param() . '
				AND l.project_id = ' . db_param();
		$t_result = db_query( $t_query, array( $t_on, $c_project_id ) );

		while( $t_row = db_fetch_array( $t_result ) ) {
			if( is_array( $p_access_level ) ) {
				$t_keep = in_array( $t_row['access_level'], $p_access_level );
			} else {
				$t_keep = $t_row['access_level'] >= $p_access_level;
			}

			if( $t_keep ) {
				$t_users[(int)$t_row['id']] = $t_row;
			} else {
				# If user's overridden level is lower than required, so remove
				#  them from the list if they were previously there
				unset( $t_users[(int)$t_row['id']] );
			}
		}
	}
