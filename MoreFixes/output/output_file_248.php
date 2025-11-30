function upgrade_to_1_1_6() {
	db_install_execute('ALTER TABLE `data_input` 
		MODIFY COLUMN `input_string` varchar(512) default NULL'
	);

	if (!db_index_exists('data_input', 'name_type_id')) {
		db_install_execute('ALTER TABLE `data_input` 
			ADD KEY `name_type_id` (`name`(171), `type_id`)'
		);
	}

	if (!db_index_exists('snmp_query_graph', 'graph_template_id_name')) {
		db_install_execute("ALTER TABLE `snmp_query_graph` 
			ADD KEY `graph_template_id_name` (`graph_template_id`, `name`)"
		);
	}

	if (!db_index_exists('graph_templates', 'multiple_name')) {
		db_install_execute(
			"ALTER TABLE `graph_templates` 
				ADD `multiple` CHAR(2) NOT NULL DEFAULT '' AFTER `name`,
				ADD KEY `multiple_name` (`multiple`, `name`(171))"
		);
	}

	db_install_execute("UPDATE graph_templates 
		SET multiple = 'on' 
		WHERE hash = '010b90500e1fc6a05abfd542940584d0'"
	);

	db_install_execute("ALTER TABLE poller_output
		MODIFY COLUMN output VARCHAR(512) NOT NULL default '',
		ENGINE=MEMORY"
	);

	if (!db_index_exists('graph_templates_gprint', 'name')) {
		db_install_execute("ALTER TABLE `graph_templates_gprint` 
			ADD KEY `name` (`name`)"
		);
	}

	if (!db_index_exists('data_source_profiles', 'name')) {
		db_install_execute("ALTER TABLE `data_source_profiles` 
			ADD KEY `name` (`name`(171))"
		);
	}

	if (!db_index_exists('cdef', 'name')) {
		db_install_execute("ALTER TABLE `cdef`
			ADD KEY `name` (`name`(171))"
		);
	}

	if (!db_index_exists('vdef', 'name')) {
		db_install_execute("ALTER TABLE `vdef`
			ADD KEY `name` (`name`(171))"
		);
	}

	if (!db_index_exists('poller', 'name')) {
		db_install_execute("ALTER TABLE `poller`
			ADD KEY `name` (`name`)"
		);
	}

	if (!db_index_exists('host_template', 'name')) {
		db_install_execute("ALTER TABLE `host_template`
			ADD KEY `name` (`name`)"
		);
	}

	if (!db_index_exists('data_template', 'name')) {
		db_install_execute("ALTER TABLE `data_template`
			ADD KEY `name` (`name`)"
		);
	}

	if (!db_index_exists('automation_tree_rules', 'name')) {
		db_install_execute("ALTER TABLE `automation_tree_rules`
			ADD KEY `name` (`name`(171))"
		);
	}

	if (!db_index_exists('automation_graph_rules', 'name')) {
		db_install_execute("ALTER TABLE `automation_graph_rules`
			ADD KEY `name` (`name`(171))"
		);
	}

	if (!db_index_exists('graph_templates', 'name')) {
		db_install_execute("ALTER TABLE `graph_templates`
			ADD KEY `name` (`name`(171))"
		);
	}

	if (!db_index_exists('graph_tree', 'name')) {
		db_install_execute("ALTER TABLE `graph_tree`
			ADD KEY `name` (`name`(171))"
		);
	}

	if (!db_index_exists('snmp_query_graph', 'snmp_query_id_name')) {
		db_install_execute("ALTER TABLE `snmp_query_graph`
			ADD KEY `snmp_query_id_name` (`snmp_query_id`, `name`)"
		);
	}

	db_install_execute("REPLACE INTO settings (name, value) VALUES ('max_display_rows', '1000')");
}
