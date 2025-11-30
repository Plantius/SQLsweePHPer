		db_install_execute("replace into settings_graphs (user_id,name,value) values (" . $item["id"] . ",'default_tree_view_mode',1);");
	}
	}

	/* drop unused tables */
	db_install_execute("DROP TABLE `user_auth_graph`;");
	db_install_execute("DROP TABLE `user_auth_tree`;");
	db_install_execute("DROP TABLE `user_auth_hosts`;");

	/* bug#72 */
	db_install_execute("UPDATE graph_templates_item set cdef_id=15 where id=25;");
	db_install_execute("UPDATE graph_templates_item set cdef_id=15 where id=26;");
	db_install_execute("UPDATE graph_templates_item set cdef_id=15 where id=27;");
	db_install_execute("UPDATE graph_templates_item set cdef_id=15 where id=28;");

	push_out_graph_item(25);
	push_out_graph_item(26);
	push_out_graph_item(27);
	push_out_graph_item(28);

	/* too many people had problems with the poller cache in 0.8.2a... */
	db_install_execute("DROP TABLE `data_input_data_cache`");
	db_install_execute("CREATE TABLE `data_input_data_cache` (
		`local_data_id` mediumint(8) unsigned NOT NULL default '0',
		`host_id` mediumint(8) NOT NULL default '0',
		`data_input_id` mediumint(8) unsigned NOT NULL default '0',
		`action` tinyint(2) NOT NULL default '1',
		`command` varchar(255) NOT NULL default '',
		`management_ip` varchar(15) NOT NULL default '',
		`snmp_community` varchar(100) NOT NULL default '',
		`snmp_version` tinyint(1) NOT NULL default '0',
		`snmp_username` varchar(50) NOT NULL default '',
		`snmp_password` varchar(50) NOT NULL default '',
		`rrd_name` varchar(19) NOT NULL default '',
		`rrd_path` varchar(255) NOT NULL default '',
		`rrd_num` tinyint(2) unsigned NOT NULL default '0',
		`arg1` varchar(255) default NULL,
		`arg2` varchar(255) default NULL,
		`arg3` varchar(255) default NULL,
		PRIMARY KEY  (`local_data_id`,`rrd_name`),
		KEY `local_data_id` (`local_data_id`)
		) TYPE=MyISAM;");
}
