									print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>\n";
								}
							}
							?>
						</select>
					</td>
					<td>
						<span>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='refresh' value='<?php print __esc('Go');?>' title='<?php __esc('Set/Refresh Filters');?>'>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='clear' value='<?php print __esc('Clear');?>' title='<?php __esc('Clear Filters');?>'>
						</span>
					</td>
				</tr>
			</table>
		</form>
		<script type='text/javascript'>

		function applyFilter() {
			strURL  = 'data_input.php?header=false';
			strURL += '&filter='+$('#filter').val();
			strURL += '&rows='+$('#rows').val();
			loadPageNoHeader(strURL);
		}

		function clearFilter() {
			strURL = 'data_input.php?clear=1&header=false';
			loadPageNoHeader(strURL);
		}

		$(function() {
			$('#refresh').click(function() {
				applyFilter();
			});

			$('#clear').click(function() {
				clearFilter();
			});

			$('#form_data_input').submit(function(event) {
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
		$sql_where = "WHERE (di.name like '%" . get_request_var('filter') . "%')";
	} else {
		$sql_where = '';
	}

	$sql_where .= ($sql_where != '' ? ' AND' : 'WHERE') . " (di.hash NOT IN ('3eb92bb845b9660a7445cf9740726522', 'bf566c869ac6443b0c75d1c32b5a350e', '80e9e4c4191a5da189ae26d0e237f015', '332111d8b54ac8ce939af87a7eac0c06'))";

	$sql_where  = api_plugin_hook_function('data_input_sql_where', $sql_where);

	$total_rows = db_fetch_cell("SELECT count(*)
		FROM data_input AS di
		$sql_where");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
