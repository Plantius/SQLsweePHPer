function upgrade_to_0_8_7b() {
	/* add Task Item Id Index */
	db_install_execute("ALTER TABLE `graph_templates_item` ADD INDEX `task_item_id` ( `task_item_id` )");
	/* make CLI more responsive */
	db_install_execute("ALTER TABLE `data_input_data` ADD INDEX `t_value`(`t_value`)");
}
