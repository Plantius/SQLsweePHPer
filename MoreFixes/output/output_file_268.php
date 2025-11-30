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
							<button type='button' class='ui-button ui-corner-all ui-widget' id='sorta' title='<?php print __esc('Sort Trees Ascending');?>'><i class='fa fa-sort-alpha-down'></i></button>
							<button type='button' class='ui-button ui-corner-all ui-widget' id='sortd' title='<?php print __esc('Sort Trees Descending');?>'><i class='fa fa-sort-alpha-up'></i></button>
						</span>
					</td>
				</tr>
			</table>
		</form>
		</td>
	</tr>
	<?php

	html_end_box();

	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where = 'WHERE (t.name LIKE ' . db_qstr('%' . get_request_var('filter') . '%') . ' OR ti.title LIKE ' . db_qstr('%' . get_request_var('filter') . '%') . ')';
	} else {
		$sql_where = '';
	}

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
