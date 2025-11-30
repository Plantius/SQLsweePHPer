function upgrade_to_0_8_2a() {
	db_install_execute("ALTER TABLE `data_input_data_cache` ADD `rrd_num` TINYINT( 2 ) UNSIGNED NOT NULL AFTER `rrd_path`;");
}
