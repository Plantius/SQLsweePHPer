									print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td>
						<span>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='refresh' value='<?php print __x('filter: use', 'Go');?>' title='<?php print __esc('Set/Refresh Filters');?>' onClick='applyFilter()'>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='clear' value='<?php print __x('filter: reset', 'Clear');?>' title='<?php print __esc('Clear Filters');?>' onClick='clearFilter()'>
						</span>
					</td>
				</tr>
			</table>
		</form>
		</td>
	</tr>
	<?php

	html_end_box();

	/* if the number of rows is -1, set it to the default */
	if (get_request_var('rows') == -1) {
		$rows = read_config_option('num_rows_table');
	} else {
		$rows = get_request_var('rows');
	}

	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where = "WHERE (name LIKE '%" . get_request_var('filter') . "%' OR description LIKE '%" . get_request_var('filter') . "%')";
	} else {
		$sql_where = '';
	}

	$total_rows = db_fetch_cell("SELECT
		COUNT(*)
		FROM user_auth_group
		$sql_where");

	$group_list = db_fetch_assoc("SELECT uag.id, uag.name, uag.description,
		uag.policy_graphs, uag.policy_hosts, uag.policy_graph_templates,
		uag.enabled, count(uagm.group_id) AS members
		FROM user_auth_group AS uag
		LEFT JOIN user_auth_group_members AS uagm
		ON uag.id = uagm.group_id
		$sql_where
		GROUP BY uag.id
		ORDER BY " . get_request_var('sort_column') . ' ' . get_request_var('sort_direction') .
		' LIMIT ' . ($rows * (get_request_var('page') - 1)) . ',' . $rows);
