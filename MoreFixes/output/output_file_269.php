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
			<script type='text/javascript'>
			function applyFilter() {
				strURL = 'automation_templates.php' +
					'?filter='     + $('#filter').val() +
					'&rows='       + $('#rows').val() +
					'&has_graphs=' + $('#has_graphs').is(':checked') +
					'&header=false';
				loadPageNoHeader(strURL);
			}

			function clearFilter() {
				strURL = 'automation_templates.php?clear=1&header=false';
				loadPageNoHeader(strURL);
			}

			$(function() {
				$('#refresh').click(function() {
					applyFilter();
				});

				$('#clear').click(function() {
					clearFilter();
				});

				$('#form_at').submit(function(event) {
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
		$sql_where = "WHERE (name LIKE '%" . get_request_var('filter') . "%' OR " .
			"sysName LIKE '%" . get_request_var('filter') . "%' OR " .
			"sysDescr LIKE '%" . get_request_var('filter') . "%' OR " .
			"sysOID LIKE '%" . get_request_var('filter') . "%')";
	} else {
		$sql_where = '';
	}

	$total_rows = db_fetch_cell("SELECT COUNT(*)
		FROM automation_templates AS at
		LEFT JOIN host_template AS ht
		ON ht.id=at.host_template
		$sql_where");

	$dts = db_fetch_assoc("SELECT at.*, ht.name
		FROM automation_templates AS at
		LEFT JOIN host_template AS ht
		ON ht.id=at.host_template
		$sql_where
		ORDER BY sequence " .
		' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows);
