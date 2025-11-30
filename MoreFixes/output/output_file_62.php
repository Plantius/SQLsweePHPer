		elseif ( $columns['TITLE'] )
		{
			$id_new = DBSeqNextID( 'school_marking_periods_marking_period_id_seq' );

			$sql = "INSERT INTO SCHOOL_MARKING_PERIODS ";

			$fields = "MARKING_PERIOD_ID,MP,SYEAR,SCHOOL_ID,";

			$values = "'" . $id_new . "','" . $_REQUEST['mp_term'] . "','" . UserSyear() . "','" . UserSchool() . "',";

			switch ( $_REQUEST['mp_term'] )
			{
				case 'SEM':
					$fields .= "PARENT_ID,";
					$values .= "'" . $_REQUEST['year_id'] . "',";
					break;

				case 'QTR':
					$fields .= "PARENT_ID,";
					$values .= "'" . $_REQUEST['semester_id'] . "',";
					break;

				case 'PRO':
					$fields .= "PARENT_ID,";
					$values .= "'" . $_REQUEST['quarter_id'] . "',";
					break;
			}

			$go = false;

			foreach ( (array) $columns as $column => $value )
			{
				if ( $column === 'START_DATE'
					|| $column === 'END_DATE'
					|| $column === 'POST_START_DATE'
					|| $column === 'POST_END_DATE' )
				{
					//FJ fix SQL bug START_DATE or END_DATE is null

					if ( ! VerifyDate( $value )
						&& $value !== ''
						|| ( ( $column === 'START_DATE'
							|| $column === 'END_DATE' )
							&& $value === '' ) )
					{
						$error[] = _( 'Not all of the dates were entered correctly.' );

						break 2;
					}

					//FJ verify END_DATE > START_DATE

					if (  ( $column === 'END_DATE'
						&& date_create( $value ) <= date_create( $columns['START_DATE'] ) )
						|| ( $column === 'POST_START_DATE'
							&& $columns['POST_END_DATE'] !== ''
							&& date_create( $value ) > date_create( $columns['POST_END_DATE'] ) ) )
					{
						$error[] = _( 'Start date must be anterior to end date.' );

						break 2;
					}
				}

				if ( ! empty( $value )
					|| $value === '0' )
				{
					$fields .= $column . ',';

					$values .= "'" . $value . "',";

					$go = true;
				}
			}

			$sql .= '(' . mb_substr( $fields, 0, -1 ) . ') values(' . mb_substr( $values, 0, -1 ) . ')';
		}
