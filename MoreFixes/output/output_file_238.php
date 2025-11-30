function upgrade_to_0_8_2() {
	db_install_execute("ALTER TABLE `data_input_data_cache` ADD `host_id` MEDIUMINT( 8 ) NOT NULL AFTER `local_data_id`;");
	db_install_execute("ALTER TABLE `host` ADD `disabled` CHAR( 2 ) , ADD `status` TINYINT( 2 ) NOT NULL;");
	db_install_execute("UPDATE host_snmp_cache set field_name='ifName' where field_name='ifAlias' and snmp_query_id=1;");
	db_install_execute("UPDATE snmp_query_graph_rrd_sv set text = REPLACE(text,'ifAlias','ifName') where (snmp_query_graph_id=1 or snmp_query_graph_id=13 or snmp_query_graph_id=14 or snmp_query_graph_id=16 or snmp_query_graph_id=9 or snmp_query_graph_id=2 or snmp_query_graph_id=3 or snmp_query_graph_id=4);");
	db_install_execute("UPDATE snmp_query_graph_sv set text = REPLACE(text,'ifAlias','ifName') where (snmp_query_graph_id=1 or snmp_query_graph_id=13 or snmp_query_graph_id=14 or snmp_query_graph_id=16 or snmp_query_graph_id=9 or snmp_query_graph_id=2 or snmp_query_graph_id=3 or snmp_query_graph_id=4);");
	db_install_execute("UPDATE host set disabled = '';");
}
