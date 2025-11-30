function aggregate_template() {
	global $aggregate_actions, $item_rows, $config;

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
			'default' => 'pgt.name',
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
			'default' => read_config_option('default_has') == 'on' ? 'true':'false'
			)
	);

	validate_store_request_vars($filters, 'sess_agg_tmp');
	/* ================= input validation ================= */

	if (get_request_var('rows') == '-1') {
		$rows = read_config_option('num_rows_table');
	} else {
		$rows = get_request_var('rows');
	}

	form_start('aggregate_templates.php', 'template');

	html_start_box(__('Aggregate Templates'), '100%', '', '3', 'center', 'aggregate_templates.php?action=edit');

	$filter_html = '<tr class="even">
		<td>
			<table class="filterTable">
				<tr>
					<td>
						' . __('Search') . '
					</td>
					<td>
						<input type="text" class="ui-state-default ui-corner-all" id="filter" size="25" value="' . html_escape_request_var('filter') . '">
					</td>
					<td>
						' . __('Templates') . '
					</td>
					<td>
						<select id="rows" onChange="applyFilter()">
						<option value="-1" ';

	if (get_request_var("rows") == "-1") {
		$filter_html .= 'selected';
	}

	$filter_html .= '>' . __('Default') . '</option>';
	if (cacti_sizeof($item_rows)) {
		foreach ($item_rows as $key => $value) {
			$filter_html .= "<option value='" . $key . "'";
			if (get_request_var("rows") == $key) {
				$filter_html .= " selected";
			}
			$filter_html .= ">" . $value . "</option>\n";
		}
	}

	$filter_html .= '</select>
					</td>
					<td>
						<span>
							<input type="checkbox" id="has_graphs" ' . (get_request_var('has_graphs') == 'true' ? 'checked':'') . ' onChange="applyFilter()">
