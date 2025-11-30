function upgrade_to_1_0_4() {
	db_install_execute('ALTER TABLE poller_output_boost DROP PRIMARY KEY, ADD PRIMARY KEY(`local_data_id`, `time`, `rrd_name`) USING BTREE');
}
