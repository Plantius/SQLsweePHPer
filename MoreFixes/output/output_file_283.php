									print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td>
						<span>
							<input type='checkbox' id='named' <?php print (get_request_var('named') == 'true' ? 'checked':'');?>>
							<label for='named'><?php print __('Named Colors');?></label>
						</span>
					</td>
					<td>
						<span>
							<input type='checkbox' id='has_graphs' <?php print (get_request_var('has_graphs') == 'true' ? 'checked':'');?>>
							<label for='has_graphs'><?php print __('Has Graphs');?></label>
						</span>
					</td>
					<td>
						<span>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='refresh' value='<?php print __esc('Go');?>' title='<?php print __esc('Set/Refresh Filters');?>'>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='clear' value='<?php print __esc('Clear');?>' title='<?php print __esc('Clear Filters');?>'>
						</span>
					</td>
					<td>
						<span>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='import' value='<?php print __esc('Import');?>' title='<?php print __esc('Import Colors');?>'>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='export' value='<?php print __esc('Export');?>' title='<?php print __esc('Export Colors');?>'>
						</span>
					</td>
				</tr>
			</table>
			</form>
			<script type='text/javascript'>
			function applyFilter() {
				strURL  = 'color.php?header=false';
				strURL += '&filter='+$('#filter').val();
				strURL += '&rows='+$('#rows').val();
				strURL += '&has_graphs='+$('#has_graphs').is(':checked');
				strURL += '&named='+$('#named').is(':checked');
				loadPageNoHeader(strURL);
			}

			function clearFilter() {
				strURL = 'color.php?clear=1&header=false';
				loadPageNoHeader(strURL);
			}

			$(function() {
				$('#refresh').click(function() {
					applyFilter();
				});

				$('#has_graphs').click(function() {
					applyFilter();
				});

				$('#named').click(function() {
					applyFilter();
				});

				$('#clear').click(function() {
					clearFilter();
				});

				$('#form_color').submit(function(event) {
					event.preventDefault();
					applyFilter();
				});

				$('#import').click(function(event) {
					strURL = 'color.php?action=import&header=false';
					loadPageNoHeader(strURL);
				});

				$('#export').click(function(event) {
					strURL = 'color.php?action=export&header=false';
					document.location = strURL;
				});
			});
			</script>
		</td>
	</tr>
	<?php

	html_end_box();

	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where = "WHERE (name LIKE '%" . get_request_var('filter') . "%'
			OR hex LIKE '%" .  get_request_var('filter') . "%')";
	} else {
		$sql_where = '';
	}

	if (get_request_var('named') == 'true') {
		$sql_where .= ($sql_where != '' ? ' AND' : 'WHERE') . " read_only='on'";
	}

	if (get_request_var('has_graphs') == 'true') {
		$sql_having = 'HAVING graphs>0 OR templates>0';
	} else {
		$sql_having = '';
	}

	$total_rows = db_fetch_cell("SELECT
		COUNT(color)
		FROM (
			SELECT
			c.id AS color,
			SUM(CASE WHEN local_graph_id>0 THEN 1 ELSE 0 END) AS graphs,
			SUM(CASE WHEN local_graph_id=0 THEN 1 ELSE 0 END) AS templates
			FROM colors AS c
			LEFT JOIN (
				SELECT DISTINCT color_id, graph_template_id, local_graph_id
				FROM graph_templates_item
				WHERE color_id>0
			) AS gti
			ON gti.color_id=c.id
			$sql_where
			GROUP BY c.id
			$sql_having
		) AS rs");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
