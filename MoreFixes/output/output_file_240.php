function upgrade_to_0_8_7a() {
	/* add alpha channel to graph items */
	db_install_execute("ALTER TABLE `graph_templates_item` ADD COLUMN `alpha` CHAR(2) DEFAULT 'FF' AFTER `color_id`;");
	/* add units=si as an option */
	db_install_execute("ALTER TABLE `graph_templates_graph` ADD COLUMN `t_scale_log_units` CHAR(2) DEFAULT 0 AFTER `auto_scale_log`, ADD COLUMN `scale_log_units` CHAR(2) DEFAULT '' AFTER `t_scale_log_units`;");
}
