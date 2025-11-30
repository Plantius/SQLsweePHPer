									print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td>
						<span>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='refresh' value='<?php print __x('filter: use', 'Go');?>' title='<?php print __esc('Set/Refresh Filters');?>'>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='clear' value='<?php print __esc('Clear');?>' title='<?php print __esc('Clear Filters');?>'>
						</span>
					</td>
				</tr>
			</table>
		</form>
		<script type='text/javascript'>
		function applyFilter() {
			strURL  = 'user_domains.php?rows=' + $('#rows').val();
			strURL += '&filter=' + $('#filter').val();
			strURL += '&header=false';
			loadPageNoHeader(strURL);
		}

		function clearFilter() {
			strURL = 'user_domains.php?clear=1&header=false';
			loadPageNoHeader(strURL);
		}

		$(function() {
			$('#refresh').click(function() {
				applyFilter();
			});

			$('#clear').click(function() {
				clearFilter();
			});

			$('#form_domains').submit(function(event) {
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
		$sql_where = "WHERE (domain_name LIKE '%%" . get_request_var('filter') . "%%') ||
			(type LIKE '%%" . get_request_var('filter') . "%%')";
	} else {
		$sql_where = '';
	}

	$total_rows = db_fetch_cell("SELECT
		count(*)
		FROM user_domains
		$sql_where");

	$domains = db_fetch_assoc("SELECT *
		FROM user_domains
		$sql_where
		ORDER BY " . get_request_var('sort_column') . ' ' . get_request_var('sort_direction') . '
		LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows);
