function reports() {
	global $config, $item_rows, $reports_interval;
	global $reports_actions, $attach_types;

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
		'status' => array(
			'filter' => FILTER_VALIDATE_INT,
			'default' => '-1'
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
		'has_graphs' => array(
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => array('options' => array('regexp' => '(true|false)')),
			'pageset' => true,
			'default' => 'true'
			)
	);

	validate_store_request_vars($filters, 'sess_reports');
	/* ================= input validation ================= */

	if (get_request_var('rows') == -1) {
		$rows = read_config_option('num_rows_table');
	} else {
		$rows = get_request_var('rows');
	}

	if ((!empty($_SESSION['sess_status'])) && (!isempty_request_var('status'))) {
		if ($_SESSION['sess_status'] != get_request_var('status')) {
			set_request_var('page', '1');
		}
	}

	form_start(get_reports_page(), 'form_report');

	html_start_box(__('Reports [%s]', (is_reports_admin() ? __('Administrator Level'):__('User Level'))), '100%', '', '3', 'center', get_reports_page() . '?action=edit&tab=details');

	print "<tr class='even'>
		<td>
			<table class='filterTable'>
				<tr>
					<td>
						" . __('Search') . "
					</td>
					<td>
						<input type='text' class='ui-state-default ui-corner-all' id='filter' size='25' value='" . html_escape_request_var('filter') . "'>
					</td>
					<td>
						" . __('Status') . "
					</td>
					<td>
						<select id='status' onChange='applyFilter()'>
							<option value='-1'" . (get_request_var('status') == '-1' ? ' selected':'') . ">" . __('Any') . "</option>
