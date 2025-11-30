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
		</td>
	</tr>
	<script type='text/javascript'>
	function applyFilter() {
		strURL  = 'automation_snmp.php?header=false';
		strURL += '&filter='+$('#filter').val();
		strURL += '&rows='+$('#rows').val();
		loadPageNoHeader(strURL);
	}

	function clearFilter() {
		strURL = 'automation_snmp.php?clear=1&header=false';
		loadPageNoHeader(strURL);
	}

	$(function() {
		$('#refresh').click(function() {
			applyFilter();
		});

		$('#clear').click(function() {
			clearFilter();
		});

		$('#snmp_form').submit(function(event) {
			event.preventDefault();
			applyFilter();
		});
	});
	</script>
	<?php

	html_end_box();

	/* form the 'where' clause for our main sql query */
	if (get_request_var('filter') != '') {
		$sql_where = "WHERE asnmp.name LIKE '%" . get_request_var('filter') . "%'";
	} else {
		$sql_where = '';
	}

	$total_rows = db_fetch_cell("SELECT
		COUNT(DISTINCT asnmp.id)
		FROM automation_snmp AS asnmp
		LEFT JOIN automation_networks AS anw
		ON asnmp.id=anw.snmp_id
		LEFT JOIN automation_snmp_items AS asnmpi
		ON asnmp.id=asnmpi.snmp_id
		$sql_where
		GROUP BY asnmp.id");

	$sql_order = get_order_string();
	$sql_limit = ' LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows;
