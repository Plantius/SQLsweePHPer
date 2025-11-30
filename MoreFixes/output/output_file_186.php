function list_rrd() {
	global $config, $item_rows, $ds_actions, $rra_path;

	/* suppress warnings */
	error_reporting(0);

	/* install the rrdclean error handler */
	set_error_handler('rrdclean_error_handler');

	/* ================= input validation and session storage ================= */
	$filters = array(
		'rows' => array(
			'filter' => FILTER_VALIDATE_INT,
			'pageset' => true,
			'default' => '-1'
			),
		'page' => array(
			'filter' => FILTER_VALIDATE_INT,
			'default' => '1'
			),
		'filter' => array(
			'filter' => FILTER_CALLBACK,
			'pageset' => true,
			'default' => '',
			'options' => array('options' => 'sanitize_search_string')
			),
		'sort_column' => array(
			'filter' => FILTER_CALLBACK,
			'default' => 'name',
			'options' => array('options' => 'sanitize_search_string')
			),
		'sort_direction' => array(
			'filter' => FILTER_CALLBACK,
			'default' => 'ASC',
			'options' => array('options' => 'sanitize_search_string')
			),
		'age' => array(
			'filter' => FILTER_VALIDATE_INT,
			'pageset' => true,
			'default' => '0'
			)
	);

	validate_store_request_vars($filters, 'sess_rrdc');

	/* ================= input validation and session storage ================= */

	if (get_request_var('rows') == '-1') {
		$rows = read_config_option('num_rows_table');
	} else {
		$rows = get_request_var('rows');
	}

	html_start_box( __('RRD Cleaner'), '100%', '', '3', 'center', '');
	filter();
	html_end_box();

	$sql_where = 'WHERE in_cacti=0';
	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where .= " AND (rc.name LIKE '%" . get_request_var('filter') . "%' OR rc.name_cache LIKE '%" . get_request_var('filter') . "%' OR dt.name LIKE '%" . get_request_var('filter') . "%')";
	}

	$secsback = get_request_var('age');

	if (get_request_var('age') == 0) {
		$sql_where .= " AND last_mod>='" . date("Y-m-d H:i:s", time()-(86400*7)) . "'";
	} else {
		$sql_where .= " AND last_mod<='" . date("Y-m-d H:i:s", (time() - $secsback)) . "'";
	}

	$total_rows = db_fetch_cell("SELECT COUNT(rc.name)
		FROM data_source_purge_temp AS rc
		LEFT JOIN data_template AS dt
		ON dt.id = rc.data_template_id
		$sql_where");

	$total_size = db_fetch_cell("SELECT ROUND(SUM(size),2)
		FROM data_source_purge_temp AS rc
		LEFT JOIN data_template AS dt
		ON dt.id = rc.data_template_id
		$sql_where");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
