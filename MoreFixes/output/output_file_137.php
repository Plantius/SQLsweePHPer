function draw_filter() {
	global $item_rows, $os_arr, $status_arr, $networks, $device_actions;

	html_start_box(__('Discovery Filters'), '100%', '', '3', 'center', '');

	?>
	<tr class='even'>
		<td class='noprint'>
		<form id='form_devices' method='get' action='automation_devices.php'>
			<table class='filterTable'>
				<tr class='noprint'>
					<td>
						<?php print __('Search');?>
					</td>
					<td>
						<input type='text' class='ui-state-default ui-corner-all' id='filter' size='25' value='<?php print html_escape_request_var('filter');?>'>
					</td>
					<td>
						<?php print __('Network');?>
					</td>
					<td>
						<select id='network' onChange='applyFilter()'>
							<option value='-1' <?php if (get_request_var('network') == -1) {?> selected<?php }?>><?php print __('Any');?></option>
							<?php
							if (cacti_sizeof($networks)) {
							foreach ($networks as $key => $name) {
								print "<option value='" . $key . "'"; if (get_request_var('network') == $key) { print ' selected'; } print '>' . $name . "</option>\n";
							}
							}
							?>
						</select>
					<td>
						<span>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='refresh' value='<?php print __esc('Go');?>' title='<?php print __esc('Set/Refresh Filters');?>'>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='clear' value='<?php print __esc('Clear');?>' title='<?php print __esc('Reset fields to defaults');?>'>
						</span>
					</td>
					<td>
						<span>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='export' value='<?php print __esc('Export');?>' title='<?php print __esc('Export to a file');?>'>
							<input type='button' class='ui-button ui-corner-all ui-widget' id='purge' value='<?php print __esc('Purge');?>' title='<?php print __esc('Purge Discovered Devices');?>'>
						</span>
					</td>
				</tr>
			</table>
			<table class='filterTable'>
				<tr>
					<td>
						<?php print __('Status');?>
					</td>
					<td>
						<select id='status' onChange='applyFilter()'>
							<option value='-1' <?php if (get_request_var('status') == '') {?> selected<?php }?>><?php print __('Any');?></option>
							<?php
							if (cacti_sizeof($status_arr)) {
							foreach ($status_arr as $st) {
								print "<option value='" . $st . "'"; if (get_request_var('status') == $st) { print ' selected'; } print '>' . $st . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td>
						<?php print __('OS');?>
					</td>
					<td>
						<select id='os' onChange='applyFilter()'>
							<option value='-1' <?php if (get_request_var('os') == '') {?> selected<?php }?>><?php print __('Any');?></option>
							<?php
							if (cacti_sizeof($os_arr)) {
							foreach ($os_arr as $st) {
								print "<option value='" . $st . "'"; if (get_request_var('os') == $st) { print ' selected'; } print '>' . $st . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td>
						<?php print __('SNMP');?>
					</td>
					<td>
						<select id='snmp' onChange='applyFilter()'>
							<option value='-1' <?php if (get_request_var('snmp') == '') {?> selected<?php }?>><?php print __('Any');?></option>
							<?php
							if (cacti_sizeof($status_arr)) {
							foreach ($status_arr as $st) {
								print "<option value='" . $st . "'"; if (get_request_var('snmp') == $st) { print ' selected'; } print '>' . $st . "</option>\n";
							}
							}
							?>
						</select>
					</td>
					<td>
						<?php print __('Devices');?>
					</td>
					<td>
						<select id='rows' onChange='applyFilter()'>
							<option value='-1'<?php print (get_request_var('rows') == '-1' ? ' selected>':'>') . __('Default');?></option>
							<?php
							if (cacti_sizeof($item_rows) > 0) {
							foreach ($item_rows as $key => $value) {
								print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . $value . "</option>\n";
							}
							}
							?>
						</select>
					</td>
				</tr>
			</table>
		</form>
		<script type='text/javascript'>

		$(function() {
			$('#refresh').click(function() {
				applyFilter();
			});

			$('#clear').click(function() {
				clearFilter();
			});

			$('#form_devices').submit(function(event) {
				event.preventDefault();
				applyFilter();
			});

			$('#purge').click(function() {
				loadPageNoHeader('automation_devices.php?header=false&action=purge&network_id='+$('#network').val());
			});

			$('#export').click(function() {
				document.location = 'automation_devices.php?action=export';
			});
		});

		function clearFilter() {
			loadPageNoHeader('automation_devices.php?header=false&clear=1');
		}

		function applyFilter() {
			strURL  = 'automation_devices.php?header=false';
			strURL += '&status=' + $('#status').val();
			strURL += '&network=' + $('#network').val();
			strURL += '&snmp=' + $('#snmp').val();
			strURL += '&os=' + $('#os').val();
			strURL += '&filter=' + $('#filter').val();
			strURL += '&rows=' + $('#rows').val();

			loadPageNoHeader(strURL);
		}

		</script>
		</td>
	</tr>
	<?php
	html_end_box();
}
