function RegistrationSaveContactNameFields( $config, $values )
{
	if ( empty( $values['FIRST_NAME'] )
		|| empty( $values['LAST_NAME'] ) )
	{
		return 0;
	}

	$person_id = DBSeqNextID( 'people_person_id_seq' );

	$sql = "INSERT INTO PEOPLE ";

	$fields = 'PERSON_ID,LAST_NAME,FIRST_NAME,MIDDLE_NAME,';

	$values_sql = "'" . $person_id . "','" . $values['LAST_NAME'] . "','" . $values['FIRST_NAME'] . "','" . $values['MIDDLE_NAME'] . "',";

	if ( $config
		&& ! empty( $values['fields'] ) )
	{
		foreach ( (array) $values['fields'] as $column => $value )
		{
			if ( is_array( $value ) )
			{
				// Select Multiple from Options field type format.
				$value = implode( '||', $value ) ? '||' . implode( '||', $value ) : '';
			}

			if ( ! empty( $value )
				|| $value == '0' )
			{
				$fields .= $column . ',';

				$values_sql .= "'" . $value . "',";
			}
		}
	}

	$sql .= '(' . mb_substr( $fields, 0, -1 ) . ') values(' . mb_substr( $values_sql, 0, -1 ) . ')';

	DBQuery( $sql );

	return $person_id;
}
