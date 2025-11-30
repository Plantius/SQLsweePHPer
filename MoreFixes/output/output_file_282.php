									print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td>
						<span>
							<input type='checkbox' id='has_hosts' <?php print (get_request_var('has_hosts') == 'true' ? 'checked':'');?>>
							<label for='has_hosts'><?php print __('Has Devices');?></label>
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
		</td>
		<script type='text/javascript'>
		function applyFilter() {
			strURL  = 'host_templates.php?header=false';
			strURL += '&filter='+$('#filter').val();
			strURL += '&rows='+$('#rows').val();
			strURL += '&has_hosts='+$('#has_hosts').is(':checked');
			loadPageNoHeader(strURL);
		}

		function clearFilter() {
			strURL = 'host_templates.php?clear=1&header=false';
			loadPageNoHeader(strURL);
		}

		$(function() {
			$('#refresh, #has_hosts').click(function() {
				applyFilter();
			});

			$('#clear').click(function() {
				clearFilter();
			});

			$('#form_host_template').submit(function(event) {
				event.preventDefault();
				applyFilter();
			});
		});
		</script>
	</tr>
	<?php

	html_end_box();

	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where = "WHERE (host_template.name LIKE '%%" . get_request_var('filter') . "%%')";
	} else {
		$sql_where = '';
	}

	if (get_request_var('has_hosts') == 'true') {
		$sql_having = 'HAVING hosts>0';
	} else {
		$sql_having = '';
	}

	$total_rows = db_fetch_cell("SELECT COUNT(`rows`)
		FROM (
			SELECT
			COUNT(host_template.id) AS `rows`, COUNT(DISTINCT host.id) AS hosts
			FROM host_template
			LEFT JOIN host ON host.host_template_id=host_template.id
			$sql_where
			GROUP BY host_template.id
			$sql_having
		) AS rs");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
