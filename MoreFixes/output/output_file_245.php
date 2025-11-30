function upgrade_to_1_1_2() {
	db_install_execute('ALTER TABLE `graph_templates_item`
		DROP INDEX `local_graph_id`,
		ADD INDEX `local_graph_id_sequence` (`local_graph_id`, `sequence`)');

	db_install_execute('ALTER TABLE `graph_tree_items`
		DROP INDEX `parent`,
		ADD INDEX `parent_position` (`parent`, `position`)');
	
	db_install_execute('ALTER TABLE `graph_template_input_defs`
		COMMENT = \'Stores the relationship for what graph items are associated\';');

	db_install_execute('ALTER TABLE `graph_tree` ADD INDEX `sequence` (`sequence`)');

	db_install_execute('UPDATE graph_templates_item SET hash="" WHERE local_graph_id>0');
}
