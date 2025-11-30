function array_rwalk( &$array, $function )
{
	$key = array_keys( $array );

	$size = count( $key );

	for ( $i = 0; $i < $size; $i++ )
	{
		if ( is_array( $array[$key[$i]] ) )
		{
			array_rwalk( $array[$key[$i]], $function );
		}
		else
		{
			$array[$key[$i]] = $function( $array[$key[$i]] );
		}
	}
}
