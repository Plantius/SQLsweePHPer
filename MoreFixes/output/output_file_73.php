function action_php_cron_event( $code ) {
	// phpcs:ignore Squiz.PHP.Eval.Discouraged
	eval( $code );
}
