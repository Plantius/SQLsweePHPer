	public function prepare_items() {
		global $wpdb;

		$this->process_bulk_action();

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$per_page     = 50;
		$current_page = $this->get_pagenum();
		$offset       = 1 === $current_page ? false : $per_page * $current_page;
		// @codingStandardsIgnoreLine
		$order = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'desc';
		// @codingStandardsIgnoreLine
		$orderby = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'date_recorded';

		// @codingStandardsIgnoreLine
		$log_type   = ! empty( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : false;
		// @codingStandardsIgnoreLine
		$country    = ! empty( $_REQUEST['country'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['country'] ) ) : false;
		// @codingStandardsIgnoreLine
		$user_ip    = ! empty( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : false;

		// Define the database table.
		$database_table = $wpdb->prefix . \ZeroSpam\Includes\DB::$tables['log'];

		// Prepare the select statements.
		$select_array = array( '*' );

		// Order & add extra select statements.
		switch ( $orderby ) {
			case 'user_ip':
				$order_statement = "ORDER BY user_ip $order";
				break;
			case 'country':
				$order_statement = "ORDER BY country $order";
				break;
			case 'region':
				$order_statement = "ORDER BY country $order";
				break;
			case 'date_recorded':
				$order_statement = "ORDER BY date_recorded $order";
				break;
			case 'log_type':
				$order_statement = "ORDER BY log_type $order";
				break;
		}

		// Where.
		$where_array = array();

		if ( $log_type ) {
			$where_array[] = "log_type = '$log_type'";
		}

		if ( $country ) {
			$where_array[] = "country = '$country'";
		}

		if ( $user_ip ) {
			$where_array[] = "user_ip = '$user_ip'";
		}

		// Limit.
		$limit_statement = "LIMIT $per_page";
		if ( $offset ) {
			$limit_statement .= ", $offset";
		}

		// Create the query.
		$database_query = 'SELECT ';

		$select_statement = implode( ', ', $select_array );
		$database_query  .= $select_statement . ' ';

		$database_query .= "FROM $database_table ";

		if ( $where_array ) {
			$database_query .= 'WHERE ';
			$database_query .= implode( ' AND ', $where_array );
		}

		if ( ! empty( $order_statement ) ) {
			$database_query .= $order_statement . ' ';
		}

		$database_query .= $limit_statement;

		// @codingStandardsIgnoreLine
		$data = $wpdb->get_results( $database_query, ARRAY_A );

		if ( ! $data ) {
			return false;
		}

		// Get total number of rows.
		$count_query = "SELECT COUNT(*) FROM $database_table ";

		if ( $where_array ) {
			$count_query .= 'WHERE ';
			$count_query .= implode( ' AND ', $where_array );
		}

		// @codingStandardsIgnoreLine
		$total_items = $wpdb->get_var( $count_query );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
				'orderby'     => $orderby,
				'order'       => $order,
			)
		);

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;

		$paging_options = array();
		if ( $log_type ) {
			$paging_options['log_type'] = $log_type;
		}

		if ( $country ) {
			$paging_options['country'] = $country;
		}

		if ( $user_ip ) {
			$paging_options['s'] = $user_ip;
		}
		// @codingStandardsIgnoreLine
		$_SERVER['REQUEST_URI'] = add_query_arg( $paging_options, wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}
