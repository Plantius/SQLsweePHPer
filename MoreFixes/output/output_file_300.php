	private function _Load_Users( $sort, $direction, &$users )
	{
		$db_weeks	= new Weeks( $this->_db );
		$current	= $db_weeks->Current();
		$direction 	= ( $direction === 'asc' ) ? 'ASC' : 'DESC';
		$sql 		= "SELECT
							u.*,
							CONCAT( u.fname, ' ', u.lname ) AS name,
							( SELECT COUNT( * ) FROM failed_logins WHERE email = u.email ) AS failed_logins,
							( SELECT COUNT( * ) FROM sessions WHERE userid = u.id ) AS active_sessions,
							COUNT( g.id ) AS remaining
						FROM
							users u
							LEFT OUTER JOIN games g ON 1 = 1
							LEFT OUTER JOIN picks p ON p.game_id = g.id AND p.user_id = u.id
						WHERE
							g.week = ? AND
							p.id IS NULL
						GROUP BY
							u.id
						ORDER BY
							{$sort} {$direction}";
		return $this->_db->select( $sql, $users, $current );
	}
