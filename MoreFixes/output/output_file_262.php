				<p>" . __('Click \'Continue\' to cancel on going Network Discovery(s).') . "</p>
				<div class='itemlist'><ul>$networks_list</ul></div>
			</td>
		</tr>\n";
	}

	if (!isset($networks_array)) {
		raise_message(40);
		header('Location: automation_networks.php?header=false');
		exit;
	} else {
		$save_html = "<input type='submit' class='ui-button ui-corner-all ui-widget' value='" . __esc('Continue') . "' name='save'>";
	}

	print "<tr>
		<td colspan='2' class='saveRow'>
			<input type='hidden' name='action' value='actions'>
			<input type='hidden' name='selected_items' value='" . (isset($networks_array) ? serialize($networks_array) : '') . "'>
