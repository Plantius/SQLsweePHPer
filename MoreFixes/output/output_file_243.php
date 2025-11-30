function upgrade_to_1_1_26() {
	db_install_execute(
		'ALTER TABLE `host` ADD KEY `status` (`status`);'
	);

	db_install_execute(
		'ALTER TABLE `user_auth_cache` ADD KEY `last_update` (`last_update`);'
	);

	db_install_execute(
		'ALTER TABLE `poller_output_realtime` ADD KEY `time` (`time`);'
	);

	db_install_execute(
		'ALTER TABLE `poller_time` ADD KEY `poller_id_end_time` (`poller_id`, `end_time`);'
	);

	db_install_execute(
		'ALTER TABLE `poller_item` 
		 DROP KEY `rrd_next_step`,
		 ADD KEY `poller_id_rrd_next_step` (`poller_id`, `rrd_next_step`);'
	);
}
