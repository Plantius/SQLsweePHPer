function display_new_graphs($rule, $url) {
	global $config, $item_rows;

	if (isset_request_var('oclear')) {
		set_request_var('clear', 'true');
	}

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
			'default' => 'description',
			'options' => array('options' => 'sanitize_search_string')
			),
		'sort_direction' => array(
			'filter' => FILTER_CALLBACK,
			'default' => 'ASC',
			'options' => array('options' => 'sanitize_search_string')
			)
	);

	validate_store_request_vars($filters, 'sess_autog');
	/* ================= input validation ================= */

	if (isset_request_var('oclear')) {
		unset_request_var('clear');
	}

	/* if the number of rows is -1, set it to the default */
	if (get_request_var('rows') == -1) {
		$rows = read_config_option('num_rows_table');
	} else {
		$rows = get_request_var('rows');
	}

	?>
	<script type='text/javascript'>
	function applyObjectFilter() {
		strURL  = '<?php print $url;?>';
		strURL += '&rows=' + $('#orows').val();
		strURL += '&filter=' + $('#filter').val();
		strURL += '&header=false';
		loadPageNoHeader(strURL);
	}

	function clearObjectFilter() {
		strURL = '<?php print $url;?>' + '&oclear=true&header=false';
		loadPageNoHeader(strURL);
	}

	$(function() {
		$('#orefresh').click(function() {
			applyObjectFilter();
		});

		$('#oclear').click(function() {
			clearObjectFilter();
		});

		$('#orows').change(function() {
			applyObjectFilter();
		});

		$('#form_automation_objects').submit(function(event) {
			event.preventDefault();
			applyObjectFilter();
		});
	});
	</script>
	<?php

	html_start_box(__('Matching Objects'), '100%', '', '3', 'center', '');

	?>
	<tr class='even'>
		<td>
			<form id='form_automation_objects' action='<?php print html_escape($url);?>'>
				<table class='filterTable'>
					<tr>
						<td>
							<?php print __('Search');?>
						</td>
						<td>
							<input type='text' class='ui-state-default ui-corner-all' id='filter' size='25' value='<?php print html_escape_request_var('filter');?>'>
						</td>
						<td>
							<?php print __('Objects');?>
						</td>
						<td>
							<select id='orows'>
								<option value='-1'<?php if (get_request_var('rows') == '-1') {?> selected<?php }?>><?php print __('Default');?></option>
								<?php
								if (cacti_sizeof($item_rows)) {
									foreach ($item_rows as $key => $value) {
										print "<option value='". $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . $value . '</option>\n';
									}
								}
								?>
							</select>
						</td>
						<td>
							<span>
								<input type='button' class='ui-button ui-corner-all ui-widget' id='orefresh' value='<?php print __esc('Go');?>'>
								<input type='button' class='ui-button ui-corner-all ui-widget' id='oclear' value='<?php print __esc('Clear');?>'>
							</span>
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
	<?php

	html_end_box();

	/* ================= input validation ================= */
	get_filter_request_var('id');
	get_filter_request_var('snmp_query_id');
	/* ==================================================== */

	$rule_items     = array();
	$created_graphs = array();
	$created_graphs = get_created_graphs($rule);

	$total_rows         = 0;
	$num_input_fields   = 0;
	$num_visible_fields = 0;

	$snmp_query = db_fetch_row_prepared('SELECT snmp_query.id, snmp_query.name, snmp_query.xml_path
		FROM snmp_query
		WHERE snmp_query.id = ?',
		array($rule['snmp_query_id']));

	if (!cacti_sizeof($snmp_query)) {
		$name = __('Not Found');
	} else {
		$name = $snmp_query['name'];
	}

	/*
	 * determine number of input fields, if any
	 * for a dropdown selection
	 */
	$xml_array = get_data_query_array($rule['snmp_query_id']);
	if (cacti_sizeof($xml_array)) {
		/* loop through once so we can find out how many input fields there are */
		foreach ($xml_array['fields'] as $field_name => $field_array) {
			if ($field_array['direction'] == 'input' || $field_array['direction'] == 'input-output') {
				$num_input_fields++;

				if (!isset($total_rows)) {
					$total_rows = db_fetch_cell_prepared('SELECT count(*)
						FROM host_snmp_cache
						WHERE snmp_query_id = ?
						AND field_name = ?',
						array($rule['snmp_query_id'], $field_name));
				}
			}
		}
	}

	if (!isset($total_rows)) {
		$total_rows = 0;
	}

	html_start_box(__('Matching Objects [ %s ]', html_escape($name)) . display_tooltip(__('A blue font color indicates that the rule will be applied to the objects in question.  Other objects will not be subject to the rule.')), '100%', '', '3', 'center', '');

	if (cacti_sizeof($xml_array)) {
		$html_dq_header     = '';
		$sql_filter         = '';
		$sql_having         = '';
		$snmp_query_indexes = array();

		$rule_items         = db_fetch_assoc_prepared('SELECT *
			FROM automation_graph_rule_items
			WHERE rule_id = ?
			ORDER BY sequence', array($rule['id']));

		$automation_rule_fields = array_rekey(
			db_fetch_assoc_prepared('SELECT DISTINCT field
				FROM automation_graph_rule_items AS agri
				WHERE field != ""
				AND rule_id = ?',
				array($rule['id'])),
			'field', 'field'
		);

		$rule_name = db_fetch_cell_prepared('SELECT name
			FROM automation_graph_rules
			WHERE id = ?',
			array($rule['id']));

		/* get the unique field values from the database */
		$field_names = array_rekey(
			db_fetch_assoc_prepared('SELECT DISTINCT field_name
				FROM host_snmp_cache AS hsc
				WHERE snmp_query_id= ?',
				array($rule['snmp_query_id'])),
			'field_name', 'field_name'
		);

		$run_query = true;

		/* check for possible SQL errors */
		foreach($automation_rule_fields as $column) {
			if (array_search($column, $field_names) === false) {
				$run_query = false;
			}
		}

		if ($run_query) {
			/* main sql */
			if (isset($xml_array['index_order_type'])) {
				$sql_order = build_sort_order($xml_array['index_order_type'], 'automation_host');
				$sql_query = build_data_query_sql($rule) . ' ' . $sql_order;
			} else {
				$sql_query = build_data_query_sql($rule);
			}

			$results = db_fetch_cell("SELECT COUNT(*) FROM ($sql_query) AS a", '', false);
		} else {
			$results = array();
		}

		if ($results) {
			/* rule item filter first */
			$sql_filter	= build_rule_item_filter($rule_items, ' a.');

			/* filter on on the display filter next */
			$sql_having = build_graph_object_sql_having($rule, get_request_var('filter'));

			/* now we build up a new query for counting the rows */
			$rows_query = "SELECT * FROM ($sql_query) AS a " . ($sql_filter != '' ? "WHERE ($sql_filter)":'') . $sql_having;
			$total_rows = cacti_sizeof(db_fetch_assoc($rows_query));

			if ($total_rows < (get_request_var('rows')*(get_request_var('page')-1))+1) {
				set_request_var('page', '1');
			}

			$sql_query = $rows_query . ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;

			$snmp_query_indexes = db_fetch_assoc($sql_query);
		} else {
