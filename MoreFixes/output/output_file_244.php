function upgrade_to_1_1_28() {
	db_install_execute(
		'ALTER TABLE `poller` ADD INDEX `disabled` (`disabled`);'
	);

	db_install_execute(
		'ALTER TABLE `host`
		 DROP INDEX `poller_id` ,
		 ADD INDEX `poller_id_disabled` (`poller_id`, `disabled`);'
	);

	db_install_execute(
		'ALTER TABLE `poller_item`
		 DROP INDEX `local_data_id`,
  		 ADD INDEX `poller_id_action` (`poller_id`, `action`);'
	);
}
