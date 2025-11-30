									print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td>
						<span>
							<input type='checkbox' id='has_data' <?php print (get_request_var('has_data') == 'true' ? 'checked':'');?>>
							<label for='has_data'><?php print __('Has Data Sources');?></label>
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
				strURL  = 'data_source_profiles.php?header=false';
				strURL += '&filter='+$('#filter').val();
				strURL += '&rows='+$('#rows').val();
				strURL += '&has_data='+$('#has_data').is(':checked');
				loadPageNoHeader(strURL);
			}

			function clearFilter() {
				strURL = 'data_source_profiles.php?clear=1&header=false';
				loadPageNoHeader(strURL);
			}

			$(function() {
				$('#refresh').click(function() {
					applyFilter();
				});

				$('#has_data').click(function() {
					applyFilter();
				});

				$('#clear').click(function() {
					clearFilter();
				});

				$('#form_dsp').submit(function(event) {
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

	if (get_request_var('has_data') == 'true') {
		$sql_having = 'HAVING data_sources>0';
	} else {
		$sql_having = '';
	}

	$total_rows = db_fetch_cell("SELECT
		COUNT(`rows`)
		FROM (
			SELECT dsp.id AS `rows`,
			SUM(CASE WHEN local_data_id>0 THEN 1 ELSE 0 END) AS data_sources
			FROM data_source_profiles AS dsp
			LEFT JOIN data_template_data AS dtd
			ON dsp.id=dtd.data_source_profile_id
			$sql_where
			GROUP BY dsp.id
			$sql_having
		) AS rs");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
