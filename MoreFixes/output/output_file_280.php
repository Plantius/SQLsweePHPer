									print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td>
						<span>
							<input type="checkbox" id='has_graphs' <?php print (get_request_var('has_graphs') == 'true' ? 'checked':'');?>>
							<label for='has_graphs'><?php print __('Has Graphs');?></label>
						</span>
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
			<script type='text/javascript'>

			function applyFilter() {
				strURL  = 'gprint_presets.php?header=false';
				strURL += '&filter='+$('#filter').val();
				strURL += '&rows='+$('#rows').val();
				strURL += '&has_graphs='+$('#has_graphs').is(':checked');
				loadPageNoHeader(strURL);
			}

			function clearFilter() {
				strURL = 'gprint_presets.php?clear=1&header=false';
				loadPageNoHeader(strURL);
			}

			$(function() {
				$('#refresh').click(function() {
					applyFilter();
				});

				$('#has_graphs').click(function() {
					applyFilter();
				});

				$('#clear').click(function() {
					clearFilter();
				});

				$('#form_gprint').submit(function(event) {
					event.preventDefault();
					applyFilter();
				});
			});

			</script>
		</td>
	</tr>
	<?php

	html_end_box();

	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where = "WHERE (name LIKE '%" . get_request_var('filter') . "%')";
	} else {
		$sql_where = '';
	}

	if (get_request_var('has_graphs') == 'true') {
		$sql_having = 'HAVING graphs>0';
	} else {
		$sql_having = '';
	}

	$total_rows = db_fetch_cell("SELECT
		COUNT(`rows`)
		FROM (
			SELECT gp.id AS `rows`,
			SUM(CASE WHEN local_graph_id>0 THEN 1 ELSE 0 END) AS graphs
			FROM graph_templates_gprint AS gp
			LEFT JOIN graph_templates_item AS gti
			ON gti.gprint_id=gp.id
			$sql_where
			GROUP BY gp.id
			$sql_having
		) AS rs");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
