function init_hooks() {
	$plugin_file = plugin_basename( PLUGIN_FILE );

	add_action( 'init',                               __NAMESPACE__ . '\action_init' );
	add_action( 'init',                               __NAMESPACE__ . '\action_handle_posts' );
	add_action( 'admin_menu',                         __NAMESPACE__ . '\action_admin_menu' );
	add_filter( "plugin_action_links_{$plugin_file}", __NAMESPACE__ . '\plugin_action_links', 10, 4 );
	add_filter( "network_admin_plugin_action_links_{$plugin_file}", __NAMESPACE__ . '\network_plugin_action_links' );
	add_filter( 'removable_query_args',               __NAMESPACE__ . '\filter_removable_query_args' );
	add_filter( 'pre_unschedule_event',               __NAMESPACE__ . '\maybe_clear_doing_cron' );
	add_filter( 'plugin_row_meta',                    __NAMESPACE__ . '\filter_plugin_row_meta', 10, 2 );

	add_action( 'load-tools_page_crontrol_admin_manage_page', __NAMESPACE__ . '\setup_manage_page' );

	add_filter( 'cron_schedules',        __NAMESPACE__ . '\filter_cron_schedules' );
	add_action( 'crontrol_cron_job',     __NAMESPACE__ . '\action_php_cron_event' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
	add_action( 'crontrol/tab-header',   __NAMESPACE__ . '\show_cron_status', 20 );
	add_action( 'activated_plugin',      __NAMESPACE__ . '\flush_status_cache', 10, 0 );
	add_action( 'deactivated_plugin',    __NAMESPACE__ . '\flush_status_cache', 10, 0 );
	add_action( 'switch_theme',          __NAMESPACE__ . '\flush_status_cache', 10, 0 );
}
