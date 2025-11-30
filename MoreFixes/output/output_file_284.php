										print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . html_escape($value) . "</option>";
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
		</td>
	</tr>
	<?php
	html_end_box();

	/* form the 'where' clause for our main sql query */
	$sql_where = "WHERE (sm.hostname LIKE '%" . get_request_var('filter') . "%'
		OR sm.description LIKE '%" . get_request_var('filter') . "%')";

	$total_rows = db_fetch_cell("SELECT
		COUNT(sm.id)
		FROM snmpagent_managers AS sm
		$sql_where");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
