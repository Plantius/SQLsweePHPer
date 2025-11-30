function getUserByUsername( $username ){
    global $database_eonweb;
    
    $usersql = sqlrequest($database_eonweb,
		"SELECT U.user_id as user_id,U.user_name as user_name,U.user_passwd as user_passwd,U.user_type as user_type,
		U.user_limitation as user_limitation,R.tab_1 as readonly,R.tab_2 as operator,R.tab_6 as admin
		FROM users as U left join groups as G on U.group_id = G.group_id left join groupright as R on R.group_id=G.group_id
		WHERE U.user_name = '".$username."'",
		false,
		array((string)$username)
	);
    
    return $usersql;
}
