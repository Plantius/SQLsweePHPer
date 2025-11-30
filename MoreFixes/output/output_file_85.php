function aggregate_graph() {
	global $graph_actions, $item_rows;

	/* ================= input validation and session storage ================= */
	$filters = array(
		'rows' => array(
			'filter' => FILTER_VALIDATE_INT,
			'pageset' => true,
			'default' => '-1'
			),
		'template_id' => array(
			'filter' => FILTER_VALIDATE_INT,
			'pageset' => true,
			'default' => '-1'
			),
		'filter' => array(
			'filter' => FILTER_CALLBACK,
			'pageset' => true,
			'default' => '',
			'options' => array('options' => 'sanitize_search_string')
			),
		'page' => array(
			'filter' => FILTER_VALIDATE_INT,
			'default' => '1'
			),
		'sort_column' => array(
			'filter' => FILTER_CALLBACK,
			'default' => 'title_cache',
			'options' => array('options' => 'sanitize_search_string')
			),
		'sort_direction' => array(
			'filter' => FILTER_CALLBACK,
			'default' => 'ASC',
			'options' => array('options' => 'sanitize_search_string')
			),
		'local_graph_ids' => array(
			'filter' => FILTER_VALIDATE_IS_NUMERIC_LIST,
			'pageset' => true,
			'default' => ''
			)
	);

	validate_store_request_vars($filters, 'sess_agraph');
	/* ================= input validation ================= */

	if (get_request_var('rows') == -1) {
		$rows = read_config_option('num_rows_table');
	} else {
		$rows = get_request_var('rows');
	}

	?>
	<script type='text/javascript'>

	function applyFilter() {
		strURL  = 'aggregate_graphs.php';
		strURL += '?rows=' + $('#rows').val();
		strURL += '&filter=' + $('#filter').val();
		strURL += '&template_id=' + $('#template_id').val();
		strURL += '&header=false';
		loadPageNoHeader(strURL);
	}

	function clearFilter() {
		strURL = 'aggregate_graphs.php?clear=1&header=false';
		loadPageNoHeader(strURL);
	}

	$(function() {
		if ($('input[id^="agg_total"]').is(':checked') || $('#template_propogation').is(':checked')) {
			$('#agg_preview').show();
		}

		$('#refresh').click(function() {
			applyFilter();
		});

		$('#clear').click(function() {
			clearFilter();
		});

		$('#filter').change(function() {
			applyFilter();
		});

		$('#form_graphs').submit(function(event) {
			event.preventDefault();
			applyFilter();
		});
	});

	</script>
	<?php

	html_start_box(__('Aggregate Graphs') . (get_request_var('local_graph_ids') != '' ? __(' [ Custom Graphs List Applied - Clear to Reset ]'): ''), '100%', '', '3', 'center', '');
