function rsvpmaker_relay_menu_pages() {

	$parent_slug = 'edit.php?post_type=rsvpemail';

	add_submenu_page(
		$parent_slug,
		__( 'Group Email', 'rsvpmaker' ),
		__( 'Group Email', 'rsvpmaker' ),
		'manage_options',
		'rsvpmaker_relay_manual_test',
		'rsvpmaker_relay_manual_test'
	);
	add_submenu_page(
		$parent_slug,
		__( 'Group Email Log', 'rsvpmaker' ),
		__( 'Group Email Log', 'rsvpmaker' ),
		'manage_options',
		'rsvpmaker_relay_log',
		'rsvpmaker_relay_log'
	);

}
