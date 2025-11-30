									print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td>
						<span>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='refresh' value='<?php print __esc('Go');?>' title='<?php print __esc('Set/Refresh Filters');?>'>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='clear' value='<?php print __esc('Clear');?>' title='<?php print __esc('Clear Filters');?>'>
						</span>
					</td>
				</tr>
			</table>
		</form>
		</td>
	</tr>
	<?php

	html_end_box();

	$sql_where = '';

	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where = "WHERE ($table.name LIKE '%" . get_request_var('filter') . "%')";
	}

	if (!isset_request_var('state')) {
		set_request_var('status', -99);
	}

	switch (get_request_var('state')) {
		case 5:
			$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . ' status IN(1,4)';
			break;
		case -98:
			$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . ' status < 0';
			break;
		case -99:
			break;
		default:
			$sql_where .= ($sql_where != '' ? ' AND ':'WHERE ') . ' status=' . get_request_var('state');
			break;
	}

	if (get_request_var('rows') == '-1') {
		$rows = read_config_option('num_rows_table');
	} else {
		$rows = get_request_var('rows');
	}

	$total_rows = db_fetch_cell("SELECT
		count(*)
		FROM $table
		$sql_where");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
