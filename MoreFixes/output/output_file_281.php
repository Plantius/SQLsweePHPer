									print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>\n";
								}
							}
							?>
						</select>
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
				</tr>
			</table>
		</form>
		<script type='text/javascript'>
		var disabled = true;

		function applyFilter() {
			strURL  = 'graph_templates.php?header=false';
			strURL += '&filter='+$('#filter').val();
			strURL += '&rows='+$('#rows').val();
			strURL += '&has_graphs='+$('#has_graphs').is(':checked');
			loadPageNoHeader(strURL);
		}

		function clearFilter() {
			strURL = 'graph_templates.php?clear=1&header=false';
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

			$('#form_graph_template').submit(function(event) {
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
		$sql_where = "WHERE (gt.name LIKE '%" . get_request_var('filter') . "%')";
	} else {
		$sql_where = '';
	}

	if (get_request_var('has_graphs') == 'true') {
		$sql_having = 'HAVING graphs>0';
	} else {
		$sql_having = '';
	}

	$total_rows = db_fetch_cell("SELECT COUNT(`rows`)
		FROM (SELECT
			COUNT(gt.id) AS `rows`,
			COUNT(gl.id) AS graphs
			FROM graph_templates AS gt
			LEFT JOIN graph_local AS gl
			ON gt.id=gl.graph_template_id
			INNER JOIN (
				SELECT *
				FROM graph_templates_graph AS gtg
				WHERE gtg.local_graph_id=0
			) AS gtg
			ON gtg.graph_template_id=gt.id
			$sql_where
			GROUP BY gt.id
			$sql_having
		) AS rs");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
